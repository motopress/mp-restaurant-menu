<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Checkout
 * @package mp_restaurant_menu\classes\models
 */
class Checkout extends Model {
	protected static $instance;
	
	/**
	 * @return Checkout
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
	public function is_checkout() {
		global $wp_query;
		$is_object_set = isset($wp_query->queried_object);
		$is_object_id_set = isset($wp_query->queried_object_id);
		$is_checkout = is_page($this->get('settings')->get_option('purchase_page'));
		if (!$is_object_set) {
			unset($wp_query->queried_object);
		}
		if (!$is_object_id_set) {
			unset($wp_query->queried_object_id);
		}
		
		return apply_filters('mprm_is_checkout', $is_checkout);
	}
	
	/**
	 * @return bool
	 */
	public function can_checkout() {
		$can_checkout = true;
		
		return (bool)apply_filters('mprm_can_checkout', $can_checkout);
	}
	
	/**
	 * @return mixed
	 */
	public function is_success_page() {
		$is_success_page = $this->get('settings')->get_option('success_page', false);
		$is_success_page = isset($is_success_page) ? is_page($is_success_page) : false;
		
		return apply_filters('mprm_is_success_page', $is_success_page);
	}
	
	/**
	 * Send to success page
	 *
	 * @param null $query_string
	 */
	public function send_to_success_page($query_string = null) {
		$redirect = $this->get_success_page_uri();
		
		if ($query_string) {
			$redirect .= $query_string;
		}
		
		$gateway = isset($_REQUEST[ 'mprm-gateway' ]) ? $_REQUEST[ 'mprm-gateway' ] : '';
		if (!headers_sent()) {
			wp_redirect(apply_filters('mprm_success_page_redirect', $redirect, $gateway, $query_string));
			$this->get('misc')->mprm_die();
		}
	}
	
	/**
	 * Get success page uri
	 *
	 * @return mixed
	 */
	public function get_success_page_uri() {
		$page_id = $this->get('settings')->get_option('success_page', 0);
		$page_id = absint($page_id);
		
		return apply_filters('mprm_get_success_page_uri', get_permalink($page_id));
	}
	
	/**
	 * Send back to checkout
	 *
	 * @param array $args
	 */
	public function send_back_to_checkout($args = array()) {
		$redirect = $this->get_checkout_uri();
		if (!empty($args)) {
			// Check for backward compatibility
			if (is_string($args))
				$args = str_replace('?', '', $args);
			$args = wp_parse_args($args);
			$redirect = add_query_arg($args, $redirect);
		}
		wp_redirect(apply_filters('mprm_send_back_to_checkout', $redirect, $args));
	}
	
	/**
	 * @return mixed
	 */
	public function get_checkout_uri() {
		$purchase_page_id = $this->get('settings')->get_option('purchase_page', false);
		$uri = !empty($purchase_page_id) ? get_permalink((int)$purchase_page_id) : NULL;
		
		if (!empty($args)) {
			// Check for backward compatibility
			if (is_string($args))
				$args = str_replace('?', '', $args);
			$args = wp_parse_args($args);
			$uri = add_query_arg($args, $uri);
		}
		
		$scheme = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? 'https' : 'admin';
		$ajax_url = admin_url('admin-ajax.php', $scheme);
		if ( ( !preg_match('/^https/', $uri) && preg_match('/^https/', $ajax_url) && !$this->get('settings')->is_ajax_disabled() ) || $this->get('settings')->is_ssl_enforced()) {
			$uri = preg_replace('/^http:/', 'https:', $uri);
		}
		if ($this->get('settings')->get_option('no_cache_checkout', false)) {
			$uri = $this->get('settings')->add_cache_busting($uri);
		}
		
		return apply_filters('mprm_get_checkout_uri', $uri);
	}
	
	/**
	 * @param null $query_string
	 *
	 * @return mixed
	 */
	public function get_success_page_url($query_string = null) {
		$success_page = $this->get('settings')->get_option('success_page', 0);
		$success_page = get_permalink($success_page);
		if ($query_string)
			$success_page .= $query_string;
		
		return apply_filters('mprm_success_page_url', $success_page);
	}
	
	/**
	 * @param bool $extras
	 *
	 * @return mixed
	 */
	public function get_failed_transaction_uri($extras = false) {
		$uri = $this->get('settings')->get_option('failure_page', '');
		$uri = !empty($uri) ? trailingslashit(get_permalink($uri)) : home_url();
		if ($extras)
			$uri .= $extras;
		
		return apply_filters('mprm_get_failed_transaction_uri', $uri);
	}
	
