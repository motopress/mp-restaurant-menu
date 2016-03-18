<?php

namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\View;

class Controller_Settings extends Controller {

	protected static $instance;

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
		$data['settings'] = $this->get_config('settings');
		$data['current_settings'] = $this->get('settings')->get_settings();
		$data['currencies'] = $this->get('settings')->get_currencies(); 
		$data['instance'] = $this->get('settings');
		View::get_instance()->render_html('settings', $data);
	}
	
	/**
	 * Action save
	 */
	public function action_save() {
		$data = $this->get('settings')->save_settings($_REQUEST);
		$this->send_json($data);
	}

}
