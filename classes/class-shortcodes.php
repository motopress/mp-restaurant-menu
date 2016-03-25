<?php
namespace mp_restaurant_menu\classes;


class Shortcodes extends Core {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * install shortcodes
	 */
	public static function install() {
		// include all core controllers
		Core::include_all(MP_RM_CLASSES_PATH . 'shortcodes/');
	}
}