<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\View;

/**
 * Class Controller_cart
 * @package mp_restaurant_menu\classes\controllers
 */
class Controller_cart extends Controller {
	protected static $instance;

	private $date = array('data' => array());

	/**
	 * @return Controller_cart
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add item to cart
	 */
	public function action_add_to_cart() {
		$request = $_REQUEST;
		$cartCount = $this->get('cart')->add_to_cart($request['menu_item_id']);
		if (isset($request['is_ajax']) && (bool)$request['is_ajax']) {
			$this->date['success'] = (is_numeric($cartCount)) ? true : false;
			$this->date['data']['cart'] = View::get_instance()->get_template_html('widgets/cart/index');
			$this->date['data']['redirect'] = (!empty($request['mprm_redirect_to_checkout']) && (bool)$request['mprm_redirect_to_checkout']) ? $this->get('checkout')->get_checkout_uri() : false;
			$this->send_json($this->date);
		} else {

			if (wp_get_referer()) {

				wp_safe_redirect(wp_get_referer());

			} else {
				if (empty($request['mprm_redirect_to_checkout'])) {
					wp_safe_redirect($request['_wp_http_referer']);
				} elseif (!empty($request['mprm_redirect_to_checkout']) && (bool)$request['mprm_redirect_to_checkout']) {
					wp_safe_redirect($this->get('checkout')->get_checkout_uri());
				}

			}

		}
	}

	/**
	 * Remove from cart
	 */
	public function action_remove() {
		$request = $_REQUEST;
		$this->get('cart')->remove_from_cart($request['cart_item']);
		if (wp_get_referer()) {
			wp_safe_redirect(wp_get_referer());
		} else {
			wp_safe_redirect($this->get('checkout')->get_checkout_uri());
		}
	}

	/**
	 * Load gateway
	 */
	public function action_load_gateway() {
		$this->date['data']['html'] = View::get_instance()->get_template_html('/shop/purchase-form', $this->date['data']);
		$this->date['success'] = !empty($this->date['data']['html']) ? true : false;
		$this->send_json($this->date);
	}

	/**
	 *  Purchase
	 */
	public function action_purchase() {
		if (Core::is_ajax()) {
			$this->get('purchase')->process_purchase_form();
		} else {
			if (!$this->get('purchase')->process_purchase_form()) {
				if (wp_get_referer()) {
					wp_safe_redirect(wp_get_referer());
				} else {
					wp_safe_redirect($this->get('checkout')->get_checkout_uri());
				}
			};
		}
	}

	/**
	 * Update cart item quantity
	 */
	public function action_update_cart_item_quantity() {
		if (!empty($_POST['quantity']) && !empty($_POST['menu_item_id'])) {

			$menu_item_id = absint($_POST['menu_item_id']);
			$quantity = absint($_POST['quantity']);
			$options = json_decode(stripslashes($_POST['options']), true);
			$position = $_POST['position'];
			$this->get('cart')->set_cart_item_quantity($menu_item_id, absint($_POST['quantity']), $options, $position);
			$total = $this->get('cart')->get_cart_total();

			$this->date['data'] = array(
				'menu_item_id' => $menu_item_id,
				'quantity' => $quantity,
				'taxes' => html_entity_decode($this->get('cart')->cart_tax(), ENT_COMPAT, 'UTF-8'),
				'subtotal' => html_entity_decode($this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($this->get('cart')->get_cart_subtotal())), ENT_COMPAT, 'UTF-8'),
				'total' => html_entity_decode($this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($total)), ENT_COMPAT, 'UTF-8')
			);

			$this->date['success'] = true;

			$this->date['data'] = apply_filters('mprm_ajax_cart_item_quantity_response', $this->date['data']);

			$this->send_json($this->date);
		}
	}
}