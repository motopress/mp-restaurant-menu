<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

class Shortcode_Cart extends Shortcodes {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Main functiob for short code category
	 *
	 * @param $args
	 *
	 * @return \mp_restaurant_menu\classes\type|string
	 */
	public function render_shortcode($args) {
		return View::get_instance()->render_html("shop/cart", $args, false);
	}
}