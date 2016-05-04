<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Customer extends Model {
	protected static $instance;
	public $id = 0;

	public $purchase_count = 0;

	public $purchase_value = 0;

	public $email;

	public $name;

	public $date_created;

	public $payment_ids;

	public $user_id;

	public $notes;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct($params = array()) {
		if (!empty($params)) {
			$customer = get_user_by($params['field'], $params['value']);
			if (empty($customer) || !is_object($customer)) {
				return false;
			}
			$this->setup_customer($customer);
		}

	}

	private function setup_customer($customer) {
		if (!is_object($customer)) {
			return false;
		}
		foreach ($customer as $key => $value) {
			switch ($key) {
				case 'notes':
					$this->$key = $this->get_notes();
					break;
				case 'data':
					$this->id = absint($value->ID);
					$this->email = $value->user_email;
					break;
				default:
					$this->$key = $value;
					break;
			}
		}
		// Customer ID and email are the only things that are necessary, make sure they exist
		if (!empty($this->id) && !empty($this->email)) {
			return true;
		}
		return false;
	}

	public function create($data = array()) {
		if ($this->id != 0 || empty($data)) {
			return false;
		}
		$defaults = array(
			'payment_ids' => ''
		);
		$args = wp_parse_args($data, $defaults);
		if (empty($args['email']) || !is_email($args['email'])) {
			return false;
		}
		if (!empty($args['payment_ids']) && is_array($args['payment_ids'])) {
			$args['payment_ids'] = implode(',', array_unique(array_values($args['payment_ids'])));
		}
		/**
		 * Fires before a customer is created
		 *
		 * @param array $args Contains customer information such as payment ID, name, and email.
		 */
		do_action('mprm_customer_pre_create', $args);
		$created = false;
		// The DB class 'add' implies an update if the customer being asked to be created already exists
//		if ($this->db->add($data)) {
//
//			// We've successfully added/updated the customer, reset the class vars with the new data
//			$customer = $this->db->get_customer_by('email', $args['email']);
//
//			// Setup the customer data with the values from DB
//			$this->setup_customer($customer);
//
//			$created = $this->id;
//		}
		/**
		 * Fires after a customer is created
		 *
		 * @param int $created If created successfully, the customer ID.  Defaults to false.
		 * @param array $args Contains customer information such as payment ID, name, and email.
		 */
		do_action('mprm_customer_post_create', $created, $args);
		return $created;
	}

	function log_user_in($user_id, $user_login, $user_pass) {
		if ($user_id < 1)
			return;
		wp_set_auth_cookie($user_id);
		wp_set_current_user($user_id, $user_login);
		do_action('wp_login', $user_login, get_userdata($user_id));
		do_action('mprm_log_user_in', $user_id, $user_login, $user_pass);
	}

	public function get_notes($length = 20, $paged = 1) {
//		$length = is_numeric($length) ? $length : 20;
//		$offset = is_numeric($paged) && $paged != 1 ? ((absint($paged) - 1) * $length) : 0;
//
//		$all_notes = $this->get_raw_notes();
//		$notes_array = array_reverse(array_filter(explode("\n", $all_notes)));
//
//		$desired_notes = array_slice($notes_array, $offset, $length);
//
//		return $desired_notes;
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

	public function decrease_value($value = 0.00) {
		$new_value = floatval($this->purchase_value) - $value;
		if ($new_value < 0) {
			$new_value = 0.00;
		}
		do_action('mprm_customer_pre_decrease_value', $value, $this->id);
		if ($this->update(array('purchase_value' => $new_value))) {
			$this->purchase_value = $new_value;
		}
		do_action('mprm_customer_post_decrease_value', $this->purchase_value, $value, $this->id);
		return $this->purchase_value;
	}

	public function decrease_purchase_count($count = 1) {
		// Make sure it's numeric and not negative
		if (!is_numeric($count) || $count != absint($count)) {
			return false;
		}
		$new_total = (int)$this->purchase_count - (int)$count;
		if ($new_total < 0) {
			$new_total = 0;
		}
		do_action('mprm_customer_pre_decrease_purchase_count', $count, $this->id);
		if ($this->update(array('purchase_count' => $new_total))) {
			$this->purchase_count = $new_total;
		}
		do_action('mprm_customer_post_decrease_purchase_count', $this->purchase_count, $count, $this->id);
		return $this->purchase_count;
	}


	public function increase_purchase_count($count = 1) {

		// Make sure it's numeric and not negative
		if (!is_numeric($count) || $count != absint($count)) {
			return false;
		}

		$new_total = (int)$this->purchase_count + (int)$count;

		do_action('mprm_customer_pre_increase_purchase_count', $count, $this->id);

		if ($this->update(array('purchase_count' => $new_total))) {
			$this->purchase_count = $new_total;
		}

		do_action('mprm_customer_post_increase_purchase_count', $this->purchase_count, $count, $this->id);

		return $this->purchase_count;
	}

	public function update($data = array()) {
		if (empty($data)) {
			return false;
		}
		//$data = $this->sanitize_columns( $data );
		do_action('mprm_customer_pre_update', $this->id, $data);
		$updated = false;
		foreach ($data as $meta_key => $meta_value) {
			update_user_meta($this->id, $meta_key, $meta_value);
		}
//		if ($this->db->update($this->id, $data)) {
		//$customer = $this->db->get_customer_by('id', $this->id);
		//$this->setup_customer($customer);
		$updated = true;
//		}
		do_action('mprm_customer_post_update', $updated, $this->id, $data);
		return $updated;
	}

	public function increase_value($value = 0.00) {
		$new_value = floatval($this->purchase_value) + $value;
		do_action('mprm_customer_pre_increase_value', $value, $this->id);
		if ($this->update(array('purchase_value' => $new_value))) {
			$this->purchase_value = $new_value;
		}
		do_action('mprm_customer_post_increase_value', $this->purchase_value, $value, $this->id);
		return $this->purchase_value;
	}

	public function get_users_purchases($user = 0, $number = 20, $pagination = false, $status = 'complete') {

		if (empty($user)) {
			$user = get_current_user_id();
		}

		if (0 === $user) {
			return false;
		}

		$status = $status === 'complete' ? 'publish' : $status;

		if ($pagination) {
			if (get_query_var('paged'))
				$paged = get_query_var('paged');
			else if (get_query_var('page'))
				$paged = get_query_var('page');
			else
				$paged = 1;
		}

		$args = array(
			'user' => $user,
			'number' => $number,
			'status' => $status,
			'orderby' => 'date'
		);

		if ($pagination) {

			$args['page'] = $paged;

		} else {

			$args['nopaging'] = true;

		}

		$customer = get_user_by('id', $user);

		$this->setup_customer($customer);
		if (!empty($customer->payment_ids)) {
			unset($args['user']);
			$args['post__in'] = array_map('absint', explode(',', $customer->payment_ids));
		}
		$purchases = $this->get('payments')->get_payments(apply_filters('mprm_get_users_purchases_args', $args));

		// No purchases
		if (!$purchases)
			return false;

		return $purchases;
	}

	public function user_pending_verification($user_id = 0) {

		if (empty($user_id)) {
			$user_id = get_current_user_id();
		}


		if (empty($user_id)) {
			return false;
		}

		$pending = get_user_meta($user_id, '_mprm_pending_verification', true);

		return (bool)apply_filters('mprm_user_pending_verification', !empty($pending), $user_id);

	}

	public function count_purchases_of_customer($user = null) {
		if (empty($user)) {
			$user = get_current_user_id();
		}

		$stats = !empty($user) ? $this->get_purchase_stats_by_user($user) : false;

		return isset($stats['purchases']) ? $stats['purchases'] : 0;
	}

	public function get_purchase_stats_by_user($user = '') {
		if (is_email($user)) {
			$field = 'email';
		} elseif (is_numeric($user)) {
			$field = 'user_id';
		}

		$stats = array();
		$customer = get_user_by($field, $user);
		$this->setup_customer($customer);

		if ($customer) {

			$stats['purchases'] = absint($this->purchase_count);
			$stats['total_spent'] = $this->get('formatting')->sanitize_amount($this->purchase_value);

		}
		return (array)apply_filters('mprm_purchase_stats_by_user', $stats, $user);
	}

	public function get_user_verification_request_url($user_id = 0) {

		if (empty($user_id)) {
			$user_id = get_current_user_id();
		}

		$url = wp_nonce_url(add_query_arg(array(
			'mprm_action' => 'send_verification_email'
		)), 'mprm-request-verification');

		return apply_filters('mprm_get_user_verification_request_url', $url, $user_id);

	}

	public function init_action() {
		add_action('mprm_customer_pre_decrease_value', 'mprm_customer_pre_decrease_value');
		add_action('mprm_customer_post_decrease_value', 'mprm_customer_post_decrease_value');
		add_action('mprm_customer_pre_decrease_purchase_count', 'mprm_customer_pre_decrease_purchase_count');
		add_action('mprm_customer_post_decrease_purchase_count', 'mprm_customer_post_decrease_purchase_count');
		add_action('mprm_customer_pre_update', 'mprm_customer_pre_update');
		add_action('mprm_customer_post_update', 'mprm_customer_post_update');
		add_action('mprm_customer_pre_create', 'mprm_customer_pre_create');
		add_action('mprm_customer_post_create', 'mprm_customer_post_create');
	}

}