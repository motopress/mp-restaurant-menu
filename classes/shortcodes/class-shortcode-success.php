<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\models\Order;
use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

/**
 * Class Shortcode_success
 * @package mp_restaurant_menu\classes\shortcodes
 */
class Shortcode_success extends Shortcodes {
	protected static $instance;

	/**
	 * @return Shortcode_success
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Render shortcode
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function render_shortcode($data) {
		global $mprm_receipt_args, $mprm_login_redirect;
		$data = empty($data) ? array() : $data;
		$payment_key = false;
		$mprm_receipt_args = shortcode_atts(array(
			'error' => __('Sorry, trouble retrieving payment receipt.', 'mp-restaurant-menu'),
			'price' => true,
			'discount' => true,
			'products' => true,
			'date' => true,
			'notes' => true,
			'payment_key' => false,
			'payment_method' => true,
			'payment_id' => true
		), $data, 'mprm_success');

		$session = $this->get('session')->get_session_by_key('mprm_purchase');
		if (isset($_GET['payment_key'])) {
			$payment_key = urldecode($_GET['payment_key']);
		} else if ($session) {
			$payment_key = $session['purchase_key'];
		}
		$data['payment_key'] = $payment_key;
		$user_can_view = $this->get('misc')->can_view_receipt($payment_key);
		$payment_id = $this->get('payments')->get_payment_id(array('search_key' => 'payment_key', 'value' => $payment_key));

		// Key was provided, but user is logged out. Offer them the ability to login and view the receipt
		if (!$user_can_view && !empty($payment_key) && !is_user_logged_in() && !$this->get('payments')->is_guest_payment($payment_id)) {
			$mprm_login_redirect = $this->get('misc')->get_current_page_url();
			$data['need_login'] = true;
			$data['login_from'] = View::get_instance()->get_template("shop/login");
		}
		if (!apply_filters('mprm_user_can_view_receipt', $user_can_view, $mprm_receipt_args)) {
			$data['can_view'] = true;
		}
		$data['receipt_args'] = $mprm_receipt_args;
		if (!empty($payment_id)) {
			$data['payment'] = get_post($payment_id);
		} else {
			$data['payment'] = false;
		}

		if (is_a($data['payment'], 'WP_Post') && 'mprm_order' == $data['payment']->post_type) {
			$data['payment_id'] = $payment_id;
			$data['order'] = new Order($payment_id);
			$data['meta'] = $this->get('payments')->get_payment_meta($payment_id);
			$data['cart'] = $this->get('payments')->get_payment_meta_cart_details($payment_id, true);
			$data['user'] = $this->get('payments')->get_payment_meta_user_info($payment_id);
			$data['email'] = $this->get('payments')->get_payment_user_email($payment_id);
			$data['status'] = $this->get('payments')->get_payment_status($data['payment'], true);
		}

		return View::get_instance()->get_template_html('shop/success', $data);
	}
}