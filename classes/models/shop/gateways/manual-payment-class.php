<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Manual_payment
 *
 * @package mp_restaurant_menu\classes\models
 */
class Manual_payment extends Model {
	protected static $instance;
	
	/**
	 * @return Manual_payment
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * @param $purchase_data
	 */
	public function manual_payment( $purchase_data ) {
		if ( ! wp_verify_nonce( $purchase_data[ 'gateway_nonce' ], 'mprm-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'mp-restaurant-menu' ), __( 'Error', 'mp-restaurant-menu' ), array( 'response' => 403 ) );
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
			'user_info' => array of user's information
			'cart_details' => array of cart details,
		);*/
		$payment_data = array(
			'price'        => $purchase_data[ 'price' ],
			'date'         => $purchase_data[ 'date' ],
			'user_email'   => $purchase_data[ 'user_email' ],
			'purchase_key' => $purchase_data[ 'purchase_key' ],
			'currency'     => $this->get( 'settings' )->get_currency(),
			'menu_items'   => $purchase_data[ 'menu_items' ],
			'user_info'    => $purchase_data[ 'user_info' ],
			'cart_details' => $purchase_data[ 'cart_details' ],
			'status'       => 'mprm-pending'
		);
		
		// Record the pending payment
		$payment = $this->get( 'payments' )->insert_payment( $payment_data );
		
		if ( $payment ) {
			// Empty the shopping cart
			$this->get( 'cart' )->empty_cart();
			// Update status to processing
			$this->get( 'payments' )->update_payment_status( $payment, 'mprm-processing' );
			
			$this->get( 'checkout' )->send_to_success_page( '?payment_key=' . $this->get( 'payments' )->get_payment_key( $payment ) );
		} else {
			// If errors are present, send the user back to the purchase page so they can be corrected
			//$this->get( 'errors' )->set_error( 'registration_required', __( 'You must register or login to complete your purchase', 'mp-restaurant-menu' ) );
			$this->get( 'checkout' )->send_back_to_checkout( '?payment-mode=' . $purchase_data[ 'post_data' ][ 'mprm-gateway' ] );
		}
	}
	
	/**
	 * Init gateway action
	 */
	public function init_action() {
		add_action( 'mprm_gateway_manual', array( $this, 'manual_payment' ) );
		add_action( 'mprm_manual_cc_form', '__return_false' );
	}
}

