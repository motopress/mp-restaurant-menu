<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\Model;

/**
 * Class Purchase
 * @package mp_restaurant_menu\classes\models
 */
class Purchase extends Model {
	protected static $instance;

	/**
	 * @return Purchase
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Purchase form
	 *
	 * @return void|boolean
	 */
	public function process_purchase_form() {
		do_action('mprm_pre_process_purchase');

		// Make sure the cart isn't empty
		if (!$this->get('cart')->get_cart_contents() && !$this->get('cart')->cart_has_fees()) {
			$valid_data = false;
			$this->get('errors')->set_error('empty_cart', __('Your cart is empty.', 'mp-restaurant-menu'));
		} else {
			// Validate the form $_POST data
			$valid_data = $this->purchase_form_validate_fields();
			// Allow themes and plugins to hook to errors
			do_action('mprm_checkout_error_checks', $valid_data, $_POST);
		}
		$is_ajax = Core::is_ajax();

		// Process the login form
		if (isset($_POST['mprm_login_submit'])) {
			$this->process_purchase_login();
		}

		// Validate the user
		$user = $this->get_purchase_form_user($valid_data);
		if (false === $valid_data || $this->get('errors')->get_errors() || !$user) {
			if ($is_ajax) {

				$errors = $this->get('errors')->get_error_html();
				if (!empty($errors)) {
					wp_send_json_error(array(
						'errors' => $errors,
						'error' => !empty($errors) ? true : false,
					));
				}
				do_action('mprm_ajax_checkout_errors');
				$this->get('misc')->mprm_die();
			} else {
				return false;
			}
		}

		if ($is_ajax) {
			$errors = $this->get('errors')->get_errors();
			wp_send_json_success(array(
				'errors' => $this->get('errors')->get_error_html(),
				'error' => !empty($errors) ? true : false,
			));
			$this->get('misc')->mprm_die();
		}

		// Setup user information
		$user_info = array(
			'id' => $user['user_id'],
			'email' => $user['user_email'],
			'first_name' => $user['user_first'],
			'last_name' => $user['user_last'],
			'discount' => $valid_data['discount'],
			'address' => $user['address']
		);
		$auth_key = defined('AUTH_KEY') ? AUTH_KEY : '';

		// Setup purchase information
		$purchase_data = array(
			'menu_items' => $this->get('cart')->get_cart_contents(),
			'fees' => $this->get('cart')->get_cart_fees(),
			'subtotal' => $this->get('cart')->get_cart_subtotal(),
			'discount' => $this->get('cart')->get_cart_discounted_amount(),
			'tax' => $this->get('cart')->get_cart_tax(),
			'price' => $this->get('cart')->get_cart_total(),
			'purchase_key' => strtolower(md5($user['user_email'] . date('Y-m-d H:i:s') . $auth_key . uniqid('mprm', true))),
			'user_email' => $user['user_email'],
			'date' => date('Y-m-d H:i:s', current_time('timestamp')),
			'user_info' => stripslashes_deep($user_info),
			'post_data' => $_POST,
			'cart_details' => $this->get('cart')->get_cart_content_details(),
			'gateway' => $valid_data['gateway'],
			'card_info' => $valid_data['cc_info'],
			'customer_note' => $valid_data['customer_note'],
			'shipping_address' => !empty($valid_data['shipping_address']) ? $valid_data['shipping_address'] : '',
			'phone_number' => $valid_data['phone_number']
		);

		// Add the user data for hooks
		$valid_data['user'] = $user;

		// Allow themes and plugins to hook before the gateway
		do_action('mprm_checkout_before_gateway', $_POST, $user_info, $valid_data);

		// If the total amount in the cart is 0, send to the manual gateway. This emulates a free menu_item purchase
		if (!$purchase_data['price']) {
			// Revert to manual
			$purchase_data['gateway'] = 'test_manual';
			$_POST['mprm-gateway'] = 'test_manual';
		}
		// Allow the purchase data to be modified before it is sent to the gateway
		$purchase_data = apply_filters('mprm_purchase_data_before_gateway', $purchase_data, $valid_data);

		// Setup the data we're storing in the purchase session
		$session_data = $purchase_data;

		// Make sure credit card numbers are never stored in sessions
		unset($session_data['card_info']['card_number']);

		// Used for showing menu_item links to non logged-in users after purchase, and for other plugins needing purchase data.
		$this->get('session')->set('mprm_purchase', $session_data);

		// Send info to the gateway for payment processing
		$this->get('gateways')->send_to_gateway($purchase_data['gateway'], $purchase_data);
	}

