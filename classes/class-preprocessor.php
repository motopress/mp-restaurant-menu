<?php

namespace mp_restaurant_menu\classes;

/**
 * Class Preprocessor
 * @package mp_restaurant_menu\classes
 */
class Preprocessor {

	protected static $instance;

	/**
	 * @return Preprocessor
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Install Preprocessors
	 */
	static function install() {
		Core::get_instance()->include_all(MP_RM_PREPROCESSORS_PATH);
	}

	/**
	 * Call controller
	 *
	 * @param string $action
	 * @param bool $controller
	 *
	 * @return mixed
	 */
	public function call_controller($action = 'content', $controller = false) {

		if ( empty($controller) ) {
			trigger_error("Wrong controller ");
		}

		$path = MP_RM_CONTROLLERS_PATH;

		// if controller exists
		if ('controller' != $controller && !file_exists("{$path}class-controller-{$controller}.php")) {
			$ControllerName = 'Controller_' . ucfirst($controller);
			if (class_exists($ControllerName)) {
				trigger_error("Wrong controller {$path}class-controller-{$controller}.php"); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		$action = "action_$action";
		$controller = Core::get_instance()->get_state()->get_controller($controller);

		// if metod exists
		if ( method_exists($controller, $action) ) {
			return $controller->$action();
		} else {
			trigger_error("Wrong {$action} in {$path}class-controller-{$controller}.php"); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

}
