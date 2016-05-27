<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\Capabilities;
use mp_restaurant_menu\classes\View;

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
		if (empty($settings)) {
			$default_settings =
				array(
					'enable_ecommerce' => '1',
					'currency' => 'USD',
					'customer_phone' => '1',
					'gateways' =>
						array(
							'paypal' => '1',
							'manual' => '1',
						),
					'item_quantities' => '1',
					'currency_position' => 'before',
					'thousands_separator' => ',',
					'decimal_separator' => '.',
					'accepted_cards' =>
						array(
							'mastercard' => 'Mastercard',
							'visa' => 'Visa',
							'americanexpress' => 'American Express',
							'discover' => 'Discover',
							'paypal' => 'PayPal',
						),
					'checkout_label' => 'Purchase',
					'add_to_cart_text' => 'Add to Cart',
					'buy_now_text' => 'Buy Now'
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
		if (!empty($settings[$key])) {
			return $settings[$key];
		} else {
			return $settings;
		}
	}

	public function get_config_settings() {
		$settings = array('tabs' => array());
		$config_settings = $this->get_config('settings');
		//$save_settings = $this->get_settings();
		foreach ($config_settings['tabs'] as $tabs => $setting) {
			$settings['tabs'][$tabs] = $setting;
		}
		return $settings;
	}

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
	 * Currency
	 *
	 * @default USD
	 *
	 * @return mixed|void
	 */
	public function get_currency() {
		$currency = $this->get_option('currency', 'USD');
		return apply_filters('mprm_currency', $currency);
	}

	/**
	 * Get list currencies
	 *
	 * @return mixed|void
	 */
	public function get_currencies() {
		$currencies = array(
			'USD' => __('US Dollars (&#36;)', 'mp-restaurant-menu'),
			'EUR' => __('Euros (&euro;)', 'mp-restaurant-menu'),
			'GBP' => __('Pounds Sterling (&pound;)', 'mp-restaurant-menu'),
			'AUD' => __('Australian Dollars (&#36;)', 'mp-restaurant-menu'),
			'BRL' => __('Brazilian Real (R&#36;)', 'mp-restaurant-menu'),
			'CAD' => __('Canadian Dollars (&#36;)', 'mp-restaurant-menu'),
			'CZK' => __('Czech Koruna', 'mp-restaurant-menu'),
			'DKK' => __('Danish Krone', 'mp-restaurant-menu'),
			'HKD' => __('Hong Kong Dollar (&#36;)', 'mp-restaurant-menu'),
			'HUF' => __('Hungarian Forint', 'mp-restaurant-menu'),
			'ILS' => __('Israeli Shekel (&#8362;)', 'mp-restaurant-menu'),
			'JPY' => __('Japanese Yen (&yen;)', 'mp-restaurant-menu'),
			'MYR' => __('Malaysian Ringgits', 'mp-restaurant-menu'),
			'MXN' => __('Mexican Peso (&#36;)', 'mp-restaurant-menu'),
			'NZD' => __('New Zealand Dollar (&#36;)', 'mp-restaurant-menu'),
			'NOK' => __('Norwegian Krone', 'mp-restaurant-menu'),
			'PHP' => __('Philippine Pesos', 'mp-restaurant-menu'),
			'PLN' => __('Polish Zloty', 'mp-restaurant-menu'),
			'SGD' => __('Singapore Dollar (&#36;)', 'mp-restaurant-menu'),
			'SEK' => __('Swedish Krona', 'mp-restaurant-menu'),
			'CHF' => __('Swiss Franc', 'mp-restaurant-menu'),
			'TWD' => __('Taiwan New Dollars', 'mp-restaurant-menu'),
			'THB' => __('Thai Baht (&#3647;)', 'mp-restaurant-menu'),
			'INR' => __('Indian Rupee (&#8377;)', 'mp-restaurant-menu'),
			'TRY' => __('Turkish Lira (&#8378;)', 'mp-restaurant-menu'),
			'RIAL' => __('Iranian Rial (&#65020;)', 'mp-restaurant-menu'),
			'RUB' => __('Russian Rubles', 'mp-restaurant-menu')
		);
		return apply_filters('mprm_currencies', $currencies);
	}

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

	public function header_callback($args = array()) {
		echo '';
	}

	public function checkbox_callback($args) {
		global $mprm_options;
		if (isset($args['faux']) && true === $args['faux']) {
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . sanitize_key($args['id']) . ']"';
		}
		$checked = isset($mprm_options[$args['id']]) ? checked(1, $mprm_options[$args['id']], false) : '';
		$html = '<input type="checkbox" id="mprm_settings[' . sanitize_key($args['id']) . ']"' . $name . ' value="1" ' . $checked . '/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function multicheck_callback($args) {
		global $mprm_options;
		if (!empty($args['options'])) {
			foreach ($args['options'] as $key => $option):
				if (isset($mprm_options[$args['id']][$key])) {
					$enabled = $option;
				} else {
					$enabled = NULL;
				}
				echo '<input name="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']" id="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']" type="checkbox" value="' . esc_attr($option) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
				echo '<label for="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']">' . wp_kses_post($option) . '</label><br/>';
			endforeach;
			echo '<p class="description">' . $args['desc'] . '</p>';
		}
	}

	public function payment_icons_callback($args) {
		global $mprm_options;
		if (!empty($args['options'])) {
			foreach ($args['options'] as $key => $option) {
				if (isset($mprm_options[$args['id']][$key])) {
					$enabled = $option;
				} else {
					$enabled = NULL;
				}
				echo '<label for="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']" style="margin-right:10px;line-height:16px;height:16px;display:inline-block;">';
				echo '<input name="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']" id="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']" type="checkbox" value="' . esc_attr($option) . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
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
			echo '<p class="description" style="margin-top:16px;">' . wp_kses_post($args['desc']) . '</p>';
		}
	}

	public function radio_callback($args) {
		global $mprm_options;
		foreach ($args['options'] as $key => $option) :
			$checked = false;
			if (isset($mprm_options[$args['id']]) && $mprm_options[$args['id']] == $key)
				$checked = true;
			elseif (isset($args['std']) && $args['std'] == $key && !isset($mprm_options[$args['id']]))
				$checked = true;
			echo '<input name="mprm_settings[' . sanitize_key($args['id']) . ']" id="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']" type="radio" value="' . sanitize_key($key) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
			echo '<label for="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']">' . esc_html($option) . '</label><br/>';
		endforeach;
		echo '<p class="description">' . wp_kses_post($args['desc']) . '</p>';
	}

	public function gateways_callback($args) {
		global $mprm_options;
		foreach ($args['options'] as $key => $option) :
			if (isset($mprm_options['gateways'][$key]))
				$enabled = '1';
			else
				$enabled = null;
			echo '<input name="mprm_settings[' . esc_attr($args['id']) . '][' . sanitize_key($key) . ']"" id="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
			echo '<label for="mprm_settings[' . sanitize_key($args['id']) . '][' . sanitize_key($key) . ']">' . esc_html($option['admin_label']) . '</label><br/>';
		endforeach;
	}

	public function gateway_select_callback($args) {
		global $mprm_options;
		echo '<select name="mprm_settings[' . sanitize_key($args['id']) . ']"" id="mprm_settings[' . sanitize_key($args['id']) . ']">';
		foreach ($args['options'] as $key => $option) :
			$selected = isset($mprm_options[$args['id']]) ? selected($key, $mprm_options[$args['id']], false) : '';
			echo '<option value="' . sanitize_key($key) . '"' . $selected . '>' . esc_html($option['admin_label']) . '</option>';
		endforeach;
		echo '</select>';
		echo '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
	}

	public function text_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		if (isset($args['faux']) && true === $args['faux']) {
			$args['readonly'] = true;
			$value = isset($args['std']) ? $args['std'] : '';
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . esc_attr($args['id']) . ']"';
		}
		$readonly = $args['readonly'] === true ? ' readonly="readonly"' : '';
		$size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args['id']) . ']" ' . $name . ' value="' . esc_attr(stripslashes($value)) . '"' . $readonly . '/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function number_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		if (isset($args['faux']) && true === $args['faux']) {
			$args['readonly'] = true;
			$value = isset($args['std']) ? $args['std'] : '';
			$name = '';
		} else {
			$name = 'name="mprm_settings[' . esc_attr($args['id']) . ']"';
		}
		$max = isset($args['max']) ? $args['max'] : 999999;
		$min = isset($args['min']) ? $args['min'] : 0;
		$step = isset($args['step']) ? $args['step'] : 1;
		$size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
		$html = '<input type="number" step="' . esc_attr($step) . '" max="' . esc_attr($max) . '" min="' . esc_attr($min) . '" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args['id']) . ']" ' . $name . ' value="' . esc_attr(stripslashes($value)) . '"/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function textarea_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$html = '<textarea class="large-text" cols="50" rows="5" id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']">' . esc_textarea(stripslashes($value)) . '</textarea>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function password_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
		$html = '<input type="password" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']" value="' . esc_attr($value) . '"/>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function missing_callback($args) {
		printf(
			__('The callback function used for the %s setting is missing.', 'mp-restaurant-menu'),
			'<strong>' . $args['id'] . '</strong>'
		);
	}

	public function select_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		if (isset($args['placeholder'])) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}
		if (isset($args['chosen'])) {
			$chosen = 'class="mprm-chosen"';
		} else {
			$chosen = '';
		}
		$html = '<select id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']" ' . $chosen . 'data-placeholder="' . esc_html($placeholder) . '" />';
		foreach ($args['options'] as $option => $name) {
			$selected = selected($option, $value, false);
			$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($name) . '</option>';
		}
		$html .= '</select>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function color_select_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$html = '<select id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']"/>';
		foreach ($args['options'] as $option => $color) {
			$selected = selected($option, $value, false);
			$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($color['label']) . '</option>';
		}
		$html .= '</select>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function rich_editor_callback($args) {
		global $mprm_options, $wp_version;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
			if (empty($args['allow_blank']) && empty($value)) {
				$value = isset($args['std']) ? $args['std'] : '';
			}
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$rows = isset($args['size']) ? $args['size'] : 20;
		if ($wp_version >= 3.3 && function_exists('wp_editor')) {
			ob_start();
			wp_editor(stripslashes($value), 'settings_' . esc_attr($args['id']), array('textarea_name' => 'mprm_settings[' . esc_attr($args['id']) . ']', 'textarea_rows' => absint($rows)));
			$html = ob_get_clean();
		} else {
			$html = '<textarea class="large-text" rows="10" id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']">' . esc_textarea(stripslashes($value)) . '</textarea>';
		}
		$html .= '<br/><label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function upload_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']" value="' . esc_attr(stripslashes($value)) . '"/>';
		$html .= '<span>&nbsp;<input type="button" class="mprm_settings_upload_button button-secondary" value="' . __('Upload File', 'mp-restaurant-menu') . '"/></span>';
		$html .= '<br><label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function color_callback($args) {
		global $mprm_options;
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		$default = isset($args['std']) ? $args['std'] : '';
		$html = '<input type="text" class="mprm-color-picker" id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($default) . '" />';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function shop_states_callback($args) {
		global $mprm_options;
		if (isset($args['placeholder'])) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}
		$states = $this->get_shop_states();
		$chosen = ($args['chosen'] ? ' mprm-chosen' : '');
		$class = empty($states) ? ' class="mprm-no-states' . $chosen . '"' : 'class="' . $chosen . '"';
		$html = '<select id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . esc_attr($args['id']) . ']"' . $class . 'data-placeholder="' . esc_html($placeholder) . '"/>';
		foreach ($states as $option => $name) {
			$selected = isset($mprm_options[$args['id']]) ? selected($option, $mprm_options[$args['id']], false) : '';
			$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($name) . '</option>';
		}
		$html .= '</select>';
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		echo $html;
	}

	public function hook_callback($args) {
		do_action('mprm_' . $args['id'], $args);
	}

	public function tax_rates_callback($args) {
		global $mprm_options;
		$rates = $this->get_tax_rates();
		ob_start(); ?>
		<p><?php echo $args['desc']; ?></p>
		<table id="tax_rates" class="wp-list-table widefat fixed posts">
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
									'selected' => $rate['country'],
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
							$states = $this->get_shop_states($rate['country']);
							if (!empty($states)) {
								View::get_instance()->render_html('../admin/settings/select',
									array(
										'options' => $states,
										'name' => 'tax_rates[' . sanitize_key($key) . '][state]',
										'selected' => $rate['state'],
										'show_option_all' => false,
										'show_option_none' => false,
										'chosen' => false,
										'placeholder' => __('Choose a state', 'mp-restaurant-menu')
									)
								);
							} else {
								$args = array(
									'name' => 'tax_rates[' . sanitize_key($key) . '][state]', $rate['state'],
									'value' => !empty($rate['state']) ? $rate['state'] : '',
								);
								View::get_instance()->render_html('../admin/settings/text', $args);
							}
							?>
						</td>
						<td class="tax_global">
							<input type="checkbox" name="tax_rates[<?php echo sanitize_key($key); ?>][global]" id="tax_rates[<?php echo sanitize_key($key); ?>][global]" value="1"<?php checked(true, !empty($rate['global'])); ?>/>
							<label for="tax_rates[<?php echo sanitize_key($key); ?>][global]"><?php _e('Apply to whole country', 'mp-restaurant-menu'); ?></label>
						</td>
						<td class="tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="tax_rates[<?php echo sanitize_key($key); ?>][rate]" value="<?php echo esc_html($rate['rate']); ?>"/></td>
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

	public function descriptive_text_callback($args) {
		echo wp_kses_post($args['desc']);
	}

	public function license_key_callback($args) {
		global $mprm_options;
		$messages = array();
		$license = get_option($args['options']['is_valid_license_option']);
		if (isset($mprm_options[$args['id']])) {
			$value = $mprm_options[$args['id']];
		} else {
			$value = isset($args['std']) ? $args['std'] : '';
		}
		if (!empty($license) && is_object($license)) {
			// activate_license 'invalid' on anything other than valid, so if there was an error capture it
			if (false === $license->success) {
				switch ($license->error) {
					case 'expired' :
						$class = 'error';
						$messages[] = sprintf(
							__('Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'mp-restaurant-menu'),
							date_i18n(get_option('date_format'), strtotime($license->expires, current_time('timestamp'))),
							'https://easydigitalmenu_items.com/checkout/?license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
						);
						$license_status = 'license-' . $class . '-notice';
						break;
					case 'missing' :
						$class = 'error';
						$messages[] = sprintf(
							__('Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'mp-restaurant-menu'),
							'https://easydigitalmenu_items.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
						);
						$license_status = 'license-' . $class . '-notice';
						break;
					case 'invalid' :
					case 'site_inactive' :
						$class = 'error';
						$messages[] = sprintf(
							__('Your %s is not active for this URL. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'mp-restaurant-menu'),
							$args['name'],
							'https://easydigitalmenu_items.com/your-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
						);
						$license_status = 'license-' . $class . '-notice';
						break;
					case 'item_name_mismatch' :
						$class = 'error';
						$messages[] = sprintf(__('This is not a %s.', 'mp-restaurant-menu'), $args['name']);
						$license_status = 'license-' . $class . '-notice';
						break;
					case 'no_activations_left':
						$class = 'error';
						$messages[] = sprintf(__('Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'mp-restaurant-menu'), 'https://easydigitalmenu_items.com/your-account/');
						$license_status = 'license-' . $class . '-notice';
						break;
				}
			} else {
				switch ($license->license) {
					case 'valid' :
					default:
						$class = 'valid';
						$now = current_time('timestamp');
						$expiration = strtotime($license->expires, current_time('timestamp'));
						if ('lifetime' === $license->expires) {
							$messages[] = __('License key never expires.', 'mp-restaurant-menu');
							$license_status = 'license-lifetime-notice';
						} elseif ($expiration > $now && $expiration - $now < (DAY_IN_SECONDS * 30)) {
							$messages[] = sprintf(
								__('Your license key expires soon! It expires on %s. <a href="%s" target="_blank" title="Renew license">Renew your license key</a>.', 'mp-restaurant-menu'),
								date_i18n(get_option('date_format'), strtotime($license->expires, current_time('timestamp'))),
								'https://easydigitalmenu_items.com/checkout/?license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
							);
							$license_status = 'license-expires-soon-notice';
						} else {
							$messages[] = sprintf(
								__('Your license key expires on %s.', 'mp-restaurant-menu'),
								date_i18n(get_option('date_format'), strtotime($license->expires, current_time('timestamp')))
							);
							$license_status = 'license-expiration-date-notice';
						}
						break;
				}
			}
		} else {
			$license_status = null;
		}
		$size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . sanitize_html_class($size) . '-text" id="mprm_settings[' . sanitize_key($args['id']) . ']" name="mprm_settings[' . sanitize_key($args['id']) . ']" value="' . esc_attr($value) . '"/>';
		if ((is_object($license) && 'valid' == $license->license) || 'valid' == $license) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __('Deactivate License', 'mp-restaurant-menu') . '"/>';
		}
		$html .= '<label for="mprm_settings[' . sanitize_key($args['id']) . ']"> ' . wp_kses_post($args['desc']) . '</label>';
		if (!empty($messages)) {
			foreach ($messages as $message) {
				$html .= '<div class="mprm-license-data mprm-license-' . $class . '">';
				$html .= '<p>' . $message . '</p>';
				$html .= '</div>';
			}
		}
		wp_nonce_field(sanitize_key($args['id']) . '-nonce', sanitize_key($args['id']) . '-nonce');
		if (isset($license_status)) {
			echo '<div class="' . $license_status . '">' . $html . '</div>';
		} else {
			echo '<div class="license-null">' . $html . '</div>';
		}
	}

	public function create_settings_pages() {

		if ($this->get_option('purchase_page')) {
			$purchase_page_id = $this->get_option('purchase_page');
			if (is_page($purchase_page_id)) {
				$post_data = get_post($purchase_page_id, ARRAY_A);
				$post_data['post_content'] = empty(preg_match('/[mprm_checkout]/', $post_data['post_content'])) ? $post_data['post_content'] . '[mprm_checkout]' : $post_data['post_content'];
				wp_update_post($post_data);
			} else {
				$purchase_page_id = wp_insert_post(
					array(
						'post_title' => 'Checkout',
						'post_content' => '[mprm_checkout]',
						'post_status' => 'publish',
						'post_type' => 'page'
					)
				);
				if ($purchase_page_id) {
					$this->set_option('purchase_page', $purchase_page_id);
				}
			}
		}
		if ($this->get_option('success_page')) {
			$success_page_id = $this->get_option('success_page');
			if (is_page($success_page_id)) {
				$post_data = get_post($success_page_id, ARRAY_A);
				$post_data['post_content'] = empty(preg_match('/[mprm_success]/', $post_data['post_content'])) ? $post_data['post_content'] . '[mprm_success]' : $post_data['post_content'];
				wp_update_post($post_data);
			} else {
				$success_page_id = wp_insert_post(
					array(
						'post_title' => 'Success',
						'post_content' => '[mprm_success]',
						'post_status' => 'publish',
						'post_type' => 'page'
					)
				);
				if ($success_page_id) {
					$this->set_option('success_page', $success_page_id);
				}
			}
		}
		if ($this->get_option('purchase_history_page')) {
			$purchase_history_page_id = $this->get_option('purchase_history_page');
			if (is_page($purchase_history_page_id)) {
				$post_data = get_post($purchase_history_page_id, ARRAY_A);
				$post_data['post_content'] = empty(preg_match('/[mprm_purchase_history]/', $post_data['post_content'])) ? $post_data['post_content'] . '[mprm_purchase_history]' : $post_data['post_content'];
				wp_update_post($post_data);
			} else {
				$purchase_history_page_id = wp_insert_post(
					array(
						'post_title' => 'Purchase history',
						'post_content' => '[mprm_purchase_history]',
						'post_status' => 'publish',
						'post_type' => 'page'
					)
				);
				if ($purchase_history_page_id) {
					$this->set_option('purchase_history_page', $purchase_history_page_id);
				}
			}
		}
		if ($this->get_option('failure_page')) {
			$failure_page_id = $this->get_option('failure_page');
			if (is_page($failure_page_id)) {
				$post_data = get_post($failure_page_id, ARRAY_A);
				$post_data['post_content'] = empty(preg_match('/Your transaction failed/', $post_data['post_content'])) ? $post_data['post_content'] . __('Your transaction failed, please try again or contact site support.', 'mp-restaurant-menu') : $post_data['post_content'];
				wp_update_post($post_data);
			} else {
				$failure_page_id = wp_insert_post(
					array(
						'post_title' => 'Transaction Failed',
						'post_content' => __('Your transaction failed, please try again or contact site support.', 'mp-restaurant-menu'),
						'post_status' => 'publish',
						'post_type' => 'page'
					)
				);
				if ($failure_page_id) {
					$this->set_option('failure_page', $failure_page_id);
				}
			}
		}
	}

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

	public function get_file_extension($str) {
		$parts = explode('.', $str);
		return end($parts);
	}

	public function get_shop_states($country = null) {
		if (empty($country)) {
			$country = $this->get_shop_country($country);
		}
		switch ($country) :
			case 'US' :
				$states = Settings_countries::get_instance()->get_states_list();
				break;
			case 'CA' :
				$states = Settings_countries::get_instance()->get_provinces_list();
				break;
			case 'AU' :
				$states = Settings_countries::get_instance()->get_australian_states_list();
				break;
			case 'BD' :
				$states = Settings_countries::get_instance()->get_bangladeshi_states_list();
				break;
			case 'BG' :
				$states = Settings_countries::get_instance()->get_bulgarian_states_list();
				break;
			case 'BR' :
				$states = Settings_countries::get_instance()->get_brazil_states_list();
				break;
			case 'CN' :
				$states = Settings_countries::get_instance()->get_chinese_states_list();
				break;
			case 'HK' :
				$states = Settings_countries::get_instance()->get_hong_kong_states_list();
				break;
			case 'HU' :
				$states = Settings_countries::get_instance()->get_hungary_states_list();
				break;
			case 'ID' :
				$states = Settings_countries::get_instance()->get_indonesian_states_list();
				break;
			case 'IN' :
				$states = Settings_countries::get_instance()->get_indian_states_list();
				break;
			case 'IR' :
				$states = Settings_countries::get_instance()->get_iranian_states_list();
				break;
			case 'IT' :
				$states = Settings_countries::get_instance()->get_italian_states_list();
				break;
			case 'JP' :
				$states = Settings_countries::get_instance()->get_japanese_states_list();
				break;
			case 'MX' :
				$states = Settings_countries::get_instance()->get_mexican_states_list();
				break;
			case 'MY' :
				$states = Settings_countries::get_instance()->get_malaysian_states_list();
				break;
			case 'NP' :
				$states = Settings_countries::get_instance()->get_nepalese_states_list();
				break;
			case 'NZ' :
				$states = Settings_countries::get_instance()->get_new_zealand_states_list();
				break;
			case 'PE' :
				$states = Settings_countries::get_instance()->get_peruvian_states_list();
				break;
			case 'TH' :
				$states = Settings_countries::get_instance()->get_thailand_states_list();
				break;
			case 'TR' :
				$states = Settings_countries::get_instance()->get_turkey_states_list();
				break;
			case 'ZA' :
				$states = Settings_countries::get_instance()->get_south_african_states_list();
				break;
			case 'ES' :
				$states = Settings_countries::get_instance()->get_spain_states_list();
				break;
			default :
				$states = array();
				break;
		endswitch;
		return apply_filters('mprm_shop_states', $states, $country);
	}

	public function get_tax_rates() {
		$rates = $this->get_option('mprm_tax_rates', array());
		return apply_filters('mprm_get_tax_rates', $rates);
	}

	public function get_option($key = '', $default = false) {
		global $mprm_options;

		if (empty($mprm_options)) {
			$mprm_options = Settings::get_instance()->get_settings();
		}

		$value = !empty($mprm_options[$key]) ? $mprm_options[$key] : $default;
		$value = apply_filters('mprm_get_option', $value, $key, $default);
		return apply_filters('mprm_get_option_' . $key, $value, $key, $default);
	}

	public function set_option($key = '', $value = false) {
		global $mprm_options;

		if (empty($mprm_options)) {
			$mprm_options = Settings::get_instance()->get_settings();
		}
		$mprm_options[$key] = apply_filters('mprm_set_option', $value, $key);
		return update_option('mprm_settings', $mprm_options);
	}

	public function mprm_settings_sanitize($input = array()) {
		global $mprm_options;
		if (empty($_POST['_wp_http_referer'])) {
			return $input;
		}
		parse_str($_POST['_wp_http_referer'], $referrer);
		$settings = Media::get_instance()->get_registered_settings();
		$tab = isset($referrer['tab']) ? $referrer['tab'] : 'general';
		$section = isset($referrer['section']) ? $referrer['section'] : 'main';
		$input = $input ? $input : array();
		$input = apply_filters('mprm_settings_' . $tab . '-' . $section . '_sanitize', $input);
		if ('main' === $section) {
			// Check for extensions that aren't using new sections
			$input = apply_filters('mprm_settings_' . $tab . '_sanitize', $input);
		}
		// Loop through each setting being saved and pass it through a sanitization filter
		foreach ($input as $key => $value) {
			// Get the setting type (checkbox, select, etc)
			$type = isset($settings[$tab][$key]['type']) ? $settings[$tab][$key]['type'] : false;
			if ($type) {
				// Field type specific filter
				$input[$key] = apply_filters('mprm_settings_sanitize_' . $type, $value, $key);
			}
			// General filter
			$input[$key] = apply_filters('mprm_settings_sanitize', $input[$key], $key);
		}
		// Loop through the whitelist and unset any that are empty for the tab being saved
		$main_settings = $section == 'main' ? $settings[$tab] : array(); // Check for extensions that aren't using new sections
		$section_settings = !empty($settings[$tab][$section]) ? $settings[$tab][$section] : array();
		$found_settings = array_merge($main_settings, $section_settings);
		if (!empty($found_settings)) {
			foreach ($found_settings as $key => $value) {
				// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
				if (is_numeric($key)) {
					$key = $value['id'];
				}
				if (empty($input[$key])) {
					unset($mprm_options[$key]);
				}
			}
		}
		// Merge our new settings with the existing
		$output = array_merge($mprm_options, $input);
		add_settings_error('mprm-notices', '', __('Settings updated.', 'mp-restaurant-menu'), 'updated');
		return $output;
	}

	public function is_ajax_disabled() {
		$retval = !$this->get_option('enable_ajax_cart');
		return apply_filters('mprm_is_ajax_disabled', $retval);
	}

	public function is_ssl_enforced() {
		$ssl_enforced = $this->get_option('enforce_ssl', false);
		return (bool)apply_filters('mprm_is_ssl_enforced', $ssl_enforced);
	}

	public function add_cache_busting($url = '') {
		$no_cache_checkout = $this->get_option('no_cache_checkout', false);
		if (Capabilities::get_instance()->is_caching_plugin_active() || ($this->get('checkout')->is_checkout() && $no_cache_checkout)) {
			$url = add_query_arg('nocache', 'true', $url);
		}
		return $url;
	}

	public function get_shop_country() {
		$country = $this->get_option('base_country', 'US');
		$country = apply_filters('mprm_shop_country', $country);
		return $country;
	}

	public function get_shop_state() {
		$state = $this->get_option('base_state', false);
		return apply_filters('mprm_shop_state', $state);
	}

	public function logged_in_only() {
		$ret = $this->get_option('logged_in_only', false);
		return (bool)apply_filters('mprm_logged_in_only', $ret);
	}
}