	/**
	 * Validate purchase form
	 *
	 * @return array|bool
	 */
	public function purchase_form_validate_fields() {
		// Check if there is $_POST
		if (empty($_POST)) return false;
		// Start an array to collect valid data
		$valid_data = array(
			'gateway' => $this->purchase_form_validate_gateway(), // Gateway fallback
			'discount' => $this->purchase_form_validate_discounts(),    // Set default discount
			'need_new_user' => false,     // New user flag
			'need_user_login' => false,     // Login user flag
			'logged_user_data' => array(),   // Logged user collected data
			'new_user_data' => array(),   // New user collected data
			'login_user_data' => array(),   // Login user collected data
			'guest_user_data' => array(),   // Guest user collected data
			'cc_info' => $this->purchase_form_validate_cc(),// Credit card info
			'customer_note' => !empty($_POST['customer_note']) ? sanitize_text_field($_POST['customer_note']) : '',
			'shipping_address' => !empty($_POST['shipping_address']) ? sanitize_text_field($_POST['shipping_address']) : '',
			'phone_number' => !empty($_POST['phone_number']) ? $this->purchase_form_validate_phone() : ''
		);

		// Validate agree to terms
		if ($this->get('settings')->get_option('show_agree_to_terms', false)) {
			$this->purchase_form_validate_agree_to_terms();
		}

		if (is_user_logged_in()) {
			// Collect logged in user data
			$valid_data['logged_in_user'] = $this->purchase_form_validate_logged_in_user();
		} else if (isset($_POST['mprm-purchase-var']) && $_POST['mprm-purchase-var'] == 'needs-to-register') {
			// Set new user registration as required
			$valid_data['need_new_user'] = true;
			// Validate new user data
			$valid_data['new_user_data'] = $this->purchase_form_validate_new_user();
			// Check if login validation is needed
		} else if (isset($_POST['mprm-purchase-var']) && $_POST['mprm-purchase-var'] == 'needs-to-login') {
			// Set user login as required
			$valid_data['need_user_login'] = true;
			// Validate users login info
			$valid_data['login_user_data'] = $this->purchase_form_validate_user_login();
		} else {
			// Not registering or logging in, so setup guest user data
			$valid_data['guest_user_data'] = $this->purchase_form_validate_guest_user();
		}
		// Return collected data
		return $valid_data;
	}

	/**
	 * Purchase form validate gateway
	 *
	 * @return string
	 */
	public function purchase_form_validate_gateway() {
		$gateway = $this->get('gateways')->get_default_gateway();
		// Check if a gateway value is present
		if (!empty($_REQUEST['mprm-gateway'])) {
			$gateway = sanitize_text_field($_REQUEST['mprm-gateway']);
			if ('0.00' == $this->get('cart')->get_cart_total()) {
				$gateway = 'test_manual';
			} elseif (!$this->get('gateways')->is_gateway_active($gateway)) {
				$this->get('errors')->set_error('invalid_gateway', __('The selected payment gateway is not enabled', 'mp-restaurant-menu'));
			}
		}
		return $gateway;
	}

	/**
	 * Purchase form validate discounts
	 *
	 * @return string
	 */
	public function purchase_form_validate_discounts() {
		// Retrieve the discount stored in cookies
		$discounts = $this->get('cart')->get_cart_discounts();
		$user = '';
		if (isset($_POST['mprm_user_login']) && !empty($_POST['mprm_user_login'])) {
			$user = sanitize_text_field($_POST['mprm_user_login']);
		} else if (isset($_POST['mprm_email']) && !empty($_POST['mprm_email'])) {
			$user = sanitize_text_field($_POST['mprm_email']);
		} else if (is_user_logged_in()) {
			$user = wp_get_current_user()->user_email;
		}
		$error = false;
		// Check for valid discount(s) is present
		if (!empty($_POST['mprm-discount']) && __('Enter discount', 'mp-restaurant-menu') != $_POST['mprm-discount']) {
			// Check for a posted discount
			$posted_discount = isset($_POST['mprm-discount']) ? trim($_POST['mprm-discount']) : false;
			// Add the posted discount to the discounts
			if ($posted_discount && (empty($discounts) || $this->get('discount')->multiple_discounts_allowed()) && $this->get('discount')->is_discount_valid($posted_discount, $user)) {
				$this->get('discount')->set_cart_discount($posted_discount);
			}
		}
		// If we have discounts, loop through them
		if (!empty($discounts)) {
			foreach ($discounts as $discount) {
				// Check if valid
				if (!$this->get('discount')->is_discount_valid($discount, $user)) {
					// Discount is not valid
					$error = true;
				}
			}
		} else {
			// No discounts
			return 'none';
		}
		if ($error) {
			$this->get('errors')->set_error('invalid_discount', __('One or more of the discounts you entered is invalid', 'mp-restaurant-menu'));
		}
		return implode(', ', $discounts);
	}

