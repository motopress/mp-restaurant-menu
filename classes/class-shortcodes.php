<?php
namespace mp_restaurant_menu\classes;
/**
 * Class Shortcodes
 * @package mp_restaurant_menu\classes
 */
class Shortcodes extends Core {
	protected static $instance;

	/**
	 * @return Shortcodes
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Install shortcodes
	 */
	public static function install() {
		// include all core controllers
		Core::include_all(MP_RM_CLASSES_PATH . 'shortcodes/');
	}

	/**
	 * Create list for Motopress
	 *
	 * @param array $data_array
	 * @param string $type
	 *
	 * @return array
	 */
	public function create_list_motopress($data_array = array(), $type = 'post') {
		$list_array = array();
		switch ($type) {
			case "post":
				foreach ($data_array as $item) {
					$list_array[$item->ID] = $item->post_title;
				}
				break;
			case "term":
				foreach ($data_array as $item) {
					$list_array[$item->term_id] = $item->name;
				}
				break;
			default:
				break;
		}
		return $list_array;
	}
}