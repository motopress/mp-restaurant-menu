<?php

namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\View;
use mp_restaurant_menu\classes\modules\Taxonomy;

class Controller_Popup extends Controller {

	protected static $instance;
	private $date;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function action_get_shortcode_builder() {
		$this->data['categories'] = Taxonomy::get_instance()->get_terms($this->get_tax_name('menu_category'));
		$this->data['tags'] = Taxonomy::get_instance()->get_terms($this->get_tax_name('menu_tag'));
		$data['data'] = View::get_instance()->render_html('../admin/popups/add-shortcodes', $this->data, false);
		$data['success'] = true;
		$this->send_json($data);
	}
}
