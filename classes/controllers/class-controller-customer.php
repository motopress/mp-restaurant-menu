<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;

class Controller_customer extends Controller {
	protected static $instance;
	private $date;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function action_add_customer() {

		$customer = $this->get('customer')->create(array('email' => $_REQUEST['email'], 'name' => $_REQUEST['name']));
		$this->date['success'] = $customer;
		if ($customer) {
			$customer_object = $this->get('customer')->get_customer(array('field' => 'email', 'value' => $_REQUEST['email']));
			$this->date['data']['html'] = mprm_customers_dropdown(array('selected' => $customer_object->id));
			$this->date['data']['customer_id'] = $customer_object->id;
		}
		$this->send_json($this->date);
	}

	public function action_remove_customer() {

	}

	public function action_get_customer_info() {

	}
}