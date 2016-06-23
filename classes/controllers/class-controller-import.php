<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Export;
use mp_restaurant_menu\classes\Controller as Controller;
use mp_restaurant_menu\classes\View;

/**
 * Class Controller_Import
 */
class Controller_Import extends Controller {
	protected static $instance;

	/**
	 * @return Controller_Import
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Action template
	 */
	public function action_content() {
		$data = array();
		View::get_instance()->render_html('../admin/import/index', $data);
	}

	public function action_export() {
		Export::get_instance()->export();
	}
}