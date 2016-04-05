<?php

namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\Core;
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
		$this->enqueue_style('mprm-style', 'style.css');
		$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.css');
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
	 * @param type $name
	 * @param type $path
	 * @param type $parent
	 * @param type $version
	 *
	 * @return type
	 */
	public function enqueue_script($name, $path, $parent = array("jquery"), $version = false) {
		if (empty($version)) {
			$version = $this->get_version();
		}
		return wp_enqueue_script($name, MP_RM_JS_URL . $path, $parent, $version);
	}

	/**
	 * Enqueue style
	 *
	 * @param type $name
	 * @param type $path
	 * @param type $parent
	 */
	public function enqueue_style($name, $path, $parent = array(), $version = false) {
		if (empty($version)) {
			$version = $this->get_version();
		}
		return wp_enqueue_style($name, MP_RM_CSS_URL . $path, $parent, $version);
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

}
