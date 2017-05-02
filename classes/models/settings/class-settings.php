<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Capabilities;
use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\View;

/**
 * Class Settings
 * @package mp_restaurant_menu\classes\models
 */
class Settings extends Model {
	
	protected static $instance;
	
	/**
	 * @return array
	 */
	public function get_config_settings() {
		$settings = array('tabs' => array());
		$config_settings = $this->get_config('settings');
		foreach ($config_settings[ 'tabs' ] as $tabs => $setting) {
			$settings[ 'tabs' ][ $tabs ] = $setting;
		}
		
		return $settings;
	}
	
	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function save_settings(array $params) {
		unset($params[ 'controller' ]);
		unset($params[ 'mprm_action' ]);
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
	 * Currency
	 *
	 * @default USD
	 *
	 * @return mixed
	 */
	public function get_currency() {
		$currency = $this->get_option('currency', 'USD');
		
		return apply_filters('mprm_currency', $currency);
	}
	
	/**
	 * @param string $key
	 * @param bool $default
	 *
	 * @return mixed
	 */
	public function get_option($key = '', $default = false) {
		global $mprm_options;
		
		if (empty($mprm_options)) {
			$mprm_options = Settings::get_instance()->get_settings();
		}
		$value = ! empty( $mprm_options[ $key ] ) ? $mprm_options[ $key ] : $default;
		$value = apply_filters('mprm_get_option', $value, $key, $default);

		return apply_filters('mprm_get_option_' . $key, $value, $key, $default);
	}
	
	/**
	 * Get settings
	 *
	 * @param bool $key
	 *
	 * @return mixed
	 */
	public function get_settings($key = false) {
		$settings = get_option('mprm_settings');
		if (empty($settings)) {
			$default_settings =
				array(
					
					'currency' => 'USD',
					'template_mode' => 'theme',
					'customer_phone' => '1',
					'gateways' =>
						array(
							'paypal' => '1',
							'manual' => '1',
						),
					'item_quantities' => '1',
					'shipping_address' => '1',
					'enable_ajax_cart' => '1',
					'default_gateway' => 'manual',
					'checkout_color' => 'inherit',
					'checkout_padding' => 'mprm-inherit',
					'currency_position' => 'before',
					'thousands_separator' => ',',
					'decimal_separator' => '.',
					'number_decimals' => '2',
					'accepted_cards' => array(
						'mastercard' => 'Mastercard',
						'visa' => 'Visa',
						'americanexpress' => 'American Express',
						'discover' => 'Discover',
						'paypal' => 'PayPal',
					),
					'checkout_include_tax' => 'no',
					'checkout_label' => __('Purchase', 'mp-restaurant-menu'),
					'add_to_cart_text' => __('Add to Cart', 'mp-restaurant-menu'),
					'buy_now_text' => __('Buy Now', 'mp-restaurant-menu'),
					'display_taxonomy' => 'default',
					'taxonomy_grid' => array(
						'col' => '3',
						'categ_name' => 'only_text',
						'feat_img' => '1',
						'ingredients' => '1',
						'link_item' => '1',
					),
					'taxonomy_list' => array(
						'col' => '2',
						'categ_name' => 'only_text',
						'feat_img' => '1',
						'ingredients' => '1',
						'link_item' => '1',
					),
					'taxonomy_simple_list' => array(
						'col' => '1',
						'price_pos' => 'points',
						'categ_name' => 'only_text',
						'price' => '1',
						'link_item' => '1',
					)
				);
			
			// Update old settings with new single option
			$general_settings = is_array(get_option('mprm_settings_general')) ? get_option('mprm_settings_general') : array();
			$gateway_settings = is_array(get_option('mprm_settings_gateways')) ? get_option('mprm_settings_gateways') : array();
			$email_settings = is_array(get_option('mprm_settings_emails')) ? get_option('mprm_settings_emails') : array();
			$style_settings = is_array(get_option('mprm_settings_styles')) ? get_option('mprm_settings_styles') : array();
			$tax_settings = is_array(get_option('mprm_settings_taxes')) ? get_option('mprm_settings_taxes') : array();
			$ext_settings = is_array(get_option('mprm_settings_extensions')) ? get_option('mprm_settings_extensions') : array();
			$license_settings = is_array(get_option('mprm_settings_licenses')) ? get_option('mprm_settings_licenses') : array();
			$misc_settings = is_array(get_option('mprm_settings_misc')) ? get_option('mprm_settings_misc') : array();
			$settings = array_merge($general_settings, $gateway_settings, $email_settings, $style_settings, $tax_settings, $ext_settings, $license_settings, $misc_settings, $default_settings);
			
			update_option('mprm_settings', $settings);
		}
		if (!empty($settings[ $key ])) {
			return $settings[ $key ];
		} else {
			return $settings;
		}
	}
	
	/**
	 * Get instance
	 * @return Settings
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Currencies with currency symbol
	 *
	 * @return mixed
	 */
	public function get_currencies_with_symbols() {
		$currencies = $this->get_currencies();
		foreach ($currencies as $key => $currency) {
			$currencies[ $key ] = $currency . " ({$this->get_currency_symbol($key)})";
		}
		
		return apply_filters('mprm_currencies_with_symbols', $currencies);
	}
	
	/**
	 * Get list currencies
	 *
	 * @return mixed
	 */
	public function get_currencies() {
		$currencies = array(
			'AED' => 'United Arab Emirates dirham',
			'AFN' => 'Afghan afghani',
			'ALL' => 'Albanian lek',
			'AMD' => 'Armenian dram',
			'ANG' => 'Netherlands Antillean guilder',
			'AOA' => 'Angolan kwanza',
			'ARS' => 'Argentine peso',
			'AUD' => 'Australian dollar',
			'AWG' => 'Aruban florin',
			'AZN' => 'Azerbaijani manat',
			'BAM' => 'Bosnia and Herzegovina convertible mark',
			'BBD' => 'Barbadian dollar',
			'BDT' => 'Bangladeshi taka',
			'BGN' => 'Bulgarian lev',
			'BHD' => 'Bahraini dinar',
			'BIF' => 'Burundian franc',
			'BMD' => 'Bermudian dollar',
			'BND' => 'Brunei dollar',
			'BOB' => 'Bolivian boliviano',
			'BRL' => 'Brazilian real',
			'BSD' => 'Bahamian dollar',
			'BTC' => 'Bitcoin',
			'BTN' => 'Bhutanese ngultrum',
			'BWP' => 'Botswana pula',
			'BYR' => 'Belarusian ruble',
			'BZD' => 'Belize dollar',
			'CAD' => 'Canadian dollar',
			'CDF' => 'Congolese franc',
			'CHF' => 'Swiss franc',
			'CLP' => 'Chilean peso',
			'CNY' => 'Chinese yuan',
			'COP' => 'Colombian peso',
			'CRC' => 'Costa Rican col&oacute;n',
			'CUC' => 'Cuban convertible peso',
			'CUP' => 'Cuban peso',
			'CVE' => 'Cape Verdean escudo',
			'CZK' => 'Czech koruna',
			'DJF' => 'Djiboutian franc',
			'DKK' => 'Danish krone',
			'DOP' => 'Dominican peso',
			'DZD' => 'Algerian dinar',
			'EGP' => 'Egyptian pound',
			'ERN' => 'Eritrean nakfa',
			'ETB' => 'Ethiopian birr',
			'EUR' => 'Euro',
			'FJD' => 'Fijian dollar',
			'FKP' => 'Falkland Islands pound',
			'GBP' => 'Pound sterling',
			'GEL' => 'Georgian lari',
			'GGP' => 'Guernsey pound',
			'GHS' => 'Ghana cedi',
			'GIP' => 'Gibraltar pound',
			'GMD' => 'Gambian dalasi',
			'GNF' => 'Guinean franc',
			'GTQ' => 'Guatemalan quetzal',
			'GYD' => 'Guyanese dollar',
			'HKD' => 'Hong Kong dollar',
			'HNL' => 'Honduran lempira',
			'HRK' => 'Croatian kuna',
			'HTG' => 'Haitian gourde',
			'HUF' => 'Hungarian forint',
			'IDR' => 'Indonesian rupiah',
			'ILS' => 'Israeli new shekel',
			'IMP' => 'Manx pound',
			'INR' => 'Indian rupee',
			'IQD' => 'Iraqi dinar',
			'IRR' => 'Iranian rial',
			'ISK' => 'Icelandic kr&oacute;na',
			'JEP' => 'Jersey pound',
			'JMD' => 'Jamaican dollar',
			'JOD' => 'Jordanian dinar',
			'JPY' => 'Japanese yen',
			'KES' => 'Kenyan shilling',
			'KGS' => 'Kyrgyzstani som',
			'KHR' => 'Cambodian riel',
			'KMF' => 'Comorian franc',
			'KPW' => 'North Korean won',
			'KRW' => 'South Korean won',
			'KWD' => 'Kuwaiti dinar',
			'KYD' => 'Cayman Islands dollar',
			'KZT' => 'Kazakhstani tenge',
			'LAK' => 'Lao kip',
			'LBP' => 'Lebanese pound',
			'LKR' => 'Sri Lankan rupee',
			'LRD' => 'Liberian dollar',
			'LSL' => 'Lesotho loti',
			'LYD' => 'Libyan dinar',
			'MAD' => 'Moroccan dirham',
			'MDL' => 'Moldovan leu',
			'MGA' => 'Malagasy ariary',
			'MKD' => 'Macedonian denar',
			'MMK' => 'Burmese kyat',
			'MNT' => 'Mongolian t&ouml;gr&ouml;g',
			'MOP' => 'Macanese pataca',
			'MRO' => 'Mauritanian ouguiya',
			'MUR' => 'Mauritian rupee',
			'MVR' => 'Maldivian rufiyaa',
			'MWK' => 'Malawian kwacha',
			'MXN' => 'Mexican peso',
			'MYR' => 'Malaysian ringgit',
			'MZN' => 'Mozambican metical',
			'NAD' => 'Namibian dollar',
			'NGN' => 'Nigerian naira',
			'NIO' => 'Nicaraguan c&oacute;rdoba',
			'NOK' => 'Norwegian krone',
			'NPR' => 'Nepalese rupee',
			'NZD' => 'New Zealand dollar',
			'OMR' => 'Omani rial',
			'PAB' => 'Panamanian balboa',
			'PEN' => 'Peruvian nuevo sol',
			'PGK' => 'Papua New Guinean kina',
			'PHP' => 'Philippine peso',
			'PKR' => 'Pakistani rupee',
			'PLN' => 'Polish z&#x142;oty',
			'PRB' => 'Transnistrian ruble',
			'PYG' => 'Paraguayan guaran&iacute;',
			'QAR' => 'Qatari riyal',
			'RON' => 'Romanian leu',
			'RSD' => 'Serbian dinar',
			'RUB' => 'Russian ruble',
			'RWF' => 'Rwandan franc',
			'SAR' => 'Saudi riyal',
			'SBD' => 'Solomon Islands dollar',
			'SCR' => 'Seychellois rupee',
			'SDG' => 'Sudanese pound',
			'SEK' => 'Swedish krona',
			'SGD' => 'Singapore dollar',
			'SHP' => 'Saint Helena pound',
			'SLL' => 'Sierra Leonean leone',
			'SOS' => 'Somali shilling',
			'SRD' => 'Surinamese dollar',
			'SSP' => 'South Sudanese pound',
			'STD' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra',
			'SYP' => 'Syrian pound',
			'SZL' => 'Swazi lilangeni',
			'THB' => 'Thai baht',
			'TJS' => 'Tajikistani somoni',
			'TMT' => 'Turkmenistan manat',
			'TND' => 'Tunisian dinar',
			'TOP' => 'Tongan pa&#x2bb;anga',
			'TRY' => 'Turkish lira',
			'TTD' => 'Trinidad and Tobago dollar',
			'TWD' => 'New Taiwan dollar',
			'TZS' => 'Tanzanian shilling',
			'UAH' => 'Ukrainian hryvnia',
			'UGX' => 'Ugandan shilling',
			'USD' => 'United States dollar',
			'UYU' => 'Uruguayan peso',
			'UZS' => 'Uzbekistani som',
			'VEF' => 'Venezuelan bol&iacute;var',
			'VND' => 'Vietnamese &#x111;&#x1ed3;ng',
			'VUV' => 'Vanuatu vatu',
			'WST' => 'Samoan t&#x101;l&#x101;',
			'XAF' => 'Central African CFA franc',
			'XCD' => 'East Caribbean dollar',
			'XOF' => 'West African CFA franc',
			'XPF' => 'CFP franc',
			'YER' => 'Yemeni rial',
			'ZAR' => 'South African rand',
			'ZMW' => 'Zambian kwacha',
		);
		
		return apply_filters('mprm_currencies', $currencies);
	}
	
	/**
	 * Currency symbol
	 *
	 * @param string $currency
	 *
	 * @return string
	 */
	public function get_currency_symbol($currency = '') {
		if (!$currency) {
			$currency = $this->get_settings('currency_code');
		}
		$currency_symbol_array = array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => '&fnof;',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'DKK',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x10da;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'Kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x639;.&#x62f;',
			'IRR' => '&#xfdfc;',
			'ISK' => 'Kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x43b;&#x432;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => 'KZT',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'L',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRO' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => '&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/.',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#x434;&#x438;&#x43d;.',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STD' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'L',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'Fr',
			'XCD' => '&#36;',
			'XOF' => 'Fr',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		);
		if (empty($currency_symbol_array[ $currency ])) {
			$currency_symbol = '';
		} else {
			$currency_symbol = $currency_symbol_array[ $currency ];
		}
		
		return $currency_symbol;
	}
	
