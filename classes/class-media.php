<?php

namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\models\Settings;
use mp_restaurant_menu\classes\modules\Post;
use mp_restaurant_menu\classes\modules\Taxonomy;
use mp_restaurant_menu\classes\modules\Menu;

class Media extends Core {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Registered page in admin wp
	 */
	public function admin_menu() {
		// get taxonomy names
		$category_name = $this->get_tax_name('menu_category');
		$tag_name = $this->get_tax_name('menu_tag');
		$ingredient_name = $this->get_tax_name('ingredient');
		// get post types
		$menu_item = $this->get_post_type('menu_item');
		$order = $this->get_post_type('order');
		$menu_slug = "edit.php?post_type={$menu_item}";
		// Restaurant menu
		Menu::add_menu_page(array(
			'title' => __('Restaurant Menu', 'mp-restaurant-menu'),
			'menu_slug' => $menu_slug,
			'icon_url' => MP_RM_MEDIA_URL . '/img/icon.png',
			'capability' => 'edit_posts',
			'position' => '59.52'
		));
		// Menu items
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Menu Items', 'mp-restaurant-menu'),
			'menu_slug' => "edit.php?post_type={$menu_item}",
			'capability' => 'edit_posts',
		));

		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Shop orders', 'mp-restaurant-menu'),
			'menu_slug' => "edit.php?post_type=$order",
			'capability' => 'edit_posts',
		));

		// Add new
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Add New', 'mp-restaurant-menu'),
			'menu_slug' => "post-new.php?post_type={$menu_item}",
			'capability' => 'edit_posts',
		));
		// Categories
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Categories', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$category_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_categories',
		));
		// Tags	
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Tags', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$tag_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_categories',
		));
		// Ingredients	
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Ingredients', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$ingredient_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_categories',
		));
		// Settings	
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Settings', 'mp-restaurant-menu'),
			'menu_slug' => "admin.php?page=mprm-settings",
			'function' => array($this->get_controller('settings'), 'action_content'),
			'capability' => 'manage_options',
		));
		//Import/Export
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Import / Export', 'mp-restaurant-menu'),
			'menu_slug' => "admin.php?page=mprm-import",
			'function' => array($this->get_controller('import'), 'action_content'),
			'capability' => 'import',
		));
		$this->register_settings();
	}

	/**
	 * Current screen
	 *
	 * @param \WP_Screen $current_screen
	 */
	public function current_screen(\WP_Screen $current_screen) {
		$this->enqueue_style('admin-styles', 'admin-styles.css');
		if (!empty($current_screen)) {
			switch ($current_screen->base) {
				case"post":
				case"page":
					wp_enqueue_script('underscore');
					$this->enqueue_script('mp-restaurant-menu', 'mp-restaurant-menu.js');
					$this->enqueue_script('jBox', 'libs/jBox.min.js');
					wp_localize_script("mp-restaurant-menu", 'admin_lang', $this->get_config('language-admin-js'));
					$this->enqueue_style('jBox', 'lib/jbox/jBox.css');
					break;
				default:
					break;
			}

			switch ($current_screen->id) {
				case "restaurant-menu_page_admin?page=mprm-settings":
					wp_enqueue_script('underscore');
					$this->enqueue_script('mp-restaurant-menu', 'mp-restaurant-menu.js');
					wp_localize_script("mp-restaurant-menu", 'admin_lang', $this->get_config('language-admin-js'));
					wp_enqueue_script('wp-util');
					break;
				case "edit-mp_menu_category":

					$this->enqueue_script('mp-restaurant-menu', 'mp-restaurant-menu.js');
					$this->enqueue_script('iconset-mprm-icon', 'libs/iconset-mprm-icon.js');
					$this->enqueue_script('fonticonpicker', 'libs/jquery.fonticonpicker.min.js', array("jquery"), '2.0.0');
					wp_localize_script("mp-restaurant-menu", 'admin_lang', $this->get_config('language-admin-js'));

					$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.css');
					$this->enqueue_style('fonticonpicker', 'lib/jquery.fonticonpicker.min.css');
					$this->enqueue_style('fonticonpicker.grey', 'lib/jquery.fonticonpicker.grey.min.css');
					wp_enqueue_media();

					break;
				case "customize":
					break;
				default:
					break;
			}


		}
	}

	/**
	 * Admin script
	 */
	public function admin_enqueue_scripts() {
		global $current_screen;
		$this->current_screen($current_screen);
	}

	/**
	 * Wp head
	 */
	public function wp_head() {
		$this->add_theme_css();
	}

	/**
	 * Wp footer
	 */
	public function wp_footer() {
		$this->add_theme_js();
	}

