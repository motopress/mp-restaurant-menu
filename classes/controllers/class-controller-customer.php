<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\View;

class Controller_customer extends Controller {
	protected static $instance;
	private $date;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function action_add_customer() {

		$customer = $this->get('customer')->create(array('email' => $_REQUEST['email'], 'name' => $_REQUEST['name']));
		$this->date['success'] = $customer;
		if ($customer) {
			$customer_object = $this->get('customer')->get_customer(array('field' => 'email', 'value' => $_REQUEST['email']));
			$this->date['data']['html'] = mprm_customers_dropdown(array('selected' => $customer_object->id));
			$this->date['data']['customer_id'] = $customer_object->id;
		}
		$this->send_json($this->date);
	}

	public function action_get_login() {
		global $mprm_login_redirect;
		$mprm_login_redirect = wp_get_referer();
		$this->date['data']['html'] = View::get_instance()->render_html('shop/login', array(), false);
		$this->date['success'] = true;
		$this->send_json($this->date);
	}

	public function action_login_ajax() {
		$request = $_POST;
		if (wp_verify_nonce($request['nonce'], 'mprm-login-nonce')) {
			$credentials = array(
				'user_login' => $request['login'],
				'user_password' => $request['pass'],
				'rememember' => true
			);

			$user = wp_signon($credentials, false);

			if (is_wp_error($user)) {
				$this->date['success'] = false;
				$code = $user->get_error_code();
				switch ($code) {
					case'incorrect_password':
						mprm_set_error('password_incorrect', __('The password you entered is incorrect', 'mp-restaurant-menu'));
						break;
					case'invalid_username':
						mprm_set_error('username_incorrect', __('The username you entered does not exist', 'mp-restaurant-menu'));
						break;
					default:
						mprm_set_error('user_or_pass_incorrect', __('The user name or password is incorrect', 'mp-restaurant-menu'));
						break;
				}
				$this->date['data']['html'] = $this->get('errors')->get_error_html();
				$this->send_json($this->date);
			} else {
				$this->date['success'] = true;
				$this->date['data']['redirect'] = true;
				$this->date['data']['redirect_url'] = esc_url_raw($request['redirect']);
				$this->send_json($this->date);
			}
		} else {
			$this->date['success'] = false;
			$this->send_json($this->date);
		}
	}

	public function action_remove_customer() {

	}

	public function action_get_customer_info() {

	}
}