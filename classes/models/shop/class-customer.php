<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Customer
 * @package mp_restaurant_menu\classes\models
 */
class Customer extends Model {

	protected static $instance;
	public $id = 0;
	public $purchase_count = 0;
	public $purchase_value = 0;
	public $email;
	public $name;
	public $telephone;
	public $date_created;
	public $payment_ids;
	public $user_id;
	public $notes;
	private $table_name = '';

	/**
	 * Customer constructor.
	 *
	 * @param array $params
	 */
	public function __construct($params = array()) {
		parent::__construct();
		global $wpdb;
		$this->table_name = $wpdb->prefix . "mprm_customer";
		if (!empty($params)) {
			$customer = $this->get_customer($params);
			if (empty($customer) || !is_object($customer)) {
				return false;
			}
			$this->setup_customer($customer);
		}
	}

	/**
	 * @param array $params
	 *
	 * @return array|bool|null|object|void
	 */
	public function get_customer($params = array()) {
		global $wpdb;
		if (empty($params)) {
			return false;
		}

		$customer = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $this->table_name . ' WHERE ' . $params["field"] . ' = %s LIMIT 1', $params['value']));

		return empty($customer) ? false : $customer;
	}

	/**
	 * @param $customer
	 *
	 * @return bool
	 */
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

	/**
	 * @param int $length
	 * @param int $paged
	 *
	 * @return array
	 */
	public function get_notes($length = 20, $paged = 1) {
		$length = is_numeric($length) ? $length : 20;
		$offset = is_numeric($paged) && $paged != 1 ? ((absint($paged) - 1) * $length) : 0;

		$all_notes = $this->get_raw_notes();
		$notes_array = array_reverse(array_filter(explode("\n", $all_notes)));

		$desired_notes = array_slice($notes_array, $offset, $length);

		return $desired_notes;
	}

	/**
	 * @return string
	 */
	private function get_raw_notes() {
		$all_notes = $this->get_column('notes', $this->id);
		return (string)$all_notes;
	}

	/**
	 * @param $column
	 * @param $customer_id
	 *
	 * @return null|string
	 */
	public function get_column($column, $customer_id) {
		global $wpdb;
		$column_value = $wpdb->get_var($wpdb->prepare("SELECT {$column} FROM  $this->table_name  WHERE id  = %d LIMIT 1", $customer_id));
		return $column_value;
	}

	/**
	 * @return Customer
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param array $args
	 *
	 * @return array|bool|null|object
	 */
	public function get_customers($args = array()) {
		global $wpdb;
		$fields = '*';
		$default_args = array(
			'number' => 30,
			'offset' => 0,
			'order' => 'DESC',
			'orderby' => 'id',
			'fields' => array('id', 'user_id', 'email', 'name')
		);

		$args = wp_parse_args($args, $default_args);

		if (is_array($args['fields']) && !empty($args['fields'])) {
			$fields = implode(',', $args['fields']);
		}
		$where = '';
		$limit = empty($args['number']) ? '' : ' LIMIT ' . $args['number'];
		$offset = empty($args['offset']) ? '' : ' OFFSET ' . $args['offset'];

		if (!empty($args['search_value']) && !empty($args['search_by'])) {
			$where = ' WHERE ' . $args['search_by'] . ' LIKE ' . "'%{$args['search_value']}%'";
		}


		$sql_reguest = "SELECT {$fields} FROM " . $this->table_name . $where . ' ORDER BY ' . $args['orderby'] . ' ' . $args['order'] . $limit . $offset;
		$customers_data = $wpdb->get_results($sql_reguest);

		return empty($customers_data) ? false : $customers_data;
	}

	/**
	 * @param bool $_id_or_email
	 *
	 * @return bool|false|int
	 */
	public function delete($_id_or_email = false) {

		if (empty($_id_or_email)) {
			return false;
		}

		$column = is_email($_id_or_email) ? 'email' : 'id';
		$customer = $this->get_customer(array('field' => $column, "value" => $_id_or_email));

		if ($customer->id > 0) {

			global $wpdb;
			return $wpdb->delete($this->table_name, array('id' => $customer->id), array('%d'));

		} else {
			return false;
		}

	}

	/**
	 * Create restaurant menu customer
	 *
	 * @param array $data
	 *
	 * @return bool|false|int
	 */
	public function create($data = array()) {
		global $wpdb;
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

		do_action('mprm_customer_pre_create', $args);

		$created = false;
		if (!$this->is_customer_exist($args['email'])) {
			$created = $wpdb->insert($this->table_name,
				array(
					'email' => $args['email'],
					'name' => $args['name'],
					'telephone' => $args['phone'],
					'payment_ids' => $args['payment_ids'],
					'date_created' => date('Y-m-d H:i:s'),
					'user_id' => empty($args['user_id']) ? 0 : $args['user_id']
				)
			);
			$customer = $this->get_customer(array('field' => 'email', 'value' => $args['email']));
			$this->setup_customer($customer);
		}

		do_action('mprm_customer_post_create', $created, $args);
		return $created;
	}

	/**
	 * @param string $customer_email
	 *
	 * @return bool
	 */
	public function is_customer_exist($customer_email = '') {
		global $wpdb;
		if (empty($customer_email)) {
			return false;
		}
		$data = $wpdb->get_results('SELECT email FROM  ' . $this->table_name . '   WHERE email = "' . $customer_email . '"');
		return empty($data) ? false : true;
	}

	/**
	 * Set login user
	 *
	 * @param $user_id
	 * @param $user_login
	 * @param $user_pass
	 */
	public function log_user_in($user_id, $user_login, $user_pass) {
		if ($user_id < 1)
			return;
		wp_set_auth_cookie($user_id);
		wp_set_current_user($user_id, $user_login);
		do_action('wp_login', $user_login, get_userdata($user_id));
		do_action('mprm_log_user_in', $user_id, $user_login, $user_pass);
	}

	/**
	 * @return mixed
	 */
	public function get_customer_address() {
		if (empty($user_id)) {
			$user_id = get_current_user_id();
		}
		$address = get_user_meta($user_id, '_mprm_user_address', true);
		if ( empty($address) )
			$address = array();

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

	/**
	 * @return array
	 */
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

	/**
	 * @param $username
	 *
	 * @return bool
	 */
	public function validate_username($username) {
		$sanitized = sanitize_user($username, false);
		$valid = ($sanitized == $username);
		return (bool)apply_filters('mprm_validate_username', $valid, $username);
	}

	/**
	 * @param int $user
	 * @param int $number
	 * @param bool $pagination
	 * @param string $status
	 *
	 * @return bool
	 */
	public function get_users_purchases($user = 0, $number = 20, $pagination = false, $status = 'mprm-complete') {
		if (empty($user)) {
			$user = get_current_user_id();
		}
		if (0 === $user) {
			return false;
		}
		$status = $status === 'mprm-complete' ? 'publish' : $status;
		$paged = 1;

		if ($pagination) {
			if (get_query_var('paged')) {
				$paged = get_query_var('paged');
			} else if (get_query_var('page')) {
				$paged = get_query_var('page');
			}
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

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function user_pending_verification($user_id = 0) {
		if (empty($user_id)) {
			$user_id = get_current_user_id();
		}

		if (empty($user_id)) {
			return false;
		}
		$pending = get_user_meta($user_id, '_mprm-pending_verification', true);
		return (bool)apply_filters('mprm_user_pending_verification', !empty($pending), $user_id);
	}

	/**
	 * @param null $user
	 *
	 * @return int
	 */
	public function count_purchases_of_customer($user = null) {
		if (empty($user)) {
			$user = get_current_user_id();
		}
		$stats = !empty($user) ? $this->get_purchase_stats_by_user($user) : false;
		return isset($stats['purchases']) ? $stats['purchases'] : 0;
	}

	/**
	 * @param string $user
	 *
	 * @return array|bool
	 */
	public function get_purchase_stats_by_user($user = '') {
		if (is_email($user)) {
			$field = 'email';
		} elseif (is_numeric($user)) {
			$field = 'user_id';
		}
		$stats = array();

		if (!empty($field)) {
			$customer = get_user_by($field, $user);
			$this->setup_customer($customer);
			if ($customer) {
				$stats['purchases'] = absint($this->purchase_count);
				$stats['total_spent'] = $this->get('formatting')->sanitize_amount($this->purchase_value);
			}
			return (array)apply_filters('mprm_purchase_stats_by_user', $stats, $user);
		}

		return false;
	}

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function get_user_verification_request_url($user_id = 0) {
		if (empty($user_id)) {
			$user_id = get_current_user_id();
		}
		$url = wp_nonce_url(add_query_arg(array(
			'mprm_action' => 'send_verification_email'
		)), 'mprm-request-verification');
		return apply_filters('mprm_get_user_verification_request_url', $url, $user_id);
	}

	/**
	 * @param int $payment_id
	 * @param bool $update_stats
	 *
	 * @return bool
	 */
	public function attach_payment($payment_id = 0, $update_stats = true) {

		if (empty($payment_id)) {
			return false;
		}

		$payment = new Order($payment_id);

		if (empty($this->payment_ids)) {

			$new_payment_ids = $payment->ID;

		} else {

			$payment_ids = array_map('absint', explode(',', $this->payment_ids));

			if (in_array($payment->ID, $payment_ids)) {
				$update_stats = false;
			}

			$payment_ids[] = $payment->ID;

			$new_payment_ids = implode(',', array_unique(array_values($payment_ids)));

		}

		do_action('mprm_customer_pre_attach_payment', $payment->ID, $this->id);

		$payment_added = $this->update(array('payment_ids' => $new_payment_ids));

		if ($payment_added) {

			$this->payment_ids = $new_payment_ids;

			// We added this payment successfully, increment the stats
			if ($update_stats) {

				if (!empty($payment->total)) {
					$this->increase_value($payment->total);
				}

				$this->increase_purchase_count();
			}

		}

		do_action('mprm_customer_post_attach_payment', $payment_added, $payment->ID, $this->id);

		return $payment_added;
	}

	/**
	 * @param array $data
	 * @param int $id
	 *
	 * @return bool
	 */
	public function update($data = array(), $id = 0) {
		global $wpdb;
		if (empty($data)) {
			return false;
		}

		if (!empty($id)) {
			$this->id = $id;
		}

		$data = $this->sanitize_columns($data);
		do_action('mprm_customer_pre_update', $this->id, $data);
		$updated = false;

		$result = $wpdb->update(
			$this->table_name,
			$data,
			array('id' => $this->id)
		);

		if ($result) {
			$customer = $this->get_customer(array('field' => 'id', 'value' => $this->id));
			$this->setup_customer($customer);
			$updated = true;
		}
		do_action('mprm_customer_post_update', $updated, $this->id, $data);

		return $updated;
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	private function sanitize_columns($data) {

		$columns = $this->get_columns();
		$default_values = $this->get_column_defaults();

		foreach ($columns as $key => $type) {

			// Only sanitize data that we were provided
			if (!array_key_exists($key, $data)) {
				continue;
			}

			switch ($type) {

				case '%s':
					if ('email' == $key) {
						$data[$key] = sanitize_email($data[$key]);
					} elseif ('notes' == $key) {
						$data[$key] = strip_tags($data[$key]);
					} else {
						$data[$key] = sanitize_text_field($data[$key]);
					}
					break;

				case '%d':
					if (!is_numeric($data[$key]) || (int)$data[$key] !== absint($data[$key])) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = absint($data[$key]);
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval($data[$key]);

					if (!is_float($value)) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = $value;
					}
					break;

				default:
					$data[$key] = sanitize_text_field($data[$key]);
					break;
			}
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return array(
			'id' => '%d',
			'user_id' => '%d',
			'name' => '%s',
			'email' => '%s',
			'payment_ids' => '%s',
			'purchase_value' => '%f',
			'purchase_count' => '%d',
			'notes' => '%s',
			'date_created' => '%s',
		);
	}

	/**
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'user_id' => 0,
			'email' => '',
			'name' => '',
			'telephone' => '',
			'payment_ids' => '',
			'purchase_value' => 0.00,
			'purchase_count' => 0,
			'notes' => '',
			'date_created' => date('Y-m-d H:i:s'),
		);
	}

	/**
	 * @param float $value
	 *
	 * @return float|int
	 */
	public function increase_value($value = 0.00) {
		$new_value = floatval($this->purchase_value) + $value;
		do_action('mprm_customer_pre_increase_value', $value, $this->id);
		if ($this->update(array('purchase_value' => $new_value))) {
			$this->purchase_value = $new_value;
		}
		do_action('mprm_customer_post_increase_value', $this->purchase_value, $value, $this->id);
		return $this->purchase_value;
	}

	/**
	 * @param int $count
	 *
	 * @return bool|int
	 */
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

	/**
	 * @param int $payment_id
	 * @param bool $update_stats
	 *
	 * @return bool
	 */
	public function remove_payment($payment_id = 0, $update_stats = true) {

		if (empty($payment_id)) {
			return false;
		}

		$payment = new Order($payment_id);

		if ('publish' !== $payment->status && 'mprm-revoked' !== $payment->status) {
			$update_stats = false;
		}

		$new_payment_ids = '';

		if (!empty($this->payment_ids)) {

			$payment_ids = array_map('absint', explode(',', $this->payment_ids));

			$pos = array_search($payment->ID, $payment_ids);
			if (false === $pos) {
				return false;
			}

			unset($payment_ids[$pos]);
			$payment_ids = array_filter($payment_ids);

			$new_payment_ids = implode(',', array_unique(array_values($payment_ids)));

		}

		do_action('mprm_customer_pre_remove_payment', $payment->ID, $this->id);

		$payment_removed = $this->update(array('payment_ids' => $new_payment_ids));

		if ($payment_removed) {

			$this->payment_ids = $new_payment_ids;

			if ($update_stats) {
				// We removed this payment successfully, decrement the stats
				if (!empty($payment->total)) {
					$this->decrease_value($payment->total);
				}
				$this->decrease_purchase_count();
			}

		}
		do_action('mprm_customer_post_remove_payment', $payment_removed, $payment->ID, $this->id);

		return $payment_removed;

	}

	/**
	 * @param float $value
	 *
	 * @return float|int
	 */
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

	/**
	 * @param int $count
	 *
	 * @return bool|int
	 */
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