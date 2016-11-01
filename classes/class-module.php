<?php
namespace mp_restaurant_menu\classes;

/**
 * Class Module
 * @package mp_restaurant_menu\classes
 */

class Module extends Core {
	/**
	 * Install controllers
	 */
	public static function install() {
		// include all core controllers
		Core::get_instance()->include_all(MP_RM_MODULES_PATH);
	}

	/**
	 * Get labels
	 *
	 * @param array $params
	 * @param string $plugin_name
	 *
	 * @return array
	 */
	public function get_labels(array $params, $plugin_name = 'mp-restaurant-menu') {
		$labels = array();
		if (!empty($params['titles'])) {
			$many = !empty($params['titles']['many']) ? $params['titles']['many'] : '';
			$single = !empty($params['titles']['single']) ? $params['titles']['single'] : '';
			$labels = array(
				'name' => _x(ucfirst($many), 'post type general name', $plugin_name),
				'singular_name' => _x(ucfirst($single), 'post type singular name', $plugin_name),
				'add_new' => _x('Add New', $many, $plugin_name),
				'add_new_item' => __('Add New ' . ucfirst($single), $plugin_name),
				'edit_item' => __('Edit ' . ucfirst($single), $plugin_name),
				'new_item' => __('New ' . ucfirst($single), $plugin_name),
				'all_items' => __('All ' . ucfirst($many), $plugin_name),
				'view_item' => __('View ' . ucfirst($single), $plugin_name),
				'search_items' => __('Search ' . ucfirst($single), $plugin_name),
				'not_found' => __("No $many found", $plugin_name),
				'not_found_in_trash' => __("No $many found in Trash", $plugin_name),
				'parent_item_colon' => 'media',
				'menu_name' => __(ucfirst($many), $plugin_name)
			);
		}
		return $labels;
	}
}