	/**
	 * Purchase form validate cc
	 *
	 * @return array
	 */
	public function purchase_form_validate_cc() {
		$card_data = $this->get_purchase_cc_info();
		// Validate the card zip
		if (!empty($card_data['card_zip'])) {
			if (!$this->purchase_form_validate_cc_zip($card_data['card_zip'], $card_data['card_country'])) {
				$this->get('errors')->set_error('invalid_cc_zip', __('The zip / postal code you entered for your billing address is invalid', 'mp-restaurant-menu'));
			}
		}
		// This should validate card numbers at some point too
		return $card_data;
	}

	/**
	 * Purchase cc info
	 *
	 * @return array
	 */
	public function get_purchase_cc_info() {
		$cc_info = array();
		$cc_info['card_name'] = isset($_POST['card_name']) ? sanitize_text_field($_POST['card_name']) : '';
		$cc_info['card_number'] = isset($_POST['card_number']) ? sanitize_text_field($_POST['card_number']) : '';
		$cc_info['card_cvc'] = isset($_POST['card_cvc']) ? sanitize_text_field($_POST['card_cvc']) : '';
		$cc_info['card_exp_month'] = isset($_POST['card_exp_month']) ? sanitize_text_field($_POST['card_exp_month']) : '';
		$cc_info['card_exp_year'] = isset($_POST['card_exp_year']) ? sanitize_text_field($_POST['card_exp_year']) : '';
		$cc_info['card_address'] = isset($_POST['card_address']) ? sanitize_text_field($_POST['card_address']) : '';
		$cc_info['card_address_2'] = isset($_POST['card_address_2']) ? sanitize_text_field($_POST['card_address_2']) : '';
		$cc_info['card_city'] = isset($_POST['card_city']) ? sanitize_text_field($_POST['card_city']) : '';
		$cc_info['card_state'] = isset($_POST['card_state']) ? sanitize_text_field($_POST['card_state']) : '';
		$cc_info['card_country'] = isset($_POST['billing_country']) ? sanitize_text_field($_POST['billing_country']) : '';
		$cc_info['card_zip'] = isset($_POST['card_zip']) ? sanitize_text_field($_POST['card_zip']) : '';
		// Return cc info
		return $cc_info;
	}

