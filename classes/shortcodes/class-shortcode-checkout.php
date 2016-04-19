<?php

namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

class Shortcode_Checkout extends Shortcodes {

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
		Media::get_instance()->add_plugin_js('shortcode');
		$args['payment_mode'] = $this->get('gateways')->get_chosen_gateway();
		$args['form_action'] = esc_url($this->get('checkout')->get_checkout_uri('payment-mode=' . $args['payment_mode']));
		$args['cart_contents'] = $this->get('cart')->get_cart_contents();
		$args['cart_has_fees'] = $this->get('cart')->cart_has_fees();

		return View::get_instance()->render_html("shop/checkout", $args, false);
	}
}