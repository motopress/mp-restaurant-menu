<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\modules\Taxonomy;
use mp_restaurant_menu\classes\View;

/**
 * Class Controller_Popup
 *
 * @package mp_restaurant_menu\classes\controllers
 */
class Controller_Popup extends Controller {

	protected static $instance;

	public $data = array();

	/**
	 * @return Controller_Popup
	 */

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get shortcode builder
	 */
	public function action_get_shortcode_builder() {
		$data = array();
		$this->data['categories'] = Taxonomy::get_instance()->get_terms($this->get_tax_name('menu_category'));
		$this->data['tags'] = Taxonomy::get_instance()->get_terms($this->get_tax_name('menu_tag'));
		$data['data'] = View::get_instance()->render_html('../admin/popups/add-shortcodes', $this->data, false);
		$data['success'] = true;
		$this->send_json($data);
	}

	public function action_get_shortcode_by_type() {
		$data = array();
		$request = $_REQUEST;

		$this->data['categories'] = Taxonomy::get_instance()->get_terms($this->get_tax_name('menu_category'));
		$this->data['tags'] = Taxonomy::get_instance()->get_terms($this->get_tax_name('menu_tag'));
		$data['data']['html'] = View::get_instance()->render_html("../admin/popups/shortcode-{$request['type']}", $this->data, false);
		$data['success'] = true;
		$this->send_json($data);
	}
}
