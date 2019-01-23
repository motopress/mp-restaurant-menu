<?php
namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\models\Cart;
use mp_restaurant_menu\classes\models\Emails;
use mp_restaurant_menu\classes\models\Extension;
use mp_restaurant_menu\classes\models\Manual_payment;
use mp_restaurant_menu\classes\models\Menu_category;
use mp_restaurant_menu\classes\models\Order;
use mp_restaurant_menu\classes\models\Payments;
use mp_restaurant_menu\classes\models\Paypal;
use mp_restaurant_menu\classes\models\Paypal_standart;
use mp_restaurant_menu\classes\models\Purchase;
use mp_restaurant_menu\classes\models\Settings_emails;
use mp_restaurant_menu\classes\models\Test_Manual_payment;
use mp_restaurant_menu\classes\modules\MPRM_Widget;
use mp_restaurant_menu\classes\modules\Post;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Cart;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Category;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Checkout;
use mp_restaurant_menu\classes\shortcodes\Shortcode_history;
use mp_restaurant_menu\classes\shortcodes\Shortcode_Item;
use mp_restaurant_menu\classes\shortcodes\Shortcode_success;

/**
 * Class Hooks
 * @package mp_restaurant_menu\classes
 */
class Hooks extends Core {
	protected static $instance;
	
	/**
	 * Init all hooks in projects
	 */
	public static function install_hooks() {
		add_action('init', array(self::get_instance(), 'init'), 0);
		add_action('admin_init', array(self::get_instance(), 'admin_init'));
		add_action('admin_menu', array(Media::get_instance(), 'admin_menu'));
		// in load theme
		add_action('wp_enqueue_scripts', array(Media::get_instance(), 'enqueue_scripts'));
		// Add script for footer theme
		add_action('wp_footer', array(Media::get_instance(), 'wp_footer'));
		add_action('export_wp', array(Export::get_instance(), 'export_wp'));
		// widgets init
		add_action('widgets_init', array(MPRM_Widget::get_instance(), 'register'));
	}
	
	/**
	 * @return Hooks
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Install templates actions
	 */
	public static function install_templates_actions() {
		Extension::get_instance()->init_action();
		
		self::install_menu_item_grid_actions();
		self::install_menu_item_list_actions();
		self::install_menu_item_simple_list_actions();
		self::install_menu_items_actions();
		self::install_category_grid_actions();
		self::install_category_list_actions();
		self::install_menu_item_actions();
		self::install_category_actions();
		self::install_tag_actions();
		self::install_cart_actions();
		self::install_checkout_actions();
		
		Test_Manual_payment::get_instance()->init_action();
		Manual_payment::get_instance()->init_action();
		Paypal_standart::get_instance()->init_action();
		Payments::get_instance()->init_action();
		Emails::get_instance()->init_action();
		Purchase::get_instance()->init_action();
	}
	
