<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Checkout extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function is_checkout() {
	}

	public function can_checkout() {
	}

	public function get_success_page_uri() {
	}

	public function is_success_page() {
	}

	public function send_to_success_page() {
	}

	public function get_checkout_uri() {
		$uri = $this->get('settings')->get_option('purchase_page', false);
		$uri = isset($uri) ? get_permalink($uri) : NULL;

		if (!empty($args)) {
			// Check for backward compatibility
			if (is_string($args))
				$args = str_replace('?', '', $args);

			$args = wp_parse_args($args);

			$uri = add_query_arg($args, $uri);
		}

		$scheme = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? 'https' : 'admin';

		$ajax_url = admin_url('admin-ajax.php', $scheme);

		if ((!preg_match('/^https/', $uri) && preg_match('/^https/', $ajax_url) && $this->get('settings')->is_ajax_enabled()) || $this->get('settings')->is_ssl_enforced()) {
			$uri = preg_replace('/^http:/', 'https:', $uri);
		}

		if ($this->get('settings')->get_option('no_cache_checkout', false)) {
			$uri = $this->get('settings')->add_cache_busting($uri);
		}

		return apply_filters('mprm_get_checkout_uri', $uri);
	}

	public function send_back_to_checkout() {
	}

	public function get_success_page_url() {
	}

	public function get_failed_transaction_uri() {
	}

	public function is_failed_transaction_page() {
	}

	public function listen_for_failed_payments() {
	}

	public function validate_card_number_format($number = 0) {

		$number = trim($number);
		if (empty($number)) {
			return false;
		}

		if (!is_numeric($number)) {
			return false;
		}

		$is_valid_format = false;

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
			$digit = $number[$i];

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
	function detect_cc_type($number) {

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

			if (preg_match($card_type['pattern'], $number)) {

				$number_length = strlen($number);
				if (in_array($number_length, $card_type['valid_length'])) {
					$return = $card_type['name'];
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
	function purchase_form_validate_cc_exp_date($exp_month, $exp_year) {

		$month_name = date('M', mktime(0, 0, 0, $exp_month, 10));
		$expiration = strtotime(date('t', strtotime($month_name . ' ' . $exp_year)) . ' ' . $month_name . ' ' . $exp_year . ' 11:59:59PM');

		return $expiration >= time();
	}

	function straight_to_checkout() {
		$ret = $this->get('settings')->get_option('redirect_on_add', false);
		return (bool)apply_filters('mprm_straight_to_checkout', $ret);
	}

	function enforced_ssl_asset_filter($content) {

		if (is_array($content)) {

			$content = array_map('edd_enforced_ssl_asset_filter', $content);

		} else {

			// Detect if URL ends in a common domain suffix. We want to only affect assets
			$extension = untrailingslashit($this->get('settings')->get_file_extension($content));
			$suffixes = array(
				'br',
				'ca',
				'cn',
				'com',
				'de',
				'dev',
				'edu',
				'fr',
				'in',
				'info',
				'jp',
				'local',
				'mobi',
				'name',
				'net',
				'nz',
				'org',
				'ru',
			);

			if (!in_array($extension, $suffixes)) {

				$content = str_replace('http:', 'https:', $content);

			}

		}

		return $content;
	}
}
