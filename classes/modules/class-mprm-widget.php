<?php
namespace mp_restaurant_menu\classes\modules;

use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\Module;

/**
 * Class MPRM_Widget
 * @package mp_restaurant_menu\classes\modules
 */
class MPRM_Widget extends Module {
	protected static $instance;

	/**
	 * @return MPRM_Widget
	 */
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
		Core::get_instance()->include_all(MP_RM_WIDGETS_PATH);
	}

	public function register() {
		register_widget('mp_restaurant_menu\classes\widgets\Menu_item_widget');
		register_widget('mp_restaurant_menu\classes\widgets\Category_widget');
		register_widget('mp_restaurant_menu\classes\widgets\Cart_widget');
	}
}
