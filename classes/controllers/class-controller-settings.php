<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\models\Settings;
use mp_restaurant_menu\classes\models\Settings_emails;
use mp_restaurant_menu\classes\View;

/**
 * Class Controller_Settings
 * @package mp_restaurant_menu\classes\controllers
 */
class Controller_Settings extends Controller {

	protected static $instance;

	/**
	 * @return Controller_Settings
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
		$data['settings_tabs'] = $settings_tabs = Media::get_instance()->get_settings_tabs();
		$settings_tabs = empty($settings_tabs) ? array() : $settings_tabs;
		$key = 'main';
		$data['active_tab'] = isset($_GET['tab']) && array_key_exists($_GET['tab'], $settings_tabs) ? $_GET['tab'] : 'general';
		$data['sections'] = Media::get_instance()->get_settings_tab_sections($data['active_tab']);
		$data['section'] = isset($_GET['section']) && !empty($data['sections']) && array_key_exists($_GET['section'], $data['sections']) ? $_GET['section'] : $key;

		echo View::get_instance()->get_template_html('settings', $data);
	}

	/**
	 * Action save
	 */
	public function action_save() {
		$data = $this->get('settings')->save_settings($_REQUEST);
		$this->send_json($data);
	}

	/**
	 *  State list
	 */
	public function action_get_state_list() {
		$data = array();
		$country = $_REQUEST['country'];
		$data['data'] = Settings::get_instance()->get_shop_states($country);
		$data['success'] = true;
		$this->send_json($data);
	}

	/**
	 * Preview email
	 */
	public function action_preview_email() {
		Settings_emails::get_instance()->display_email_template_preview();
	}

	/**
	 * Test email
	 */
	public function action_send_test_email() {
		if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'mprm-test-email')) {
			return;
		}
		// Send a test email
		$this->get('emails')->email_test_purchase_receipt();
		// Remove the test email query arg
		wp_redirect(remove_query_arg('mprm_action'));
		exit;
	}

	/**
	 *  Create pages
	 */
	public function action_create_pages() {
		$this->get('settings')->create_settings_pages();
		$this->get('settings')->set_option('enable_ecommerce', true);
		update_option('mprm_install_page', true);
		wp_redirect(admin_url('edit.php?post_type=mp_menu_item&page=mprm-settings'));
	}

	/**
	 * Skip create pages
	 */
	public function action_skip_create_pages() {
		update_option('mprm_install_page', true);
		wp_redirect(admin_url('edit.php?post_type=mp_menu_item&page=mprm-settings'));
	}
}
