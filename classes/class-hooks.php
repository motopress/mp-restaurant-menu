<?php

namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\models\Cart;
use mp_restaurant_menu\classes\models\Errors;
use mp_restaurant_menu\classes\models\Order;
use mp_restaurant_menu\classes\models\Settings_emails;
use mp_restaurant_menu\classes\modules\Post;
use mp_restaurant_menu\classes\modules\MPRM_Widget;
use mp_restaurant_menu\classes\models\Menu_category;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Cart;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Category;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Checkout;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Item;

class Hooks extends Core {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init all hooks in projects
	 */
	public static function install_hooks() {
		add_action('init', array(self::get_instance(), "init"));
		add_action("admin_init", array(self::get_instance(), "admin_init"));
		add_action('admin_menu', array(Media::get_instance(), 'admin_menu'));
		// in load theme
		add_action('wp_head', array(Media::get_instance(), 'wp_head'));
		// Add script for footer theme
		add_action('wp_footer', array(Media::get_instance(), 'wp_footer'));
		add_action('export_wp', array(Export::get_instance(), 'export_wp'));
		// widgets init
		add_action('widgets_init', array(MPRM_Widget::get_instance(), 'register'));
	}

	/**
	 * Hooks for admin panel
	 */
	public function admin_init() {
		// load languages
		$this->load_language();


		// install metaboxes
		$this->get('menu_item')->init_metaboxes();
		add_action('add_meta_boxes', array(Post::get_instance(), 'add_meta_boxes'));
		add_action('save_post', array(Post::get_instance(), 'save'));
		add_action('edit_form_after_title', array(Post::get_instance(), "edit_form_after_title"));

		// List posts
		add_filter("manage_{$this->get_post_type('menu_item')}_posts_columns", array(Post::get_instance(), "init_menu_columns"), 10);
		add_action("manage_{$this->get_post_type('menu_item')}_posts_custom_column", array(Post::get_instance(), "show_menu_columns"), 10, 2);

		// Disable Auto Save
		add_action('admin_print_scripts', array(Media::get_instance(), 'disable_autosave'));

		// Manage and sortable order
		add_filter("manage_{$this->get_post_type('order')}_posts_columns", array(Order::get_instance(), "order_columns"), 10);
		add_action("manage_{$this->get_post_type('order')}_posts_custom_column", array(Order::get_instance(), "render_order_columns"), 10, 2);
		add_action("manage_edit-{$this->get_post_type('order')}_sortable_columns", array(Order::get_instance(), "order_sortable_columns"), 10, 2);

		// ajax redirect
		add_action('wp_ajax_route_url', array(Core::get_instance(), "wp_ajax_route_url"));
		//mce editor plugins
		add_filter("mce_external_plugins", array(Media::get_instance(), "mce_external_plugins"));
		add_filter('mce_buttons', array(Media::get_instance(), "mce_buttons"));

		// add edit mp_menu_category colums
		$category_name = $this->get_tax_name('menu_category');
		add_action("{$category_name}_add_form_fields", array(Menu_category::get_instance(), 'add_form_fields'));
		add_action("{$category_name}_edit_form_fields", array(Menu_category::get_instance(), 'edit_form_fields'));

		// save mp_menu_category
		add_action("edited_{$category_name}", array(Menu_category::get_instance(), 'save_menu_category'));
		add_action("create_{$category_name}", array(Menu_category::get_instance(), 'save_menu_category'));

		// load current admin screen
		add_action('current_screen', array(Media::get_instance(), 'current_screen'));
		//add media in admin WP
		add_action('admin_enqueue_scripts', array(Media::get_instance(), "admin_enqueue_scripts"));
		register_importer('mprm-importer', 'Restaurant menu', __('Import menu item with categories data', 'mp-restaurant-menu'), array(Import::get_instance(), 'import'));
		//Emails
		add_action('mprm_email_settings', array(Settings_emails::get_instance(), 'email_template_preview'));

		add_action('admin_notices', array($this, 'admin_notices_action'));
	}

