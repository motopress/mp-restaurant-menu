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
}