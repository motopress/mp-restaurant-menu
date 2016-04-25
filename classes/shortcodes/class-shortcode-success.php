<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

class Shortcode_success extends Shortcodes {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Render shortcode
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function render_shortcode($args) {
		$args = array();
		return View::get_instance()->render_html("shop/success", $args, false);
	}
}