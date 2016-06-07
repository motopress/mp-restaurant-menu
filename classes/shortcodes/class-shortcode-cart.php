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
	 * Render shortcode cart
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function render_shortcode($args) {
		return View::get_instance()->render_html("shop/cart", $args, false);
	}
}