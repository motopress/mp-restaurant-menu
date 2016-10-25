<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

/**
 * Class Shortcode_history
 * @package mp_restaurant_menu\classes\shortcodes
 */
class Shortcode_history extends Shortcodes {
	protected static $instance;

	/**
	 * @return Shortcode_history
	 */
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
		return View::get_instance()->get_template_html('shop/history', $data);
	}
}