	/**
	 * Validate cc zip
	 *
	 * @param int $zip
	 * @param string $country_code
	 *
	 * @return bool|mixed
	 */
	public function purchase_form_validate_cc_zip($zip = 0, $country_code = '') {
		$ret = false;
		if (empty($zip) || empty($country_code))
			return $ret;
		$country_code = strtoupper($country_code);
		$zip_regex = array(
			"AD" => "AD\d{3}",
			"AM" => "(37)?\d{4}",
			"AR" => "^([A-Z]{1}\d{4}[A-Z]{3}|[A-Z]{1}\d{4}|\d{4})$",
			"AS" => "96799",
			"AT" => "\d{4}",
			"AU" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
			"AX" => "22\d{3}",
			"AZ" => "\d{4}",
			"BA" => "\d{5}",
			"BB" => "(BB\d{5})?",
			"BD" => "\d{4}",
			"BE" => "^[1-9]{1}[0-9]{3}$",
			"BG" => "\d{4}",
			"BH" => "((1[0-2]|[2-9])\d{2})?",
			"BM" => "[A-Z]{2}[ ]?[A-Z0-9]{2}",
			"BN" => "[A-Z]{2}[ ]?\d{4}",
			"BR" => "\d{5}[\-]?\d{3}",
			"BY" => "\d{6}",
			"CA" => "^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$",
			"CC" => "6799",
			"CH" => "^[1-9][0-9][0-9][0-9]$",
			"CK" => "\d{4}",
			"CL" => "\d{7}",
			"CN" => "\d{6}",
			"CR" => "\d{4,5}|\d{3}-\d{4}",
			"CS" => "\d{5}",
			"CV" => "\d{4}",
			"CX" => "6798",
			"CY" => "\d{4}",
			"CZ" => "\d{3}[ ]?\d{2}",
			"DE" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
			"DK" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
			"DO" => "\d{5}",
			"DZ" => "\d{5}",
			"EC" => "([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?",
			"EE" => "\d{5}",
			"EG" => "\d{5}",
			"ES" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
			"ET" => "\d{4}",
			"FI" => "\d{5}",
			"FK" => "FIQQ 1ZZ",
			"FM" => "(9694[1-4])([ \-]\d{4})?",
			"FO" => "\d{3}",
			"FR" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
			"GE" => "\d{4}",
			"GF" => "9[78]3\d{2}",
			"GL" => "39\d{2}",
			"GN" => "\d{3}",
			"GP" => "9[78][01]\d{2}",
			"GR" => "\d{3}[ ]?\d{2}",
			"GS" => "SIQQ 1ZZ",
			"GT" => "\d{5}",
			"GU" => "969[123]\d([ \-]\d{4})?",
			"GW" => "\d{4}",
			"HM" => "\d{4}",
			"HN" => "(?:\d{5})?",
			"HR" => "\d{5}",
			"HT" => "\d{4}",
			"HU" => "\d{4}",
			"ID" => "\d{5}",
			"IE" => "((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?",
			"IL" => "\d{5}",
			"IN" => "^[1-9][0-9][0-9][0-9][0-9][0-9]$", //india
			"IO" => "BBND 1ZZ",
			"IQ" => "\d{5}",
			"IS" => "\d{3}",
			"IT" => "^(V-|I-)?[0-9]{5}$",
			"JO" => "\d{5}",
			"JP" => "\d{3}-\d{4}",
			"KE" => "\d{5}",
			"KG" => "\d{6}",
			"KH" => "\d{5}",
			"KR" => "\d{3}[\-]\d{3}",
			"KW" => "\d{5}",
			"KZ" => "\d{6}",
			"LA" => "\d{5}",
			"LB" => "(\d{4}([ ]?\d{4})?)?",
			"LI" => "(948[5-9])|(949[0-7])",
			"LK" => "\d{5}",
			"LR" => "\d{4}",
			"LS" => "\d{3}",
			"LT" => "\d{5}",
			"LU" => "\d{4}",
			"LV" => "\d{4}",
			"MA" => "\d{5}",
			"MC" => "980\d{2}",
			"MD" => "\d{4}",
			"ME" => "8\d{4}",
			"MG" => "\d{3}",
			"MH" => "969[67]\d([ \-]\d{4})?",
			"MK" => "\d{4}",
			"MN" => "\d{6}",
			"MP" => "9695[012]([ \-]\d{4})?",
			"MQ" => "9[78]2\d{2}",
			"MT" => "[A-Z]{3}[ ]?\d{2,4}",
			"MU" => "(\d{3}[A-Z]{2}\d{3})?",
			"MV" => "\d{5}",
			"MX" => "\d{5}",
			"MY" => "\d{5}",
			"NC" => "988\d{2}",
			"NE" => "\d{4}",
			"NF" => "2899",
			"NG" => "(\d{6})?",
			"NI" => "((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?",
			"NL" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
			"NO" => "\d{4}",
			"NP" => "\d{5}",
			"NZ" => "\d{4}",
			"OM" => "(PC )?\d{3}",
			"PF" => "987\d{2}",
			"PG" => "\d{3}",
			"PH" => "\d{4}",
			"PK" => "\d{5}",
			"PL" => "\d{2}-\d{3}",
			"PM" => "9[78]5\d{2}",
			"PN" => "PCRN 1ZZ",
			"PR" => "00[679]\d{2}([ \-]\d{4})?",
			"PT" => "\d{4}([\-]\d{3})?",
			"PW" => "96940",
			"PY" => "\d{4}",
			"RE" => "9[78]4\d{2}",
			"RO" => "\d{6}",
			"RS" => "\d{5}",
			"RU" => "\d{6}",
			"SA" => "\d{5}",
			"SE" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
			"SG" => "\d{6}",
			"SH" => "(ASCN|STHL) 1ZZ",
			"SI" => "\d{4}",
			"SJ" => "\d{4}",
			"SK" => "\d{3}[ ]?\d{2}",
			"SM" => "4789\d",
			"SN" => "\d{5}",
			"SO" => "\d{5}",
			"SZ" => "[HLMS]\d{3}",
			"TC" => "TKCA 1ZZ",
			"TH" => "\d{5}",
			"TJ" => "\d{6}",
			"TM" => "\d{6}",
			"TN" => "\d{4}",
			"TR" => "\d{5}",
			"TW" => "\d{3}(\d{2})?",
			"UA" => "\d{5}",
			"UK" => "^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
			"US" => "^\d{5}([\-]?\d{4})?$",
			"UY" => "\d{5}",
			"UZ" => "\d{6}",
			"VA" => "00120",
			"VE" => "\d{4}",
			"VI" => "008(([0-4]\d)|(5[01]))([ \-]\d{4})?",
			"WF" => "986\d{2}",
			"YT" => "976\d{2}",
			"YU" => "\d{5}",
			"ZA" => "\d{4}",
			"ZM" => "\d{5}"
		);
		if (!isset ($zip_regex[$country_code]) || preg_match("/" . $zip_regex[$country_code] . "/i", $zip))
			$ret = true;
		return apply_filters('mprm_is_zip_valid', $ret, $zip, $country_code);
	}

	/**
	 * Validate phone
	 *
	 * @return bool|string
	 */
	public function purchase_form_validate_phone() {
		$number = sanitize_text_field($_POST['phone_number']);
//		$regex = "/(\+?\d[- .]*){7,13}/i";
//		$valid = preg_match($regex, $number) ? true : false;
//		if (!$valid) {
//			$this->get('errors')->set_error('invalid_discount', __('Invalid phone number format. error even when I enter the correct number format', 'mp-restaurant-menu'));
//		}
//		return $valid ? $number : false;
		return $number;
	}

