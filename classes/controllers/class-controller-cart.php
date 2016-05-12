<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\View;

class Controller_cart extends Controller {
	protected static $instance;
	private $date;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		$this->date = array();
	}

	public function action_add_to_cart() {
		$request = $_REQUEST;
		$cartCount = $this->get('cart')->add_to_cart($request['menu_item_id']);
		if ((bool)$request['is_ajax']) {
			$this->date['success'] = (is_numeric($cartCount)) ? true : false;
			$this->send_json($this->date);
		} else {
			wp_safe_redirect($request['_wp_http_referer']);
		}
	}

	public function action_remove() {
		$request = $_REQUEST;
		$this->get('cart')->remove_from_cart($request['cart_item']);
		wp_safe_redirect($this->get('checkout')->get_checkout_uri());
	}

	public function action_load_gateway() {
		$this->date['data']['html'] = View::get_instance()->render_html('/shop/purchase-form', $this->date['data'], false);
		$this->date['success'] = !empty($this->date['data']['html']) ? true : false;
		$this->send_json($this->date);
	}

	public function action_purchase() {
		if (Core::is_ajax()) {
			$this->get('purchase')->process_purchase_form();
		} else {
			$this->get('purchase')->process_purchase_form();
		}
	}

	public function action_update_cart_item_quantity() {
		if (!empty($_POST['quantity']) && !empty($_POST['menu_item_id'])) {

			$menu_item_id = absint($_POST['menu_item_id']);
			$quantity = absint($_POST['quantity']);
			$options = json_decode(stripslashes($_POST['options']), true);

			$this->get('cart')->set_cart_item_quantity($menu_item_id, absint($_POST['quantity']), $options);
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