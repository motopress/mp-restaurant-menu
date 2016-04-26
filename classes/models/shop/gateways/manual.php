<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Manual_payment extends Model {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function manual_payment($purchase_data) {
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
			'status' => 'pending'
		);

		// Record the pending payment
		$payment = $this->get('payments')->insert_payment($payment_data);

		if ($payment) {
			$this->get('payments')->update_payment_status($payment, 'publish');
			// Empty the shopping cart
			$this->get('cart')->empty_cart();
			$this->get('checkout')->send_to_success_page();
		} else {
			//edd_record_gateway_error(__('Payment Error', 'mp-restaurant-menu'), sprintf(__('Payment creation failed while processing a manual (free or test) purchase. Payment data: %s', 'mp-restaurant-menu'), json_encode($payment_data)), $payment);
			// If errors are present, send the user back to the purchase page so they can be corrected
			$this->get('checkout')->send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['mprm-gateway']);
		}
	}

	public function init_action() {
		add_action('mprm_gateway_manual', array($this, 'manual_payment'));
		add_action('mprm_manual_cc_form', '__return_false');
	}
}



