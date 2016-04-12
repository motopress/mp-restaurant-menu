<?php

namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\models\Settings;
use mp_restaurant_menu\classes\models\Settings_countries;
use mp_restaurant_menu\classes\models\Settings_emails;
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
		$data['settings_tabs'] = $settings_tabs = Media::get_instance()->get_settings_tabs();
		$settings_tabs = empty($settings_tabs) ? array() : $settings_tabs;
		$key = 'main';
		$data['active_tab'] = isset($_GET['tab']) && array_key_exists($_GET['tab'], $settings_tabs) ? $_GET['tab'] : 'general';
		$data['sections'] = Media::get_instance()->get_settings_tab_sections($data['active_tab']);
		$data['section'] = isset($_GET['section']) && !empty($data['sections']) && array_key_exists($_GET['section'], $data['sections']) ? $_GET['section'] : $key;
		View::get_instance()->render_html('settings', $data);
	}

	/**
	 * Action save
	 */
	public function action_save() {
		$data = $this->get('settings')->save_settings($_REQUEST);
		$this->send_json($data);
	}

	public function action_get_state_list() {
		$data = array();
		$country = $_REQUEST['country'];
		$data['data'] = Settings::get_instance()->get_shop_states($country);
		$data['success'] = true;
		$this->send_json($data);
	}

	public function action_preview_email() {
		Settings_emails::get_instance()->display_email_template_preview();
		echo "<H2> TEST</H2>";
	}
}
