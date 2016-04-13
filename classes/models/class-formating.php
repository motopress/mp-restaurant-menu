<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Formatting extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function currency_decimal_filter($decimals = 2) {

		$currency = edd_get_currency();

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
	 * Sanitizes a string key for EDD Settings
	 *
	 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
	 *
	 * @since  2.5.8
	 *
	 * @param  string $key String key
	 *
	 * @return string Sanitized key
	 */
	function sanitize_key($key) {
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

}