	/**
	 * Init hook
	 */
	public function init() {
		//Check if Theme Supports Post Thumbnails
		if (!current_theme_supports('post-thumbnails')) {
			add_theme_support('post-thumbnails');
		}
		// Register attachment sizes
		$this->get('image')->add_image_sizes();
		// Image downsize
		add_action('image_downsize', array($this->get('image'), 'image_downsize'), 10, 3);
		// Register custom post type and taxonomies
		Media::get_instance()->register_all_post_type();
		Media::get_instance()->register_all_taxonomies();
		// Include template
		add_filter('template_include', array(Media::get_instance(), 'template_include'));
		// post_class filter
		add_filter('post_class', 'mprm_post_class', 20, 3);
		// Route url
		Core::get_instance()->wp_ajax_route_url();
		// Shortcodes
		add_shortcode('mprm_categories', array(Shortcode_Category::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_items', array(Shortcode_Item::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_cart', array(Shortcode_Cart::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_checkout', array(Shortcode_Checkout::get_instance(), 'render_shortcode'));

		// Integrate in motopress
		add_action('mp_library', array(Shortcode_Category::get_instance(), 'integration_motopress'), 10, 1);
		add_action('mp_library', array(Shortcode_Item::get_instance(), 'integration_motopress'), 10, 1);

		add_action('mprm_purchase_form_before_submit', array(Errors::get_instance(), 'print_errors'));
		add_action('mprm_ajax_checkout_errors', array(Errors::get_instance(), 'print_errors'));


	}

	/**
	 * Install templates actions
	 */
	public static function install_templates_actions() {
		self::install_menu_item_grid_actions();
		self::install_menu_item_list_actions();
		self::install_menu_items_actions();
		self::install_category_grid_actions();
		self::install_category_list_actions();
		self::install_menu_item_actions();
		self::install_category_actions();
		self::install_tag_actions();
	}

	/**
	 * Install tag actions
	 */
	public static function install_tag_actions() {
		/**
		 * Before Menu_item list
		 *
		 * @see mprm_before_tag_list()
		 */
		add_action('mprm_before_tag_list', 'mprm_before_tag_list', 10);

		/**
		 * Menu_item list
		 *
		 * @see mprm_single_tag_list_header()
		 * @see mprm_single_tag_list_content()
		 * @see mprm_single_tag_list_footer()
		 */
		add_action('mprm_tag_list', 'mprm_single_tag_list_header', 5);
		add_action('mprm_tag_list', 'mprm_single_tag_list_content', 10);
		add_action('mprm_tag_list', 'mprm_single_tag_list_footer', 20);

		/**
		 * After Menu_item list
		 *
		 * @see mprm_after_tag_list
		 */
		add_action('mprm_after_tag_list', 'mprm_after_tag_list', 10);
	}

	/**
	 * Install category actions
	 */
	public static function install_category_actions() {

		add_action('mprm-single-category-before-wrapper', 'mprm_theme_wrapper_before');
		add_action('mprm-single-category-after-wrapper', 'mprm_theme_wrapper_after');

		/**
		 * Before Menu_item list
		 *
		 * @see mprm_before_category_list()
		 */
		add_action('mprm_taxonomy_category_list', 'mprm_before_taxonomy_list', 10);
		/**
		 * Menu_item list
		 *
		 * @see mprm_single_category_list_header()
		 * @see mprm_single_category_list_title()
		 * @see mprm_single_category_list_ingredients()
		 * @see mprm_single_category_list_footer()
		 */
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_before_left', 10);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_image', 15);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_after_left', 20);

		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_before_right', 25);

		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_header_title', 30);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_ingredients', 35);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_tags', 40);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_price', 45);

		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_after_right', 50);

		/**
		 * After Menu_item list
		 *
		 * @see mprm_after_category_list
		 */
		add_action('mprm_taxonomy_after_list', 'mprm_after_taxonomy_list', 10);
		/**
		 * Before Menu_item grid
		 *
		 * @see mprm_before_taxonomy_grid()
		 */
		add_action('mprm_before_taxonomy_grid', 'mprm_before_taxonomy_grid', 10);

		/**
		 * Menu_item grid
		 *
		 * @see mprm_single_category_grid_header()
		 * @see mprm_single_category_grid_image()
		 * @see mprm_single_category_grid_description()
		 * @see mprm_single_category_grid_footer()
		 */
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_header', 20);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_image', 25);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_wrapper_start', 35);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_title', 40);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_ingredients', 45);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_tags', 50);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_price', 55);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_wrapper_end', 60);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_footer', 65);

		/**
		 * After Menu_item grid
		 *
		 * @see mprm_after_taxonomy_grid
		 */
		add_action('mprm_after_taxonomy_grid', 'mprm_after_taxonomy_grid', 10);
		/**
		 * Before Menu_item header
		 *
		 * @see mprm_before_category_header()
		 */
		add_action('mprm_before_category_header', 'mprm_before_category_header', 10);

		/**
		 * Menu_item header
		 *
		 * @see mprm_category_header()
		 */
		add_action('mprm_category_header', 'mprm_category_header', 5);

		/**
		 * After Menu_item header
		 *
		 * @see mprm_after_category_header
		 */
		add_action('mprm_after_category_header', 'mprm_after_category_header', 10);
	}

	/**
	 * Install menu item actions
	 */
	public static function install_menu_item_actions() {
		/**
		 * output Wordpress standard them  wrapper
		 */
		add_action('mprm-before-main-wrapper', 'mprm_theme_wrapper_before');
		add_action('mprm-after-main-wrapper', 'mprm_theme_wrapper_after');

		/**
		 * Before Menu_item header
		 *
		 * @see mprm_before_menu_item_header()
		 */
		add_action('mprm_before_menu_item_header', 'mprm_before_menu_item_header', 10);

		/**
		 * Menu_item header
		 *
		 * @see mprm_menu_item_header()
		 */
		add_action('mprm_menu_item_header', 'mprm_menu_item_header', 5);

		/**
		 * After Menu_item header
		 *
		 * @see mprm_after_menu_item_header
		 */
		add_action('mprm_after_menu_item_header', 'mprm_after_menu_item_header', 10);

		/**
		 * Before Menu_item gallery
		 *
		 * @see mprm_before_menu_item_gallery()
		 */
		add_action('mprm_before_menu_item_gallery', 'mprm_before_menu_item_gallery', 10);

		/**
		 * Menu item gallery
		 *
		 * @see mprm_menu_item_gallery()
		 */
		add_action('mprm_menu_item_gallery', 'mprm_menu_item_gallery', 10);

		/**
		 * After Menu_item gallery
		 *
		 * @see mprm_after_menu_item_gallery
		 */
		add_action('mprm_after_menu_item_gallery', 'mprm_after_menu_item_gallery', 10);

		/**
		 * Menu item content
		 *
		 * @see mprm_menu_item_content()
		 * @see mprm_menu_item_content_author()
		 * @see mprm_menu_item_content_comments()
		 */
		//add_action('mprm_menu_item_content', 'mprm_menu_item_price', 5);
		add_action('mprm_menu_item_content', 'mprm_menu_item_content', 10);
		add_action('mprm_menu_item_content', 'mprm_menu_item_content_author', 20);
		add_action('mprm_menu_item_content', 'mprm_menu_item_content_comments', 30);

		/**
		 * Before Menu_item slidebar
		 *
		 * @see mprm_before_menu_item_sidebar()
		 */
		add_action('mprm_before_menu_item_sidebar', 'mprm_before_menu_item_sidebar', 10);

		/**
		 * Menu item slidebar
		 *
		 * @see mprm_menu_item_price()
		 * @see mprm_menu_item_slidebar_attributes()
		 * @see mprm_menu_item_slidebar_ingredients()
		 * @see mprm_menu_item_slidebar_nutritional()
		 * @see mprm_menu_item_slidebar_related_items()
		 */
		add_action('mprm_menu_item_slidebar', 'mprm_menu_item_price', 5);
		add_action('mprm_menu_item_slidebar', 'mprm_get_purchase_template', 10);
		add_action('mprm_menu_item_slidebar', 'mprm_purchase_link', 15);
		add_action('mprm_menu_item_slidebar', 'mprm_menu_item_slidebar_attributes', 20);
		add_action('mprm_menu_item_slidebar', 'mprm_menu_item_slidebar_ingredients', 25);
		add_action('mprm_menu_item_slidebar', 'mprm_menu_item_slidebar_nutritional', 30);
		add_action('mprm_menu_item_slidebar', 'mprm_menu_item_slidebar_related_items', 40);

		/**
		 * After Menu_item gallery
		 *
		 * @see mprm_after_menu_item_sidebar
		 */
		add_action('mprm_after_menu_item_sidebar', 'mprm_after_menu_item_sidebar', 10);
	}

	/**
	 * Install menu_items actions
	 */
	public static function install_menu_items_actions() {
		/**
		 * Before menu_items header
		 *
		 * @see mprm_before_menu_items_header()
		 */
		add_action('mprm_before_menu_items_header', 'mprm_before_menu_items_header', 10);
		/**
		 * Menu_items header
		 *
		 * @see mprm_menu_items_header()
		 */
		add_action('mprm_menu_items_header', 'mprm_menu_items_header', 5);

		/**
		 * After Menu_items header
		 *
		 * @see mprm_after_menu_items_header
		 */
		add_action('mprm_after_menu_items_header', 'mprm_after_menu_items_header', 10);
	}

	/**
	 * Install category grid actions
	 */
	public static function install_category_grid_actions() {
		/**
		 * Before Category grid
		 *
		 * @see mprm_before_taxonomy_grid_header()
		 * @see mprm_before_taxonomy_grid_footer()
		 */
		add_action('mprm_before_shortcode_category_grid', 'mprm_before_taxonomy_grid_header', 10);
		add_action('mprm_before_shortcode_category_grid', 'mprm_before_taxonomy_grid_footer', 20);

		/**
		 * Category grid
		 *
		 * @see mprm_taxonomy_grid_header()
		 * @see mprm_taxonomy_grid_title()
		 * @see mprm_taxonomy_grid_description()
		 * @see mprm_taxonomy_grid_footer()
		 */
		add_action('mprm_shortcode_category_grid', 'mprm_shortcode_grid_item', 10);
		/*add_action('mprm_shortcode_category_grid', 'mprm_taxonomy_grid_header', 5);
		add_action('mprm_shortcode_category_grid', 'mprm_taxonomy_grid_title', 10);
		add_action('mprm_shortcode_category_grid', 'mprm_taxonomy_grid_description', 20);
		add_action('mprm_shortcode_category_grid', 'mprm_taxonomy_grid_footer', 30);*/

		/**
		 * After Category grid
		 *
		 * @see mprm_after_taxonomy_grid_header
		 * @see mprm_after_taxonomy_grid_footer
		 */
		add_action('mprm_after_shortcode_category_grid', 'mprm_after_taxonomy_grid_header', 10);
		add_action('mprm_after_shortcode_category_grid', 'mprm_after_taxonomy_grid_footer', 20);

		/**
		 * Before Category grid
		 *
		 * @see mprm_before_taxonomy_grid_header()
		 * @see mprm_before_taxonomy_grid_footer()
		 */
		add_action('mprm_before_widget_category_grid', 'mprm_before_taxonomy_grid_header', 10);
		add_action('mprm_before_widget_category_grid', 'mprm_before_taxonomy_grid_footer', 20);

		/**
		 * Category grid
		 *
		 * @see mprm_taxonomy_grid_header()
		 * @see mprm_taxonomy_grid_title()
		 * @see mprm_taxonomy_grid_description()
		 * @see mprm_taxonomy_grid_footer()
		 */
		/*add_action('mprm_widget_category_grid', 'mprm_taxonomy_grid_header', 5);
		add_action('mprm_widget_category_grid', 'mprm_taxonomy_grid_title', 10);
		add_action('mprm_widget_category_grid', 'mprm_taxonomy_grid_description', 20);
		add_action('mprm_widget_category_grid', 'mprm_taxonomy_grid_footer', 30);*/

		/**
		 * After Category grid
		 *
		 * @see mprm_after_taxonomy_grid_header
		 * @see mprm_after_taxonomy_grid_footer
		 */
		add_action('mprm_after_widget_category_grid', 'mprm_after_taxonomy_grid_header', 10);
		add_action('mprm_after_widget_category_grid', 'mprm_after_taxonomy_grid_footer', 20);
	}

	/**
	 * Install category list actions
	 */
	public static function install_category_list_actions() {
		/**
		 * Before Category list
		 *
		 * @see mprm_before_category_list_header()
		 * @see mprm_before_category_list_footer()
		 */
		add_action('mprm_before_shortcode_category_list', 'mprm_before_category_list_header', 10);
		add_action('mprm_before_shortcode_category_list', 'mprm_before_category_list_footer', 20);

		/**
		 * Category list
		 *
		 * @see mprm_category_list_header()
		 * @see mprm_category_list_title()
		 * @see mprm_category_list_description()
		 * @see mprm_category_list_footer()
		 */
		add_action('mprm_shortcode_category_list', 'mprm_category_list_item', 10);
		/*add_action('mprm_shortcode_category_list', 'mprm_category_list_title', 10);
		add_action('mprm_shortcode_category_list', 'mprm_category_list_description', 20);
		add_action('mprm_shortcode_category_list', 'mprm_category_list_footer', 30);*/

		/**
		 * After Category list
		 *
		 * @see mprm_after_category_list_header
		 * @see mprm_after_category_list_footer
		 */
		add_action('mprm_after_shortcode_category_list', 'mprm_after_category_list_header', 10);
		add_action('mprm_after_shortcode_category_list', 'mprm_after_category_list_footer', 20);

		/**
		 * Before Category list
		 *
		 * @see mprm_before_category_list_header()
		 * @see mprm_before_category_list_footer()
		 */
		add_action('mprm_before_widget_category_list', 'mprm_before_category_list_header', 10);
		add_action('mprm_before_widget_category_list', 'mprm_before_category_list_footer', 20);

		/**
		 * Category list
		 *
		 * @see mprm_category_list_header()
		 * @see mprm_category_list_title()
		 * @see mprm_category_list_description()
		 * @see mprm_category_list_footer()
		 */
		add_action('mprm_widget_category_list', 'mprm_category_list_item', 10);
		/*add_action('mprm_widget_category_list', 'mprm_category_list_title', 10);
		add_action('mprm_widget_category_list', 'mprm_category_list_description', 20);
		add_action('mprm_widget_category_list', 'mprm_category_list_footer', 30);*/

		/**
		 * After Category list
		 *
		 * @see mprm_after_category_list_header
		 * @see mprm_after_category_list_footer
		 */
		add_action('mprm_after_widget_category_list', 'mprm_after_category_list_header', 10);
		add_action('mprm_after_widget_category_list', 'mprm_after_category_list_footer', 20);
	}

	/**
	 * Install menu item list actions
	 */
	public static function install_menu_item_list_actions() {
		/**
		 * Before Menu item list
		 *
		 * @see mprm_before_menu_item_list_header()
		 * @see mprm_before_menu_item_list_footer()
		 */
		add_action('mprm_before_shortcode_menu_item_list', 'mprm_before_menu_item_list_header', 10);
		add_action('mprm_before_shortcode_menu_item_list', 'mprm_before_menu_item_list_footer', 20);

		/**
		 * Menu item list
		 *
		 * @see mprm_menu_item_list_header()
		 * @see mprm_menu_item_list_image()
		 * @see mprm_menu_item_list_tags()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_excerpt()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_attributes()
		 * @see mprm_menu_item_list_excerpt()
		 */
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_header', 5);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_image', 10);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_right_header', 15);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_title', 20);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_ingredients', 25);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_attributes', 30);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_excerpt', 35);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_tags', 40);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_price', 45);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_right_footer', 50);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_footer', 55);

		/**
		 * After Menu item list
		 *
		 * @see mprm_after_menu_item_list_header
		 * @see mprm_after_menu_item_list_footer
		 */
		add_action('mprm_after_shortcode_menu_item_list', 'mprm_after_menu_item_list_header', 10);
		add_action('mprm_after_shortcode_menu_item_list', 'mprm_after_menu_item_list_footer', 20);
		/**
		 * Before Menu item list
		 *
		 * @see mprm_before_menu_item_list_header()
		 * @see mprm_before_menu_item_list_footer()
		 */
		add_action('mprm_before_widget_menu_item_list', 'mprm_before_menu_item_list_header', 10);
		add_action('mprm_before_widget_menu_item_list', 'mprm_before_menu_item_list_footer', 20);

		/**
		 * Menu item list
		 *
		 * @see mprm_menu_item_list_header()
		 * @see mprm_menu_item_list_image()
		 * @see mprm_menu_item_list_tags()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_excerpt()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_excerpt()
		 */
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_header', 5);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_image', 10);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_right_header', 20);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_title', 30);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_ingredients', 40);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_attributes', 50);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_excerpt', 60);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_tags', 70);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_price', 80);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_right_footer', 90);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_footer', 95);

		/**
		 * After Menu item list
		 *
		 * @see mprm_after_menu_item_list_header
		 * @see mprm_after_menu_item_list_footer
		 */
		add_action('mprm_after_widget_menu_item_list', 'mprm_after_menu_item_list_header', 10);
		add_action('mprm_after_widget_menu_item_list', 'mprm_after_menu_item_list_footer', 20);
	}

	/**
	 * Install menu item grid actions
	 */
	public static function install_menu_item_grid_actions() {
		/**
		 * Before Menu item grid
		 *
		 * @see mprm_before_menu_item_grid_header()
		 * @see mprm_before_menu_item_grid_footer()
		 */
		add_action('mprm_before_shortcode_menu_item_grid', 'mprm_before_menu_item_grid_header', 10);
		add_action('mprm_before_shortcode_menu_item_grid', 'mprm_before_menu_item_grid_footer', 20);

		/**
		 * Menu item grid
		 *
		 * @see mprm_menu_item_grid_header()
		 * @see mprm_menu_item_grid_image()
		 * @see mprm_menu_item_grid_tags()
		 * @see mprm_menu_item_grid_ingredients()
		 * @see mprm_menu_item_grid_attributes()
		 * @see mprm_menu_item_grid_excerpt()
		 * @see mprm_menu_item_grid_price()
		 * @see mprm_menu_item_grid_footer()
		 */
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_header', 10);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_image', 20);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_title', 30);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_ingredients', 40);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_attributes', 50);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_excerpt', 60);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_tags', 70);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_price', 80);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_footer', 90);

		/**
		 * After Menu item grid
		 *
		 * @see mprm_after_menu_item_grid_header
		 * @see mprm_after_menu_item_grid_footer
		 */
		add_action('mprm_after_shortcode_menu_item_grid', 'mprm_after_menu_item_grid_header', 10);
		add_action('mprm_after_shortcode_menu_item_grid', 'mprm_after_menu_item_grid_footer', 20);
		/**
		 * Before Menu item grid
		 *
		 * @see mprm_before_menu_item_grid_header()
		 * @see mprm_before_menu_item_grid_footer()
		 */
		add_action('mprm_before_widget_menu_item_grid', 'mprm_before_menu_item_grid_header', 10);
		add_action('mprm_before_widget_menu_item_grid', 'mprm_before_menu_item_grid_footer', 20);

		/**
		 * Menu item grid
		 *
		 * @see mprm_menu_item_grid_header()
		 * @see mprm_menu_item_grid_image()
		 * @see mprm_menu_item_grid_tags()
		 * @see mprm_menu_item_grid_ingredients()
		 * @see mprm_menu_item_grid_excerpt()
		 * @see mprm_menu_item_grid_ingredients()
		 * @see mprm_menu_item_grid_excerpt()
		 */
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_header', 10);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_image', 20);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_title', 30);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_ingredients', 40);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_attributes', 50);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_excerpt', 60);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_tags', 70);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_price', 80);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_footer', 90);

		/**
		 * After Menu item grid
		 *
		 * @see mprm_after_menu_item_grid_header
		 * @see mprm_after_menu_item_grid_footer
		 */
		add_action('mprm_after_widget_menu_item_grid', 'mprm_after_menu_item_grid_header', 10);
		add_action('mprm_after_widget_menu_item_grid', 'mprm_after_menu_item_grid_footer', 20);
	}

	public function admin_notices_action() {
		settings_errors('mprm-notices');
	}
}