	/**
	 * Check failed transaction
	 *
	 * @return mixed
	 */
	public function is_failed_transaction_page() {
		$ret = $this->get('settings')->get_option('failure_page', false);
		$ret = isset($ret) ? is_page($ret) : false;
		
		return apply_filters('mprm_is_failure_page', $ret);
	}
	
	/**
	 *  Listen for failed payments
	 */
	public function listen_for_failed_payments() {
		$failed_page = $this->get('settings')->get_option('failure_page', 0);
		if (!empty($failed_page) && is_page($failed_page) && !empty($_GET[ 'payment-id' ])) {
			$payment_id = absint($_GET[ 'payment-id' ]);
			$payment = get_post($payment_id);
			$status = $this->get('payments')->get_payment_status($payment);
			if ($status && 'mprm-pending' === strtolower($status)) {
				$this->get('payments')->update_payment_status($payment_id, 'mprm-failed');
			}
		}
	}
	
	/**
	 * @param int $number
	 *
	 * @return bool|mixed
	 */
	public function validate_card_number_format($number = 0) {
		$number = trim($number);
		if (empty($number)) {
			return false;
		}
		if (!is_numeric($number)) {
			return false;
		}
		// First check if it passes with the passed method, Luhn by default
		$is_valid_format = $this->validate_card_number_format_luhn($number);
		// Run additional checks before we start the regexing and looping by type
		$is_valid_format = apply_filters('mprm_valiate_card_format_pre_type', $is_valid_format, $number);
		if (true === $is_valid_format) {
			// We've passed our method check, onto card specific checks
			$card_type = $this->detect_cc_type($number);
			$is_valid_format = !empty($card_type) ? true : false;
		}
		
		return apply_filters('mprm_cc_is_valid_format', $is_valid_format, $number);
	}
	
	/**
	 * Validate credit card number based on the luhn algorithm
	 *
	 * @since  2.4
	 *
	 * @param string $number
	 *
	 * @return bool
	 */
	public function validate_card_number_format_luhn($number) {
		// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
		$number = preg_replace('/\D/', '', $number);
		// Set the string length and parity
		$length = strlen($number);
		$parity = $length % 2;
		// Loop through each digit and do the math
		$total = 0;
		for ($i = 0; $i < $length; $i++) {
			$digit = $number[ $i ];
			// Multiply alternate digits by two
			if ($i % 2 == $parity) {
				$digit *= 2;
				// If the sum is two digits, add them together (in effect)
				if ($digit > 9) {
					$digit -= 9;
				}
			}
			// Total up the digits
			$total += $digit;
		}
		
		// If the total mod 10 equals 0, the number is valid
		return ($total % 10 == 0) ? true : false;
	}
	
	/**
	 * Detect credit card type based on the number and return an
	 * array of data to validate the credit card number
	 *
	 * @since  2.4
	 *
	 * @param string $number
	 *
	 * @return string|bool
	 */
	public function detect_cc_type($number) {
		$return = false;
		$card_types = array(
			array(
				'name' => 'amex',
				'pattern' => '/^3[4|7]/',
				'valid_length' => array(15),
			),
			array(
				'name' => 'diners_club_carte_blanche',
				'pattern' => '/^30[0-5]/',
				'valid_length' => array(14),
			),
			array(
				'name' => 'diners_club_international',
				'pattern' => '/^36/',
				'valid_length' => array(14),
			),
			array(
				'name' => 'jcb',
				'pattern' => '/^35(2[89]|[3-8][0-9])/',
				'valid_length' => array(16),
			),
			array(
				'name' => 'laser',
				'pattern' => '/^(6304|670[69]|6771)/',
				'valid_length' => array(16, 17, 18, 19),
			),
			array(
				'name' => 'visa_electron',
				'pattern' => '/^(4026|417500|4508|4844|491(3|7))/',
				'valid_length' => array(16),
			),
			array(
				'name' => 'visa',
				'pattern' => '/^4/',
				'valid_length' => array(16),
			),
			array(
				'name' => 'mastercard',
				'pattern' => '/^5[1-5]/',
				'valid_length' => array(16),
			),
			array(
				'name' => 'maestro',
				'pattern' => '/^(5018|5020|5038|6304|6759|676[1-3])/',
				'valid_length' => array(12, 13, 14, 15, 16, 17, 18, 19),
			),
			array(
				'name' => 'discover',
				'pattern' => '/^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/',
				'valid_length' => array(16),
			),
		);
		$card_types = apply_filters('mprm_cc_card_types', $card_types);
		if (!is_array($card_types)) {
			return false;
		}
		foreach ($card_types as $card_type) {
			if (preg_match($card_type[ 'pattern' ], $number)) {
				$number_length = strlen($number);
				if (in_array($number_length, $card_type[ 'valid_length' ])) {
					$return = $card_type[ 'name' ];
					break;
				}
			}
		}
		
		return apply_filters('mprm_cc_found_card_type', $return, $number, $card_types);
	}
	
