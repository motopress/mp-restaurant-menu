<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Gateways
 * @package mp_restaurant_menu\classes\models
 */
class Gateways extends Model {
	protected static $instance;
	
	/**
	 * @return Gateways
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * @return mixed
	 */
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
	
	/**
	 * @param bool $sort
	 *
	 * @return mixed
	 */
	public function get_enabled_payment_gateways($sort = false) {
		$gateways = $this->get_payment_gateways();
		$enabled = $this->get('settings')->get_option('gateways', false);
		$gateway_list = array();
		foreach ($gateways as $key => $gateway) {
			if (isset($enabled[ $key ]) && $enabled[ $key ] == 1) {
				$gateway_list[ $key ] = $gateway;
			}
		}
		if (true === $sort) {
			// Reorder our gateways so the default is first
			$default_gateway_id = $this->get_default_gateway();
			if ($this->is_gateway_active($default_gateway_id)) {
				$default_gateway = array($default_gateway_id => $gateway_list[ $default_gateway_id ]);
				unset($gateway_list[ $default_gateway_id ]);
				$gateway_list = array_merge($default_gateway, $gateway_list);
			}
		}
		
		return apply_filters('mprm_enabled_payment_gateways', $gateway_list);
	}
	
	/**
	 * @return mixed
	 */
	public function get_payment_gateways() {
		
		// Default, built-in gateways
		$gateways = array(
			'paypal' => array(
				'admin_label' => __('PayPal Standard', 'mp-restaurant-menu'),
				'checkout_label' => $this->get('settings')->get_option('paypal_title', __('Pay via PayPal', 'mp-restaurant-menu')),
				'checkout_description' => $this->get('settings')->get_option('paypal_description', false),
				'supports' => array('buy_now')
			),
			'test_manual' => array(
				'admin_label' => __('Test Payment', 'mp-restaurant-menu'),
				'checkout_label' => __('Test Payment', 'mp-restaurant-menu'),
			),
			'manual' => array(
				'admin_label' => __('Cash on delivery', 'mp-restaurant-menu'),
				'checkout_label' => $this->get('settings')->get_option('cod_title', __('Cash on delivery', 'mp-restaurant-menu')),
				'checkout_description' => $this->get('settings')->get_option('cod_description', false),
			)
		);
		
		return apply_filters('mprm_payment_gateways', $gateways);
	}
	
	/**
	 * @return mixed
	 */
	public function get_default_gateway() {
		$default = $this->get('settings')->get_option('default_gateway', 'paypal');
		if (!$this->is_gateway_active($default)) {
			$gateways = $this->get_enabled_payment_gateways();
			$gateways = array_keys($gateways);
			$default = reset($gateways);
		}
		
		return apply_filters('mprm_default_gateway', $default);
	}
	
	/**
	 * @param $gateway
	 *
	 * @return mixed
	 */
	public function is_gateway_active($gateway) {
		$gateways = $this->get_enabled_payment_gateways();
		$ret = array_key_exists($gateway, $gateways);
		
		return apply_filters('mprm_is_gateway_active', $ret, $gateway, $gateways);
	}
	
	/**
	 * @param $gateway
	 *
	 * @return mixed
	 */
	public function gateway_supports_buy_now($gateway) {
		$supports = $this->get_gateway_supports($gateway);
		$ret = in_array('buy_now', $supports);
		
		return apply_filters('mprm_gateway_supports_buy_now', $ret, $gateway);
	}
	
	/**
	 * @param $gateway
	 *
	 * @return mixed
	 */
	public function get_gateway_supports($gateway) {
		$gateways = $this->get_enabled_payment_gateways();
		$supports = isset($gateways[ $gateway ][ 'supports' ]) ? $gateways[ $gateway ][ 'supports' ] : array();
		
		return apply_filters('mprm_gateway_supports', $supports, $gateway);
	}
	
	/**
	 * @return mixed
	 */
	public function get_chosen_gateway() {
		$gateways = $this->get_enabled_payment_gateways();
		$chosen = isset($_REQUEST[ 'payment-mode' ]) ? $_REQUEST[ 'payment-mode' ] : false;
		if (false !== $chosen) {
			$chosen = preg_replace('/[^a-zA-Z0-9-_]+/', '', $chosen);
		}
		if (!empty ($chosen)) {
			$enabled_gateway = urldecode($chosen);
		} else if (count($gateways) >= 1 && !$chosen) {
			foreach ($gateways as $gateway_id => $gateway):
				$enabled_gateway = $gateway_id;
				if ($this->get('cart')->get_cart_subtotal() <= 0) {
					$enabled_gateway = 'test_manual'; // This allows a free menu_item by filling in the info
				}
			endforeach;
		} else if ($this->get('cart')->get_cart_subtotal() <= 0) {
			$enabled_gateway = 'test_manual';
		} else {
			$enabled_gateway = $this->get_default_gateway();
		}
		
		return apply_filters('mprm_chosen_gateway', $enabled_gateway);
	}
	
