<?php
namespace mp_restaurant_menu\classes;

/**
 * Controller class
 */
class Controller extends Core {
	protected static $instance;

	/**
	 * @return Controller
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Install controllers
	 */
	public function install() {
		// include all core controllers
		Core::get_instance()->include_all(MP_RM_CONTROLLERS_PATH);
	}

	/**
	 * Send json data
	 *
	 * @param array /mixed $data
	 */
	public function send_json($data) {
		if (is_array($data) && isset($data['success']) && !$data['success']) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data['data']);
		}
	}
}
