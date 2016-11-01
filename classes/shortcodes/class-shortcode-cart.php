<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

/**
 * Class Shortcode_Cart
 * @package mp_restaurant_menu\classes\shortcodes
 */
class Shortcode_Cart extends Shortcodes {
	protected static $instance;

	/**
	 * @return Shortcode_Cart
	 */
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
		return View::get_instance()->get_template_html("shop/cart", $args);
	}
}