<?php
namespace mp_restaurant_menu\classes\modules;

use mp_restaurant_menu\classes\Module;

class Widget extends Module {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Include all widgets
	 */
	public static function install() {
		self::include_all(MP_RM_WIDGETS_PATH);
	}

	public function register(){
		register_widget('mp_restaurant_menu\classes\widgets\Menu_item_widget');
		register_widget('mp_restaurant_menu\classes\widgets\Category_widget');
	}
}