	/**
	 * Install menu item grid actions
	 */
	public static function install_menu_item_grid_actions() {
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
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_before_content', 15);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_image', 20);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_title', 30);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_ingredients', 40);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_attributes', 50);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_excerpt', 60);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_tags', 70);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_price', 75);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_after_content', 80);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_get_purchase_template', 85);
		add_action('mprm_shortcode_menu_item_grid', 'mprm_menu_item_grid_footer', 90);
		
		
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_header', 10);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_before_content', 15);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_image', 20);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_title', 30);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_ingredients', 40);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_attributes', 50);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_excerpt', 60);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_tags', 70);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_price', 75);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_after_content', 80);
		add_action('mprm_page_template_menu_item_grid', 'mprm_get_purchase_template', 85);
		add_action('mprm_page_template_menu_item_grid', 'mprm_menu_item_grid_footer', 90);
		
		/**
		 * Menu widget item grid
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
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_before_content', 15);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_image', 20);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_title', 30);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_ingredients', 40);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_attributes', 50);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_excerpt', 60);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_tags', 70);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_price', 80);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_after_content', 85);
		add_action('mprm_widget_menu_item_grid', 'mprm_get_purchase_template', 90);
		add_action('mprm_widget_menu_item_grid', 'mprm_menu_item_grid_footer', 95);
	}
	
	/**
	 * Install menu item list actions
	 */
	public static function install_menu_item_list_actions() {
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
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_before_content', 10);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_image', 15);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_right_header', 20);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_title', 25);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_ingredients', 30);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_attributes', 35);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_excerpt', 40);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_tags', 45);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_price', 50);
		add_action('mprm_shortcode_menu_item_list', 'mprm_get_purchase_template', 55);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_right_footer', 60);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_after_content', 65);
		add_action('mprm_shortcode_menu_item_list', 'mprm_menu_item_list_footer', 70);
		/**
		 * Menu widget item list
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
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_before_content', 10);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_image', 15);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_right_header', 20);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_title', 30);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_ingredients', 35);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_attributes', 40);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_excerpt', 50);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_tags', 60);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_price', 70);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_after_content', 75);
		add_action('mprm_widget_menu_item_list', 'mprm_get_purchase_template', 80);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_right_footer', 85);
		add_action('mprm_widget_menu_item_list', 'mprm_menu_item_list_footer', 95);
		
		/**
		 * Menu page template item list
		 *
		 * @see mprm_menu_item_list_header()
		 * @see mprm_menu_item_list_image()
		 * @see mprm_menu_item_list_tags()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_excerpt()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_excerpt()
		 */
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_header', 5);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_before_content', 10);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_image', 15);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_right_header', 20);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_title', 30);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_ingredients', 35);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_attributes', 40);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_excerpt', 50);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_tags', 60);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_price', 70);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_after_content', 75);
		add_action('mprm_page_template_menu_item_list', 'mprm_get_purchase_template', 80);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_right_footer', 85);
		add_action('mprm_page_template_menu_item_list', 'mprm_menu_item_list_footer', 95);
	}
	
	public static function install_menu_item_simple_list_actions() {
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
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_before_content', 5);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_simple_list_header', 10);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_list_title_simple', 20);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_list_ingredients', 25);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_list_attributes', 30);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_list_excerpt', 35);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_list_tags', 40);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_simple_list_footer', 50);
		add_action('mprm_shortcode_menu_item_simple-list', 'mprm_menu_item_after_content', 55);
		
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
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_before_content', 5);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_simple_list_header', 10);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_list_title_simple', 20);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_list_ingredients', 30);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_list_attributes', 40);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_list_excerpt', 50);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_list_tags', 60);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_simple_list_footer', 65);
		add_action('mprm_widget_menu_item_simple_list', 'mprm_menu_item_after_content', 70);
		/**
		 * Category page template item list
		 *
		 * @see mprm_menu_item_list_header()
		 * @see mprm_menu_item_list_image()
		 * @see mprm_menu_item_list_tags()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_excerpt()
		 * @see mprm_menu_item_list_ingredients()
		 * @see mprm_menu_item_list_excerpt()
		 */
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_before_content', 5);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_simple_list_header', 10);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_list_title_simple', 20);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_list_ingredients', 30);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_list_attributes', 40);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_list_excerpt', 50);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_list_tags', 60);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_simple_list_footer', 65);
		add_action('mprm_page_template_menu_item_simple-list', 'mprm_menu_item_after_content', 70);
	}
	
	/**
	 * Install menu_items actions
	 */
	public static function install_menu_items_actions() {
		/**
		 * Menu_items header
		 *
		 * @see mprm_menu_items_header()
		 */
		add_action('mprm_menu_items_header', 'mprm_menu_items_header', 5);
	}
	
	/**
	 * Install category grid actions
	 */
	public static function install_category_grid_actions() {
		/**
		 * Category grid
		 *
		 * @see mprm_taxonomy_grid_header()
		 * @see mprm_taxonomy_grid_title()
		 * @see mprm_taxonomy_grid_description()
		 * @see mprm_taxonomy_grid_footer()
		 */
		add_action('mprm_shortcode_category_grid', 'mprm_shortcode_grid_item', 10);
	}
	
	/**
	 * Install category list actions
	 */
	public static function install_category_list_actions() {
		
		/**
		 * Category list
		 *
		 * @see mprm_category_list_header()
		 * @see mprm_category_list_title()
		 * @see mprm_category_list_description()
		 * @see mprm_category_list_footer()
		 */
		add_action('mprm_shortcode_category_list', 'mprm_category_list_item', 10);
		
		/**
		 * Category list
		 *
		 * @see mprm_category_list_header()
		 * @see mprm_category_list_title()
		 * @see mprm_category_list_description()
		 * @see mprm_category_list_footer()
		 */
		add_action('mprm_widget_category_list', 'mprm_category_list_item', 10);
		
		/**
		 * After Category list
		 *
		 * @see mprm_after_category_list_header
		 * @see mprm_after_category_list_footer
		 */
		add_action('mprm_after_widget_category_list', 'mprm_after_category_list_header', 10);
	}
	
	/**
	 * Install menu item actions
	 */
	public static function install_menu_item_actions() {
		
		add_action('mprm_menu_item_single_theme_view', 'get_gallery_theme_view', 5);
		add_action('mprm_menu_item_single_theme_view', 'get_price_theme_view', 10);
		add_action('mprm_menu_item_single_theme_view', 'mprm_get_purchase_template', 15);
		add_action('mprm_menu_item_single_theme_view', 'get_ingredients_theme_view', 20);
		add_action('mprm_menu_item_single_theme_view', 'get_attributes_theme_view', 25);
		add_action('mprm_menu_item_single_theme_view', 'get_nutritional_theme_view', 30);
		add_action('mprm_menu_item_single_theme_view', 'get_related_items_theme_view', 35);
		
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
		 * Menu item gallery
		 *
		 * @see mprm_menu_item_gallery()
		 */
		add_action('mprm_menu_item_gallery', 'mprm_menu_item_gallery', 10);
		
		/**
		 * Menu item content
		 *
		 * @see mprm_menu_item_content()
		 * @see mprm_menu_item_content_author()
		 * @see mprm_menu_item_content_comments()
		 */
		add_action('mprm_menu_item_content', 'mprm_menu_item_content', 10);
		add_action('mprm_menu_item_content', 'mprm_menu_item_content_author', 20);
		add_action('mprm_menu_item_content', 'mprm_menu_item_content_comments', 30);
		/**
		 * Before Menu_item sidebar
		 *
		 * @see mprm_before_menu_item_sidebar()
		 */
		add_action('mprm_before_menu_item_sidebar', 'mprm_before_menu_item_sidebar', 10);
		/**
		 * Menu item sidebar
		 *
		 * @see mprm_menu_item_price()
		 * @see mprm_menu_item_slidebar_attributes()
		 * @see mprm_menu_item_slidebar_ingredients()
		 * @see mprm_menu_item_slidebar_nutritional()
		 * @see mprm_menu_item_slidebar_related_items()
		 */
		add_action('mprm_menu_item_slidebar', 'mprm_menu_item_price', 5);
		add_action('mprm_menu_item_slidebar', 'mprm_get_purchase_template', 10);
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
		add_action('mprm_taxonomy_list', 'mprm_category_menu_item_before_content', 5);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_before_left', 10);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_image', 15);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_after_left', 20);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_before_right', 25);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_header_title', 30);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_ingredients', 35);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_tags', 40);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_price', 45);
		add_action('mprm_taxonomy_list', 'mprm_category_menu_item_after_content', 50);
		add_action('mprm_taxonomy_list', 'mprm_get_purchase_template', 55);
		add_action('mprm_taxonomy_list', 'mprm_taxonomy_list_after_right', 60);
		
		/**
		 * After Menu_item list
		 *
		 * @see mprm_after_category_list
		 */
		add_action('mprm_taxonomy_after_list', 'mprm_after_taxonomy_list', 10);
		/**
		 * Menu_item grid
		 *
		 * @see mprm_single_category_grid_header()
		 * @see mprm_single_category_grid_image()
		 * @see mprm_single_category_grid_description()
		 * @see mprm_single_category_grid_footer()
		 */
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_header', 10);
		add_action('mprm_taxonomy_grid', 'mprm_category_menu_item_before_content', 15);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_image', 25);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_wrapper_start', 35);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_title', 40);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_ingredients', 45);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_tags', 50);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_price', 55);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_wrapper_end', 60);
		add_action('mprm_taxonomy_grid', 'mprm_category_menu_item_after_content', 65);
		add_action('mprm_taxonomy_grid', 'mprm_get_purchase_template', 70);
		add_action('mprm_taxonomy_grid', 'mprm_single_category_grid_footer', 75);
		
		/**
		 * Menu_item header
		 *
		 * @see mprm_category_header()
		 */
		add_action('mprm_category_header', 'mprm_category_header', 5);
		
		/**
		 * Menu_item header
		 *
		 * @see mprm_page_template_taxonomy_header()
		 */
		add_action('mprm_page_template_taxonomy_header', 'mprm_page_template_taxonomy_header', 5);
	}
	
	/**
	 * Install tag actions
	 */
	public static function install_tag_actions() {
		
		add_action('mprm_tag_before_wrapper', 'mprm_theme_wrapper_before');
		add_action('mprm_tag_after_wrapper', 'mprm_theme_wrapper_after');
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
		add_action('mprm_tag_header', 'mprm_category_header', 5);
	}
	
	/**
	 * Install cart actions
	 */
	public static function install_cart_actions() {
		if (Cart::get_instance()->item_quantities_enabled()) {
			add_action('mprm_cart_footer_buttons', 'mprm_update_cart_button');
		}
		// Check if the Save Cart button should be shown
		if (!Cart::get_instance()->is_cart_saving_disabled()) {
			add_action('mprm_cart_footer_buttons', 'mprm_save_cart_button');
		}
		add_action('mprm_cart_empty', 'mprm_cart_empty');
		add_action('mprm_success_page_cart_item', 'mprm_success_page_cart_item', 10, 2);
		add_action('mprm_payment_mode_select', 'mprm_payment_mode_select');
		add_action('mprm_purchase_form', 'mprm_purchase_form');
		add_action('mprm_purchase_form_top', 'mprm_purchase_form_top');
		add_action('mprm_purchase_form_register_fields', 'mprm_get_register_fields');
		add_action('mprm_register_fields_before', 'mprm_user_info_fields');
		add_action('mprm_purchase_form_before_register_login', 'mprm_purchase_form_before_register_login');
		add_action('mprm_purchase_form_login_fields', 'mprm_get_login_fields');
		add_action('mprm_purchase_form_before_cc_form', 'mprm_purchase_form_before_cc_form');
		add_action('mprm_purchase_form_after_cc_form', 'mprm_checkout_tax_fields', 999);
		add_action('mprm_purchase_form_after_cc_form', 'mprm_checkout_submit', 9999);
		add_action('mprm_purchase_form_no_access', 'mprm_purchase_form_no_access');
		add_action('mprm_purchase_form_after_user_info', 'mprm_user_info_fields');
		add_action('mprm_cc_billing_top', 'mprm_cc_billing_top');
		add_action('mprm_cc_billing_bottom', 'mprm_cc_billing_bottom');
		add_action('mprm_purchase_form_before_submit', 'mprm_checkout_additional_information');
		add_action('mprm_purchase_form_before_submit', 'mprm_terms_agreement');
		add_action('mprm_purchase_form_before_submit', 'mprm_print_errors');
		add_action('mprm_purchase_form_before_submit', 'mprm_checkout_final_total', 999);
		add_action('mprm_ajax_checkout_errors', 'mprm_print_errors');
		add_action('mprm_cc_form', 'mprm_get_cc_form');
		add_action('mprm_weekly_scheduled_events', array(Cart::get_instance(), 'delete_saved_carts'));
	}
	
	/**
	 * Install checkout actions
	 */
	public static function install_checkout_actions() {
		add_action('mprm_cart_fee_rows_before', 'mprm_cart_fee_rows_before');
		add_action('mprm_cart_fee_rows_after', 'mprm_cart_fee_rows_after');
		add_action('mprm_payment_mode_top', 'mprm_payment_mode_top');
		add_action('mprm_checkout_summary_table', 'mprm_checkout_summary_table');
		add_action('mprm_checkout_additional_information', 'mprm_checkout_delivery_address', 5);
		add_action('mprm_checkout_additional_information', 'mprm_checkout_order_note', 10);
		add_filter('the_content', 'mprm_filter_success_page_content', 99999);
	}
	
	/**
	 * Init hook
	 */
	public function init() {
		
		//Register custom post types
		Post::register_post_status();
		//Add PayPal listener
		Paypal_standart::get_instance()->listen_for_paypal_ipn();
		Paypal::get_instance()->listen_for_paypal_ipn();

		//Check if Theme Supports Post Thumbnails
		if (!current_theme_supports('post-thumbnails')) {
			add_theme_support('post-thumbnails', array( 'mp_menu_item', 'mprm_toppings' ));
		}

		// Register attachment sizes
		$this->get('image')->add_image_sizes();

		// Image downsize
		add_action('image_downsize', array($this->get('image'), 'image_downsize'), 10, 3);

		// Register custom post type and taxonomies
		Media::get_instance()->register_all_post_type();
		Media::get_instance()->register_all_taxonomies();

		// Include template
		if (Media::get_instance()->get_template_mode() == 'plugin') {
			add_filter('template_include', array(Media::get_instance(), 'template_include'));
		} else {
			add_filter('single_template', array(Media::get_instance(), 'modify_single_template'), 99);
			add_filter('template_include', array(View::get_instance(), 'template_loader'));
		}

		// Route url
		Core::get_instance()->wp_ajax_route_url();

		// Shortcodes
		add_shortcode('mprm_categories', array(Shortcode_Category::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_items', array(Shortcode_Item::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_cart', array(Shortcode_Cart::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_checkout', array(Shortcode_Checkout::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_success', array(Shortcode_success::get_instance(), 'render_shortcode'));
		add_shortcode('mprm_purchase_history', array(Shortcode_history::get_instance(), 'render_shortcode'));

		// Integrate in motopress
		add_action('mp_library', array(Shortcode_Category::get_instance(), 'integration_motopress'), 10, 1);
		add_action('mp_library', array(Shortcode_Item::get_instance(), 'integration_motopress'), 10, 1);

		// post_class filter
		add_filter('post_class', 'mprm_post_class', 20, 3);

		//Adding shop class body
		add_filter('body_class', 'mprm_add_body_classes');
		add_filter('the_tags', array(self::get_instance()->get('menu_tag'), 'create_custom_tags_list'), 10, 5);
		add_filter('the_category', array(self::get_instance()->get('menu_category'), 'create_custom_category_list'), 10, 3);
		add_filter('mprm_get_option_template_mode', array(Core::get_instance(), 'filter_template_mode'), 10, 3);
		add_filter('mprm_get_option_button_style', array(Core::get_instance(), 'filter_button_style'), 10, 3);
		add_filter('mprm_get_option_checkout_color', array(Core::get_instance(), 'filter_checkout_color'), 10, 3);
		add_filter('mprm_available_theme_mode', array(Core::get_instance(), 'available_theme_mode'), 10, 3);
		add_filter('mprm_settings_general', array($this, 'filter_options'), 10, 1);
		
		add_action('wp_head', array($this, 'wpHeadFinished'), 999);
	}
	
	/**
	 * Flag Head finished
	 */
	public function wpHeadFinished() {
		Core::$wpHeadFinished = true;
	}
	
	/**
	 * Hooks for admin panel
	 */
	public function admin_init() {
		
		// install metaboxes
		$this->get('menu_item')->init_metaboxes();
		add_filter('post_updated_messages', array($this, 'post_updated_messages'));
		add_filter('bulk_post_updated_messages', array($this, 'bulk_post_updated_messages'), 10, 2);
		add_action('add_meta_boxes', array(Post::get_instance(), 'add_meta_boxes'));
		// Shop order search
		add_filter('get_search_query', array($this, 'mprm_order_search_label'));
		add_filter('query_vars', array($this->get('query'), 'add_custom_query_var'));
		add_action('parse_query', array($this->get('query'), 'mprm_search_custom_fields'));
		add_action('admin_head', array($this, 'edit_screen_title'));
		add_filter('views_edit-mprm_order', array($this, 'clear_admin_filter'));
		// Bulk / quick edit
		add_action('bulk_edit_custom_box', array($this, 'bulk_edit'), 10, 2);
		add_action('quick_edit_custom_box', array($this, 'quick_edit'), 10, 2);
		// Edit
		add_filter('manage_posts_columns', array($this, 'add_posts_column'), 10, 2);
		add_filter('manage_posts_columns', array($this, 'add_posts_column'), 10, 2);
		add_filter('manage_edit-mp_menu_item_columns', array($this, 'remove_posts_column'));
		add_filter('manage_edit-mprm_order_columns', array($this, 'remove_posts_column'));
		add_action('save_post', array($this, 'bulk_and_quick_edit_save_post'), 10, 2);
		add_action('admin_footer', array($this, 'bulk_admin_footer'), 10);
		add_action('load-edit.php', array($this, 'bulk_action'));
		add_action('admin_notices', array($this, 'show_admin_notices'));
		add_action('admin_notices', array($this, 'admin_notices_action'));
		add_filter('post_row_actions', array($this, 'remove_row_actions'), 10, 2);
		add_action('save_post', array(Post::get_instance(), 'save'));
		add_action('edit_form_after_title', array(Post::get_instance(), "edit_form_after_title"));
		// Menu item List posts
		add_filter("manage_{$this->get_post_type('menu_item')}_posts_columns", array(Post::get_instance(), "init_menu_columns"), 10);
		add_action("manage_{$this->get_post_type('menu_item')}_posts_custom_column", array(Post::get_instance(), "show_menu_columns"), 10, 2);
		// Disable Auto Save
		add_action('admin_print_scripts', array(Media::get_instance(), 'disable_autosave'));
		// Manage and sortable order
		add_filter("manage_{$this->get_post_type('order')}_posts_columns", array(Order::get_instance(), "order_columns"), 10);
		add_action("manage_{$this->get_post_type('order')}_posts_custom_column", array(Order::get_instance(), "render_order_columns"), 10, 2);
		add_action("manage_edit-{$this->get_post_type('order')}_sortable_columns", array(Order::get_instance(), "order_sortable_columns"), 10, 2);
		add_filter('request', array($this, 'order_total_orderby'));
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
		
		register_importer('mprm-importer', 'Restaurant Menu', __('Import menu items, categories, images and other data.', 'mp-restaurant-menu'), array(Import::get_instance(), 'import'));
		//Emails
		add_action('mprm_email_settings', array(Settings_emails::get_instance(), 'email_template_preview'));
		
	}
	
	/**
	 * Order by order total
	 *
	 * @param $vars
	 *
	 * @return array
	 */
	public function order_total_orderby($vars) {
		if (isset($vars[ 'orderby' ]) && 'order_total' == $vars[ 'orderby' ] && $vars[ 'post_type' ] == 'mprm_order') {
			$vars = array_merge($vars, array(
				'meta_key' => '_mprm_order_total',
				'orderby' => 'meta_value_num'
			));
		}
		
		return $vars;
	}
	
	/**
	 *  Settings error
	 */
	public function admin_notices_action() {
		settings_errors('mprm-notices');
	}
	
	/**
	 * @param $query
	 *
	 * @return array|string
	 */
	public function mprm_order_search_label($query) {
		global $pagenow, $typenow;
		
		if ('edit.php' != $pagenow) {
			return $query;
		}
		
		if ($typenow != 'mprm_order') {
			return $query;
		}
		
		if (!get_query_var('mprm_order_search')) {
			return $query;
		}
		
		return wp_unslash($_GET[ 's' ]);
	}
	
	/**
	 * Add advanced post_status
	 */
	public function bulk_admin_footer() {
		global $post_type;
		$order_statuses = mprm_get_payment_statuses();
		if ('mprm_order' == $post_type) {
			?>
			<script type="text/javascript">
				jQuery(function() {
					<?php foreach($order_statuses as $key => $value): ?>
					jQuery('<option>').val('<?php echo 'set-order-' . $key ?>').text('<?php _e("Set to {$value}", 'mp-restaurant-menu')?>').appendTo('select[name="action"]');
					jQuery('<option>').val('<?php echo 'set-order-' . $key ?>').text('<?php _e("Set to {$value}", 'mp-restaurant-menu')?>').appendTo('select[name="action2"]');
					<?php endforeach;?>
				});
			</script>
			<?php
		}
	}
	
	/**
	 * @param $column_name
	 * @param $post_type
	 */
	public function quick_edit($column_name, $post_type) {
		
		switch ($post_type) {
			case "{$this->post_types['menu_item']}":
				$this->get_view()->render_html('../admin/quick-edit/menu-item', array('column_name' => $column_name), true);
				break;
			case "{$this->post_types['order']}":
				break;
			default:
				break;
		}
		
	}
	
	/**
	 * @param $column_name
	 * @param $post_type
	 */
	public function bulk_edit($column_name, $post_type) {
	}
	
	/**
	 * Bulk action
	 */
	public function bulk_action() {
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$action = $wp_list_table->current_action();
		
		// Bail out if this is not a status-changing action
		if (strpos($action, 'set-order-') === false) {
			return;
		}
		
		$order_statuses = mprm_get_payment_statuses();
		
		$new_status = substr($action, 10); // get the status name from action
		
		if (!isset($order_statuses[ $new_status ]) && $new_status != 'publish') {
			return;
		}
		
		$changed = 0;
		
		$post_ids = array_map('absint', (array)$_REQUEST[ 'post' ]);
		
		foreach ($post_ids as $post_id) {
			$order = new Order($post_id);
			$order->update_status($new_status);
			$changed++;
		}
		
		$sendback = add_query_arg(array('post_type' => 'mprm_order', 'changed' => $changed, 'ids' => join(',', $post_ids)), '');
		
		if (isset($_GET[ 'post_status' ])) {
			$sendback = add_query_arg('post_status', sanitize_text_field($_GET[ 'post_status' ]), $sendback);
		}
		
		wp_redirect(esc_url_raw($sendback));
		exit();
	}
	
	/**
	 * Add posts column by post type
	 *
	 * @param $posts_columns
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public function add_posts_column($posts_columns, $post_type) {
		switch ($post_type) {
			case "{$this->post_types['menu_item']}":
				break;
			case "{$this->post_types['order']}":
				break;
			default:
				break;
		}
		
		return $posts_columns;
	}
	
	/**
	 * @param $posts_columns
	 *
	 * @return mixed
	 */
	public function remove_posts_column($posts_columns) {
		global $post_type;
		
		switch ($post_type) {
			case "{$this->post_types['menu_item']}":
				break;
			case "{$this->post_types['order']}":
				break;
			default:
				break;
		}
		
		return $posts_columns;
	}
	
	/**
	 * @param $post_id
	 * @param $post
	 *
	 * @return mixed
	 */
	public function bulk_and_quick_edit_save_post($post_id) {
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		
		// Don't save revisions and autosaves
		if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
			return $post_id;
		}
		
		// Check user permission
		if (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
		
		return $post_id;
	}
	
	/**
	 * Show admin notices
	 */
	public function show_admin_notices() {
		global $pagenow;
		if ($pagenow == 'plugins.php') {
			if (current_user_can('install_plugins') && current_user_can('manage_options')) {
				if (!get_option('mprm_install_page')) {
					View::get_instance()->render_html('../admin/notices/install-plugin');
				}
			}
		}
		if (isset($_REQUEST[ 'page' ])) {
			if ($pagenow == 'edit.php' && $_REQUEST[ 'page' ] == 'mprm-customers') {
				if (!empty($_REQUEST[ 'message' ])) {
					View::get_instance()->render_html('../admin/notices/' . $_REQUEST[ 'message' ]);
				}
			}
		}
	}
	
	/**
	 * @param $messages
	 *
	 * @return mixed
	 */
	public function post_updated_messages($messages) {
		global $post;
		$messages[ 'mprm_order' ] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __('Order updated.', 'mp-restaurant-menu'),
			2 => __('Custom field updated.', 'mp-restaurant-menu'),
			3 => __('Custom field deleted.', 'mp-restaurant-menu'),
			4 => __('Order updated.', 'mp-restaurant-menu'),
			5 => isset($_GET[ 'revision' ]) ? sprintf(__('Order restored to revision from %s', 'mp-restaurant-menu'), wp_post_revision_title((int)$_GET[ 'revision' ], false)) : false,
			6 => __('Order updated.', 'mp-restaurant-menu'),
			7 => __('Order saved.', 'mp-restaurant-menu'),
			8 => __('Order submitted.', 'mp-restaurant-menu'),
			9 => sprintf(__('Order scheduled for: <strong>%1$s</strong>.', 'mp-restaurant-menu'),
				date_i18n(__('M j, Y @ G:i', 'mp-restaurant-menu'), strtotime($post->post_date))),
			10 => __('Order draft updated.', 'mp-restaurant-menu'),
			11 => __('Order updated and email sent.', 'mp-restaurant-menu')
		);
		
		return $messages;
	}
	
	/**
	 * @param $bulk_messages
	 * @param $bulk_counts
	 *
	 * @return mixed
	 */
	public function bulk_post_updated_messages($bulk_messages, $bulk_counts) {
		
		$bulk_messages[ 'mprm_menu_item' ] = array(
			'updated' => _n('%s menu item updated.', '%s menu items updated.', $bulk_counts[ 'updated' ], 'mp-restaurant-menu'),
			'locked' => _n('%s menu item not updated, somebody is editing it.', '%s menu items not updated, somebody is editing them.', $bulk_counts[ 'locked' ], 'mp-restaurant-menu'),
			'deleted' => _n('%s menu item permanently deleted.', '%s menu items permanently deleted.', $bulk_counts[ 'deleted' ], 'mp-restaurant-menu'),
			'trashed' => _n('%s menu item moved to the Trash.', '%s menu items moved to the Trash.', $bulk_counts[ 'trashed' ], 'mp-restaurant-menu'),
			'untrashed' => _n('%s menu item restored from the Trash.', '%s menu items restored from the Trash.', $bulk_counts[ 'untrashed' ], 'mp-restaurant-menu'),
		);
		
		$bulk_messages[ 'mprm_order' ] = array(
			'updated' => _n('%s order updated.', '%s orders updated.', $bulk_counts[ 'updated' ], 'mp-restaurant-menu'),
			'locked' => _n('%s order not updated, somebody is editing it.', '%s orders not updated, somebody is editing them.', $bulk_counts[ 'locked' ], 'mp-restaurant-menu'),
			'deleted' => _n('%s order permanently deleted.', '%s orders permanently deleted.', $bulk_counts[ 'deleted' ], 'mp-restaurant-menu'),
			'trashed' => _n('%s order moved to the Trash.', '%s orders moved to the Trash.', $bulk_counts[ 'trashed' ], 'mp-restaurant-menu'),
			'untrashed' => _n('%s order restored from the Trash.', '%s orders restored from the Trash.', $bulk_counts[ 'untrashed' ], 'mp-restaurant-menu'),
		);
		
		return $bulk_messages;
	}
	
	/**
	 * @param $views
	 *
	 * @return mixed
	 */
	public function clear_admin_filter($views) {
		unset($views[ 'mine' ]);
		unset($views[ 'draft' ]);
		if (!empty($views[ 'publish' ])) {
			$views[ 'publish' ] = preg_replace('/Published/', 'Payment Complete', $views[ 'publish' ]);
		}
		
		return $views;
	}
	
	/**
	 * Edit screen title
	 */
	function edit_screen_title() {
		global $post, $title, $action, $current_screen;
		
		if (isset($current_screen->post_type) && $current_screen->post_type == $this->post_types[ 'order' ] && $action == 'edit') {
			$title = sprintf( __('Edit Order #%s', 'mp-restaurant-menu'), $post->ID) ;
		}
	}
	
	/**
	 * @param $actions
	 * @param $post
	 *
	 * @return mixed
	 */
	public function remove_row_actions($actions, $post) {
		global $current_screen;
		if (is_object($current_screen)) {
			if ($current_screen->post_type != 'mprm_order') {
				return $actions;
			}
		} elseif ($post->post_type != 'mprm_order') {
			return $actions;
		}
		
		unset($actions[ 'view' ]);
		unset($actions[ 'inline hide-if-no-js' ]);
		
		return $actions;
	}
	
	/**
	 * Filter settings options
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function filter_options($args) {
		
		if (isset($args[ 'main' ][ 'category_view' ])) {
			if (mprm_get_option('template_mode', 'theme') == 'theme') {
				unset($args[ 'main' ][ 'category_view' ]);
			}
		}
		
		return $args;
	}
}