	/**
	 * Validate agree to terms
	 */
	public function purchase_form_validate_agree_to_terms() {
		// Validate agree to terms
		if (!isset($_POST['mprm_agree_to_terms']) || $_POST['mprm_agree_to_terms'] != 1) {
			// User did not agree
			$this->get('errors')->set_error('agree_to_terms', apply_filters('mprm_agree_to_terms_text', __('You must agree to the terms of use', 'mp-restaurant-menu')));
		}
	}

	/**
	 * Validate logged in user
	 *
	 * @return array
	 */
	public function purchase_form_validate_logged_in_user() {
		global $user_ID;
		// Start empty array to collect valid user data
		$valid_user_data = array(
			// Assume there will be errors
			'user_id' => -1
		);
		// Verify there is a user_ID
		if ($user_ID > 0) {
			// Get the logged in user data
			$user_data = get_userdata($user_ID);
			// Loop through required fields and show error messages
			foreach ($this->purchase_form_required_fields() as $field_name => $value) {
				if (in_array($value, $this->purchase_form_required_fields()) && empty($_POST[$field_name])) {
					$this->get('errors')->set_error($value['error_id'], $value['error_message']);
				}
			}
			// Verify data
			if ($user_data) {
				// Collected logged in user data
				$valid_user_data = array(
					'user_id' => $user_ID,
					'user_email' => isset($_POST['mprm_email']) ? sanitize_email($_POST['mprm_email']) : $user_data->user_email,
					'user_first' => isset($_POST['mprm_first']) && !empty($_POST['mprm_first']) ? sanitize_text_field($_POST['mprm_first']) : $user_data->first_name,
					'user_last' => isset($_POST['mprm_last']) && !empty($_POST['mprm_last']) ? sanitize_text_field($_POST['mprm_last']) : $user_data->last_name,
				);
				if (!is_email($valid_user_data['user_email'])) {
					$this->get('errors')->set_error('email_invalid', __('Invalid email', 'mp-restaurant-menu'));
				}
			} else {
				// Set invalid user error
				$this->get('errors')->set_error('invalid_user', __('The user information is invalid', 'mp-restaurant-menu'));
			}
		}
		// Return user data
		return $valid_user_data;
	}

