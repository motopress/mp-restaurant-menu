<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Gateways extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_enabled_payment_gateways($sort = false) {
		$gateways = $this->get_payment_gateways();
		$enabled = $this->get('settings')->get_option('gateways', false);

		$gateway_list = array();

		foreach ($gateways as $key => $gateway) {
			if (isset($enabled[$key]) && $enabled[$key] == 1) {
				$gateway_list[$key] = $gateway;
			}
		}

		if (true === $sort) {
			// Reorder our gateways so the default is first
			$default_gateway_id = $this->get_default_gateway();

			if ($this->is_gateway_active($default_gateway_id)) {

				$default_gateway = array($default_gateway_id => $gateway_list[$default_gateway_id]);
				unset($gateway_list[$default_gateway_id]);

				$gateway_list = array_merge($default_gateway, $gateway_list);

			}

		}

		return apply_filters('mprm_enabled_payment_gateways', $gateway_list);
	}

	public function get_payment_gateways() {
		// Default, built-in gateways
		$gateways = array(
			'paypal' => array(
				'admin_label' => __('PayPal Standard', 'mp-restaurant-menu'),
				'checkout_label' => __('PayPal', 'mp-restaurant-menu'),
				'supports' => array('buy_now')
			),
			'manual' => array(
				'admin_label' => __('Test Payment', 'mp-restaurant-menu'),
				'checkout_label' => __('Test Payment', 'mp-restaurant-menu')
			),
		);

		return apply_filters('mprm_payment_gateways', $gateways);
	}

	public function get_default_gateway() {
		$default = $this->get('settings')->get_option('default_gateway', 'paypal');

		if (!$this->is_gateway_active($default)) {
			$gateways = $this->get_enabled_payment_gateways();
			$gateways = array_keys($gateways);
			$default = reset($gateways);
		}

		return apply_filters('mprm_default_gateway', $default);
	}

	public function is_gateway_active($gateway) {
		$gateways = $this->get_enabled_payment_gateways();
		$ret = array_key_exists($gateway, $gateways);
		return apply_filters('mprm_is_gateway_active', $ret, $gateway, $gateways);
	}

	public function shop_supports_buy_now() {
		$gateways = $this->get_enabled_payment_gateways();
		$ret = false;

		if (!$this->get('taxes')->use_taxes() && $gateways) {
			foreach ($gateways as $gateway_id => $gateway) {
				if ($this->gateway_supports_buy_now($gateway_id)) {
					$ret = true;
					break;
				}
			}
		}

		return apply_filters('mprm_shop_supports_buy_now', $ret);
	}

	public function gateway_supports_buy_now($gateway) {
		$supports = $this->get_gateway_supports($gateway);
		$ret = in_array('buy_now', $supports);
		return apply_filters('mprm_gateway_supports_buy_now', $ret, $gateway);
	}

	public function get_gateway_supports($gateway) {
		$gateways = $this->get_enabled_payment_gateways();
		$supports = isset($gateways[$gateway]['supports']) ? $gateways[$gateway]['supports'] : array();
		return apply_filters('mprm_gateway_supports', $supports, $gateway);
	}

	public function get_chosen_gateway() {
		$gateways = $this->get_enabled_payment_gateways();
		$chosen = isset($_REQUEST['payment-mode']) ? $_REQUEST['payment-mode'] : false;

		if (false !== $chosen) {
			$chosen = preg_replace('/[^a-zA-Z0-9-_]+/', '', $chosen);
		}

		if (!empty ($chosen)) {
			$enabled_gateway = urldecode($chosen);
		} else if (count($gateways) >= 1 && !$chosen) {
			foreach ($gateways as $gateway_id => $gateway):
				$enabled_gateway = $gateway_id;
				if ($this->get('cart')->get_cart_subtotal() <= 0) {
					$enabled_gateway = 'manual'; // This allows a free download by filling in the info
				}
			endforeach;
		} else if ($this->get('cart')->get_cart_subtotal() <= 0) {
			$enabled_gateway = 'manual';
		} else {
			$enabled_gateway = $this->get_default_gateway();
		}

		return apply_filters('mprm_chosen_gateway', $enabled_gateway);
	}

	public function show_gateways() {
		$gateways = $this->get_enabled_payment_gateways();
		$show_gateways = false;

		$chosen_gateway = isset($_GET['payment-mode']) ? preg_replace('/[^a-zA-Z0-9-_]+/', '', $_GET['payment-mode']) : false;

		if (count($gateways) > 1 && empty($chosen_gateway)) {
			$show_gateways = true;
			if ($this->get('cart')->get_cart_total() <= 0) {
				$show_gateways = false;
			}
		}

		return apply_filters('mprm_show_gateways', $show_gateways);
	}

}