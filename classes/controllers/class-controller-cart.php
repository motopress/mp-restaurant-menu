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
		$this->get('cart')->add_to_cart($request['menu_item_id']);

		wp_safe_redirect($request['_wp_http_referer']);
	}

	public function action_remove_from_cart() {
	}
}