	/**
	 * Required fields
	 *
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
		if ($require_address && $this->get('settings')->get_option('taxes_cc_form', false)) {
			$required_fields['card_zip'] = array(
				'error_id' => 'invalid_zip_code',
				'error_message' => __('Please enter your zip / postal code', 'mp-restaurant-menu')
			);
			$required_fields['card_city'] = array(
				'error_id' => 'invalid_city',
				'error_message' => __('Please enter your billing city', 'mp-restaurant-menu')
			);
			$required_fields['billing_country'] = array(
				'error_id' => 'invalid_country',
				'error_message' => __('Please select your billing country', 'mp-restaurant-menu')
			);
			$required_fields['card_state'] = array(
				'error_id' => 'invalid_state',
				'error_message' => __('Please enter billing state / province', 'mp-restaurant-menu')
			);
		}
		return apply_filters('mprm_purchase_form_required_fields', $required_fields);
	}

	/**
	 * Validate new user
	 *
	 * @return array
	 */
	public function purchase_form_validate_new_user() {
		$registering_new_user = false;
		// Start an empty array to collect valid user data
		$valid_user_data = array(
			// Assume there will be errors
			'user_id' => -1,
			// Get first name
			'user_first' => isset($_POST["mprm_first"]) ? sanitize_text_field($_POST["mprm_first"]) : '',
			// Get last name
			'user_last' => isset($_POST["mprm_last"]) ? sanitize_text_field($_POST["mprm_last"]) : '',
		);
		// Check the new user's credentials against existing ones
		$user_login = isset($_POST["mprm_user_login"]) ? trim($_POST["mprm_user_login"]) : false;
		$user_email = isset($_POST['mprm_email']) ? trim($_POST['mprm_email']) : false;
		$user_pass = isset($_POST["mprm_user_pass"]) ? trim($_POST["mprm_user_pass"]) : false;
		$pass_confirm = isset($_POST["mprm_user_pass_confirm"]) ? trim($_POST["mprm_user_pass_confirm"]) : false;
		// Loop through required fields and show error messages
		foreach ($this->purchase_form_required_fields() as $field_name => $value) {
			if (in_array($value, $this->purchase_form_required_fields()) && empty($_POST[$field_name])) {
				$this->get('errors')->set_error($value['error_id'], $value['error_message']);
			}
		}
		// Check if we have an username to register
		if ($user_login && strlen($user_login) > 0) {
			$registering_new_user = true;
			// We have an user name, check if it already exists
			if (username_exists($user_login)) {
				// Username already registered
				$this->get('errors')->set_error('username_unavailable', __('Username already taken', 'mp-restaurant-menu'));
				// Check if it's valid
			} else if (!$this->get('customer')->validate_username($user_login)) {
				// Invalid username
				if (is_multisite())
					$this->get('errors')->set_error('username_invalid', __('Invalid username. Only lowercase letters (a-z) and numbers are allowed', 'mp-restaurant-menu'));
				else
					$this->get('errors')->set_error('username_invalid', __('Invalid username', 'mp-restaurant-menu'));
			} else {
				// All the checks have run and it's good to go
				$valid_user_data['user_login'] = $user_login;
			}
		} else {
			if ($this->get('misc')->no_guest_checkout()) {
				$this->get('errors')->set_error('registration_required', __('You must register or login to complete your purchase', 'mp-restaurant-menu'));
			}
		}
		// Check if we have an email to verify
		if ($user_email && strlen($user_email) > 0) {
			// Validate email
			if (!is_email($user_email)) {
				$this->get('errors')->set_error('email_invalid', __('Invalid email', 'mp-restaurant-menu'));
				// Check if email exists
			} else if (email_exists($user_email) && $registering_new_user) {
				$this->get('errors')->set_error('email_used', __('Email already used', 'mp-restaurant-menu'));
			} else {
				// All the checks have run and it's good to go
				$valid_user_data['user_email'] = $user_email;
			}
		} else {
			// No email
			$this->get('errors')->set_error('email_empty', __('Enter an email', 'mp-restaurant-menu'));
		}
		// Check password
		if ($user_pass && $pass_confirm) {
			// Verify confirmation matches
			if ($user_pass != $pass_confirm) {
				// Passwords do not match
				$this->get('errors')->set_error('password_mismatch', __('Passwords don\'t match', 'mp-restaurant-menu'));
			} else {
				// All is good to go
				$valid_user_data['user_pass'] = $user_pass;
			}
		} else {
			// Password or confirmation missing
			if (!$user_pass && $registering_new_user) {
				// The password is invalid
				$this->get('errors')->set_error('password_empty', __('Enter a password', 'mp-restaurant-menu'));
			} else if (!$pass_confirm && $registering_new_user) {
				// Confirmation password is invalid
				$this->get('errors')->set_error('confirmation_empty', __('Enter the password confirmation', 'mp-restaurant-menu'));
			}
		}
		return $valid_user_data;
	}

	/**
	 * Validate user login
	 *
	 * @return array
	 */
	public function purchase_form_validate_user_login() {
		// Start an array to collect valid user data
		$valid_user_data = array(
			// Assume there will be errors
			'user_id' => -1
		);
		// Username
		if (empty($_POST['mprm_user_login']) && $this->get('misc')->no_guest_checkout()) {
			$this->get('errors')->set_error('must_log_in', __('You must login or register to complete your purchase', 'mp-restaurant-menu'));
			return $valid_user_data;
		}
		// Get the user by login
		$user_data = get_user_by('login', strip_tags($_POST['mprm_user_login']));
		// Check if user exists
		if ($user_data) {
			// Get password
			$user_pass = isset($_POST["mprm_user_pass"]) ? $_POST["mprm_user_pass"] : false;
			// Check user_pass
			if ($user_pass) {
				// Check if password is valid
				if (!wp_check_password($user_pass, $user_data->user_pass, $user_data->ID)) {
					// Incorrect password
					$this->get('errors')->set_error(
						'password_incorrect',
						sprintf(
							__('The password you entered is incorrect. %sReset Password%s', 'mp-restaurant-menu'),
							'<a href="' . wp_lostpassword_url($this->get('checkout')->get_checkout_uri()) . '" title="' . __('Lost Password', 'mp-restaurant-menu') . '">',
							'</a>'
						)
					);
					// All is correct
				} else {
					// Repopulate the valid user data array
					$valid_user_data = array(
						'user_id' => $user_data->ID,
						'user_login' => $user_data->user_login,
						'user_email' => $user_data->user_email,
						'user_first' => $user_data->first_name,
						'user_last' => $user_data->last_name,
						'user_pass' => $user_pass,
					);
				}
			} else {
				// Empty password
				$this->get('errors')->set_error('password_empty', __('Enter a password', 'mp-restaurant-menu'));
			}
		} else {
			// no username
			$this->get('errors')->set_error('username_incorrect', __('The username you entered does not exist', 'mp-restaurant-menu'));
		}
		return $valid_user_data;
	}

