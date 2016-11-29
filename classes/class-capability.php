<?php
namespace mp_restaurant_menu\classes;
/**
 * Class Capabilities
 * @package mp_restaurant_menu\classes
 */
class Capabilities extends Core {

	protected static $instance;

	protected $capability_version = '2.1.1';

	/**
	 * Get things going
	 */
	public function __construct() {
		add_filter('map_meta_cap', array($this, 'meta_caps'), 10, 4);
	}

	/**
	 * @return Capabilities
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Capability version
	 */
	public function get_capability_version() {
		return $this->capability_version;
	}

	/**
	 * Add new shop roles with default WP caps
	 *
	 * @access public
	 * @return void
	 */
	public function add_roles() {

		add_role('mprm_manager', __('Restaurant Manager', 'mp-restaurant-menu'), array(
			'read' => true,
			'edit_posts' => true,
			'delete_posts' => true,
			'unfiltered_html' => true,
			'upload_files' => true,
			'export' => true,
			'import' => true,
			'delete_others_pages' => true,
			'delete_others_posts' => true,
			'delete_pages' => true,
			'delete_private_pages' => true,
			'delete_private_posts' => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages' => true,
			'edit_others_posts' => true,
			'edit_pages' => true,
			'edit_private_pages' => true,
			'edit_private_posts' => true,
			'edit_published_pages' => true,
			'edit_published_posts' => true,
			'manage_categories' => true,
			'manage_links' => true,
			'moderate_comments' => true,
			'publish_pages' => true,
			'publish_posts' => true,
			'read_private_pages' => true,
			'read_private_posts' => true
		));

		add_role('mprm_customer', __('Restaurant Customer', 'mp-restaurant-menu'), array(
			'read' => true
		));
	}

	/**
	 * Add new shop-specific capabilities
	 *
	 * @access public
	 * @global \WP_Roles $wp_roles
	 * @return void
	 */
	public function add_caps() {
		global $wp_roles;
		if (class_exists('WP_Roles')) {
			if (!isset($wp_roles)) {
				$wp_roles = new \WP_Roles();
			}
		}
		if (is_object($wp_roles)) {
			$wp_roles->add_cap('mprm_manager', 'manage_restaurant_settings');
			$wp_roles->add_cap('mprm_manager', 'manage_restaurant_terms');
			$wp_roles->add_cap('mprm_manager', 'manage_restaurant_menu');
			$wp_roles->add_cap('mprm_manager', 'manage_options');

			$wp_roles->add_cap('administrator', 'manage_restaurant_settings');
			$wp_roles->add_cap('administrator', 'manage_restaurant_terms');


			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					$wp_roles->add_cap('mprm_manager', $cap);
					$wp_roles->add_cap('administrator', $cap);
				}
			}

		}
	}

	/**
	 * Gets the core post type capabilities
	 *
	 * @access public
	 * @return array $capabilities Core post type capabilities
	 */
	public function get_core_caps() {
		$capabilities = array();
		$capability_types = array($this->get_post_type('menu_item'), $this->get_post_type('order'));

		$capabilities['core'] = array(
			'manage_restaurant_menu'
		);

		foreach ($capability_types as $capability_type) {
			$capabilities[$capability_type] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",
				// Custom
				"view_{$capability_type}_stats"
			);
		}

		return $capabilities;
	}

	/**
	 * Map meta caps to primitive caps
	 *
	 * @param $caps
	 * @param $cap
	 * @param $user_id
	 * @param $args
	 *
	 * @return array
	 */
	public function meta_caps($caps, $cap, $user_id, $args) {
		switch ($cap) {
			case 'view_product_stats' :
				if (empty($args[0])) {
					break;
				}
				$menu_item = get_post($args[0]);
				if (empty($menu_item)) {
					break;
				}
				if (user_can($user_id, 'view_shop_reports') || $user_id == $menu_item->post_author) {
					$caps = array();
				}
				break;
		}
		return $caps;
	}

	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @access public
	 * @return void
	 */
	public function remove_caps() {
		global $wp_roles;

		if (class_exists('WP_Roles')) {
			if (!isset($wp_roles)) {
				$wp_roles = new \WP_Roles();
			}
		}

		if (is_object($wp_roles)) {

			/** Shop Manager Capabilities */
			$wp_roles->remove_cap('mprm_manager', 'view_shop_reports');
			$wp_roles->remove_cap('mprm_manager', 'manage_restaurant_settings');
			$wp_roles->remove_cap('mprm_manager', 'view_shop_sensitive_data');
			$wp_roles->remove_cap('mprm_manager', 'export_shop_reports');
			$wp_roles->remove_cap('mprm_manager', 'manage_shop_discounts');
			$wp_roles->remove_cap('mprm_manager', 'manage_shop_settings');
			$wp_roles->remove_cap('mprm_manager', 'manage_restaurant_menu');

			/** Site Administrator Capabilities */
			$wp_roles->remove_cap('administrator', 'view_shop_reports');
			$wp_roles->remove_cap('administrator', 'view_shop_sensitive_data');
			$wp_roles->remove_cap('administrator', 'export_shop_reports');
			$wp_roles->remove_cap('administrator', 'manage_shop_discounts');
			$wp_roles->remove_cap('administrator', 'manage_shop_settings');
			$wp_roles->remove_cap('administrator', 'manage_restaurant_settings');
			$wp_roles->remove_cap('administrator', 'manage_restaurant_menu');

			/** Site Editor Capabilities */
			$wp_roles->remove_cap('editor', 'view_shop_reports');
			$wp_roles->remove_cap('editor', 'view_shop_sensitive_data');
			$wp_roles->remove_cap('editor', 'export_shop_reports');
			$wp_roles->remove_cap('editor', 'manage_shop_discounts');
			$wp_roles->remove_cap('editor', 'manage_shop_settings');
			$wp_roles->remove_cap('editor', 'manage_restaurant_settings');
			$wp_roles->remove_cap('editor', 'manage_restaurant_menu');

			/** Remove the Main Post Type Capabilities */

			$capabilities = $this->get_core_caps();

			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					$wp_roles->remove_cap('mprm_manager', $cap);
					$wp_roles->remove_cap('administrator', $cap);
					$wp_roles->remove_cap('editor', $cap);
				}
			}
			$this->remove_roles(array('mprm_manager', 'mprm_customer', 'mprm_shop_manager', 'mprm_shop_accountant', 'mprm_shop_vendor', 'mprm_shop_worker'));

			delete_option('mprm_capabilities_version');
		}
	}

	/**
	 * Remove roles
	 *
	 * @param $remove_roles
	 */
	public function remove_roles($remove_roles) {
		if (!empty($remove_roles)) {
			foreach ($remove_roles as $role) {
				if (get_role($role)) {
					remove_role($role);
				}
			}
		}
	}

	/**
	 * Caching plugin
	 *
	 * @return mixed
	 */
	function is_caching_plugin_active() {
		$caching = (function_exists('wpsupercache_site_admin') || defined('W3TC') || function_exists('rocket_init'));
		return apply_filters('mprm_is_caching_plugin_active', $caching);
	}
}
