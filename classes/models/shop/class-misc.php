<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Capabilities;
use mp_restaurant_menu\classes\Model;

class Misc extends Model {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_current_page_url($nocache = false) {
		global $wp;
		if (get_option('permalink_structure')) {
			$base = trailingslashit(home_url($wp->request));
		} else {
			$base = add_query_arg($wp->query_string, '', trailingslashit(home_url($wp->request)));
			$base = remove_query_arg(array('post_type', 'name'), $base);
		}
		$scheme = is_ssl() ? 'https' : 'http';
		$uri = set_url_scheme($base, $scheme);
		if (is_front_page()) {
			$uri = home_url('/');
		} elseif ($this->get('checkout')->is_checkout(array(), false)) {
			$uri = $this->get('checkout')->get_checkout_uri();
		}
		$uri = apply_filters('mprm_get_current_page_url', $uri);
		if ($nocache) {
			$uri = $this->add_cache_busting($uri);
		}
		return $uri;
	}

	/**
	 * Adds the 'nocache' parameter to the provided URL
	 *
	 * @since  2.4.4
	 *
	 * @param  string $url The URL being requested
	 *
	 * @return string      The URL with cache busting added or not
	 */
	public function add_cache_busting($url = '') {
		$no_cache_checkout = $this->get('settings')->get_option('no_cache_checkout', false);
		if (Capabilities::get_instance()->is_caching_plugin_active() || ($this->get('checkout')->is_checkout() && $no_cache_checkout)) {
			$url = add_query_arg('nocache', 'true', $url);
		}
		return $url;
	}

	public function is_test_mode() {
		$ret = $this->get('settings')->get_option('test_mode', false);
		return (bool)apply_filters('mprm_is_test_mode', $ret);
	}

	public function no_guest_checkout() {
		$ret = $this->get('settings')->get_option('logged_in_only', false);
		return (bool)apply_filters('mprm_no_guest_checkout', $ret);
	}

	public function get_ip() {
		$ip = '127.0.0.1';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters('mprm_get_ip', $ip);
	}

	public function get_currency_name($code = 'USD') {
		$currencies = $this->get('settings')->get_currencies();
		$name = isset($currencies[$code]) ? $currencies[$code] : $code;
		return apply_filters('mprm_currency_name', $name);
	}

	public function mprm_die_handler() {
		die();
	}

	public function mprm_die($message = '', $title = '', $status = 400) {
		add_filter('wp_die_ajax_handler', array($this, 'mprm_die_handler'), 10, 3);
		add_filter('wp_die_handler', array($this, 'mprm_die_handler'), 10, 3);
		wp_die($message, $title, array('response' => $status));
	}

	public function use_skus() {
		$ret = $this->get('settings')->get_option('enable_skus', false);
		return (bool)apply_filters('mprm_use_skus', $ret);
	}

	public function can_view_receipt($payment_key = '') {
		global $mprm_receipt_args;
		$return = false;
		if (empty($payment_key)) {
			return $return;
		}
		$mprm_receipt_args['id'] = $this->get('payments')->get_purchase_id_by_key($payment_key);
		$user_id = (int)$this->get('payments')->get_payment_user_id($mprm_receipt_args['id']);
		$payment_meta = $this->get('payments')->get_payment_meta($mprm_receipt_args['id']);
		if (is_user_logged_in()) {
			if ($user_id === (int)get_current_user_id()) {
				$return = true;
			} elseif (wp_get_current_user()->user_email === $this->get('payments')->get_payment_user_email($mprm_receipt_args['id'])) {
				$return = true;
			} elseif (current_user_can('view_shop_sensitive_data')) {
				$return = true;
			}
		}
		$session = $this->get('session')->get_session_by_key('mprm_purchase');
		if (!empty($session) && !is_user_logged_in()) {
			if ($session['purchase_key'] === $payment_meta['key']) {
				$return = true;
			}
		}
		return (bool)apply_filters('mprm_can_view_receipt', $return, $payment_key);
	}

	public function get_php_arg_separator_output() {
		return ini_get('arg_separator.output');
	}
}