	/**
	 * @return mixed
	 */
	public function show_gateways() {
		$gateways = $this->get_enabled_payment_gateways();
		$show_gateways = false;
		$chosen_gateway = isset($_GET[ 'payment-mode' ]) ? preg_replace('/[^a-zA-Z0-9-_]+/', '', $_GET[ 'payment-mode' ]) : false;
		if (count($gateways) > 1 && empty($chosen_gateway)) {
			$show_gateways = true;
			if ($this->get('cart')->get_cart_total() <= 0) {
				$show_gateways = false;
			}
		}
		
		return apply_filters('mprm_show_gateways', $show_gateways);
	}
	
	/**
	 * @param $gateway
	 * @param $payment_data
	 */
	public function send_to_gateway($gateway, $payment_data) {
		$payment_data[ 'gateway_nonce' ] = wp_create_nonce('mprm-gateway');
		// $gateway must match the ID used when registering the gateway
		add_action('http_api_curl', array($this, 'http_api_curl'));
		
		do_action('mprm_gateway_' . $gateway, $payment_data);
		
		remove_action('http_api_curl', array($this, 'http_api_curl'));
	}
	
	/**
	 * Set TLS 1.2 for CURL
	 *
	 * @param $handle
	 */
	public function http_api_curl($handle) {
		curl_setopt($handle, CURLOPT_SSLVERSION, 6);
	}
	
	/**
	 * @param int $menu_item_id
	 * @param array $options
	 * @param int $quantity
	 *
	 * @return mixed
	 */
	function build_straight_to_gateway_data($menu_item_id = 0, $options = array(), $quantity = 1) {
		$price_options = array();
		if (empty($options) || !$this->get('menu_item')->has_variable_prices($menu_item_id)) {
			$price = $this->get('menu_item')->get_price($menu_item_id);
		} else {
			if (is_array($options[ 'price_id' ])) {
				$price_id = $options[ 'price_id' ][ 0 ];
			} else {
				$price_id = $options[ 'price_id' ];
			}
			$prices = $this->get('menu_item')->get_variable_prices($menu_item_id);
			// Make sure a valid price ID was supplied
			if (!isset($prices[ $price_id ])) {
				wp_die(__('The requested price ID does not exist.', 'mp-restaurant-menu'), __('Error', 'mp-restaurant-menu'), array('response' => 404));
			}
			$price_options = array(
				'price_id' => $price_id,
				'amount' => $prices[ $price_id ][ 'amount' ]
			);
			$price = $prices[ $price_id ][ 'amount' ];
		}
		// Set up Menu items array
		$menu_items = array(
			array(
				'id' => $menu_item_id,
				'options' => $price_options
			)
		);
		// Set up Cart Details array
		$cart_details = array(
			array(
				'name' => get_the_title($menu_item_id),
				'id' => $menu_item_id,
				'item_number' => array(
					'id' => $menu_item_id,
					'options' => $price_options
				),
				'tax' => 0,
				'discount' => 0,
				'item_price' => $price,
				'subtotal' => ($price * $quantity),
				'price' => ($price * $quantity),
				'quantity' => $quantity,
			)
		);
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
		}
		
		// Setup user information
		$user_info = array(
			'id' => is_user_logged_in() ? get_current_user_id() : -1,
			'email' => is_user_logged_in() ? $current_user->user_email : '',
			'first_name' => is_user_logged_in() ? $current_user->user_firstname : '',
			'last_name' => is_user_logged_in() ? $current_user->user_lastname : '',
			'discount' => 'none',
			'address' => array()
		);
		// Setup purchase information
		$purchase_data = array(
			'menu_items' => $menu_items,
			'fees' => $this->get('cart')->get_cart_fees(),
			'subtotal' => $price * $quantity,
			'discount' => 0,
			'tax' => 0,
			'price' => $price * $quantity,
			'purchase_key' => strtolower(md5(uniqid())),
			'user_email' => $user_info[ 'email' ],
			'date' => date('Y-m-d H:i:s', current_time('timestamp')),
			'user_info' => $user_info,
			'post_data' => array(),
			'cart_details' => $cart_details,
			'gateway' => 'paypal',
			'buy_now' => true,
			'card_info' => array()
		);
		
		return apply_filters('mprm_straight_to_gateway_purchase_data', $purchase_data);
	}
	
	/**
	 * @param $gateway
	 *
	 * @return mixed
	 */
	function get_gateway_checkout_label($gateway) {
		$gateways = $this->get_payment_gateways();
		$label = isset($gateways[ $gateway ]) ? $gateways[ $gateway ][ 'checkout_label' ] : $gateway;
		if ($gateway == 'test_manual') {
			$label = __('Free Purchase', 'mp-restaurant-menu');
		}
		
		return apply_filters('mprm_gateway_checkout_label', $label, $gateway);
	}
	
	/**
	 * @param $gateway
	 *
	 * @return mixed
	 */
	function get_gateway_admin_label($gateway) {
		$gateways = $this->get_payment_gateways();
		$label = isset($gateways[ $gateway ]) ? $gateways[ $gateway ][ 'admin_label' ] : $gateway;
		$payment = isset($_GET[ 'id' ]) ? absint($_GET[ 'id' ]) : false;
		if ($gateway == 'test_manual' && $payment) {
			if ($this->get('payments')->get_payment_amount($payment) == 0) {
				$label = __('Free Purchase', 'mp-restaurant-menu');
			}
		}
		
		return apply_filters('mprm_gateway_admin_label', $label, $gateway);
	}
}