	/**
	 * Validate guest user
	 *
	 * @return array
	 */
	public function purchase_form_validate_guest_user() {
		// Start an array to collect valid user data
		$valid_user_data = array(
			// Set a default id for guests
			'user_id' => 0,
		);
		// Show error message if user must be logged in
		if ($this->get('settings')->logged_in_only()) {
			$this->get('errors')->set_error('logged_in_only', __('You must be logged into an account to purchase', 'mp-restaurant-menu'));
		}
		// Get the guest email
		$guest_email = isset($_POST['mprm_email']) ? $_POST['mprm_email'] : false;
		// Check email
		if ($guest_email && strlen($guest_email) > 0) {
			// Validate email
			if (!is_email($guest_email)) {
				// Invalid email
				$this->get('errors')->set_error('email_invalid', __('Invalid email', 'mp-restaurant-menu'));
			} else {
				// All is good to go
				$valid_user_data['user_email'] = $guest_email;
			}
		} else {
			// No email
			$this->get('errors')->set_error('email_empty', __('Enter an email', 'mp-restaurant-menu'));
		}
		// Loop through required fields and show error messages
		foreach ($this->purchase_form_required_fields() as $field_name => $value) {
			if (in_array($value, $this->purchase_form_required_fields()) && empty($_POST[$field_name])) {
				$this->get('errors')->set_error($value['error_id'], $value['error_message']);
			}
		}
		return $valid_user_data;
	}

	/**
	 * Process purchase login
	 */
	public function process_purchase_login() {
		$is_ajax = isset($_POST['mprm_ajax']);
		$user_data = $this->purchase_form_validate_user_login();
		if ($this->get('errors')->get_errors() || $user_data['user_id'] < 1) {
			if ($is_ajax) {
				do_action('mprm_ajax_checkout_errors');
				wp_die();
			} else {
				wp_redirect($_SERVER['HTTP_REFERER']);
				exit;
			}
		}
		$this->get('customer')->log_user_in($user_data['user_id'], $user_data['user_login'], $user_data['user_pass']);
		if ($is_ajax) {
			//wp_die();
		} else {
			wp_redirect($this->get('checkout')->get_checkout_uri($_SERVER['QUERY_STRING']));
		}
	}

	/**
	 * Get purchase form user
	 *
	 * @param array $valid_data
	 *
	 * @return bool
	 */
	public function get_purchase_form_user($valid_data = array()) {
		// Initialize user
		$user = false;
		$is_ajax = defined('DOING_AJAX') && DOING_AJAX;
		if (is_user_logged_in()) {
			// Set the valid user as the logged in collected data
			$user = $valid_data['logged_in_user'];
		} else if ($valid_data['need_new_user'] === true || $valid_data['need_user_login'] === true) {
			// New user registration
			if ($valid_data['need_new_user'] === true) {
				// Set user
				$user = $valid_data['new_user_data'];
				// Register and login new user
				$user['user_id'] = $this->register_and_login_new_user($user);
				// User login
			} else if ($valid_data['need_user_login'] === true && !$is_ajax) {
				// Set user
				$user = $valid_data['login_user_data'];
				// Login user
				$this->get('customer')->log_user_in($user['user_id'], $user['user_login'], $user['user_pass']);
			}
		}
		// Check guest checkout
		if (false === $user && false === $this->get('misc')->no_guest_checkout()) {
			// Set user
			$user = $valid_data['guest_user_data'];
		}
		// Verify we have an user
		if (false === $user || empty($user)) {
			// Return false
			return false;
		}
		// Get user first name
		if (!isset($user['user_first']) || strlen(trim($user['user_first'])) < 1) {
			$user['user_first'] = isset($_POST["mprm_first"]) ? strip_tags(trim($_POST["mprm_first"])) : '';
		}
		// Get user last name
		if (!isset($user['user_last']) || strlen(trim($user['user_last'])) < 1) {
			$user['user_last'] = isset($_POST["mprm_last"]) ? strip_tags(trim($_POST["mprm_last"])) : '';
		}
		// Get the user's billing address details
		$user['address'] = array();
		$user['address']['line1'] = !empty($_POST['card_address']) ? sanitize_text_field($_POST['card_address']) : false;
		$user['address']['line2'] = !empty($_POST['card_address_2']) ? sanitize_text_field($_POST['card_address_2']) : false;
		$user['address']['city'] = !empty($_POST['card_city']) ? sanitize_text_field($_POST['card_city']) : false;
		$user['address']['state'] = !empty($_POST['card_state']) ? sanitize_text_field($_POST['card_state']) : false;
		$user['address']['country'] = !empty($_POST['billing_country']) ? sanitize_text_field($_POST['billing_country']) : false;
		$user['address']['zip'] = !empty($_POST['card_zip']) ? sanitize_text_field($_POST['card_zip']) : false;
		if (empty($user['address']['country']))
			$user['address'] = false; // Country will always be set if address fields are present
		if (!empty($user['user_id']) && $user['user_id'] > 0 && !empty($user['address'])) {
			// Store the address in the user's meta so the cart can be pre-populated with it on return purchases
			update_user_meta($user['user_id'], '_mprm_user_address', $user['address']);
		}
		// Return valid user
		return $user;
	}

