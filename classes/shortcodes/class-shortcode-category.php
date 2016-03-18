<?php

namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

class Shortcode_Category extends Shortcodes {

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
		global $mprm_view_args;
		$mprm_view_args = $args;
		$mprm_view_args['categories_terms'] = array();
		$mprm_view_args['action_path'] = "shortcodes/category/{$args['view']}/item";
		return View::get_instance()->render_html("shortcodes/category/index", $args, false);
	}
}
