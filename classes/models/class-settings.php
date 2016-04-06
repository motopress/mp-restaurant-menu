<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Settings extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get settings
	 *
	 * @param bool $key
	 *
	 * @return mixed|void
	 */
	public function get_settings($key = false) {
		$settings = get_option('mprm_settings');
		if (!empty($settings[$key])) {
			return $settings[$key];
		} else {
			return $settings;
		}
	}

	public function get_config_settings() {
		$settings = array('tabs' => array());
		$config_settings = $this->get_config('settings');
		$save_settings = $this->get_settings();
		foreach ($config_settings['tabs'] as $tabs => $setting) {
			$settings['tabs'][$tabs] = $setting;
		}
		return $settings;
	}

	/**
	 * Save settings
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function save_settings(array $params) {
		unset($params['controller']);
		unset($params['mprm_action']);
		$success = false;
		if (!empty($params)) {
			if ($params != get_option('mprm_settings')) {
				$success = update_option('mprm_settings', $params);
			} else {
				$success = true;
			}
		}
		return $this->get_arr($params, $success);
	}

	/**
	 * Get Currency symbol.
	 *
	 * @param string $currency (default: '')
	 *
	 * @return string
	 */
	public function get_currency_symbol($currency = '') {
		if (!$currency) {
			$currency = $this->get_settings('currency_code');
		}

		switch ($currency) {
			case 'AED' :
				$currency_symbol = 'د.إ';
				break;
			case 'AUD' :
			case 'ARS' :
			case 'CAD' :
			case 'CLP' :
			case 'COP' :
			case 'HKD' :
			case 'MXN' :
			case 'NZD' :
			case 'SGD' :
			case 'USD' :
				$currency_symbol = '&#36;';
				break;
			case 'BDT':
				$currency_symbol = '&#2547;&nbsp;';
				break;
			case 'BGN' :
				$currency_symbol = '&#1083;&#1074;.';
				break;
			case 'BRL' :
				$currency_symbol = '&#82;&#36;';
				break;
			case 'CHF' :
				$currency_symbol = '&#67;&#72;&#70;';
				break;
			case 'CNY' :
			case 'JPY' :
			case 'RMB' :
				$currency_symbol = '&yen;';
				break;
			case 'CZK' :
				$currency_symbol = '&#75;&#269;';
				break;
			case 'DKK' :
				$currency_symbol = 'DKK';
				break;
			case 'DOP' :
				$currency_symbol = 'RD&#36;';
				break;
			case 'EGP' :
				$currency_symbol = 'EGP';
				break;
			case 'EUR' :
				$currency_symbol = '&euro;';
				break;
			case 'GBP' :
				$currency_symbol = '&pound;';
				break;
			case 'HRK' :
				$currency_symbol = 'Kn';
				break;
			case 'HUF' :
				$currency_symbol = '&#70;&#116;';
				break;
			case 'IDR' :
				$currency_symbol = 'Rp';
				break;
			case 'ILS' :
				$currency_symbol = '&#8362;';
				break;
			case 'INR' :
				$currency_symbol = 'Rs.';
				break;
			case 'ISK' :
				$currency_symbol = 'Kr.';
				break;
			case 'KIP' :
				$currency_symbol = '&#8365;';
				break;
			case 'KRW' :
				$currency_symbol = '&#8361;';
				break;
			case 'MYR' :
				$currency_symbol = '&#82;&#77;';
				break;
			case 'NGN' :
				$currency_symbol = '&#8358;';
				break;
			case 'NOK' :
				$currency_symbol = '&#107;&#114;';
				break;
			case 'NPR' :
				$currency_symbol = 'Rs.';
				break;
			case 'PHP' :
				$currency_symbol = '&#8369;';
				break;
			case 'PLN' :
				$currency_symbol = '&#122;&#322;';
				break;
			case 'PYG' :
				$currency_symbol = '&#8370;';
				break;
			case 'RON' :
				$currency_symbol = 'lei';
				break;
			case 'RUB' :
				$currency_symbol = '&#1088;&#1091;&#1073;.';
				break;
			case 'SEK' :
				$currency_symbol = '&#107;&#114;';
				break;
			case 'THB' :
				$currency_symbol = '&#3647;';
				break;
			case 'TRY' :
				$currency_symbol = '&#8378;';
				break;
			case 'TWD' :
				$currency_symbol = '&#78;&#84;&#36;';
				break;
			case 'UAH' :
				$currency_symbol = '&#8372;';
				break;
			case 'VND' :
				$currency_symbol = '&#8363;';
				break;
			case 'ZAR' :
				$currency_symbol = '&#82;';
				break;
			default :
				$currency_symbol = '';
				break;
		}

		return $currency_symbol;
	}

	/**
	 * Get full list of currency codes.
	 * @return array
	 */
	function get_currencies() {
		return array(
			'AED' => __('United Arab Emirates Dirham', 'mp-restaurant-menu'),
			'ARS' => __('Argentine Peso', 'mp-restaurant-menu'),
			'AUD' => __('Australian Dollars', 'mp-restaurant-menu'),
			'BDT' => __('Bangladeshi Taka', 'mp-restaurant-menu'),
			'BRL' => __('Brazilian Real', 'mp-restaurant-menu'),
			'BGN' => __('Bulgarian Lev', 'mp-restaurant-menu'),
			'CAD' => __('Canadian Dollars', 'mp-restaurant-menu'),
			'CLP' => __('Chilean Peso', 'mp-restaurant-menu'),
			'CNY' => __('Chinese Yuan', 'mp-restaurant-menu'),
			'COP' => __('Colombian Peso', 'mp-restaurant-menu'),
			'CZK' => __('Czech Koruna', 'mp-restaurant-menu'),
			'DKK' => __('Danish Krone', 'mp-restaurant-menu'),
			'DOP' => __('Dominican Peso', 'mp-restaurant-menu'),
			'EUR' => __('Euros', 'mp-restaurant-menu'),
			'HKD' => __('Hong Kong Dollar', 'mp-restaurant-menu'),
			'HRK' => __('Croatia kuna', 'mp-restaurant-menu'),
			'HUF' => __('Hungarian Forint', 'mp-restaurant-menu'),
			'ISK' => __('Icelandic krona', 'mp-restaurant-menu'),
			'IDR' => __('Indonesia Rupiah', 'mp-restaurant-menu'),
			'INR' => __('Indian Rupee', 'mp-restaurant-menu'),
			'NPR' => __('Nepali Rupee', 'mp-restaurant-menu'),
			'ILS' => __('Israeli Shekel', 'mp-restaurant-menu'),
			'JPY' => __('Japanese Yen', 'mp-restaurant-menu'),
			'KIP' => __('Lao Kip', 'mp-restaurant-menu'),
			'KRW' => __('South Korean Won', 'mp-restaurant-menu'),
			'MYR' => __('Malaysian Ringgits', 'mp-restaurant-menu'),
			'MXN' => __('Mexican Peso', 'mp-restaurant-menu'),
			'NGN' => __('Nigerian Naira', 'mp-restaurant-menu'),
			'NOK' => __('Norwegian Krone', 'mp-restaurant-menu'),
			'NZD' => __('New Zealand Dollar', 'mp-restaurant-menu'),
			'PYG' => __('Paraguayan Guaraní', 'mp-restaurant-menu'),
			'PHP' => __('Philippine Pesos', 'mp-restaurant-menu'),
			'PLN' => __('Polish Zloty', 'mp-restaurant-menu'),
			'GBP' => __('Pounds Sterling', 'mp-restaurant-menu'),
			'RON' => __('Romanian Leu', 'mp-restaurant-menu'),
			'RUB' => __('Russian Ruble', 'mp-restaurant-menu'),
			'SGD' => __('Singapore Dollar', 'mp-restaurant-menu'),
			'ZAR' => __('South African rand', 'mp-restaurant-menu'),
			'SEK' => __('Swedish Krona', 'mp-restaurant-menu'),
			'CHF' => __('Swiss Franc', 'mp-restaurant-menu'),
			'TWD' => __('Taiwan New Dollars', 'mp-restaurant-menu'),
			'THB' => __('Thai Baht', 'mp-restaurant-menu'),
			'TRY' => __('Turkish Lira', 'mp-restaurant-menu'),
			'UAH' => __('Ukrainian Hryvnia', 'mp-restaurant-menu'),
			'USD' => __('US Dollars', 'mp-restaurant-menu'),
			'VND' => __('Vietnamese Dong', 'mp-restaurant-menu'),
			'EGP' => __('Egyptian Pound', 'mp-restaurant-menu')
		);
	}

}
