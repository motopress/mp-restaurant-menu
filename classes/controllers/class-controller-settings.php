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
		$data = $this->get('settings')->get_config_settings();
		$data['current_tab'] = empty($_GET['tab']) ? 'general' : sanitize_title($_GET['tab']);
		$data['current_section'] = empty($_REQUEST['section']) ? '' : sanitize_title($_REQUEST['section']);

		//$data['settings_template'] = ();
		$tabs =
//		$data['current_settings'] = $this->get('settings')->get_settings();
//		$data['currencies'] = $this->get('settings')->get_currencies();
//		$data['instance'] = $this->get('settings');
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
