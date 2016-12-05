<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\View;

/**
 * Class Controller_Extension
 *
 * @package mp_restaurant_menu\classes\controllers
 */
class Controller_Extension extends Controller {

	protected static $instance;

	public $data = array();

	/**
	 * @return Controller_Extension
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Action content
	 */
	public function action_content() {
		$data = array();
		$data['extensions'] = $this->get_model('extension')->get_extensions();
		$data['extensions_html'] = View::get_instance()->get_template_html('../admin/extensions/extensions-view', $data);

		echo View::get_instance()->get_template_html('../admin/extensions/extensions-page', $data);
	}
}