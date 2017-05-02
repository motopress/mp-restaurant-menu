<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Test_Manual_payment
 * @package mp_restaurant_menu\classes\models
 */
class Test_Manual_payment extends Model {
	protected static $instance;

	/**
	 * @return Test_Manual_payment
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
	public function test_manual_payment($purchase_data) {
		if (!wp_verify_nonce($purchase_data['gateway_nonce'], 'mprm-gateway')) {
			wp_die(__('Nonce verification has failed', 'mp-restaurant-menu'), __('Error', 'mp-restaurant-menu'), array('response' => 403));
		}
		/*
		* Purchase data comes in like this
		*
		$purchase_data = array(
			'menu_items' => array of menu_item IDs,
			'price' => total price of cart contents,
			'purchase_key' =>  // Random key
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
			'currency' => $this->get('settings')->get_currency(),
			'menu_items' => $purchase_data['menu_items'],
			'user_info' => $purchase_data['user_info'],
			'cart_details' => $purchase_data['cart_details'],
			'status' => 'mprm-pending'
		);
		// Record the pending payment
		$payment = $this->get('payments')->insert_payment($payment_data);
		if ($payment) {
			$this->get('payments')->update_payment_status($payment, 'publish');
			// Empty the shopping cart
			$this->get('cart')->empty_cart();
			$this->get('checkout')->send_to_success_page('?payment_key=' . $this->get('payments')->get_payment_key($payment));
		} else {
			// mprm_record_gateway_error(__('Payment Error', 'mp-restaurant-menu'), sprintf(__('Payment creation failed while processing a manual (free or test) purchase. Payment data: %s', 'mp-restaurant-menu'), json_encode($payment_data)), $payment);
			// If errors are present, send the user back to the purchase page so they can be corrected
			$this->get('checkout')->send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['mprm-gateway']);
		}
	}

	public function init_action() {
		add_action('mprm_gateway_test_manual', array($this, 'test_manual_payment'));
		add_action('mprm_test_manual_cc_form', '__return_false');
	}
}

