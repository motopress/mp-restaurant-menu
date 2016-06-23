<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\libs\IpnListener;
use mp_restaurant_menu\classes\Model;

/**
 * Class Paypal
 * @package mp_restaurant_menu\classes\models
 */
class Paypal extends Model {
	protected static $instance;

	/**
	 * @return Paypal
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function paypal_remove_cc_form() {
		// we only register the action so that the default CC form is not shown
	}

	/**
	 * Process PayPal Purchase
	 *
	 * @access      private
	 * @since       1.0
	 *
	 * @param $purchase_data
	 */
	public function process_paypal_purchase($purchase_data) {
		global $mprm_options;
		/*
		* purchase data comes in like this
		*
		$purchase_data = array(
			'menu_items' => array of menu_item IDs,
			'price' => total price of cart contents,
			'purchase_key' =>  // random key
			'user_email' => $user_email,
			'date' => date('Y-m-d H:i:s'),
			'user_id' => $user_id,
			'post_data' => $_POST,
			'user_info' => array of user's information and used discount code
			'cart_details' => array of cart details,
		);
		*/
		$payment_data = array(
			'price' => $purchase_data['price'],
			'date' => $purchase_data['date'],
			'user_email' => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency' => $mprm_options['currency'],
			'menu_items' => $purchase_data['menu_items'],
			'user_info' => $purchase_data['user_info'],
			'cart_details' => $purchase_data['cart_details'],
			'status' => 'mprm-pending'
		);
		// record the pending payment
		$payment = $this->get('payments')->insert_payment($payment_data);
		if ($payment) {
			// only send to paypal if the pending payment is created successfully
			$listener_url = trailingslashit(home_url()) . '?mprm-listener=IPN';
			$return_url = add_query_arg('payment-confirmation', 'paypal', get_permalink($mprm_options['success_page']));
			$cart_summary = $this->get('cart')->get_purchase_summary($purchase_data, false);
			// one time payment
			if ($this->get('misc')->is_test_mode()) {
				$paypal_redirect = 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
			} else {
				$paypal_redirect = 'https://www.paypal.com/cgi-bin/webscr/?';
			}
			$paypal_args = array(
				'cmd' => '_xclick',
				'amount' => $purchase_data['price'],
				'business' => $mprm_options['paypal_email'],
				'item_name' => $cart_summary,
				'email' => $purchase_data['user_email'],
				'no_shipping' => '1',
				'no_note' => '1',
				'currency_code' => $mprm_options['currency'],
				'item_number' => $purchase_data['purchase_key'],
				'charset' => 'UTF-8',
				'custom' => $payment,
				'rm' => '2',
				'return' => $return_url,
				'notify_url' => $listener_url
			);
			//var_dump(http_build_query($paypal_args)); exit;
			$paypal_redirect .= http_build_query($paypal_args);
			//var_dump(urldecode($paypal_redirect)); exit;
			// get rid of cart contents
			$this->get('cart')->empty_cart();
			// Redirect to paypal
			wp_redirect($paypal_redirect);
			exit;
		} else {
			// if errors are present, send the user back to the purchase page so they can be corrected
			$this->get('checkout')->send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['mprm-gateway']);
		}
	}

	/**
	 * Listen For PayPal IPN
	 *
	 * Listens for a PayPal IPN requests and then sends to the processing function.
	 *
	 * @access      private
	 * @since       1.0
	 * @return      void
	 */
	public function listen_for_paypal_ipn() {
		global $mprm_options;
		// regular PayPal IPN
		if (!isset($mprm_options['paypal_alternate_verification'])) {
			if (isset($_GET['mprm-listener']) && $_GET['mprm-listener'] == 'IPN') {
				do_action('mprm_verify_paypal_ipn');
			}
			// alternate purchase verification
		} else {
			if (isset($_GET['tx']) && isset($_GET['st']) && isset($_GET['amt']) && isset($_GET['cc']) && isset($_GET['cm']) && isset($_GET['item_number'])) {
				// we are using the alternate method of verifying PayPal purchases
				// setup each of the variables from PayPal
				$payment_status = $_GET['st'];
				$paypal_amount = $_GET['amt'];
				$payment_id = $_GET['cm'];
				$purchase_key = $_GET['item_number'];
				$currency = $_GET['cc'];
				// retrieve the meta info for this payment
				$payment_meta = get_post_meta($payment_id, '_mprm_order_meta', true);
				$payment_amount = $this->get('formatting')->format_amount($payment_meta['amount']);
				if ($currency != $mprm_options['currency']) {
					return; // the currency code is invalid
				}
				if ($paypal_amount != $payment_amount) {
					return; // the prices don't match
				}
				if ($purchase_key != $payment_meta['key']) {
					return; // purchase keys don't match
				}
				if (strtolower($payment_status) != 'mprm-completed' || $this->get('misc')->is_test_mode()) {
					return; // payment wasn't completed
				}
				// everything has been verified, update the payment to "complete"
				$this->get('payments')->update_payment_status($payment_id, 'publish');
			}
		}
	}

	/**
	 * Process PayPal IPN
	 *
	 * @access      private
	 * @since       1.0
	 * @return      void
	 */
	public function process_paypal_ipn() {
		global $mprm_options;
		$listener = new IpnListener();
		if ($this->get('misc')->is_test_mode()) {
			$listener->use_sandbox = true;
		}
		if (isset($mprm_options['ssl'])) {
			$listener->use_ssl = false;
		}
		// to post using the fsockopen() function rather than cURL, use:
		if (isset($mprm_options['paypal_disable_curl'])) {
			$listener->use_curl = false;
		}
		try {
			$listener->requirePostMethod();
			$verified = $listener->processIpn();
		} catch (\Exception $e) {
			exit(0);
		}
		if ($verified) {
			$payment_id = $_POST['custom'];
			$purchase_key = $_POST['item_number'];
			$paypal_amount = $_POST['mc_gross'];
			$payment_status = $_POST['payment_status'];
			$currency_code = strtolower($_POST['mc_currency']);
			// retrieve the meta info for this payment
			$payment_meta = get_post_meta($payment_id, '_mprm_order_meta', true);
			$payment_amount = $this->get('formatting')->format_amount($payment_meta['amount']);
			if ($currency_code != strtolower($mprm_options['currency'])) {
				return; // the currency code is invalid
			}
			if ($paypal_amount != $payment_amount) {
				return; // the prices don't match
			}
			if ($purchase_key != $payment_meta['key']) {
				return; // purchase keys don't match
			}
			if (isset($_POST['txn_type']) && $_POST['txn_type'] == 'web_accept') {
				$status = strtolower($payment_status);
				if ($status == 'mprm-completed' || $this->get('misc')->is_test_mode()) {
					// set the payment to complete. This also sends the emails
					$this->get('payments')->update_payment_status($payment_id, 'publish');
				} else if ($status == 'mprm-refunded') {
					// this refund process doesn't work yet
					$payment_data = get_post_meta($payment_id, '_mprm_order_meta', true);
					$menu_items = maybe_unserialize($payment_data['menu_items']);
					if (is_array($menu_items)) {
						foreach ($menu_items as $menu_item) {
							$this->get('payments')->undo_purchase($menu_item['id'], $payment_id);
						}
					}
					wp_update_post(array('ID' => $payment_id, 'post_status' => 'mprm-refunded'));
				}
			}
		} else {
			wp_mail(get_bloginfo('admin_email'), __('Invalid IPN', 'mprm'), $listener->getTextReport());
		}
	}

	public function init_action() {
		add_action('init', array($this, 'listen_for_paypal_ipn'));
		add_action('mprm_verify_paypal_ipn', array($this, 'process_paypal_ipn'));
		add_action('mprm_gateway_paypal', array($this, 'process_paypal_purchase'));
		add_action('mprm_paypal_cc_form', array($this, 'paypal_remove_cc_form'));
	}
}