//
//	/**
//	 * Add admin js
//	 *
//	 * @param \WP_Screen $current_screen
//	 */
//	private function add_admin_js(\WP_Screen $current_screen) {
//		wp_enqueue_script('underscore');
//		$this->enqueue_script('registry-factory', 'registry-factory.js');
//		$this->enqueue_script('mp-restaurant-menu', 'mp-restaurant-menu.js');
//		$this->enqueue_script('jBox', 'libs/jBox.min.js');
//		//$this->enqueue_script('html-builder', 'html-builder.js');
//		$this->enqueue_script('admin-functions', 'admin-functions.js');
//		wp_localize_script("admin-functions", 'admin_lang', $this->get_config('language-admin-js'));
//		switch ($current_screen->id) {
//			case 'mp_menu_item':
//				//$this->enqueue_script('menu-item', 'menu-item.js');
//				break;
//			case 'edit-mp_menu_category':
//				wp_enqueue_media();
//				$this->enqueue_script('iconset-mprm-icon', 'libs/iconset-mprm-icon.js');
//				$this->enqueue_script('fonticonpicker', 'libs/jquery.fonticonpicker.min.js', array("jquery"), '2.0.0');
//				//$this->enqueue_script('menu-category', 'menu-category.js');
//				break;
//			case 'restaurant-menu_page_admin?page=mprm-settings':
//				wp_enqueue_media();
//				//$this->enqueue_script('menu-settings', 'menu-settings.js');
//				break;
//		}
//	}
//
//	/**
//	 * Add admin css
//	 *
//	 * @param \WP_Screen $current_screen
//	 */
//	private function add_admin_css(\WP_Screen $current_screen) {
//		$this->enqueue_style('jBox', 'lib/jbox/jBox.css');
//		$this->enqueue_style('notice-border', 'lib/jbox/themes/NoticeBorder.css');
//		$this->enqueue_style('admin-styles', 'admin-styles.css');
//		switch ($current_screen->id) {
//			case 'edit-mp_menu_category':
//				$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.css');
//				$this->enqueue_style('fonticonpicker', 'lib/jquery.fonticonpicker.min.css');
//				$this->enqueue_style('fonticonpicker.grey', 'lib/jquery.fonticonpicker.grey.min.css');
//				break;
//		}
//	}

	/**
	 * Add theme css
	 */
	private function add_theme_css() {
		global $post_type;
		$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.css');
		$this->enqueue_style('mprm-style', 'style.css');
		switch ($post_type) {
			case"mp_menu_item":
				$this->enqueue_style('magnific-popup', 'lib/magnific-popup.css');
				break;
			default:
				break;
		}
	}

	/**
	 * Add theme js
	 */
	private function add_theme_js() {
		global $post_type;
		switch ($post_type) {
			case"mp_menu_item":
				wp_enqueue_script('underscore');
				$this->enqueue_script('mp-restaurant-menu', 'mp-restaurant-menu.js');
				$this->enqueue_script('magnific-popup', 'libs/jquery.magnific-popup.min.js', array("jquery"), '1.0.1');
				break;
			default:
				break;
		}
	}

	/**
	 * Register all post type
	 */
	public function register_all_post_type() {
		Post::get_instance()->register_post_type(array(
			'post_type' => $this->get_post_type('menu_item'),
			'titles' => array('many' => 'menu items', 'single' => 'menu item'),
			'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'page-attributes'),
			'slug' => 'menu'
		));
//		Post::get_instance()->register_post_type(array(
//			'post_type' => $this->get_post_type('order'),
//			'titles' => array('many' => 'orders', 'single' => 'order'),
//			'supports' => array('title', 'comments', 'custom-fields'),
//			'slug' => 'menu'
//		));

		register_post_type($this->get_post_type('order'), array(
			'labels' => array(
				'name' => __('Orders', 'mp-restaurant-menu'),
				'singular_name' => _x('Order', 'shop_order post type singular name', 'mp-restaurant-menu'),
				'add_new' => __('Add Order', 'mp-restaurant-menu'),
				'add_new_item' => __('Add New Order', 'mp-restaurant-menu'),
				'edit' => __('Edit', 'mp-restaurant-menu'),
				'edit_item' => __('Edit Order', 'mp-restaurant-menu'),
				'new_item' => __('New Order', 'mp-restaurant-menu'),
				'view' => __('View Order', 'mp-restaurant-menu'),
				'view_item' => __('View Order', 'mp-restaurant-menu'),
				'search_items' => __('Search Orders', 'mp-restaurant-menu'),
				'not_found' => __('No Orders found', 'mp-restaurant-menu'),
				'not_found_in_trash' => __('No Orders found in trash', 'mp-restaurant-menu'),
				'parent' => __('Parent Orders', 'mp-restaurant-menu'),
				'menu_name' => _x('Orders', 'Admin menu name', 'mp-restaurant-menu')
			),
			'description' => __('This is where store orders are stored.', 'mp-restaurant-menu'),
			'public' => false,
			'show_ui' => true,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
//			'show_in_menu' => current_user_can('manage_mp_restaurant_menu') ? 'mp-restaurant-menu' : true,
			'show_in_menu' => false,
			'hierarchical' => false,
			'show_in_nav_menus' => false,
			'rewrite' => false,
			'query_var' => false,
			'supports' => array('title', 'comments', 'custom-fields'),
			'has_archive' => false,
		));

	}

	/**
	 * Register all taxonomies
	 */
	public function register_all_taxonomies() {
		$menu_item = $this->get_post_type('menu_item');
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('menu_category'),
			'object_type' => array($menu_item),
			'titles' => array('many' => 'menu categories', 'single' => 'menu category'),
			'slug' => 'menu-category'
		));
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('menu_tag'),
			'object_type' => array($menu_item),
			'titles' => array('many' => 'menu tags', 'single' => 'menu tag'),
			'slug' => 'menu-tag'
		));
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('ingredient'),
			'object_type' => array($menu_item),
			'titles' => array('many' => 'ingredients', 'single' => 'ingredient'),
		));
	}

	/**
	 * Template include
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function template_include($template) {
		global $post, $taxonomy;
		if (!empty($taxonomy)) {
			foreach ($this->taxonomy_names as $taxonomy_name) {
				if (basename($template) != "taxonomy-$taxonomy_name.php") {
					$path = MP_RM_TEMPLATES_PATH . "taxonomy-$taxonomy_name.php";
					if (is_tax($taxonomy_name) && $taxonomy == $taxonomy_name && file_exists($path)) {
						$template = $path;
					}
				}
			}
		} elseif (!empty($post)) {
			foreach ($this->post_types as $post_type) {
				if (basename($template) != "single-$post_type.php") {
					$path = MP_RM_TEMPLATES_PATH . "single-$post_type.php";
					if ($post->post_type == $post_type && file_exists($path)) {
						$template = $path;
					}
				}
			}
		}
		return $template;
	}

	/**
	 * Connect js for MCE editor
	 *
	 * @param $plugin_array
	 *
	 * @return mixed
	 */
	public function mce_external_plugins($plugin_array) {
		$path = MP_RM_MEDIA_URL . 'js/mce-mp-restaurant-menu-plugin.js';
		$plugin_array['mp_restaurant_menu'] = $path;
		return $plugin_array;
	}

	/**
	 * Add button in MCE editor
	 *
	 * @param $buttons
	 *
	 * @return mixed
	 */
	public function mce_buttons($buttons) {
		array_push($buttons, 'mp_add_menu');
		return $buttons;
	}

	/**
	 * Enqueue script
	 *
	 * @param string $name
	 * @param string $path
	 * @param array $parent
	 * @param bool /string $version
	 *
	 * @return void
	 */
	public function enqueue_script($name, $path, $parent = array("jquery"), $version = false) {
		if (empty($version)) {
			$version = $this->get_version();
		}
		wp_enqueue_script($name, MP_RM_JS_URL . $path, $parent, $version);
	}

	/**
	 * Enqueue style
	 *
	 * @param string $name
	 * @param string $path
	 * @param array $parent
	 * @param bool /string $version
	 * * @return void
	 */
	public function enqueue_style($name, $path, $parent = array(), $version = false) {
		if (empty($version)) {
			$version = $this->get_version();
		}
		wp_enqueue_style($name, MP_RM_CSS_URL . $path, $parent, $version);
	}

	/**
	 * Cut string
	 *
	 * @param string $args
	 *
	 * @return int|mixed|string
	 */
	public static function cut_str($args = '') {

		$default = array(
			'maxchar' => 350,
			'text' => '',
			'save_format' => false,
			'more_text' => __('Read more', 'mp-restaurant-menu') . '...',
			'echo' => false,
		);

		if (is_array($args)) {
			$rgs = $args;
		} else {
			parse_str($args, $rgs);
		}

		$args = array_merge($default, $rgs);
		$args['maxchar'] += 0;

		// cutting
		if (mb_strlen($args['text']) > $args['maxchar'] && $args['maxchar'] != 0) {
			$args['text'] = mb_substr($args['text'], 0, $args['maxchar']);
			$args['text'] = $args['text'] . '...';
		}

		// save br ad paragraph
		if ($args['save_format']) {
			$args['text'] = str_replace("\r", '', $args['text']);
			$args['text'] = preg_replace("~\n\n+~", "</p><p>", $args['text']);
			$args['text'] = "<p>" . str_replace("\n", "<br />", trim($args['text'])) . "</p>";
		}

		if ($args['echo']) {
			return print $args['text'];
		}
		return $args['text'];
	}

	public function disable_autosave() {
		global $post;
		if (!empty($post) && $post->post_type == 'mprm_order') {
			wp_dequeue_script('autosave');
		}
	}

	public function register_settings() {
		if (false == get_option('mprm_settings')) {
			add_option('mprm_settings');
		}

		foreach ($this->get_registered_settings() as $tab => $sections) {
			foreach ($sections as $section => $settings) {

				// Check for backwards compatibility
				$section_tabs = $this->get_settings_tab_sections($tab);
				if (!is_array($section_tabs) || !array_key_exists($section, $section_tabs)) {
					$section = 'main';
					$settings = $sections;
				}

				add_settings_section('mprm_settings_' . $tab . '_' . $section, __return_null(), '__return_false', 'mprm_settings_' . $tab . '_' . $section);

				foreach ($settings as $option) {
					// For backwards compatibility
					if (empty($option['id'])) {
						continue;
					}

					$name = isset($option['name']) ? $option['name'] : '';

					add_settings_field(
						'mprm_settings[' . $option['id'] . ']',
						$name,
						function_exists('mprm_' . $option['type'] . '_callback') ? 'mprm_' . $option['type'] . '_callback' : 'mprm_missing_callback',
						'mprm_settings_' . $tab . '_' . $section,
						'mprm_settings_' . $tab . '_' . $section,
						array(
							'section' => $section,
							'id' => isset($option['id']) ? $option['id'] : null,
							'desc' => !empty($option['desc']) ? $option['desc'] : '',
							'name' => isset($option['name']) ? $option['name'] : null,
							'size' => isset($option['size']) ? $option['size'] : null,
							'options' => isset($option['options']) ? $option['options'] : '',
							'std' => isset($option['std']) ? $option['std'] : '',
							'min' => isset($option['min']) ? $option['min'] : null,
							'max' => isset($option['max']) ? $option['max'] : null,
							'step' => isset($option['step']) ? $option['step'] : null,
							'chosen' => isset($option['chosen']) ? $option['chosen'] : null,
							'placeholder' => isset($option['placeholder']) ? $option['placeholder'] : null,
							'allow_blank' => isset($option['allow_blank']) ? $option['allow_blank'] : true,
							'readonly' => isset($option['readonly']) ? $option['readonly'] : false,
							'faux' => isset($option['faux']) ? $option['faux'] : false,
						)
					);
				}
			}

		}
		// Creates our settings in the options table
		register_setting('mprm_settings', 'mprm_settings', 'mprm_settings_sanitize');
	}

	public function get_registered_settings() {
		$mprm_settings = array(
			/** General Settings */
			'general' => apply_filters('mprm_settings_general',
				array(
					'main' => array(
						'page_settings' => array(
							'id' => 'page_settings',
							'name' => '<h3>' . __('Page Settings', 'easy-digital-downloads') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),
						'purchase_page' => array(
							'id' => 'purchase_page',
							'name' => __('Checkout Page', 'easy-digital-downloads'),
							'desc' => __('This is the checkout page where buyers will complete their purchases. The [download_checkout] short code must be on this page.', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'easy-digital-downloads'),
						),
						'success_page' => array(
							'id' => 'success_page',
							'name' => __('Success Page', 'easy-digital-downloads'),
							'desc' => __('This is the page buyers are sent to after completing their purchases. The [mprm_receipt] short code should be on this page.', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'easy-digital-downloads'),
						),
						'failure_page' => array(
							'id' => 'failure_page',
							'name' => __('Failed Transaction Page', 'easy-digital-downloads'),
							'desc' => __('This is the page buyers are sent to if their transaction is cancelled or fails', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'easy-digital-downloads'),
						),
						'purchase_history_page' => array(
							'id' => 'purchase_history_page',
							'name' => __('Purchase History Page', 'easy-digital-downloads'),
							'desc' => __('This page shows a complete purchase history for the current user, including download links', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'easy-digital-downloads'),
						),
						'locale_settings' => array(
							'id' => 'locale_settings',
							'name' => '<h3>' . __('Store Location', 'easy-digital-downloads') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),
						'base_country' => array(
							'id' => 'base_country',
							'name' => __('Base Country', 'easy-digital-downloads'),
							'desc' => __('Where does your store operate from?', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => Settings::get_instance()->get_currencies(),
							'chosen' => true,
							'placeholder' => __('Select a country', 'easy-digital-downloads'),
						),
						'base_state' => array(
							'id' => 'base_state',
							'name' => __('Base State / Province', 'easy-digital-downloads'),
							'desc' => __('What state / province does your store operate from?', 'easy-digital-downloads'),
							'type' => 'shop_states',
							'chosen' => true,
							'placeholder' => __('Select a state', 'easy-digital-downloads'),
						),
						'tracking_settings' => array(
							'id' => 'tracking_settings',
							'name' => '<h3>' . __('Tracking Settings', 'easy-digital-downloads') . '</h3>',
							'desc' => '',
							'type' => 'header',
						)

					),
					'currency' => array(
						'currency_settings' => array(
							'id' => 'currency_settings',
							'name' => '<h3>' . __('Currency Settings', 'easy-digital-downloads') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),
						'currency' => array(
							'id' => 'currency',
							'name' => __('Currency', 'easy-digital-downloads'),
							'desc' => __('Choose your currency. Note that some payment gateways have currency restrictions.', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => Settings::get_instance()->get_currency_symbol(),
							'chosen' => true,
						),
						'currency_position' => array(
							'id' => 'currency_position',
							'name' => __('Currency Position', 'easy-digital-downloads'),
							'desc' => __('Choose the location of the currency sign.', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => array(
								'before' => __('Before - $10', 'easy-digital-downloads'),
								'after' => __('After - 10$', 'easy-digital-downloads'),
							),
						),
						'thousands_separator' => array(
							'id' => 'thousands_separator',
							'name' => __('Thousands Separator', 'easy-digital-downloads'),
							'desc' => __('The symbol (usually , or .) to separate thousands', 'easy-digital-downloads'),
							'type' => 'text',
							'size' => 'small',
							'std' => ',',
						),
						'decimal_separator' => array(
							'id' => 'decimal_separator',
							'name' => __('Decimal Separator', 'easy-digital-downloads'),
							'desc' => __('The symbol (usually , or .) to separate decimal points', 'easy-digital-downloads'),
							'type' => 'text',
							'size' => 'small',
							'std' => '.',
						),
					),
					'api' => array(
						'api_settings' => array(
							'id' => 'api_settings',
							'name' => '<h3>' . __('API Settings', 'easy-digital-downloads') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),
						'api_allow_user_keys' => array(
							'id' => 'api_allow_user_keys',
							'name' => __('Allow User Keys', 'easy-digital-downloads'),
							'desc' => __('Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
					),
				)
			),
			/** Payment Gateways Settings */
			'gateways' => apply_filters('mprm_settings_gateways',
				array(
					'main' => array(
						'gateway_settings' => array(
							'id' => 'api_header',
							'name' => '<h3>' . __('Gateway Settings', 'easy-digital-downloads') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),
						'test_mode' => array(
							'id' => 'test_mode',
							'name' => __('Test Mode', 'easy-digital-downloads'),
							'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'gateways' => array(
							'id' => 'gateways',
							'name' => __('Payment Gateways', 'easy-digital-downloads'),
							'desc' => __('Choose the payment gateways you want to enable.', 'easy-digital-downloads'),
							'type' => 'gateways',
							'options' => $this->get_payment_gateways(),
						),
						'default_gateway' => array(
							'id' => 'default_gateway',
							'name' => __('Default Gateway', 'easy-digital-downloads'),
							'desc' => __('This gateway will be loaded automatically with the checkout page.', 'easy-digital-downloads'),
							'type' => 'gateway_select',
							'options' => $this->get_payment_gateways(),
						),
						'accepted_cards' => array(
							'id' => 'accepted_cards',
							'name' => __('Accepted Payment Method Icons', 'easy-digital-downloads'),
							'desc' => __('Display icons for the selected payment methods', 'easy-digital-downloads') . '<br/>' . __('You will also need to configure your gateway settings if you are accepting credit cards', 'easy-digital-downloads'),
							'type' => 'payment_icons',
							'options' => apply_filters('mprm_accepted_payment_icons', array(
									'mastercard' => 'Mastercard',
									'visa' => 'Visa',
									'americanexpress' => 'American Express',
									'discover' => 'Discover',
									'paypal' => 'PayPal',
								)
							),
						),
					),
					'paypal' => array(
						'paypal_settings' => array(
							'id' => 'paypal_settings',
							'name' => '<h3>' . __('PayPal Standard Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'paypal_email' => array(
							'id' => 'paypal_email',
							'name' => __('PayPal Email', 'easy-digital-downloads'),
							'desc' => __('Enter your PayPal account\'s email', 'easy-digital-downloads'),
							'type' => 'text',
							'size' => 'regular',
						),
						'paypal_page_style' => array(
							'id' => 'paypal_page_style',
							'name' => __('PayPal Page Style', 'easy-digital-downloads'),
							'desc' => __('Enter the name of the page style to use, or leave blank for default', 'easy-digital-downloads'),
							'type' => 'text',
							'size' => 'regular',
						),
						'disable_paypal_verification' => array(
							'id' => 'disable_paypal_verification',
							'name' => __('Disable PayPal IPN Verification', 'easy-digital-downloads'),
							'desc' => __('If payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
					),
				)
			),
			/** Emails Settings */
			'emails' => apply_filters('mprm_settings_emails',
				array(
					'main' => array(
						'email_settings_header' => array(
							'id' => 'email_settings_header',
							'name' => '<h3>' . __('Email Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'email_template' => array(
							'id' => 'email_template',
							'name' => __('Email Template', 'easy-digital-downloads'),
							'desc' => __('Choose a template. Click "Save Changes" then "Preview Purchase Receipt" to see the new template.', 'easy-digital-downloads'),
							'type' => 'select',
							//'options' => mprm_get_email_templates(),
						),
						'email_logo' => array(
							'id' => 'email_logo',
							'name' => __('Logo', 'easy-digital-downloads'),
							'desc' => __('Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'easy-digital-downloads'),
							'type' => 'upload',
						),
						'email_settings' => array(
							'id' => 'email_settings',
							'name' => '',
							'desc' => '',
							'type' => 'hook',
						),
					),
					'purchase_receipts' => array(
						'purchase_receipt_settings' => array(
							'id' => 'purchase_receipt_settings',
							'name' => '<h3>' . __('Purchase Receipts', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'from_name' => array(
							'id' => 'from_name',
							'name' => __('From Name', 'easy-digital-downloads'),
							'desc' => __('The name purchase receipts are said to come from. This should probably be your site or shop name.', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => get_bloginfo('name'),
						),
						'from_email' => array(
							'id' => 'from_email',
							'name' => __('From Email', 'easy-digital-downloads'),
							'desc' => __('Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => get_bloginfo('admin_email'),
						),
						'purchase_subject' => array(
							'id' => 'purchase_subject',
							'name' => __('Purchase Email Subject', 'easy-digital-downloads'),
							'desc' => __('Enter the subject line for the purchase receipt email', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => __('Purchase Receipt', 'easy-digital-downloads'),
						),
						'purchase_heading' => array(
							'id' => 'purchase_heading',
							'name' => __('Purchase Email Heading', 'easy-digital-downloads'),
							'desc' => __('Enter the heading for the purchase receipt email', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => __('Purchase Receipt', 'easy-digital-downloads'),
						),
						'purchase_receipt' => array(
							'id' => 'purchase_receipt',
							'name' => __('Purchase Receipt', 'easy-digital-downloads'),
							'desc' => __('Enter the text that is sent as purchase receipt email to users after completion of a successful purchase. HTML is accepted. Available template tags:', 'easy-digital-downloads') . '<br/>' /*. mprm_get_emails_tags_list()*/,
							'type' => 'rich_editor',
							'std' => __("Dear", "easy-digital-downloads") . " {name},\n\n" . __("Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads") . "\n\n{download_list}\n\n{sitename}",
						),
					),
					'sale_notifications' => array(
						'sale_notification_settings' => array(
							'id' => 'sale_notification_settings',
							'name' => '<h3>' . __('Sale Notifications', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'sale_notification_subject' => array(
							'id' => 'sale_notification_subject',
							'name' => __('Sale Notification Subject', 'easy-digital-downloads'),
							'desc' => __('Enter the subject line for the sale notification email', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => 'New download purchase - Order #{payment_id}',
						),
						'sale_notification' => array(
							'id' => 'sale_notification',
							'name' => __('Sale Notification', 'easy-digital-downloads'),
							'desc' => __('Enter the text that is sent as sale notification email after completion of a purchase. HTML is accepted. Available template tags:', 'easy-digital-downloads') . '<br/>'  /* . mprm_get_emails_tags_list()*/,
							'type' => 'rich_editor',
							//'std' => mprm_get_default_sale_notification_email(),
						),
						'admin_notice_emails' => array(
							'id' => 'admin_notice_emails',
							'name' => __('Sale Notification Emails', 'easy-digital-downloads'),
							'desc' => __('Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'easy-digital-downloads'),
							'type' => 'textarea',
							'std' => get_bloginfo('admin_email'),
						),
						'disable_admin_notices' => array(
							'id' => 'disable_admin_notices',
							'name' => __('Disable Admin Notifications', 'easy-digital-downloads'),
							'desc' => __('Check this box if you do not want to receive sales notification emails.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
					),
				)
			),
			/** Styles Settings */
			'styles' => apply_filters('mprm_settings_styles',
				array(
					'main' => array(
						'style_settings' => array(
							'id' => 'style_settings',
							'name' => '<h3>' . __('Style Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'disable_styles' => array(
							'id' => 'disable_styles',
							'name' => __('Disable Styles', 'easy-digital-downloads'),
							'desc' => __('Check this to disable all included styling of buttons, checkout fields, and all other elements.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'button_header' => array(
							'id' => 'button_header',
							'name' => '<strong>' . __('Buttons', 'easy-digital-downloads') . '</strong>',
							'desc' => __('Options for add to cart and purchase buttons', 'easy-digital-downloads'),
							'type' => 'header',
						),
						'button_style' => array(
							'id' => 'button_style',
							'name' => __('Default Button Style', 'easy-digital-downloads'),
							'desc' => __('Choose the style you want to use for the buttons.', 'easy-digital-downloads'),
							'type' => 'select',
							'options' => $this->get_button_styles(),
						),
						'checkout_color' => array(
							'id' => 'checkout_color',
							'name' => __('Default Button Color', 'easy-digital-downloads'),
							'desc' => __('Choose the color you want to use for the buttons.', 'easy-digital-downloads'),
							'type' => 'color_select',
							'options' => $this->get_button_colors(),
						),
					),
				)
			),
			/** Taxes Settings */
			'taxes' => apply_filters('mprm_settings_taxes',
				array(
					'main' => array(
						'tax_settings' => array(
							'id' => 'tax_settings',
							'name' => '<h3>' . __('Tax Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'enable_taxes' => array(
							'id' => 'enable_taxes',
							'name' => __('Enable Taxes', 'easy-digital-downloads'),
							'desc' => __('Check this to enable taxes on purchases.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'tax_rates' => array(
							'id' => 'tax_rates',
							'name' => '<strong>' . __('Tax Rates', 'easy-digital-downloads') . '</strong>',
							'desc' => __('Enter tax rates for specific regions.', 'easy-digital-downloads'),
							'type' => 'tax_rates',
						),
						'tax_rate' => array(
							'id' => 'tax_rate',
							'name' => __('Fallback Tax Rate', 'easy-digital-downloads'),
							'desc' => __('Enter a percentage, such as 6.5. Customers not in a specific rate will be charged this rate.', 'easy-digital-downloads'),
							'type' => 'text',
							'size' => 'small',
						),
						'prices_include_tax' => array(
							'id' => 'prices_include_tax',
							'name' => __('Prices entered with tax', 'easy-digital-downloads'),
							'desc' => __('This option affects how you enter prices.', 'easy-digital-downloads'),
							'type' => 'radio',
							'std' => 'no',
							'options' => array(
								'yes' => __('Yes, I will enter prices inclusive of tax', 'easy-digital-downloads'),
								'no' => __('No, I will enter prices exclusive of tax', 'easy-digital-downloads'),
							),
						),
						'display_tax_rate' => array(
							'id' => 'display_tax_rate',
							'name' => __('Display Tax Rate on Prices', 'easy-digital-downloads'),
							'desc' => __('Some countries require a notice when product prices include tax.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'checkout_include_tax' => array(
							'id' => 'checkout_include_tax',
							'name' => __('Display during checkout', 'easy-digital-downloads'),
							'desc' => __('Should prices on the checkout page be shown with or without tax?', 'easy-digital-downloads'),
							'type' => 'select',
							'std' => 'no',
							'options' => array(
								'yes' => __('Including tax', 'easy-digital-downloads'),
								'no' => __('Excluding tax', 'easy-digital-downloads'),
							),
						),
					),
				)
			),
			/** Extension Settings */
			'extensions' => apply_filters('mprm_settings_extensions',
				array()
			),
			'licenses' => apply_filters('mprm_settings_licenses',
				array()
			),
			/** Misc Settings */
			'misc' => apply_filters('mprm_settings_misc',
				array(
					'main' => array(
						'misc_settings' => array(
							'id' => 'misc_settings',
							'name' => '<h3>' . __('Misc Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'enable_ajax_cart' => array(
							'id' => 'enable_ajax_cart',
							'name' => __('Enable Ajax', 'easy-digital-downloads'),
							'desc' => __('Check this to enable AJAX for the shopping cart.', 'easy-digital-downloads'),
							'type' => 'checkbox',
							'std' => '1',
						),
						'redirect_on_add' => array(
							'id' => 'redirect_on_add',
							'name' => __('Redirect to Checkout', 'easy-digital-downloads'),
							'desc' => __('Immediately redirect to checkout after adding an item to the cart?', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'item_quantities' => array(
							'id' => 'item_quantities',
							'name' => __('Item Quantities', 'easy-digital-downloads'),
							'desc' => __('Allow item quantities to be changed.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'uninstall_on_delete' => array(
							'id' => 'uninstall_on_delete',
							'name' => __('Remove Data on Uninstall?', 'easy-digital-downloads'),
							'desc' => __('Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
					),
					'checkout' => array(
						'checkout_settings' => array(
							'id' => 'checkout_settings',
							'name' => '<h3>' . __('Checkout Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'enforce_ssl' => array(
							'id' => 'enforce_ssl',
							'name' => __('Enforce SSL on Checkout', 'easy-digital-downloads'),
							'desc' => __('Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'logged_in_only' => array(
							'id' => 'logged_in_only',
							'name' => __('Disable Guest Checkout', 'easy-digital-downloads'),
							'desc' => __('Require that users be logged-in to purchase files.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'show_register_form' => array(
							'id' => 'show_register_form',
							'name' => __('Show Register / Login Form?', 'easy-digital-downloads'),
							'desc' => __('Display the registration and login forms on the checkout page for non-logged-in users.', 'easy-digital-downloads'),
							'type' => 'select',
							'std' => 'none',
							'options' => array(
								'both' => __('Registration and Login Forms', 'easy-digital-downloads'),
								'registration' => __('Registration Form Only', 'easy-digital-downloads'),
								'login' => __('Login Form Only', 'easy-digital-downloads'),
								'none' => __('None', 'easy-digital-downloads'),
							),
						),
						'allow_multiple_discounts' => array(
							'id' => 'allow_multiple_discounts',
							'name' => __('Multiple Discounts', 'easy-digital-downloads'),
							'desc' => __('Allow customers to use multiple discounts on the same purchase?', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'enable_cart_saving' => array(
							'id' => 'enable_cart_saving',
							'name' => __('Enable Cart Saving', 'easy-digital-downloads'),
							'desc' => __('Check this to enable cart saving on the checkout.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
					),
					'button_text' => array(
						'button_settings' => array(
							'id' => 'button_settings',
							'name' => '<h3>' . __('Button Text', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'checkout_label' => array(
							'id' => 'checkout_label',
							'name' => __('Complete Purchase Text', 'easy-digital-downloads'),
							'desc' => __('The button label for completing a purchase.', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => __('Purchase', 'easy-digital-downloads'),
						),
						'add_to_cart_text' => array(
							'id' => 'add_to_cart_text',
							'name' => __('Add to Cart Text', 'easy-digital-downloads'),
							'desc' => __('Text shown on the Add to Cart Buttons.', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => __('Add to Cart', 'easy-digital-downloads'),
						),
						'buy_now_text' => array(
							'id' => 'buy_now_text',
							'name' => __('Buy Now Text', 'easy-digital-downloads'),
							'desc' => __('Text shown on the Buy Now Buttons.', 'easy-digital-downloads'),
							'type' => 'text',
							'std' => __('Buy Now', 'easy-digital-downloads'),
						),
					),
					'file_downloads' => array(
						'file_settings' => array(
							'id' => 'file_settings',
							'name' => '<h3>' . __('File Download Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'download_method' => array(
							'id' => 'download_method',
							'name' => __('Download Method', 'easy-digital-downloads'),
							'desc' => sprintf(__('Select the file download method. Note, not all methods work on all servers.', 'easy-digital-downloads'), $this->get_label_singular()),
							'type' => 'select',
							'options' => array(
								'direct' => __('Forced', 'easy-digital-downloads'),
								'redirect' => __('Redirect', 'easy-digital-downloads'),
							),
						),
						'symlink_file_downloads' => array(
							'id' => 'symlink_file_downloads',
							'name' => __('Symlink File Downloads?', 'easy-digital-downloads'),
							'desc' => __('Check this if you are delivering really large files or having problems with file downloads completing.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'file_download_limit' => array(
							'id' => 'file_download_limit',
							'name' => __('File Download Limit', 'easy-digital-downloads'),
							'desc' => sprintf(__('The maximum number of times files can be downloaded for purchases. Can be overwritten for each %s.', 'easy-digital-downloads'), $this->get_label_singular()),
							'type' => 'number',
							'size' => 'small',
						),
						'download_link_expiration' => array(
							'id' => 'download_link_expiration',
							'name' => __('Download Link Expiration', 'easy-digital-downloads'),
							'desc' => __('How long should download links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'easy-digital-downloads'),
							'type' => 'number',
							'size' => 'small',
							'std' => '24',
							'min' => '0',
						),
						'disable_redownload' => array(
							'id' => 'disable_redownload',
							'name' => __('Disable Redownload?', 'easy-digital-downloads'),
							'desc' => __('Check this if you do not want to allow users to redownload items from their purchase history.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
					),
					'accounting' => array(
						'accounting_settings' => array(
							'id' => 'accounting_settings',
							'name' => '<h3>' . __('Accounting Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'enable_skus' => array(
							'id' => 'enable_skus',
							'name' => __('Enable SKU Entry', 'easy-digital-downloads'),
							'desc' => __('Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'enable_sequential' => array(
							'id' => 'enable_sequential',
							'name' => __('Sequential Order Numbers', 'easy-digital-downloads'),
							'desc' => __('Check this box to enable sequential order numbers.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'sequential_start' => array(
							'id' => 'sequential_start',
							'name' => __('Sequential Starting Number', 'easy-digital-downloads'),
							'desc' => __('The number at which the sequence should begin.', 'easy-digital-downloads'),
							'type' => 'number',
							'size' => 'small',
							'std' => '1',
						),
						'sequential_prefix' => array(
							'id' => 'sequential_prefix',
							'name' => __('Sequential Number Prefix', 'easy-digital-downloads'),
							'desc' => __('A prefix to prepend to all sequential order numbers.', 'easy-digital-downloads'),
							'type' => 'text',
						),
						'sequential_postfix' => array(
							'id' => 'sequential_postfix',
							'name' => __('Sequential Number Postfix', 'easy-digital-downloads'),
							'desc' => __('A postfix to append to all sequential order numbers.', 'easy-digital-downloads'),
							'type' => 'text',
						),
					),
					'site_terms' => array(
						'terms_settings' => array(
							'id' => 'terms_settings',
							'name' => '<h3>' . __('Agreement Settings', 'easy-digital-downloads') . '</h3>',
							'type' => 'header',
						),
						'show_agree_to_terms' => array(
							'id' => 'show_agree_to_terms',
							'name' => __('Agree to Terms', 'easy-digital-downloads'),
							'desc' => __('Check this to show an agree to terms on the checkout that users must agree to before purchasing.', 'easy-digital-downloads'),
							'type' => 'checkbox',
						),
						'agree_label' => array(
							'id' => 'agree_label',
							'name' => __('Agree to Terms Label', 'easy-digital-downloads'),
							'desc' => __('Label shown next to the agree to terms check box.', 'easy-digital-downloads'),
							'type' => 'text',
							'size' => 'regular',
						),
						'agree_text' => array(
							'id' => 'agree_text',
							'name' => __('Agreement Text', 'easy-digital-downloads'),
							'desc' => __('If Agree to Terms is checked, enter the agreement terms here.', 'easy-digital-downloads'),
							'type' => 'rich_editor',
						),
					),
				)
			)
		);
		return apply_filters('mprm_registered_settings', $mprm_settings);
	}

	public function get_settings_tab_sections($tab) {

		$tabs = false;
		$sections = $this->get_registered_settings_sections();

		if ($tab && !empty($sections[$tab])) {
			$tabs = $sections[$tab];
		} else if ($tab) {
			$tabs = false;
		}

		return $tabs;

	}

	public function get_registered_settings_sections() {

		static $sections = false;

		if (false !== $sections) {
			return $sections;
		}

		$sections = array(
			'general' => apply_filters('mprm_settings_sections_general', array(
				'main' => __('General Settings', 'easy-digital-downloads'),
				'currency' => __('Currency Settings', 'easy-digital-downloads'),
				'api' => __('API Settings', 'easy-digital-downloads'),
			)),
			'gateways' => apply_filters('mprm_settings_sections_gateways', array(
				'main' => __('Gateway Settings', 'easy-digital-downloads'),
				'paypal' => __('PayPal Standard', 'easy-digital-downloads'),
			)),
			'emails' => apply_filters('mprm_settings_sections_emails', array(
				'main' => __('Email Settings', 'easy-digital-downloads'),
				'purchase_receipts' => __('Purchase Receipts', 'easy-digital-downloads'),
				'sale_notifications' => __('New Sale Notifications', 'easy-digital-downloads'),
			)),
			'styles' => apply_filters('mprm_settings_sections_styles', array(
				'main' => __('Style Settings', 'easy-digital-downloads'),
			)),
			'taxes' => apply_filters('mprm_settings_sections_taxes', array(
				'main' => __('Tax Settings', 'easy-digital-downloads'),
			)),
			'extensions' => apply_filters('mprm_settings_sections_extensions', array(
				'main' => __('Main', 'easy-digital-downloads')
			)),
			'licenses' => apply_filters('mprm_settings_sections_licenses', array()),
			'misc' => apply_filters('mprm_settings_sections_misc', array(
				'main' => __('Misc Settings', 'easy-digital-downloads'),
				'checkout' => __('Checkout Settings', 'easy-digital-downloads'),
				'button_text' => __('Button Text', 'easy-digital-downloads'),
				'file_downloads' => __('File Downloads', 'easy-digital-downloads'),
				'accounting' => __('Accounting Settings', 'easy-digital-downloads'),
				'site_terms' => __('Terms of Agreement', 'easy-digital-downloads'),
			)),
		);

		$sections = apply_filters('mprm_settings_sections', $sections);

		return $sections;
	}

	public function get_pages($force = false) {

		$pages_options = array('' => ''); // Blank option

		if ((!isset($_GET['page']) || 'mprm-settings' != $_GET['page']) && !$force) {
			return $pages_options;
		}

		$pages = get_pages();
		if ($pages) {
			foreach ($pages as $page) {
				$pages_options[$page->ID] = $page->post_title;
			}
		}

		return $pages_options;
	}

	public function get_payment_gateways() {
		// Default, built-in gateways
		$gateways = array(
			'paypal' => array(
				'admin_label' => __('PayPal Standard', 'easy-digital-downloads'),
				'checkout_label' => __('PayPal', 'easy-digital-downloads'),
				'supports' => array('buy_now')
			),
			'manual' => array(
				'admin_label' => __('Test Payment', 'easy-digital-downloads'),
				'checkout_label' => __('Test Payment', 'easy-digital-downloads')
			),
		);

		return apply_filters('edd_payment_gateways', $gateways);
	}

	public function get_button_styles() {
		$styles = array(
			'button' => __('Button', 'easy-digital-downloads'),
			'plain' => __('Plain Text', 'easy-digital-downloads')
		);

		return apply_filters('edd_button_styles', $styles);
	}

	public function get_button_colors() {
		$colors = array(
			'white' => array(
				'label' => __('White', 'easy-digital-downloads'),
				'hex' => '#ffffff'
			),
			'gray' => array(
				'label' => __('Gray', 'easy-digital-downloads'),
				'hex' => '#f0f0f0'
			),
			'blue' => array(
				'label' => __('Blue', 'easy-digital-downloads'),
				'hex' => '#428bca'
			),
			'red' => array(
				'label' => __('Red', 'easy-digital-downloads'),
				'hex' => '#d9534f'
			),
			'green' => array(
				'label' => __('Green', 'easy-digital-downloads'),
				'hex' => '#5cb85c'
			),
			'yellow' => array(
				'label' => __('Yellow', 'easy-digital-downloads'),
				'hex' => '#f0ad4e'
			),
			'orange' => array(
				'label' => __('Orange', 'easy-digital-downloads'),
				'hex' => '#ed9c28'
			),
			'dark-gray' => array(
				'label' => __('Dark Gray', 'easy-digital-downloads'),
				'hex' => '#363636'
			),
			'inherit' => array(
				'label' => __('Inherit', 'easy-digital-downloads'),
				'hex' => ''
			)
		);

		return apply_filters('edd_button_colors', $colors);
	}

	public function get_label_singular($lowercase = false) {
		$defaults = $this->get_default_labels();
		return ($lowercase) ? strtolower($defaults['singular']) : $defaults['singular'];
	}

	public function get_default_labels() {
		$defaults = array(
			'singular' => __('Download', 'easy-digital-downloads'),
			'plural' => __('Downloads', 'easy-digital-downloads')
		);
		return apply_filters('edd_default_downloads_name', $defaults);
	}

	public function get_settings_tabs() {

		$settings = $this->get_registered_settings();

		$tabs = array();
		$tabs['general'] = __('General', 'easy-digital-downloads');
		$tabs['gateways'] = __('Payment Gateways', 'easy-digital-downloads');
		$tabs['emails'] = __('Emails', 'easy-digital-downloads');
		$tabs['styles'] = __('Styles', 'easy-digital-downloads');
		$tabs['taxes'] = __('Taxes', 'easy-digital-downloads');

		if (!empty($settings['extensions'])) {
			$tabs['extensions'] = __('Extensions', 'easy-digital-downloads');
		}
		if (!empty($settings['licenses'])) {
			$tabs['licenses'] = __('Licenses', 'easy-digital-downloads');
		}

		$tabs['misc'] = __('Misc', 'easy-digital-downloads');

		return apply_filters('mprm_settings_tabs', $tabs);
	}
}
