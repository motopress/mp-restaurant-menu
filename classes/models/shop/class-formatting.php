<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Formatting
 * @package mp_restaurant_menu\classes\models
 */
class Formatting extends Model {
	protected static $instance;

	/**
	 * @return Formatting
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param int $decimals
	 *
	 * @return mixed
	 */
	public function currency_decimal_filter($decimals = 2) {
		$currency = $this->get('settings')->get_currency();
		switch ($currency) {
			case 'RIAL' :
			case 'JPY' :
			case 'TWD' :
			case 'HUF' :
				$decimals = 0;
				break;
		}
		return apply_filters('mprm_currency_decimal_count', $decimals, $currency);
	}

	/**
	 * Sanitizes a string key
	 *
	 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
	 *
	 * @since  2.5.8
	 *
	 * @param  string $key String key
	 *
	 * @return string Sanitized key
	 */
	public function sanitize_key($key) {
		$raw_key = $key;
		$key = preg_replace('/[^a-zA-Z0-9_\-\.\:\/]/', '', $key);
		/**
		 * Filter a sanitized key string.
		 *
		 * @since 2.5.8
		 *
		 * @param string $key Sanitized key.
		 * @param string $raw_key The key prior to sanitization.
		 */
		return apply_filters('mprm_sanitize_key', $key, $raw_key);
	}

	/**
	 * Sanitize amount
	 *
	 * @param $amount
	 *
	 * @return mixed
	 */
	public function sanitize_amount($amount) {
		$is_negative = false;
		$thousands_sep = $this->get('settings')->get_option('thousands_separator', ',');
		$decimal_sep = $this->get('settings')->get_option('decimal_separator', '.');
		// Sanitize the amount
		if ($decimal_sep == ',' && false !== ($found = strpos($amount, $decimal_sep))) {
			if (($thousands_sep == '.' || $thousands_sep == ' ') && false !== ($found = strpos($amount, $thousands_sep))) {
				$amount = str_replace($thousands_sep, '', $amount);
			} elseif (empty($thousands_sep) && false !== ($found = strpos($amount, '.'))) {
				$amount = str_replace('.', '', $amount);
			}
			$amount = str_replace($decimal_sep, '.', $amount);
		} elseif ($thousands_sep == ',' && false !== ($found = strpos($amount, $thousands_sep))) {
			$amount = str_replace($thousands_sep, '', $amount);
		}
		if ($amount < 0) {
			$is_negative = true;
		}
		$amount = preg_replace('/[^0-9\.]/', '', $amount);
		/**
		 * Filter number of decimals to use for prices
		 *
		 * @since unknown
		 *
		 * @param int $number Number of decimals
		 * @param int|string $amount Price
		 */
		$decimals = apply_filters('mprm_sanitize_amount_decimals', 2, $amount);
		$amount = number_format((double)$amount, $decimals, '.', '');
		if ($is_negative) {
			$amount *= -1;
		}
		/**
		 * Filter the sanitized price before returning
		 *
		 * @since unknown
		 *
		 * @param string $amount Price
		 */
		return apply_filters('mprm_sanitize_amount', $amount);
	}

	/**
	 * Formatting amount $decimals(true/false)
	 *
	 * @param $amount
	 * @param bool $decimals
	 *
	 * @return mixed
	 */
	public function format_amount($amount, $decimals = true) {
		$thousands_sep = $this->get('settings')->get_option('thousands_separator', ',');
		$decimal_sep = $this->get('settings')->get_option('decimal_separator', '.');
		$number_decimals = $this->get('settings')->get_option('number_decimals', '');

		if (!is_numeric($amount)) {
			$amount = 0;
		}

		//Format the amount
		if ($decimal_sep == ',' && false !== ($sep_found = strpos($amount, $decimal_sep))) {
			$whole = substr($amount, 0, $sep_found);
			$part = substr($amount, $sep_found + 1, (strlen($amount) - 1));
			$amount = $whole . '.' . $part;
		}

		// Strip , from the amount (if set as the thousands separator)
		if ($thousands_sep == ',' && false !== ($found = strpos($amount, $thousands_sep))) {
			$amount = str_replace(',', '', $amount);
		}

		// Strip ' ' from the amount (if set as the thousands separator)
		if ($thousands_sep == ' ' && false !== ($found = strpos($amount, $thousands_sep))) {
			$amount = str_replace(' ', '', $amount);
		}

		if (empty($amount)) {
			$amount = 0;
		}

		$decimals = apply_filters('mprm_format_amount_decimals', $decimals ? (int)$number_decimals : 0, $amount);

		$formatted = number_format($amount, $decimals, $decimal_sep, $thousands_sep);
		return apply_filters('mprm_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep);
	}
}