	/**
	 * Header callback
	 */
	public function header_callback() {
		echo '';
	}
	
	/**
	 * Render html checkbox by params settings
	 *
	 * @param $args
	 */
	public function checkbox_callback($args) {
		global $mprm_options;
		
		if (isset($args[ 'faux' ]) && true === $args[ 'faux' ]) {
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"';
		}
		$checked = isset($mprm_options[ $args[ 'id' ] ]) ? checked(1, $mprm_options[ $args[ 'id' ] ], false) : '';
		$html = '<input type="checkbox" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"' . $name . ' value="1" ' . $checked . '/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		
		echo $html;
	}
	
	/**
	 * Render html checkbox by params settings
	 *
	 * @param $args
	 */
	public function section_checkbox_callback($args) {
		global $mprm_options;
		
		if (isset($args[ 'faux' ]) && true === $args[ 'faux' ]) {
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . trim($args[ 'section' ]) . '][' . sanitize_key($args[ 'id' ]) . ']"';
		}
		
		
		if (!isset($mprm_options[ $args[ 'section' ] ])) {
			$checked = checked(1, $args[ 'std' ], false);
		} else {
			$checked = isset($mprm_options[ $args[ 'section' ] ][ $args[ 'id' ] ]) ? checked(1, $mprm_options[ $args[ 'section' ] ][ $args[ 'id' ] ], false) : '';
		}
		
		
		$html = '<input type="checkbox" id="mprm_settings[' . trim($args[ 'section' ]) . '][' . sanitize_key($args[ 'id' ]) . ']"' . $name . ' value="1" ' . $checked . '/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function multicheck_callback($args) {
		global $mprm_options;
		if (!empty($args[ 'options' ])) {
			foreach ($args[ 'options' ] as $key => $option):
				if (isset($mprm_options[ $args[ 'id' ] ][ $key ])) {
					$enabled = $option;
				} else {
					$enabled = NULL;
				}
				echo '<input name="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']" type="checkbox" value="' . esc_attr($option) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
				echo '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']">' . wp_kses_post($option) . '</label><br/>';
			endforeach;
			echo '<p class="description">' . $args[ 'desc' ] . '</p>';
		}
	}
	
	/**
	 * @param $args
	 */
	public function payment_icons_callback($args) {
		global $mprm_options;
		if (!empty($args[ 'options' ])) {
			foreach ($args[ 'options' ] as $key => $option) {
				if (isset($mprm_options[ $args[ 'id' ] ][ $key ])) {
					$enabled = $option;
				} else {
					$enabled = NULL;
				}
				echo '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']" style="margin-right:10px;line-height:16px;height:16px;display:inline-block;">';
				echo '<input name="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']" type="checkbox" value="' . esc_attr($option) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
				
				if ($this->string_is_image_url($key)) {
					echo '<img class="payment-icon" src="' . esc_url($key) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';
				} else {
					$card = strtolower(str_replace(' ', '', $option));
					if (has_filter('accepted_payment_' . $card . '_image')) {
						$image = apply_filters('accepted_payment_' . $card . '_image', '');
					} else {
						$image = MP_RM_MEDIA_URL . 'img/' . 'icons/' . $card . '.gif';
						$content_dir = WP_CONTENT_DIR;
						if (function_exists('wp_normalize_path')) {
							// Replaces backslashes with forward slashes for Windows systems
							//$image = wp_normalize_path($image);
							$content_dir = wp_normalize_path($content_dir);
						}
						$image = str_replace($content_dir, content_url(), $image);
					}
					echo '<img class="payment-icon" src="' . esc_url($image) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';
				}
				echo $option . '</label>';
			}
			echo '<p class="description" style="margin-top:16px;">' . wp_kses_post($args[ 'desc' ]) . '</p>';
		}
	}
	
	/**
	 * @param $str
	 *
	 * @return bool
	 */
	public function string_is_image_url($str) {
		$ext = $this->get_file_extension($str);
		switch (strtolower($ext)) {
			case 'jpg';
				$return = true;
				break;
			case 'png';
				$return = true;
				break;
			case 'gif';
				$return = true;
				break;
			default:
				$return = false;
				break;
		}
		
		return (bool)apply_filters('mprm_string_is_image', $return, $str);
	}
	
	/**
	 * @param $str
	 *
	 * @return mixed
	 */
	public function get_file_extension($str) {
		$parts = explode('.', $str);
		
		return end($parts);
	}
	
	/**
	 * @param $args
	 */
	public function radio_callback($args) {
		global $mprm_options;
		foreach ($args[ 'options' ] as $key => $option) :
			$checked = false;
			if (isset($mprm_options[ $args[ 'id' ] ]) && $mprm_options[ $args[ 'id' ] ] == $key)
				$checked = true;
			elseif (isset($args[ 'std' ]) && $args[ 'std' ] == $key && !isset($mprm_options[ $args[ 'id' ] ]))
				$checked = true;
			echo '<input name="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']" type="radio" value="' . sanitize_key($key) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
			echo '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']">' . $option . '</label><br/>';
		endforeach;
		echo '<p class="description">' . wp_kses_post($args[ 'desc' ]) . '</p>';
	}
	
	/**
	 * @param $args
	 */
	public function gateways_callback($args) {
		global $mprm_options;
		
		foreach ($args[ 'options' ] as $key => $option) :
			
			if (isset($mprm_options[ 'gateways' ][ $key ])) {
				$enabled = '1';
			} else {
				$enabled = null;
			}
			
			echo '<input name="mprm_settings[' . esc_attr($args[ 'id' ]) . '][' . sanitize_key($key) . ']"" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
			echo '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . '][' . sanitize_key($key) . ']">' . esc_html($option[ 'admin_label' ]) . '</label><br/>';
		endforeach;
		
	}
	
	/**
	 * @param $args
	 */
	public function gateway_select_callback($args) {
		global $mprm_options;
		echo '<select name="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']">';
		foreach ($args[ 'options' ] as $key => $option) :
			$selected = isset($mprm_options[ $args[ 'id' ] ]) ? selected($key, $mprm_options[ $args[ 'id' ] ], false) : '';
			echo '<option value="' . sanitize_key($key) . '"' . $selected . '>' . esc_html($option[ 'admin_label' ]) . '</option>';
		endforeach;
		echo '</select>';
		echo '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
	}
	
	/**
	 * Generate text settings  field
	 *
	 * @param $args
	 */
	public function text_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		if (isset($args[ 'faux' ]) && true === $args[ 'faux' ]) {
			$args[ 'readonly' ] = true;
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']"';
		}
		$placeholder = !isset($args[ 'placeholder' ]) ? '' : $args[ 'placeholder' ];
		
		$readonly = $args[ 'readonly' ] === true ? ' readonly="readonly"' : '';
		$size = (isset($args[ 'size' ]) && !is_null($args[ 'size' ])) ? $args[ 'size' ] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" ' . $name . ' placeholder="' . esc_attr(stripslashes($placeholder)) . '"' . ' value="' . esc_attr(stripslashes($value)) . '"' . $readonly . '/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function section_text_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'section' ] ][ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'section' ] ][ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		
		if (isset($args[ 'faux' ]) && true === $args[ 'faux' ]) {
			$args[ 'readonly' ] = true;
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . esc_attr($args[ 'section' ]) . '][' . esc_attr($args[ 'id' ]) . ']"';
		}
		$id = 'mprm_settings[' . sanitize_key($args[ 'section' ]) . '][' . sanitize_key($args[ 'id' ]) . ']';
		$placeholder = !isset($args[ 'placeholder' ]) ? '' : $args[ 'placeholder' ];
		
		$readonly = $args[ 'readonly' ] === true ? ' readonly="readonly"' : '';
		$size = (isset($args[ 'size' ]) && !is_null($args[ 'size' ])) ? $args[ 'size' ] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class($size) . '-text" id="' . $id . '" ' . $name . ' placeholder="' . esc_attr(stripslashes($placeholder)) . '"' . ' value="' . esc_attr(stripslashes($value)) . '"' . $readonly . '/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function number_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		if (isset($args[ 'faux' ]) && true === $args[ 'faux' ]) {
			$args[ 'readonly' ] = true;
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']"';
		}
		$max = isset($args[ 'max' ]) ? $args[ 'max' ] : 999999;
		$min = isset($args[ 'min' ]) ? $args[ 'min' ] : 0;
		$step = isset($args[ 'step' ]) ? $args[ 'step' ] : 1;
		$size = (isset($args[ 'size' ]) && !is_null($args[ 'size' ])) ? $args[ 'size' ] : 'regular';
		$html = '<input type="number" step="' . esc_attr($step) . '" max="' . esc_attr($max) . '" min="' . esc_attr($min) . '" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" ' . $name . ' value="' . esc_attr(stripslashes($value)) . '"/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function textarea_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		$html = '<textarea class="large-text" cols="50" rows="5" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']">' . esc_textarea(stripslashes($value)) . '</textarea>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function password_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		$size = (isset($args[ 'size' ]) && !is_null($args[ 'size' ])) ? $args[ 'size' ] : 'regular';
		$html = '<input type="password" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']" value="' . esc_attr($value) . '"/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function missing_callback($args) {
		printf(
			__('The callback function used for the %s setting is missing.', 'mp-restaurant-menu'),
			'<strong>' . $args[ 'id' ] . '</strong>'
		);
	}
	
	/**
	 * @param $args
	 */
	public function select_callback($args) {
		global $mprm_options;
		
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		
		if (isset($args[ 'multiple' ])) {
			$multiple = 'multiple size="' . $args[ 'multiple' ] . '"';
		} else {
			$multiple = '';
		}
		
		if (isset($args[ 'placeholder' ])) {
			$placeholder = $args[ 'placeholder' ];
		} else {
			$placeholder = '';
		}
		if (isset($args[ 'readonly' ]) && ($args[ 'readonly' ] == true)) {
			$disabled = 'disabled="disabled"';
		} else {
			$disabled = '';
		}
		
		if (isset($args[ 'chosen' ]) && $args[ 'chosen' ]) {
			$chosen = 'class="mprm-chosen mprm-select-chosen"';
		} else {
			$chosen = '';
		}
		$html = '<select id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" ' . $disabled . ' name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']" ' . $chosen . 'data-placeholder="' . esc_html($placeholder) . '"' . $multiple . ' />';
		foreach ($args[ 'options' ] as $option => $name) {
			$selected = selected($option, $value, false);
			$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($name) . '</option>';
		}
		$html .= '</select>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * Generate select settings field
	 *
	 * @param $args
	 */
	public function section_select_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'section' ] ][ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'section' ] ][ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		
		if (isset($args[ 'multiple' ])) {
			$multiple = 'multiple size="' . $args[ 'multiple' ] . '"';
		} else {
			$multiple = '';
		}
		
		if (isset($args[ 'placeholder' ])) {
			$placeholder = $args[ 'placeholder' ];
		} else {
			$placeholder = '';
		}
		if (isset($args[ 'readonly' ]) && ($args[ 'readonly' ] == true)) {
			$disabled = 'disabled="disabled"';
		} else {
			$disabled = '';
		}
		
		if (isset($args[ 'chosen' ]) && $args[ 'chosen' ]) {
			$chosen = 'class="mprm-chosen mprm-select-chosen"';
		} else {
			$chosen = '';
		}
		
		$html = '<select id="mprm_settings[' . sanitize_key($args[ 'section' ]) . '][' . sanitize_key($args[ 'id' ]) . ']" ' . $disabled . ' name="mprm_settings[' . esc_attr($args[ 'section' ]) . '][' . esc_attr($args[ 'id' ]) . ']" ' . $chosen . 'data-placeholder="' . esc_html($placeholder) . '"' . $multiple . ' />';
		
		foreach ($args[ 'options' ] as $option => $name) {
			$selected = selected($option, $value, false);
			$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($name) . '</option>';
		}
		
		$html .= '</select>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'section' ]) . '][' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function color_select_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		$html = '<select id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']"/>';
		foreach ($args[ 'options' ] as $option => $color) {
			$selected = selected($option, $value, false);
			$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($color[ 'label' ]) . '</option>';
		}
		$html .= '</select>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function rich_editor_callback($args) {
		global $mprm_options, $wp_version;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
			if (empty($args[ 'allow_blank' ]) && empty($value)) {
				$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
			}
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		$rows = isset($args[ 'size' ]) ? $args[ 'size' ] : 20;
		if ($wp_version >= 3.3 && function_exists('wp_editor')) {
			ob_start();
			wp_editor(stripslashes($value), 'settings_' . esc_attr($args[ 'id' ]), array('textarea_name' => 'mprm_settings[' . esc_attr($args[ 'id' ]) . ']', 'textarea_rows' => absint($rows)));
			$html = ob_get_clean();
		} else {
			$html = '<textarea class="large-text" rows="10" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']">' . esc_textarea(stripslashes($value)) . '</textarea>';
		}
		$html .= '<br/><label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function upload_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		$size = (isset($args[ 'size' ]) && !is_null($args[ 'size' ])) ? $args[ 'size' ] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']" value="' . esc_attr(stripslashes($value)) . '"/>';
		$html .= '<span>&nbsp;<input type="button" class="mprm_settings_upload_button button-secondary" value="' . __('Upload File', 'mp-restaurant-menu') . '"/></span>';
		$html .= '<br><label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function color_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[ $args[ 'id' ] ])) {
			$value = $mprm_options[ $args[ 'id' ] ];
		} else {
			$value = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		}
		$default = isset($args[ 'std' ]) ? $args[ 'std' ] : '';
		$html = '<input type="text" class="mprm-color-picker" id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($default) . '" />';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param $args
	 */
	public function shop_states_callback($args) {
		global $mprm_options;
		if (isset($args[ 'placeholder' ])) {
			$placeholder = $args[ 'placeholder' ];
		} else {
			$placeholder = '';
		}
		$states = $this->get_shop_states();
		$chosen = ($args[ 'chosen' ] ? ' mprm-chosen' : '');
		$class = empty($states) ? ' class="mprm-no-states' . $chosen . '"' : 'class="' . $chosen . '"';
		$html = '<select id="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']" name="mprm_settings[' . esc_attr($args[ 'id' ]) . ']"' . $class . 'data-placeholder="' . esc_html($placeholder) . '"/>';
		foreach ($states as $option => $name) {
			$selected = isset($mprm_options[ $args[ 'id' ] ]) ? selected($option, $mprm_options[ $args[ 'id' ] ], false) : '';
			$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($name) . '</option>';
		}
		$html .= '</select>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args[ 'id' ]) . ']"> ' . wp_kses_post($args[ 'desc' ]) . '</label>';
		echo $html;
	}
	
	/**
	 * @param null $country
	 *
	 * @return mixed
	 */
	public function get_shop_states($country = null) {
		if (empty($country)) {
			$country = $this->get_shop_country();
		}
		switch ($country) :
			case 'US' :
				$states = $this->get('settings_countries')->get_states_list();
				break;
			case 'CA' :
				$states = $this->get('settings_countries')->get_provinces_list();
				break;
			case 'AU' :
				$states = $this->get('settings_countries')->get_australian_states_list();
				break;
			case 'BD' :
				$states = $this->get('settings_countries')->get_bangladeshi_states_list();
				break;
			case 'BG' :
				$states = $this->get('settings_countries')->get_bulgarian_states_list();
				break;
			case 'BR' :
				$states = $this->get('settings_countries')->get_brazil_states_list();
				break;
			case 'CN' :
				$states = $this->get('settings_countries')->get_chinese_states_list();
				break;
			case 'HK' :
				$states = $this->get('settings_countries')->get_hong_kong_states_list();
				break;
			case 'HU' :
				$states = $this->get('settings_countries')->get_hungary_states_list();
				break;
			case 'ID' :
				$states = $this->get('settings_countries')->get_indonesian_states_list();
				break;
			case 'IN' :
				$states = $this->get('settings_countries')->get_indian_states_list();
				break;
			case 'IR' :
				$states = $this->get('settings_countries')->get_iranian_states_list();
				break;
			case 'IT' :
				$states = $this->get('settings_countries')->get_italian_states_list();
				break;
			case 'JP' :
				$states = $this->get('settings_countries')->get_japanese_states_list();
				break;
			case 'MX' :
				$states = $this->get('settings_countries')->get_mexican_states_list();
				break;
			case 'MY' :
				$states = $this->get('settings_countries')->get_malaysian_states_list();
				break;
			case 'NP' :
				$states = $this->get('settings_countries')->get_nepalese_states_list();
				break;
			case 'NZ' :
				$states = $this->get('settings_countries')->get_new_zealand_states_list();
				break;
			case 'PE' :
				$states = $this->get('settings_countries')->get_peruvian_states_list();
				break;
			case 'TH' :
				$states = $this->get('settings_countries')->get_thailand_states_list();
				break;
			case 'TR' :
				$states = $this->get('settings_countries')->get_turkey_states_list();
				break;
			case 'ZA' :
				$states = $this->get('settings_countries')->get_south_african_states_list();
				break;
			case 'ES' :
				$states = $this->get('settings_countries')->get_spain_states_list();
				break;
			default :
				$states = array();
				break;
		endswitch;
		
		return apply_filters('mprm_shop_states', $states, $country);
	}
	
	/**
	 *  Shop country
	 * @return mixed
	 */
	public function get_shop_country() {
		$country = $this->get_option('base_country', 'US');
		$country = apply_filters('mprm_shop_country', $country);
		
		return $country;
	}
	
	/**
	 * @param $args
	 */
	public function hook_callback($args) {
		do_action('mprm_' . $args[ 'id' ], $args);
	}
	
	/**
	 * @param $args
	 */
	public function tax_rates_callback($args) {
		//	global $mprm_options;
		$rates = $this->get_tax_rates();
		ob_start(); ?>
		<p><?php echo $args[ 'desc' ]; ?></p>
		<table id="mprm-tax-rates" class="wp-list-table widefat fixed posts">
			<thead>
			<tr>
				<th scope="col" class="tax_country"><?php _e('Country', 'mp-restaurant-menu'); ?></th>
				<th scope="col" class="tax_state"><?php _e('State / Province', 'mp-restaurant-menu'); ?></th>
				<th scope="col" class="tax_global" title="<?php _e('Apply rate to whole country, regardless of state / province', 'mp-restaurant-menu'); ?>"><?php _e('Country Wide', 'mp-restaurant-menu'); ?></th>
				<th scope="col" class="tax_rate"><?php _e('Rate', 'mp-restaurant-menu'); ?></th>
				<th scope="col"><?php _e('Remove', 'mp-restaurant-menu'); ?></th>
			</tr>
			</thead>
			<?php if (!empty($rates)) : ?>
				<?php foreach ($rates as $key => $rate) : ?>
					<tr>
						<td class="tax_country">
							<?php
							View::get_instance()->render_html('../admin/settings/select',
								array(
									'options' => $this->get_country_list(),
									'name' => 'tax_rates[' . sanitize_key($key) . '][country]',
									'selected' => $rate[ 'country' ],
									'show_option_all' => false,
									'show_option_none' => false,
									'class' => 'mprm-tax-country',
									'chosen' => false,
									'placeholder' => __('Choose a country', 'mp-restaurant-menu')
								)
							);
							?>
						</td>
						<td class="tax_state">
							<?php
							$states = $this->get_shop_states($rate[ 'country' ]);
							if (!empty($states)) {
								View::get_instance()->render_html('../admin/settings/select',
									array(
										'options' => $states,
										'name' => 'tax_rates[' . sanitize_key($key) . '][state]',
										'selected' => $rate[ 'state' ],
										'show_option_all' => false,
										'show_option_none' => false,
										'chosen' => false,
										'placeholder' => __('Choose a state', 'mp-restaurant-menu')
									)
								);
							} else {
								$args = array(
									'name' => 'tax_rates[' . sanitize_key($key) . '][state]', $rate[ 'state' ],
									'value' => !empty($rate[ 'state' ]) ? $rate[ 'state' ] : '',
								);
								View::get_instance()->render_html('../admin/settings/text', $args);
							}
							?>
						</td>
						<td class="tax_global">
							<input type="checkbox" name="tax_rates[<?php echo sanitize_key($key); ?>][global]" id="tax_rates[<?php echo sanitize_key($key); ?>][global]" value="1"<?php checked(true, !empty($rate[ 'global' ])); ?>/>
							<label for="tax_rates[<?php echo sanitize_key($key); ?>][global]"><?php _e('Apply to whole country', 'mp-restaurant-menu'); ?></label>
						</td>
						<td class="tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="tax_rates[<?php echo sanitize_key($key); ?>][rate]" value="<?php echo esc_html($rate[ 'rate' ]); ?>"/></td>
						<td><span class="remove_tax_rate button-secondary"><?php _e('Remove Rate', 'mp-restaurant-menu'); ?></span></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td class="tax_country">
						<?php
						View::get_instance()->render_html('../admin/settings/select',
							array(
								'options' => $this->get_country_list(),
								'name' => 'tax_rates[0][country]',
								'show_option_all' => false,
								'show_option_none' => false,
								'class' => 'mprm-tax-country',
								'chosen' => false,
								'placeholder' => __('Choose a country', 'mp-restaurant-menu')
							)
						);
						?>
					</td>
					<td class="tax_state">
						<?php
						View::get_instance()->render_html('../admin/settings/text', array(
							'name' => 'tax_rates[0][state]'
						)); ?>
					</td>
					<td class="tax_global">
						<input type="checkbox" name="tax_rates[0][global]" value="1"/>
						<label for="tax_rates[0][global]"><?php _e('Apply to whole country', 'mp-restaurant-menu'); ?></label>
					</td>
					<td class="tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" name="tax_rates[0][rate]" value=""/></td>
					<td><span class="remove_tax_rate button-secondary"><?php _e('Remove Rate', 'mp-restaurant-menu'); ?></span></td>
				</tr>
			<?php endif; ?>
		</table>
		<p>
			<span class="button-secondary" id="add_tax_rate"><?php _e('Add Tax Rate', 'mp-restaurant-menu'); ?></span>
		</p>
		<?php
		echo ob_get_clean();
	}
	
	/**
	 * @return mixed
	 */
	public function get_tax_rates() {
		$rates = $this->get_option('mprm_tax_rates', array());
		
		return apply_filters('mprm_get_tax_rates', $rates);
	}
	
	/**
	 * Country list
	 *
	 * @return mixed
	 */
	public function get_country_list() {
		$countries = array(
			'' => '',
			'US' => 'United States',
			'CA' => 'Canada',
			'GB' => 'United Kingdom',
			'AF' => 'Afghanistan',
			'AX' => '&#197;land Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darrussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CD' => 'Congo, Democratic People\'s Republic',
			'CG' => 'Congo, Republic of',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote d\'Ivoire',
			'HR' => 'Croatia/Hrvatska',
			'CU' => 'Cuba',
			'CW' => 'Cura&Ccedil;ao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'GQ' => 'Equatorial Guinea',
			'SV' => 'El Salvador',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GR' => 'Greece',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard and McDonald Islands',
			'VA' => 'Holy See (City Vatican State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macau',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova, Republic of',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territories',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Phillipines',
			'PN' => 'Pitcairn Island',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'XK' => 'Republic of Kosovo',
			'RE' => 'Reunion Island',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barth&eacute;lemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin (French)',
			'SX' => 'Saint Martin (Dutch)',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'SM' => 'San Marino',
			'ST' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovak Republic',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia',
			'KR' => 'South Korea',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen Islands',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'UY' => 'Uruguay',
			'UM' => 'US Minor Outlying Islands',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VG' => 'Virgin Islands (British)',
			'VI' => 'Virgin Islands (USA)',
			'WF' => 'Wallis and Futuna Islands',
			'EH' => 'Western Sahara',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);
		
		return apply_filters('mprm_countries', $countries);
	}
	
	/**
	 * @param $args
	 */
	public function descriptive_text_callback($args) {
		echo wp_kses_post($args[ 'desc' ]);
	}
	
	/**
	 * Create pages with shortcode
	 */
	public function create_settings_pages() {
		$purchase_page_id = wp_insert_post(
			array(
				'post_title' => 'Checkout',
				'post_content' => '[mprm_checkout]',
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_parent' => 0
			)
		);
		if ($purchase_page_id) {
			$this->set_option('purchase_page', $purchase_page_id);
		}
		
		$success_page_id = wp_insert_post(
			array(
				'post_title' => 'Success',
				'post_content' => '[mprm_success]',
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_parent' => empty($purchase_page_id) ? 0 : $purchase_page_id
			)
		);
		if ($success_page_id) {
			$this->set_option('success_page', $success_page_id);
		}
		
		
		$purchase_history_page_id = wp_insert_post(
			array(
				'post_title' => 'Purchase history',
				'post_content' => '[mprm_purchase_history]',
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_parent' => empty($purchase_page_id) ? 0 : $purchase_page_id
			)
		);
		if ($purchase_history_page_id) {
			$this->set_option('purchase_history_page', $purchase_history_page_id);
		}
		
		$failure_page_id = wp_insert_post(
			array(
				'post_title' => 'Transaction Failed',
				'post_content' => __('Your transaction failed, please try again or contact site support.', 'mp-restaurant-menu'),
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_parent' => empty($purchase_page_id) ? 0 : $purchase_page_id
			)
		);
		if ($failure_page_id) {
			$this->set_option('failure_page', $failure_page_id);
		}
	}
	
	/**
	 * @param string $key
	 * @param bool $value
	 *
	 * @return bool
	 */
	public function set_option($key = '', $value = false) {
		global $mprm_options;
		
		if (empty($mprm_options)) {
			$mprm_options = Settings::get_instance()->get_settings();
		}
		$mprm_options[ $key ] = apply_filters('mprm_set_option', $value, $key);
		
		return update_option('mprm_settings', $mprm_options);
	}
	
	/**
	 * @param array $input
	 *
	 * @return array|mixed
	 */
	public function mprm_settings_sanitize($input = array()) {
		global $mprm_options;
		
		if (empty($_POST[ '_wp_http_referer' ])) {
			return $input;
		}
		
		parse_str($_POST[ '_wp_http_referer' ], $referrer);
		
		$settings = Media::get_instance()->get_registered_settings();
		$tab = isset($referrer[ 'tab' ]) ? $referrer[ 'tab' ] : 'general';
		$section = isset($referrer[ 'section' ]) ? $referrer[ 'section' ] : 'main';
		
		
		$input = $input ? $input : array();
		$input = apply_filters('mprm_settings_' . $tab . '-' . $section . '_sanitize', $input);
		
		if ('main' === $section) {
			// Check for extensions that aren't using new sections
			$input = apply_filters('mprm_settings_' . $tab . '_sanitize', $input);
		}
		
		// Loop through each setting being saved and pass it through a sanitization filter
		foreach ($input as $key => $value) {
			// Get the setting type (checkbox, select, etc)
			$type = isset($settings[ $tab ][ $key ][ 'type' ]) ? $settings[ $tab ][ $key ][ 'type' ] : false;
			if ($type) {
				// Field type specific filter
				$input[ $key ] = apply_filters('mprm_settings_sanitize_' . $type, $value, $key);
			}
			// General filter
			$input[ $key ] = apply_filters('mprm_settings_sanitize', $input[ $key ], $key);
		}
		
		// Loop through the whitelist and unset any that are empty for the tab being saved
		$main_settings = $section == 'main' ? $settings[ $tab ] : array(); // Check for extensions that aren't using new sections
		
		$section_settings = !empty($settings[ $tab ][ $section ]) ? $settings[ $tab ][ $section ] : array();
		
		if (isset($main_settings[ $section ])) {
			$found_settings = array_merge($main_settings[ $section ], $section_settings);
		} else {
			$found_settings = array_merge($main_settings, $section_settings);
		}
		
		if (!empty($found_settings)) {
			
			foreach ($found_settings as $key => $value) {
				// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
				if (is_numeric($key)) {
					$key = $value[ 'id' ];
				}
				
				if (empty($input[ $key ])) {
					unset($mprm_options[ $key ]);
				}
			}
			
		}
		// Merge our new settings with the existing
		$output = array_merge($mprm_options, $input);
		
		add_settings_error('mprm-notices', '', __('Settings updated.', 'mp-restaurant-menu'), 'updated');
		
		return $output;
	}
	
	/**
	 * @return mixed
	 */
	public function is_ajax_disabled() {
		$retval = !$this->get_option('enable_ajax_cart');
		
		return apply_filters('mprm_is_ajax_disabled', $retval);
	}
	
	/**
	 * @return bool
	 */
	public function is_ssl_enforced() {
		$ssl_enforced = $this->get_option('enforce_ssl', false);
		
		return (bool)apply_filters('mprm_is_ssl_enforced', $ssl_enforced);
	}
	
	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public function add_cache_busting($url = '') {
		$no_cache_checkout = $this->get_option('no_cache_checkout', false);
		if (Capabilities::get_instance()->is_caching_plugin_active() || ($this->get('checkout')->is_checkout() && $no_cache_checkout)) {
			$url = add_query_arg('nocache', 'true', $url);
		}
		
		return $url;
	}
	
	/**
	 * Get shop state
	 * @return mixed
	 */
	public function get_shop_state() {
		$state = $this->get_option('base_state', false);
		
		return apply_filters('mprm_shop_state', $state);
	}
	
	/**
	 * @return bool
	 */
	public function logged_in_only() {
		$ret = $this->get_option('logged_in_only', false);
		
		return (bool)apply_filters('mprm_logged_in_only', $ret);
	}
}