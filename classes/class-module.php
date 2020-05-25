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

}
