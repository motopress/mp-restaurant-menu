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
	}

	public function action_remove_from_cart() {
	}
}