<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Customer extends Model {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_customer_address() {
		if (empty($user_id)) {
			$user_id = get_current_user_id();
		}
		$address = get_user_meta($user_id, '_mprm_user_address', true);
		if (!isset($address['line1']))
			$address['line1'] = '';
		if (!isset($address['line2']))
			$address['line2'] = '';
		if (!isset($address['city']))
			$address['city'] = '';
		if (!isset($address['zip']))
			$address['zip'] = '';
		if (!isset($address['country']))
			$address['country'] = '';
		if (!isset($address['state']))
			$address['state'] = '';
		return $address;
	}

	public function get_session_customer() {
		$customer = $this->get('session')->get_session_by_key('customer');
		$customer = wp_parse_args($customer, array('first_name' => '', 'last_name' => '', 'email' => ''));
		if (is_user_logged_in()) {
			$user_data = get_userdata(get_current_user_id());
			foreach ($customer as $key => $field) {
				if ('email' == $key && empty($field)) {
					$customer[$key] = $user_data->user_email;
				} elseif (empty($field)) {
					$customer[$key] = $user_data->$key;
				}
			}
		}
		return $customer = array_map('sanitize_text_field', $customer);
	}

	public function validate_username($username) {
		$sanitized = sanitize_user($username, false);
		$valid = ($sanitized == $username);
		return (bool)apply_filters('mprm_validate_username', $valid, $username);
	}

	function log_user_in($user_id, $user_login, $user_pass) {
		if ($user_id < 1)
			return;

		wp_set_auth_cookie($user_id);
		wp_set_current_user($user_id, $user_login);
		do_action('wp_login', $user_login, get_userdata($user_id));
		do_action('mprm_log_user_in', $user_id, $user_login, $user_pass);
	}

	public function decrease_value($value = 0.00) {

		$new_value = floatval($this->purchase_value) - $value;

		if ($new_value < 0) {
			$new_value = 0.00;
		}

		do_action('edd_customer_pre_decrease_value', $value, $this->id);

		if ($this->update(array('purchase_value' => $new_value))) {
			$this->purchase_value = $new_value;
		}

		do_action('edd_customer_post_decrease_value', $this->purchase_value, $value, $this->id);

		return $this->purchase_value;
	}
	public function decrease_purchase_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->purchase_count - (int) $count;

		if( $new_total < 0 ) {
			$new_total = 0;
		}

		do_action( 'edd_customer_pre_decrease_purchase_count', $count, $this->id );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		do_action( 'edd_customer_post_decrease_purchase_count', $this->purchase_count, $count, $this->id );

		return $this->purchase_count;
	}

}