<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

class Shortcode_history extends Shortcodes {
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
	 * @param $data
	 *
	 * @return mixed
	 */
	public function render_shortcode($data) {
		return View::get_instance()->render_html("shop/history", $data, false);
	}
}