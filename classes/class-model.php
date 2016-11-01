<?php
namespace mp_restaurant_menu\classes;

/**
 * Model class
 */
class Model extends Core {
	protected static $instance;

	/**
	 * @return Model
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Install models by type
	 */
	static function install() {
		Core::get_instance()->include_all(MP_RM_MODELS_PATH . '/parents');
		// include all core models
		Core::get_instance()->include_all(MP_RM_MODELS_PATH);
	}

	/**
	 * Get return Array
	 *
	 * @param array $data
	 * @param bool|false $success
	 *
	 * @return array
	 */
	public function get_arr($data = array(), $success = false) {
		return array('success' => $success, 'data' => $data);
	}
}
