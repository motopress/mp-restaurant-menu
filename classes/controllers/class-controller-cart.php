<?php

namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;

class Controller_cart extends Controller {
	protected static $instance;
	private $date;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function action_add_to_cart() {
		$request = $_REQUEST;
		$data = array();
		$cartCount = $this->get('cart')->add_to_cart($request['menu_item_id']);
		if ((bool)$request['is_ajax']) {

			$data['success'] = (is_numeric($cartCount)) ? true : false;
			$this->send_json($data);
		} else {
			wp_safe_redirect($request['_wp_http_referer']);
		}

	}

	public function action_remove() {
		$request = $_REQUEST;
		$this->get('cart')->remove_from_cart($request['cart_item']);
		wp_safe_redirect($this->get('checkout')->get_checkout_uri());
	}
}