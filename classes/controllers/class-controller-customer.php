<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;
use mp_restaurant_menu\classes\libs\GUMP;
use mp_restaurant_menu\classes\models\Customer;
use mp_restaurant_menu\classes\View;

/**
 * Class Controller_customer
 * @package mp_restaurant_menu\classes\controllers
 */
class Controller_customer extends Controller {
	protected static $instance;
	private $date;

	/**
	 * @return Controller_customer
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add customer
	 */
	public function action_add_customer() {

		$customer = $this->get('customer')->create(array(
				'email' => sanitize_email($_REQUEST['email']),
				'name' => sanitize_text_field($_REQUEST['name']),
				'phone' => sanitize_text_field($_REQUEST['phone'])
			)
		);
		$this->date['success'] = $customer;
		if ($customer) {
			$customer_object = $this->get('customer')->get_customer(array('field' => 'email', 'value' => $_REQUEST['email']));
			$this->date['data']['html'] = mprm_customers_dropdown(array('selected' => $customer_object->id));
			$this->date['data']['customer_information'] = View::get_instance()->render_html('../admin/metaboxes/order/customer-information', array('customer_id' => $customer_object->id), false);
			$this->date['data']['customer_id'] = $customer_object->id;
		}
		$this->send_json($this->date);
	}

	/**
	 * Get login form
	 */
	public function action_get_login() {
		global $mprm_login_redirect;
		$mprm_login_redirect = wp_get_referer();
		$this->date['data']['html'] = View::get_instance()->get_template_html('shop/login', array());
		$this->date['success'] = true;
		$this->send_json($this->date);
	}

	/**
	 * Ajax login user
	 */
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

	/**
	 * Get customer information
	 */
	public function action_get_customer_information() {
		$customer_object = $this->get('customer')->get_customer(array('field' => 'id', 'value' => $_REQUEST['customer_id']));
		if (!empty($customer_object)) {
			$this->date['success'] = true;
			$this->date['data']['customer_information'] = View::get_instance()->render_html('../admin/metaboxes/order/customer-information', array('customer_id' => $customer_object->id), false);
		} else {
			$this->date['success'] = false;
		}
		$this->send_json($this->date);
	}

	/**
	 *  Content
	 */
	public function action_content() {
		if (!empty($_REQUEST['view']) && !empty($_REQUEST['id'])) {
			$view = sanitize_text_field($_REQUEST['view']);
			View::get_instance()->render_html('../admin/customers/' . $view, array('id' => sanitize_text_field($_REQUEST['id'])));
		} else {
			View::get_instance()->render_html('../admin/customers/index');
		}
	}

	/**
	 * Update customer
	 */
	public function action_update_customer() {
		$result = false;
		$gump = new GUMP();
		$request = $gump->sanitize($_REQUEST);
		$id = $request['id'];

		$data = array(
			'email' => $request['mprm-email'],
			'telephone' => $request['mprm-telephone'],
			'name' => $request['mprm-name'],
			'user_id' => $request['mprm-user']
		);
		$gump->validation_rules(array(
			'name' => 'required|max_len,100|min_len,6',
			'telephone' => 'required',
			'email' => 'required|valid_email'

		));
		$gump->filter_rules(array(
			'name' => 'trim|sanitize_string',
			'telephone' => 'trim',
			'email' => 'trim|sanitize_email'
		));

		$validated_data = $gump->run($data);

		if ($validated_data) {
			$result = $this->get('customer')->update($data, $id);
		} else {
			$errors = $gump->get_errors_array(true);
			if (!empty($errors)) {
				foreach ($errors as $key => $error) {
					mprm_set_error('mprm_' . $key, $error);
				}
			}
		}
		if ($result) {
			if (wp_get_referer()) {
				wp_safe_redirect(wp_get_referer());
			} else {
				wp_safe_redirect(admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&message=customer-updated' . '&s=' . $id));
			}
		} else {
			wp_safe_redirect(admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=overview&id=' . $id));
		}
	}

	/**
	 * Action delete
	 */
	public function action_delete() {
		$customer_edit_role = apply_filters('mprm_edit_customers_role', 'edit_shop_payments');

		if (!is_admin() || !current_user_can($customer_edit_role)) {
			wp_die(__('You do not have permission to delete this customer.', 'mp-restaurant-menu'));
		}
		$gump = new GUMP();
		$request = $gump->sanitize($_REQUEST);
		if (empty($request)) {
			return;
		}

		$customer_id = (int)$request['customer_id'];
		$confirm = !empty($request['mprm-customer-delete-confirm']) ? true : false;
		$remove_data = !empty($request['mprm-customer-delete-records']) ? true : false;
		$nonce = $request['_wpnonce'];

		if (!wp_verify_nonce($nonce, 'delete-customer')) {
			wp_die(__('Cheatin\' eh?!', 'mp-restaurant-menu'));
		}

		if (!$confirm) {
			mprm_set_error('customer-delete-no-confirm', __('Please confirm you want to delete this customer', 'mp-restaurant-menu'));
		}

		if ($this->get('errors')->get_errors()) {
			wp_redirect(admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=overview&id=' . $customer_id));
			exit;
		}

		$customer = new Customer(array('field' => 'id', 'value' => $customer_id));

		do_action('mprm_pre_delete_customer', $customer_id, $confirm, $remove_data);

		if ($customer->id > 0) {

			$payments_array = explode(',', $customer->payment_ids);
			$success = $this->get('customer')->delete($customer->id);

			if ($success) {
				if ($remove_data) {
					// Remove all payments, logs, etc
					foreach ($payments_array as $payment_id) {
						$this->get('payments')->delete_purchase($payment_id, false, true);
					}
				} else {
					// Just set the payments to customer_id of 0
					foreach ($payments_array as $payment_id) {
						$this->get('payments')->update_payment_meta($payment_id, '_mprm_payment_customer_id', 0);
					}
				}
				$redirect = admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=overview&message=customer-deleted');

			} else {
				mprm_set_error('mprm-customer-delete-failed', __('Error deleting customer', 'mp-restaurant-menu'));
				$redirect = admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=delete&id=' . $customer_id);
			}
		} else {
			mprm_set_error('mprm-customer-delete-invalid-id', __('Invalid Customer ID', 'mp-restaurant-menu'));
			$redirect = admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers');
		}
		wp_redirect($redirect);
		exit;
	}
}