<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\View;

/**
 * Class Paypal_standart
 * @package mp_restaurant_menu\classes\models
 */
class Paypal_standart extends Model {
	protected static $instance;

	/**
	 * @return Paypal_standart
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param $purchase_data
	 */
	public function process_paypal_purchase($purchase_data) {
		if (!wp_verify_nonce($purchase_data['gateway_nonce'], 'mprm-gateway')) {
			wp_die(__('Nonce verification has failed', 'mp-restaurant-menu'), __('Error', 'mp-restaurant-menu'), array('response' => 403));
		}
		// Collect payment data
		$payment_data = array(
			'price' => $purchase_data['price'],
			'date' => $purchase_data['date'],
			'user_email' => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency' => $this->get('settings')->get_currency(),
			'menu_items' => $purchase_data['menu_items'],
			'user_info' => $purchase_data['user_info'],
			'cart_details' => $purchase_data['cart_details'],
			'gateway' => 'paypal',
			'status' => !empty($purchase_data['buy_now']) ? 'private' : 'mprm-pending'
		);
		// Record the pending payment
		$payment = $this->get('payments')->insert_payment($payment_data);
		// Check payment
		if (!$payment) {
			// Record the error
			//mprm_record_gateway_error(__('Payment Error', 'mp-restaurant-menu'), sprintf(__('Payment creation failed before sending buyer to PayPal. Payment data: %s', 'mp-restaurant-menu'), json_encode($payment_data)), $payment);
			// Problems? send back
			$this->get('checkout')->send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['mprm-gateway']);
		} else {
			// Only send to PayPal if the pending payment is created successfully
			$listener_url = add_query_arg(array('mprm-listener' => 'IPN'), home_url('index.php'));
			// Get the success url
			$return_url = add_query_arg(array(
				'payment-confirmation' => 'paypal',
				'payment-id' => $payment
			), get_permalink($this->get('settings')->get_option('success_page', false)));
			// Get the PayPal redirect uri
			$paypal_redirect = trailingslashit($this->get_paypal_redirect()) . '?';
			// Setup PayPal arguments
			$paypal_args = array(
				'business' => $this->get('settings')->get_option('paypal_email', false),
				'email' => $purchase_data['user_email'],
				'first_name' => $purchase_data['user_info']['first_name'],
				'last_name' => $purchase_data['user_info']['last_name'],
				'invoice' => $purchase_data['purchase_key'],
				'no_shipping' => '1',
				'shipping' => '0',
				'no_note' => '1',
				'currency_code' => $this->get('settings')->get_currency(),
				'charset' => get_bloginfo('charset'),
				'custom' => $payment,
				'rm' => '2',
				'return' => $return_url,
				'cancel_return' => $this->get('checkout')->get_failed_transaction_uri('?payment-id=' . $payment),
				'notify_url' => $listener_url,
				'page_style' => $this->get_paypal_page_style(),
				'cbt' => get_bloginfo('name'),
				'bn' => 'MotoPress_SP_MPRM'
			);
			if (!empty($purchase_data['user_info']['address'])) {
				$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
				$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
				$paypal_args['city'] = $purchase_data['user_info']['address']['city'];
				$paypal_args['country'] = $purchase_data['user_info']['address']['country'];
			}
			$paypal_extra_args = array(
				'cmd' => '_cart',
				'upload' => '1'
			);
			$paypal_args = array_merge($paypal_extra_args, $paypal_args);
			// Add cart items
			$i = 1;
			if (is_array($purchase_data['cart_details']) && !empty($purchase_data['cart_details'])) {
				foreach ($purchase_data['cart_details'] as $item) {
					$item_amount = round(($item['subtotal'] / $item['quantity']) - ($item['discount'] / $item['quantity']), 2);
					if ($item_amount <= 0) {
						$item_amount = 0;
					}
					$paypal_args['item_name_' . $i] = stripslashes_deep(html_entity_decode($this->get('cart')->get_cart_item_name($item), ENT_COMPAT, 'UTF-8'));
					$paypal_args['quantity_' . $i] = $item['quantity'];
					$paypal_args['amount_' . $i] = $item_amount;
					if ($this->get('misc')->use_skus()) {
						$paypal_args['item_number_' . $i] = mprm_get_menu_item_sku($item['id']);
					}
					$i++;
				}
			}
			// Calculate discount
			$discounted_amount = 0.00;
			if (!empty($purchase_data['fees'])) {
				$i = empty($i) ? 1 : $i;
				foreach ($purchase_data['fees'] as $fee) {
					if (floatval($fee['amount']) > '0') {
						// this is a positive fee
						$paypal_args['item_name_' . $i] = stripslashes_deep(html_entity_decode(wp_strip_all_tags($fee['label']), ENT_COMPAT, 'UTF-8'));
						$paypal_args['quantity_' . $i] = '1';
						$paypal_args['amount_' . $i] = $this->get('formatting')->sanitize_amount($fee['amount']);
						$i++;
					} else {
						$discounted_amount += abs($fee['amount']);
					}
				}
			}
			if ($discounted_amount > '0') {
				$paypal_args['discount_amount_cart'] = $this->get('formatting')->sanitize_amount($discounted_amount);
			}
			// Add taxes to the cart
			if ($this->get('taxes')->use_taxes()) {
				$paypal_args['tax_cart'] = $this->get('formatting')->sanitize_amount($purchase_data['tax']);
			}

			if (isset($purchase_data['shipping'])) {
				$paypal_args['no_shipping'] = '0';
				$paypal_args['shipping_1'] = apply_filters('mprm_shipping_cost_gateway', $purchase_data['shipping_cost']);
			}


			$paypal_args = apply_filters('mprm_paypal_redirect_args', $paypal_args, $purchase_data);
			// Build query
			$paypal_redirect .= http_build_query($paypal_args);
			// Fix for some sites that encode the entities
			$paypal_redirect = str_replace('&amp;', '&', $paypal_redirect);
			// Get rid of cart contents
			$this->get('cart')->empty_cart();
			// Redirect to PayPal
			wp_redirect($paypal_redirect);

			exit;
		}
	}

	/**
	 * Get PayPal Redirect
	 *
	 * @since 1.0.8.2
	 *
	 * @param bool $ssl_check Is SSL?
	 *
	 * @return string
	 */
	public function get_paypal_redirect($ssl_check = false) {
		if (is_ssl() || !$ssl_check) {
			$protocal = 'https://';
		} else {
			$protocal = 'http://';
		}
		// Check the current payment mode
		if ($this->get('misc')->is_test_mode()) {
			// Test mode
			$paypal_uri = $protocal . 'www.sandbox.paypal.com/cgi-bin/webscr';
		} else {
			// Live mode
			$paypal_uri = $protocal . 'www.paypal.com/cgi-bin/webscr';
		}
		return apply_filters('mprm_paypal_uri', $paypal_uri);
	}

	/**
	 * Set the Page Style for PayPal Purchase page
	 *
	 * @since 1.4.1
	 * @return string
	 */
	public function get_paypal_page_style() {
		$page_style = trim($this->get('settings')->get_option('paypal_page_style', 'PayPal'));
		return apply_filters('mprm_paypal_page_style', $page_style);
	}

	/**
	 * Listens for a PayPal IPN requests and then sends to the processing function
	 *
	 * @since 1.0
	 * @return void
	 */
	public function listen_for_paypal_ipn() {
		// Regular PayPal IPN
		if (isset($_GET['mprm-listener']) && $_GET['mprm-listener'] == 'IPN') {

			do_action('mprm_verify_paypal_ipn');
		}
	}

	/**
	 * Process PayPal IPN
	 *
	 * @since 1.0
	 * @return void
	 */
	public function process_paypal_ipn() {
		// Check the request method is POST
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
			return;
		}
		// Set initial post data to empty string
		$post_data = '';
		// Fallback just in case post_max_size is lower than needed
		if (ini_get('allow_url_fopen')) {
			$post_data = file_get_contents('php://input');
		} else {
			// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
			ini_set('post_max_size', '12M');
		}
		// Start the encoded data collection with notification command
		$encoded_data = 'cmd=_notify-validate';
		// Get current arg separator
		$arg_separator = $this->get('misc')->get_php_arg_separator_output();
		// Verify there is a post_data
		if ($post_data || strlen($post_data) > 0) {
			// Append the data
			$encoded_data .= $arg_separator . $post_data;
		} else {
			// Check if POST is empty
			if (empty($_POST)) {
				// Nothing to do
				return;
			} else {
				// Loop through each POST
				foreach ($_POST as $key => $value) {
					// Encode the value and append the data
					$encoded_data .= $arg_separator . "$key=" . urlencode($value);
				}
			}
		}
		// Convert collected post data to an array
		parse_str($encoded_data, $encoded_data_array);
		foreach ($encoded_data_array as $key => $value) {
			if (false !== strpos($key, 'amp;')) {
				$new_key = str_replace('&amp;', '&', $key);
				$new_key = str_replace('amp;', '&', $new_key);
				unset($encoded_data_array[$key]);
				$encoded_data_array[$new_key] = $value;
			}
		}
		// Get the PayPal redirect uri
		$paypal_redirect = $this->get_paypal_redirect(true);
		if (!$this->get('settings')->get_option('disable_paypal_verification')) {
			// Validate the IPN
			$remote_post_vars = array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'host' => 'www.paypal.com',
					'connection' => 'close',
					'content-type' => 'application/x-www-form-urlencoded',
					'post' => '/cgi-bin/webscr HTTP/1.1',
				),
				'sslverify' => false,
				'body' => $encoded_data_array
			);
			// Get response
			$api_response = wp_remote_post($this->get_paypal_redirect(), $remote_post_vars);
			if (is_wp_error($api_response)) {
				//	mprm_record_gateway_error(__('IPN Error', 'mp-restaurant-menu'), sprintf(__('Invalid IPN verification response. IPN data: %s', 'mp-restaurant-menu'), json_encode($api_response)));
				return; // Something went wrong
			}
			if ($api_response['body'] !== 'VERIFIED' && $this->get('settings')->get_option('disable_paypal_verification', false)) {
				//	mprm_record_gateway_error(__('IPN Error', 'mp-restaurant-menu'), sprintf(__('Invalid IPN verification response. IPN data: %s', 'mp-restaurant-menu'), json_encode($api_response)));
				return; // Response not okay
			}
		}
		// Check if $post_data_array has been populated
		if (!is_array($encoded_data_array) && !empty($encoded_data_array))
			return;
		$defaults = array(
			'txn_type' => '',
			'payment_status' => ''
		);
		$encoded_data_array = wp_parse_args($encoded_data_array, $defaults);
		$payment_id = isset($encoded_data_array['custom']) ? absint($encoded_data_array['custom']) : 0;
		if (has_action('mprm_paypal_' . $encoded_data_array['txn_type'])) {
			// Allow PayPal IPN types to be processed separately
			do_action('mprm_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id);
		} else {
			// Fallback to web accept just in case the txn_type isn't present
			do_action('mprm_paypal_web_accept', $encoded_data_array, $payment_id);
		}
		exit;
	}

	/**
	 * Process web accept (one time) payment IPNs
	 *
	 * @since 1.3.4
	 *
	 * @param array $data IPN Data
	 * @param int $payment_id
	 *
	 * @return void
	 */
	public function process_paypal_web_accept_and_cart($data, $payment_id) {
		if ($data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded') {
			return;
		}
		if (empty($payment_id)) {
			return;
		}
		// Collect payment details
		$purchase_key = isset($data['invoice']) ? $data['invoice'] : $data['item_number'];
		$paypal_amount = $data['mc_gross'];
		$payment_status = strtolower($data['payment_status']);
		$currency_code = strtolower($data['mc_currency']);
		$business_email = isset($data['business']) && is_email($data['business']) ? trim($data['business']) : trim($data['receiver_email']);
		$payment_meta = $this->get('payments')->get_payment_meta($payment_id);

		if ($this->get('payments')->get_payment_gateway($payment_id) != 'paypal') {
			return; // this isn't a PayPal standard IPN
		}
		// Verify payment recipient
		if (strcasecmp($business_email, trim($this->get('settings')->get_option('paypal_email', false))) != 0) {
			//mprm_record_gateway_error(__('IPN Error', 'mp-restaurant-menu'), sprintf(__('Invalid business email in IPN response. IPN data: %s', 'mp-restaurant-menu'), json_encode($data)), $payment_id);
			$this->get('payments')->update_payment_status($payment_id, 'mprm-failed');
			$this->get('payments')->insert_payment_note($payment_id, __('Payment failed due to invalid PayPal business email.', 'mp-restaurant-menu'));
			return;
		}
		// Verify payment currency
		if ($currency_code != strtolower($payment_meta['currency'])) {
			//mprm_record_gateway_error(__('IPN Error', 'mp-restaurant-menu'), sprintf(__('Invalid currency in IPN response. IPN data: %s', 'mp-restaurant-menu'), json_encode($data)), $payment_id);
			$this->get('payments')->update_payment_status($payment_id, 'mprm-failed');
			$this->get('payments')->insert_payment_note($payment_id, __('Payment failed due to invalid currency in PayPal IPN.', 'mp-restaurant-menu'));
			return;
		}
		if (!$this->get('payments')->get_payment_user_email($payment_id)) {
			// This runs when a Buy Now purchase was made. It bypasses checkout so no personal info is collected until PayPal
			// No email associated with purchase, so store from PayPal
			$this->get('payments')->update_payment_meta($payment_id, '_mprm_order_user_email', $data['payer_email']);
			// Setup and store the customers's details
			$address = array();
			$address['line1'] = !empty($data['address_street']) ? sanitize_text_field($data['address_street']) : false;
			$address['city'] = !empty($data['address_city']) ? sanitize_text_field($data['address_city']) : false;
			$address['state'] = !empty($data['address_state']) ? sanitize_text_field($data['address_state']) : false;
			$address['country'] = !empty($data['address_country_code']) ? sanitize_text_field($data['address_country_code']) : false;
			$address['zip'] = !empty($data['address_zip']) ? sanitize_text_field($data['address_zip']) : false;
			$user_info = array(
				'id' => '-1',
				'email' => sanitize_text_field($data['payer_email']),
				'first_name' => sanitize_text_field($data['first_name']),
				'last_name' => sanitize_text_field($data['last_name']),
				'discount' => '',
				'address' => $address
			);
			$payment_meta['user_info'] = $user_info;
			$this->get('payments')->update_payment_meta($payment_id, '_mprm_order_meta', $payment_meta);
		}
		if ($payment_status == 'refunded' || $payment_status == 'reversed') {
			// Process a refund
			$this->process_paypal_refund($data, $payment_id);
		} else {
			if (get_post_status($payment_id) == 'publish') {
				return; // Only complete payments once
			}
			// Retrieve the total purchase amount (before PayPal)
			$payment_amount = $this->get('payments')->get_payment_amount($payment_id);
			if (number_format((float)$paypal_amount, 2) < number_format((float)$payment_amount, 2)) {
				// The prices don't match
				//mprm_record_gateway_error(__('IPN Error', 'mp-restaurant-menu'), sprintf(__('Invalid payment amount in IPN response. IPN data: %s', 'mp-restaurant-menu'), json_encode($data)), $payment_id);
				$this->get('payments')->update_payment_status($payment_id, 'mprm-failed');
				$this->get('payments')->insert_payment_note($payment_id, __('Payment failed due to invalid amount in PayPal IPN.', 'mp-restaurant-menu'));
				return;
			}
			if ($purchase_key != $this->get('payments')->get_payment_key($payment_id)) {
				// Purchase keys don't match
				//mprm_record_gateway_error(__('IPN Error', 'mp-restaurant-menu'), sprintf(__('Invalid purchase key in IPN response. IPN data: %s', 'mp-restaurant-menu'), json_encode($data)), $payment_id);
				$this->get('payments')->update_payment_status($payment_id, 'mprm-failed');
				$this->get('payments')->insert_payment_note($payment_id, __('Payment failed due to invalid purchase key in PayPal IPN.', 'mp-restaurant-menu'));
				return;
			}
			if ('completed' == $payment_status || $this->get('misc')->is_test_mode()) {
				$this->get('payments')->insert_payment_note($payment_id, sprintf(__('PayPal Transaction ID: %s', 'mp-restaurant-menu'), $data['txn_id']));
				$this->get('payments')->set_payment_transaction_id($payment_id, $data['txn_id']);
				$this->get('payments')->update_payment_status($payment_id, 'publish');
			} else if ('pending' == $payment_status && isset($data['pending_reason'])) {
				// Look for possible pending reasons, such as an echeck
				$note = '';
				switch (strtolower($data['pending_reason'])) {
					case 'echeck' :
						$note = __('Payment made via eCheck and will clear automatically in 5-8 days', 'mp-restaurant-menu');
						break;
					case 'address' :
						$note = __('Payment requires a confirmed customer address and must be accepted manually through PayPal', 'mp-restaurant-menu');
						break;
					case 'intl' :
						$note = __('Payment must be accepted manually through PayPal due to international account regulations', 'mp-restaurant-menu');
						break;
					case 'multi-currency' :
						$note = __('Payment received in non-shop currency and must be accepted manually through PayPal', 'mp-restaurant-menu');
						break;
					case 'paymentreview' :
					case 'regulatory_review' :
						$note = __('Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'mp-restaurant-menu');
						break;
					case 'unilateral' :
						$note = __('Payment was sent to non-confirmed or non-registered email address.', 'mp-restaurant-menu');
						break;
					case 'upgrade' :
						$note = __('PayPal account must be upgraded before this payment can be accepted', 'mp-restaurant-menu');
						break;
					case 'verify' :
						$note = __('PayPal account is not verified. Verify account in order to accept this payment', 'mp-restaurant-menu');
						break;
					case 'other' :
						$note = __('Payment is pending for unknown reasons. Contact PayPal support for assistance', 'mp-restaurant-menu');
						break;
				}
				if (!empty($note)) {
					$this->get('payments')->insert_payment_note($payment_id, $note);
				}
			}
		}
	}

	/**
	 * Process PayPal IPN Refunds
	 *
	 * @since 1.3.4
	 *
	 * @param array $data IPN Data
	 * @param $payment_id
	 *
	 * @return void
	 */
	public function process_paypal_refund($data, $payment_id = 0) {
		// Collect payment details
		if (empty($payment_id)) {
			return;
		}
		if (get_post_status($payment_id) == 'mprm-refunded') {
			return; // Only refund payments once
		}
		$payment_amount = $this->get('payments')->get_payment_amount($payment_id);
		$refund_amount = $data['mc_gross'] * -1;
		if (number_format((float)$refund_amount, 2) < number_format((float)$payment_amount, 2)) {
			$this->get('payments')->insert_payment_note($payment_id, sprintf(__('Partial PayPal refund processed: %s', 'mp-restaurant-menu'), $data['parent_txn_id']));
			return; // This is a partial refund
		}
		$this->get('payments')->insert_payment_note($payment_id, sprintf(__('PayPal Payment #%s Refunded for reason: %s', 'mp-restaurant-menu'), $data['parent_txn_id'], $data['reason_code']));
		$this->get('payments')->insert_payment_note($payment_id, sprintf(__('PayPal Refund Transaction ID: %s', 'mp-restaurant-menu'), $data['txn_id']));
		$this->get('payments')->update_payment_status($payment_id, 'mprm-refunded');
	}

	/**
	 * Shows "Purchase Processing" message for PayPal payments are still pending on site return
	 *
	 * This helps address the Race Condition, as detailed in issue #1839
	 *
	 * @since 1.9
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function paypal_success_page_content($content) {
		if (!isset($_GET['payment-id']) && !$this->get('cart')->get_purchase_session()) {
			return $content;
		}
		$payment_id = isset($_GET['payment-id']) ? absint($_GET['payment-id']) : false;
		if (!$payment_id) {
			$session = $this->get('cart')->get_purchase_session();
			$payment_id = $this->get('payments')->get_payment_id(array('search_key' => 'payment_key', 'value' => $session['purchase_key']));
		}
		$payment = get_post($payment_id);
		if ($payment && 'mprm-pending' == $payment->post_status) {
			$success_page_uri = $this->get('checkout')->get_success_page_uri();
			$content = View::get_instance()->get_template('shop/processing', array('success_page_uri' => $success_page_uri));
		}
		return $content;
	}

	/**
	 * Given a Payment ID, extract the transaction ID
	 *
	 * @since  2.1
	 *
	 * @param  string $payment_id Payment ID
	 *
	 * @return string                   Transaction ID
	 */
	public function paypal_get_payment_transaction_id($payment_id) {
		$transaction_id = '';
		$notes = $this->get('payments')->get_payment_notes($payment_id);
		foreach ($notes as $note) {
			if (preg_match('/^PayPal Transaction ID: ([^\s]+)/', $note->comment_content, $match)) {
				$transaction_id = $match[1];
				continue;
			}
		}
		return apply_filters('mprm_paypal_set_payment_transaction_id', $transaction_id, $payment_id);
	}

	/**
	 * Given a transaction ID, generate a link to the PayPal transaction ID details
	 *
	 * @since  2.2
	 *
	 * @param  string $transaction_id The Transaction ID
	 * @param  int $payment_id The payment ID for this transaction
	 *
	 * @return string                 A link to the PayPal transaction details
	 */
	public function paypal_link_transaction_id($transaction_id, $payment_id) {
		$paypal_base_url = 'https://www.paypal.com/webscr?cmd=_history-details-from-hub&id=';
		$transaction_url = '<a href="' . esc_url($paypal_base_url . $transaction_id) . '" target="_blank">' . $transaction_id . '</a>';
		return apply_filters('mprm_paypal_link_payment_details_transaction_id', $transaction_url);
	}

	public function init_action() {
		add_action('init', array($this, 'listen_for_paypal_ipn'));
		add_filter('mprm_payment_details_transaction_id-paypal', array($this, 'paypal_link_transaction_id'), 10, 2);
		add_filter('mprm_get_payment_transaction_id-paypal', array($this, 'paypal_get_payment_transaction_id'), 10, 1);
		add_filter('mprm_payment_confirm_paypal', array($this, 'paypal_success_page_content'));
		add_action('mprm_paypal_web_accept', array($this, 'process_paypal_web_accept_and_cart'), 10, 2);
		add_action('mprm_verify_paypal_ipn', array($this, 'process_paypal_ipn'));
		add_action('mprm_gateway_paypal', array($this, 'process_paypal_purchase'));
		add_action('mprm_paypal_cc_form', '__return_false');
	}
}