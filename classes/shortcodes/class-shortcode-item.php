<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

class Shortcode_Item extends Shortcodes {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init shortode item
	 *
	 * @param array $args
	 *
	 * @return type
	 */
	public function render_shortcode(array $args) {
		global $mprm_view_args;
		$mprm_view_args = $args;
		$mprm_view_args['action_path'] = "shortcodes/menu/{$args['view']}/item";
		return View::get_instance()->render_html("shortcodes/menu/index", $args, false);
	}

}
