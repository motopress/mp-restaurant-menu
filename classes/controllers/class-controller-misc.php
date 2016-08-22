<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;

/**
 * Class Controller_misc
 * @package mp_restaurant_menu\classes\controllers
 */
class Controller_misc extends Controller {
	protected static $instance;

	private $date = array('data' => array());

	/**
	 * @return Controller_misc
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function action_get_errors_html() {
		$this->date['data']['html'] = $this->get('errors')->get_error_html();
		$this->date['success'] = true;
		$this->send_json($this->date);
	}
}