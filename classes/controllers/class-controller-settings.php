<?php

namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\Media;
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
		//$data
		$settings_tabs = Media::get_instance()->get_settings_tabs();
		$settings_tabs = empty($settings_tabs) ? array() : $settings_tabs;

		$data['active_tab'] = isset($_GET['tab']) && array_key_exists($_GET['tab'], $settings_tabs) ? $_GET['tab'] : 'general';
		$data['section'] = empty($_REQUEST['section']) ? '' : sanitize_title($_REQUEST['section']);

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
