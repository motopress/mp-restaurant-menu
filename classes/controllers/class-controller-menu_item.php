<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller as Controller;

/**
 * Class Controller_menu_item
 */
class Controller_menu_item extends Controller {

	protected static $instance;

	private $date;

	/**
	 * @return Controller_menu_item
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get price
	 */
	public function action_get_price() {
		$price = $this->get('menu_item')->get_price($_POST['menu_item']);
		$tax = $this->get('taxes')->calculate_tax($price);

		if (is_numeric($price) && !empty($price)) {
			$this->date['success'] = true;
			$this->date['data']['price'] = $price;
			$this->date['data']['tax'] = $tax;
		}

		$this->send_json($this->date);
	}
}