	/**
	 * Validate credit card expiration date
	 *
	 * @since  2.4
	 *
	 * @param string $exp_month
	 * @param string $exp_year
	 *
	 * @return bool
	 */
	public function purchase_form_validate_cc_exp_date($exp_month, $exp_year) {
		$month_name = date('M', mktime(0, 0, 0, $exp_month, 10));
		$expiration = strtotime(date('t', strtotime($month_name . ' ' . $exp_year)) . ' ' . $month_name . ' ' . $exp_year . ' 11:59:59PM');
		
		return $expiration >= time();
	}
	
	/**
	 * @return bool
	 */
	public function straight_to_checkout() {
		$ret = $this->get('settings')->get_option('redirect_on_add', false);
		
		return (bool)apply_filters('mprm_straight_to_checkout', $ret);
	}
	
	/**
	 * @param $content
	 *
	 * @return array|mixed
	 */
	public function enforced_ssl_asset_filter($content) {
		if (is_array($content)) {
			$content = array_map(array($this, 'enforced_ssl_asset_filter'), $content);
		} else {
			// Detect if URL ends in a common domain suffix. We want to only affect assets
			$extension = untrailingslashit($this->get('settings')->get_file_extension($content));
			$suffixes = array('br', 'ca', 'cn', 'com', 'de', 'dev', 'edu', 'fr', 'in', 'info', 'jp', 'local', 'mobi', 'name', 'net', 'nz', 'org', 'ru');
			if (!in_array($extension, $suffixes)) {
				$content = str_replace('http:', 'https:', $content);
			}
		}
		
		return $content;
	}
	
	/**
	 * @return mixed
	 */
	public function is_purchase_history_page() {
		$ret = $this->get('settings')->get_option('purchase_history_page', false);
		$ret = $ret ? is_page($ret) : false;
		
		return apply_filters('mprm_is_purchase_history_page', $ret);
	}
	
	/**
	 * @param string $field
	 *
	 * @return bool
	 */
	public function field_is_required($field = '') {
		$required_fields = $this->purchase_form_required_fields();
		
		return array_key_exists($field, $required_fields);
	}
	
	/**
	 * @return mixed
	 */
	public function purchase_form_required_fields() {
		$required_fields = array(
			'mprm_email' => array(
				'error_id' => 'invalid_email',
				'error_message' => __('Please enter a valid email address', 'mp-restaurant-menu')
			),
			'mprm_first' => array(
				'error_id' => 'invalid_first_name',
				'error_message' => __('Please enter your first name', 'mp-restaurant-menu')
			)
		);
		// Let payment gateways and other extensions determine if address fields should be required
		$require_address = apply_filters('mprm_require_billing_address', $this->get('taxes')->use_taxes() && $this->get('cart')->get_cart_total());
		// $this->get('settings')->get_option('taxes_cc_form', false) later
		if ($require_address && $this->get('settings')->get_option('taxes_cc_form', false)) {
			$required_fields[ 'card_zip' ] = array(
				'error_id' => 'invalid_zip_code',
				'error_message' => __('Please enter your zip / postal code', 'mp-restaurant-menu')
			);
			$required_fields[ 'card_city' ] = array(
				'error_id' => 'invalid_city',
				'error_message' => __('Please enter your billing city', 'mp-restaurant-menu')
			);
			$required_fields[ 'billing_country' ] = array(
				'error_id' => 'invalid_country',
				'error_message' => __('Please select your billing country', 'mp-restaurant-menu')
			);
			$required_fields[ 'card_state' ] = array(
				'error_id' => 'invalid_state',
				'error_message' => __('Please enter billing state / province', 'mp-restaurant-menu')
			);
		}
		
		return apply_filters('mprm_purchase_form_required_fields', $required_fields);
	}
}