	/**
	 * Register and login new user
	 *
	 * @param array $user_data
	 *
	 * @return int|\WP_Error
	 */
	public function register_and_login_new_user($user_data = array()) {
		// Verify the array
		if (empty($user_data))
			return -1;
		if ($this->get('errors')->get_errors())
			return -1;
		$user_args = apply_filters('mprm_insert_user_args', array(
			'user_login' => isset($user_data['user_login']) ? $user_data['user_login'] : '',
			'user_pass' => isset($user_data['user_pass']) ? $user_data['user_pass'] : '',
			'user_email' => isset($user_data['user_email']) ? $user_data['user_email'] : '',
			'first_name' => isset($user_data['user_first']) ? $user_data['user_first'] : '',
			'last_name' => isset($user_data['user_last']) ? $user_data['user_last'] : '',
			'user_registered' => date('Y-m-d H:i:s'),
			'role' => 'mprm_customer'
		), $user_data);
		// Insert new user
		$user_id = wp_insert_user($user_args);
		// Validate inserted user
		if (is_wp_error($user_id))
			return -1;
		// Allow themes and plugins to filter the user data
		$user_data = apply_filters('mprm_insert_user_data', $user_data, $user_args);
		// Allow themes and plugins to hook
		do_action('mprm_insert_user', $user_id, $user_data);
		// Login new user
		$this->get('customer')->log_user_in($user_id, $user_data['user_login'], $user_data['user_pass']);
		// Return user id
		return $user_id;
	}

	/**
	 * Check purchase email
	 *
	 * @param $valid_data
	 *
	 * @param $posted
	 */
	public function check_purchase_email($valid_data, $posted) {
		$is_banned = false;
		$banned = $this->get('emails')->get_banned_emails();
		if (empty($banned)) {
			return;
		}
		if (is_user_logged_in()) {
			// The user is logged in, check that their account email is not banned
			$user_data = get_userdata(get_current_user_id());
			if ($this->get('emails')->is_email_banned($user_data->user_email)) {
				$is_banned = true;
			}
			if ($this->get('emails')->is_email_banned($posted['mprm_email'])) {
				$is_banned = true;
			}
		} elseif (isset($posted['mprm-purchase-var']) && $posted['mprm-purchase-var'] == 'needs-to-login') {
			// The user is logging in, check that their email is not banned
			$user_data = get_user_by('login', $posted['mprm_user_login']);
			if ($user_data && $this->get('emails')->is_email_banned($user_data->user_email)) {
				$is_banned = true;
			}
		} else {
			// Guest purchase, check that the email is not banned
			if ($this->get('emails')->is_email_banned($posted['mprm_email'])) {
				$is_banned = true;
			}
		}
		if ($is_banned) {
			// Set an error and give the customer a general error (don't alert them that they were banned)
			$this->get('errors')->set_error('email_banned', __('An internal error has occurred, please try again or contact support.', 'mp-restaurant-menu'));
		}
	}

	/**
	 * Process straight to gateway
	 *
	 * @param $data
	 */
	public function process_straight_to_gateway($data) {
		$menu_item_id = $data['menu_item_id'];
		$options = isset($data['mprm_options']) ? $data['mprm_options'] : array();
		$quantity = isset($data['mprm_menu_item_quantity']) ? $data['mprm_menu_item_quantity'] : 1;
		if (empty($menu_item_id) || !$this->get('menu_item')->get_menu_item($menu_item_id)) {
			return;
		}
		$purchase_data = $this->get('gateways')->build_straight_to_gateway_data($menu_item_id, $options, $quantity);
		$this->get('session')->set('mprm_purchase', $purchase_data);
		$this->get('gateways')->send_to_gateway($purchase_data['gateway'], $purchase_data);
	}

	/**
	 * Init purchase actions
	 */
	public function init_action() {
		add_action('mprm_straight_to_gateway', array($this, 'process_straight_to_gateway'));
		add_action('mprm_purchase', array($this, 'process_purchase_form'));
		add_action('wp_ajax_mprm_process_checkout', array($this, 'process_purchase_form'));
		add_action('wp_ajax_nopriv_mprm_process_checkout', array($this, 'process_purchase_form'));
		add_action('mprm_checkout_error_checks', array($this, 'check_purchase_email'), 10, 2);
		add_action('wp_ajax_mprm_process_checkout_login', array($this, 'process_purchase_login'));
		add_action('wp_ajax_nopriv_mprm_process_checkout_login', array($this, 'process_purchase_login'));
	}
}