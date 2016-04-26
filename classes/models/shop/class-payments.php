<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Payments extends Model {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_payments($args = array()) {

		// Fallback to post objects to ensure backwards compatibility
		if (!isset($args['output'])) {
			$args['output'] = 'posts';
		}

		$args = apply_filters('mprm_get_payments_args', $args);
		$payments = new \EDD_Payments_Query($args);
		return $payments->get_payments();
	}


	public function get_payment_by($field = '', $value = '') {

		if (empty($field) || empty($value)) {
			return false;
		}

		switch (strtolower($field)) {

			case 'id':
				$payment = $this->get('order');
				$payment->setup_payment($value);
				$id = $payment->ID;

				if (empty($id)) {
					return false;
				}

				break;

			case 'key':
				$payment = $this->get_payments(array(
					'meta_key' => '_mprm_order_purchase_key',
					'meta_value' => $value,
					'posts_per_page' => 1,
					'fields' => 'ids',
				));

				if ($payment) {
					$payment = $payment->setup_payment($payment[0]);
				}

				break;

			case 'payment_number':
				$payment = $this->get_payments(array(
					'meta_key' => '_mprm_order_number',
					'meta_value' => $value,
					'posts_per_page' => 1,
					'fields' => 'ids',
				));

				if ($payment) {
					$payment = $payment->setup_payment($payment[0]);
				}

				break;

			default:
				return false;
		}

		if ($payment) {
			return $payment;
		}

		return false;
	}


	public function insert_payment($payment_data = array()) {

		if (empty($payment_data)) {
			return false;
		}

		$payment = $this->get('order');

		if (is_array($payment_data['cart_details']) && !empty($payment_data['cart_details'])) {

			foreach ($payment_data['cart_details'] as $item) {

				$args = array(
					'quantity' => $item['quantity'],
					'price_id' => isset($item['item_number']['options']['price_id']) ? $item['item_number']['options']['price_id'] : null,
					'tax' => $item['tax'],
					'item_price' => isset($item['item_price']) ? $item['item_price'] : $item['price'],
					'fees' => isset($item['fees']) ? $item['fees'] : array(),
					'discount' => isset($item['discount']) ? $item['discount'] : 0,
				);

				$options = isset($item['item_number']['options']) ? $item['item_number']['options'] : array();

				$payment->add_menu_item($item['id'], $args, $options);
			}

		}

		$payment->increase_tax($this->get('cart')->get_cart_fee_tax());

		$gateway = !empty($payment_data['gateway']) ? $payment_data['gateway'] : '';
		$gateway = empty($gateway) && isset($_POST['mprm-gateway']) ? $_POST['mprm-gateway'] : $gateway;

		$payment->status = !empty($payment_data['status']) ? $payment_data['status'] : 'pending';
		$payment->currency = !empty($payment_data['currency']) ? $payment_data['currency'] : $this->get('settings')->get_currency();
		$payment->user_info = $payment_data['user_info'];
		$payment->gateway = $gateway;
		$payment->user_id = $payment_data['user_info']['id'];
		$payment->email = $payment_data['user_email'];
		$payment->first_name = $payment_data['user_info']['first_name'];
		$payment->last_name = $payment_data['user_info']['last_name'];
		$payment->email = $payment_data['user_info']['email'];
		$payment->ip = $this->get('misc')->get_ip();
		$payment->key = $payment_data['purchase_key'];
		$payment->mode = $this->get('misc')->is_test_mode() ? 'test' : 'live';
		$payment->parent_payment = !empty($payment_data['parent']) ? absint($payment_data['parent']) : '';
		$payment->discounts = !empty($payment_data['user_info']['discount']) ? $payment_data['user_info']['discount'] : array();

		if (isset($payment_data['post_date'])) {
			$payment->date = $payment_data['post_date'];
		}

		if ($this->get('settings')->get_option('enable_sequential')) {
			$number = $this->get_next_payment_number();
			$payment->number = $this->format_payment_number($number);
			update_option('mprm_last_payment_number', $number);
		}

		// Clear the user's purchased cache
		delete_transient('mprm_user_' . $payment_data['user_info']['id'] . '_purchases');

		$payment->save();

		do_action('mprm_insert_payment', $payment->ID, $payment_data);

		if (!empty($payment->ID)) {
			return $payment->ID;
		}

		// Return false if no payment was inserted
		return false;
	}

	public function update_payment_status($payment_id, $new_status = 'publish') {

		$payment = $this->get('order');
		$payment->setup_payment($payment_id);
		$payment->status = $new_status;
		$updated = $payment->save();

		return $updated;
	}

	public function delete_purchase($payment_id = 0, $update_customer = true, $delete_menu_item_logs = false) {
		global $mprm_logs;
		$payment = new Order($payment_id);

		// Update sale counts and earnings for all purchased products
		$this->undo_purchase(false, $payment_id);

		$amount = $this->get_payment_amount($payment_id);
		$status = $payment->post_status;
		$customer_id = $this->get_payment_customer_id($payment_id);

		$customer = new Customer(array('field', 'ID', 'value' => $customer_id));

		if ($status == 'revoked' || $status == 'publish') {
			// Only decrease earnings if they haven't already been decreased (or were never increased for this payment)
			$this->decrease_total_earnings($amount);
			// Clear the This Month earnings (this_monththis_month is NOT a typo)
			delete_transient(md5('mprm_earnings_this_monththis_month'));

			if ($customer->id && $update_customer) {

				// Decrement the stats for the customer
				$customer->decrease_purchase_count();
				$customer->decrease_value($amount);

			}
		}

		do_action('mprm_order_delete', $payment_id);

		if ($customer->id && $update_customer) {

			// Remove the payment ID from the customer
			$customer->remove_payment($payment_id);

		}

		// Remove the payment
		wp_delete_post($payment_id, true);

		// Remove related sale log entries
		$mprm_logs->delete_logs(
			null,
			'sale',
			array(
				array(
					'key' => '_mprm_log_payment_id',
					'value' => $payment_id
				)
			)
		);

		if ($delete_menu_item_logs) {
			$mprm_logs->delete_logs(
				null,
				'file_menu_item',
				array(
					array(
						'key' => '_mprm_log_payment_id',
						'value' => $payment_id
					)
				)
			);
		}

		do_action('mprm_order_deleted', $payment_id);
	}

	public function undo_purchase($menu_item_id = false, $payment_id) {

		$payment = $this->get('order');
		$payment->setup_payment($payment_id);

		$cart_details = $payment->cart_details;
		$user_info = $payment->user_info;

		if (is_array($cart_details)) {

			foreach ($cart_details as $item) {

				// get the item's price
				$amount = isset($item['price']) ? $item['price'] : false;

				// Decrease earnings/sales and fire action once per quantity number
				for ($i = 0; $i < $item['quantity']; $i++) {

					// variable priced menu_items
					if (false === $amount && $this->get('menu_item')->has_variable_prices($item['id'])) {
						$price_id = isset($item['item_number']['options']['price_id']) ? $item['item_number']['options']['price_id'] : null;
						$amount = !isset($item['price']) && 0 !== $item['price'] ? $this->get('menu_item')->get_price_option_amount($item['id'], $price_id) : $item['price'];
					}

					if (!$amount) {
						// This function is only used on payments with near 1.0 cart data structure
						$amount = $this->get('menu_item')->get_final_price($item['id'], $user_info, $amount);
					}

				}
				$maybe_decrease_earnings = apply_filters('mprm_decrease_earnings_on_undo', true, $payment, $item['id']);
				if (true === $maybe_decrease_earnings) {
					// decrease earnings
					$this->get('menu_item')->decrease_earnings($item['id'], $amount);
				}

				$maybe_decrease_sales = apply_filters('mprm_decrease_sales_on_undo', true, $payment, $item['id']);
				if (true === $maybe_decrease_sales) {
					// decrease purchase count
					$this->get('menu_item')->decrease_purchase_count($item['id'], $item['quantity']);
				}

			}

		}

	}

	public function count_payments($args = array()) {
		global $wpdb;

		$defaults = array(
			'user' => null,
			's' => null,
			'start-date' => null,
			'end-date' => null,
			'menu_item' => null,
		);

		$args = wp_parse_args($args, $defaults);

		$select = "SELECT p.post_status,count( * ) AS num_posts";
		$join = '';
		$where = "WHERE p.post_type = 'mprm_order'";

		// Count payments for a specific user
		if (!empty($args['user'])) {

			if (is_email($args['user']))
				$field = 'email';
			elseif (is_numeric($args['user']))
				$field = 'id';
			else
				$field = '';

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";

			if (!empty($field)) {
				$where .= "
				AND m.meta_key = '_mprm_order_user_{$field}'
				AND m.meta_value = '{$args['user']}'";
			}

			// Count payments for a search
		} elseif (!empty($args['s'])) {

			if (is_email($args['s']) || strlen($args['s']) == 32) {

				if (is_email($args['s']))
					$field = '_mprm_order_user_email';
				else
					$field = '_mprm_order_purchase_key';


				$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
				$where .= $wpdb->prepare("
				AND m.meta_key = %s
				AND m.meta_value = %s",
					$field,
					$args['s']
				);

			} elseif ('#' == substr($args['s'], 0, 1)) {

				$search = str_replace('#:', '', $args['s']);
				$search = str_replace('#', '', $search);

				$select = "SELECT p2.post_status,count( * ) AS num_posts ";
				$join = "LEFT JOIN $wpdb->postmeta m ON m.meta_key = '_mprm_log_payment_id' AND m.post_id = p.ID ";
				$join .= "INNER JOIN $wpdb->posts p2 ON m.meta_value = p2.ID ";
				$where = "WHERE p.post_type = 'mprm_log' ";
				$where .= $wpdb->prepare("AND p.post_parent = %d} ", $search);

			} elseif (is_numeric($args['s'])) {

				$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
				$where .= $wpdb->prepare("
				AND m.meta_key = '_mprm_order_user_id'
				AND m.meta_value = %d",
					$args['s']
				);

			} elseif (0 === strpos($args['s'], 'discount:')) {

				$search = str_replace('discount:', '', $args['s']);
				$search = 'discount.*' . $search;

				$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
				$where .= $wpdb->prepare("
				AND m.meta_key = '_mprm_order_meta'
				AND m.meta_value REGEXP %s",
					$search
				);

			} else {
				$search = $wpdb->esc_like($args['s']);
				$search = '%' . $search . '%';

				$where .= $wpdb->prepare("AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))", $search, $search);
			}

		}

		if (!empty($args['menu_item']) && is_numeric($args['menu_item'])) {

			$where .= $wpdb->prepare(" AND p.post_parent = %d", $args['menu_item']);
		}

		// Limit payments count by date
		if (!empty($args['start-date']) && false !== strpos($args['start-date'], '/')) {

			$date_parts = explode('/', $args['start-date']);
			$month = !empty($date_parts[0]) && is_numeric($date_parts[0]) ? $date_parts[0] : 0;
			$day = !empty($date_parts[1]) && is_numeric($date_parts[1]) ? $date_parts[1] : 0;
			$year = !empty($date_parts[2]) && is_numeric($date_parts[2]) ? $date_parts[2] : 0;

			$is_date = checkdate($month, $day, $year);
			if (false !== $is_date) {

				$date = new \DateTime($args['start-date']);
				$where .= $wpdb->prepare(" AND p.post_date >= '%s'", $date->format('Y-m-d'));

			}

			// Fixes an issue with the payments list table counts when no end date is specified (partly with stats class)
			if (empty($args['end-date'])) {
				$args['end-date'] = $args['start-date'];
			}
		}
		if (!empty ($args['end-date']) && false !== strpos($args['end-date'], '/')) {

			$date_parts = explode('/', $args['end-date']);

			$month = !empty($date_parts[0]) ? $date_parts[0] : 0;
			$day = !empty($date_parts[1]) ? $date_parts[1] : 0;
			$year = !empty($date_parts[2]) ? $date_parts[2] : 0;

			$is_date = checkdate($month, $day, $year);
			if (false !== $is_date) {

				$date = new \DateTime($args['end-date']);
				$where .= $wpdb->prepare(" AND p.post_date <= '%s'", $date->format('Y-m-d'));
			}
		}
		$where = apply_filters('mprm_count_payments_where', $where);
		$join = apply_filters('mprm_count_payments_join', $join);

		$query = "$select
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status";

		$cache_key = md5($query);

		$count = wp_cache_get($cache_key, 'counts');
		if (false !== $count) {
			return $count;
		}

		$count = $wpdb->get_results($query, ARRAY_A);

		$stats = array();
		$statuses = get_post_stati();
		if (isset($statuses['private']) && empty($args['s'])) {
			unset($statuses['private']);
		}

		foreach ($statuses as $state) {
			$stats[$state] = 0;
		}

		foreach ((array)$count as $row) {

			if ('private' == $row['post_status'] && empty($args['s'])) {
				continue;
			}

			$stats[$row['post_status']] = $row['num_posts'];
		}

		$stats = (object)$stats;
		wp_cache_set($cache_key, $stats, 'counts');

		return $stats;
	}

	public function check_for_existing_payment($payment_id) {
		$exists = false;
		$payment = new Order($payment_id);

		if ($payment_id === $payment->ID && 'publish' === $payment->status) {
			$exists = true;
		}

		return $exists;
	}


	public function get_payment_status($payment, $return_label = false) {

		if (!is_object($payment) || !isset($payment->post_status)) {
			return false;
		}

		$statuses = $this->get_payment_statuses();

		if (!is_array($statuses) || empty($statuses)) {
			return false;
		}

		$payment = $payment = new Order($payment->ID);

		if (array_key_exists($payment->status, $statuses)) {
			if (true === $return_label) {
				return $statuses[$payment->status];
			} else {
				// Account that our 'publish' status is labeled 'Complete'
				$post_status = 'publish' == $payment->status ? 'Complete' : $payment->post_status;

				// Make sure we're matching cases, since they matter
				return array_search(strtolower($post_status), array_map('strtolower', $statuses));
			}
		}

		return false;
	}

	public function get_payment_statuses() {
		$payment_statuses = array(
			'pending' => __('Pending', 'mp-restaurant-menu'),
			'publish' => __('Complete', 'mp-restaurant-menu'),
			'refunded' => __('Refunded', 'mp-restaurant-menu'),
			'failed' => __('Failed', 'mp-restaurant-menu'),
			'abandoned' => __('Abandoned', 'mp-restaurant-menu'),
			'revoked' => __('Revoked', 'mp-restaurant-menu')
		);

		return apply_filters('mprm_payment_statuses', $payment_statuses);
	}

	public function get_payment_status_keys() {
		$statuses = array_keys($this->get_payment_statuses());
		asort($statuses);

		return array_values($statuses);
	}

	public function get_earnings_by_date($day = null, $month_num, $year = null, $hour = null, $include_taxes = true) {
		global $wpdb;

		$args = array(
			'post_type' => 'mprm_order',
			'nopaging' => true,
			'year' => $year,
			'monthnum' => $month_num,
			'post_status' => array('publish', 'revoked'),
			'fields' => 'ids',
			'update_post_term_cache' => false,
			'include_taxes' => $include_taxes,
		);
		if (!empty($day))
			$args['day'] = $day;

		if (!empty($hour))
			$args['hour'] = $hour;

		$args = apply_filters('mprm_get_earnings_by_date_args', $args);
		$key = 'mprm_stats_' . substr(md5(serialize($args)), 0, 15);
		$earnings = get_transient($key);

		if (false === $earnings) {
			$sales = get_posts($args);
			$earnings = 0;
			if ($sales) {
				$sales = implode(',', $sales);

				$total_earnings = $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_mprm_order_total' AND post_id IN ({$sales})");
				$total_tax = 0;

				if (!$include_taxes) {
					$total_tax = $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_mprm_order_tax' AND post_id IN ({$sales})");
				}

				$earnings += ($total_earnings - $total_tax);
			}
			// Cache the results for one hour
			set_transient($key, $earnings, HOUR_IN_SECONDS);
		}

		return round($earnings, 2);
	}

	public function get_sales_by_date($day = null, $month_num = null, $year = null, $hour = null) {

		$args = array(
			'post_type' => 'mprm_order',
			'nopaging' => true,
			'year' => $year,
			'fields' => 'ids',
			'post_status' => array('publish', 'revoked'),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		);

		$show_free = apply_filters('mprm_sales_by_date_show_free', true, $args);

		if (false === $show_free) {
			$args['meta_query'] = array(
				array(
					'key' => '_mprm_order_total',
					'value' => 0,
					'compare' => '>',
					'type' => 'NUMERIC',
				),
			);
		}

		if (!empty($month_num))
			$args['monthnum'] = $month_num;

		if (!empty($day))
			$args['day'] = $day;

		if (!empty($hour))
			$args['hour'] = $hour;

		$args = apply_filters('mprm_get_sales_by_date_args', $args);

		$key = 'mprm_stats_' . substr(md5(serialize($args)), 0, 15);
		$count = get_transient($key);

		if (false === $count) {
			$sales = new \WP_Query($args);
			$count = (int)$sales->post_count;
			// Cache the results for one hour
			set_transient($key, $count, HOUR_IN_SECONDS);
		}

		return $count;
	}

	public function is_payment_complete($payment_id = 0) {
		$payment = new Order($payment_id);

		$ret = false;

		if ($payment->ID > 0) {

			if ((int)$payment_id === (int)$payment->ID && 'publish' == $payment->status) {
				$ret = true;
			}

		}

		return apply_filters('mprm_is_payment_complete', $ret, $payment_id, $payment->post_status);
	}

	public function get_total_sales() {
		$payments = $this->count_payments();
		return $payments->revoked + $payments->publish;
	}

	public function get_total_earnings() {

		$total = get_option('mprm_earnings_total', false);

		// If no total stored in DB, use old method of calculating total earnings
		if (false === $total) {

			global $wpdb;

			$total = get_transient('mprm_earnings_total');

			if (false === $total) {

				$total = (float)0;

				$args = apply_filters('mprm_get_total_earnings_args', array(
					'offset' => 0,
					'number' => -1,
					'status' => array('publish', 'revoked'),
					'fields' => 'ids'
				));


				$payments = $this->get_payments($args);
				if ($payments) {

					if (did_action('edd_update_payment_status')) {
						array_pop($payments);
					}

					if (!empty($payments)) {
						$payments = implode(',', $payments);
						$total += $wpdb->get_var("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_mprm_order_total' AND post_id IN({$payments})");
					}

				}

				// Cache results for 1 day. This cache is cleared automatically when a payment is made
				set_transient('mprm_earnings_total', $total, 86400);

				// Store the total for the first time
				update_option('mprm_earnings_total', $total);
			}
		}

		if ($total < 0) {
			$total = 0; // Don't ever show negative earnings
		}

		return apply_filters('mprm_total_earnings', round($total, $this->get('formatting')->currency_decimal_filter()));
	}

	public function increase_total_earnings($amount = 0) {
		$total = $this->get_total_earnings();
		$total += $amount;
		update_option('mprm_earnings_total', $total);
		return $total;
	}

	public function decrease_total_earnings($amount = 0) {
		$total = $this->get_total_earnings();
		$total -= $amount;
		if ($total < 0) {
			$total = 0;
		}
		update_option('mprm_earnings_total', $total);
		return $total;
	}

	public function get_payment_meta($payment_id = 0, $meta_key = '_mprm_order_meta', $single = true) {
		$payment = new Order($payment_id);
		return $payment->get_meta($meta_key, $single);
	}

	public function update_payment_meta($payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '') {
		$payment = new Order($payment_id);
		return $payment->update_meta($meta_key, $meta_value, $prev_value);
	}

	public function get_payment_meta_user_info($payment_id) {
		$payment = new Order($payment_id);
		return $payment->user_info;
	}

	public function get_payment_meta_menu_items($payment_id) {
		$payment = new Order($payment_id);
		return $payment->menu_items;
	}

	public function get_payment_meta_cart_details($payment_id, $include_bundle_files = false) {
		$payment = $this->get('order');
		$payment->setup_payment($payment_id);
		$cart_details = $payment->cart_details;

		$payment_currency = $payment->currency;

		if (!empty($cart_details) && is_array($cart_details)) {

			foreach ($cart_details as $key => $cart_item) {
				$cart_details[$key]['currency'] = $payment_currency;

				// Ensure subtotal is set, for pre-1.9 orders
				if (!isset($cart_item['subtotal'])) {
					$cart_details[$key]['subtotal'] = $cart_item['price'];
				}

				if ($include_bundle_files) {

					if ('bundle' != $this->get('menu_item')->get_menu_item_type($cart_item['id']))
						continue;

					$products = $this->get('menu_item')->get_bundled_products($cart_item['id']);
					if (empty($products))
						continue;

					foreach ($products as $product_id) {
						$cart_details[] = array(
							'id' => $product_id,
							'name' => get_the_title($product_id),
							'item_number' => array(
								'id' => $product_id,
								'options' => array(),
							),
							'price' => 0,
							'subtotal' => 0,
							'quantity' => 1,
							'tax' => 0,
							'in_bundle' => 1,
							'parent' => array(
								'id' => $cart_item['id'],
								'options' => isset($cart_item['item_number']['options']) ? $cart_item['item_number']['options'] : array()
							)
						);
					}
				}
			}

		}

		return apply_filters('mprm_payment_meta_cart_details', $cart_details, $payment_id);
	}

	public function get_payment_user_email($payment_id) {
		$payment = new Order($payment_id);
		return $payment->email;
	}

	public function is_guest_payment($payment_id) {
		$payment_user_id = $this->get_payment_user_id($payment_id);
		$is_guest_payment = !empty($payment_user_id) && $payment_user_id > 0 ? false : true;

		return (bool)apply_filters('mprm_is_guest_payment', $is_guest_payment, $payment_id);
	}

	public function get_payment_user_id($payment_id) {
		$payment = new Order($payment_id);
		return $payment->user_id;
	}

	public function get_payment_customer_id($payment_id) {
		$payment = new Order($payment_id);
		return $payment->customer_id;
	}

	public function payment_has_unlimited_menu_items($payment_id) {
		$payment = new Order($payment_id);
		return $payment->has_unlimited_menu_items;
	}

	public function get_payment_user_ip($payment_id) {
		$payment = new Order($payment_id);
		return $payment->ip;
	}

	public function get_payment_completed_date($payment_id = 0) {
		$payment = new Order($payment_id);
		return $payment->completed_date;
	}

	public function get_payment_gateway($payment_id) {
		$payment = new Order($payment_id);
		return $payment->gateway;
	}

	public function get_payment_currency_code($payment_id = 0) {
		$payment = new Order($payment_id);
		return $payment->currency;
	}

	public function get_payment_currency($payment_id = 0) {
		$currency = $this->get_payment_currency_code($payment_id);
		return apply_filters('mprm_payment_currency', $this->get('misc')->get_currency_name($currency), $payment_id);
	}

	public function get_payment_key($payment_id = 0) {
		$payment = new Order($payment_id);
		return $payment->key;
	}

	public function get_payment_number($payment_id = 0) {
		$payment = new Order($payment_id);
		return $payment->number;
	}

	public function format_payment_number($number) {

		if (!$this->get('settings')->get_option('enable_sequential')) {
			return $number;
		}

		if (!is_numeric($number)) {
			return $number;
		}

		$prefix = $this->get('settings')->get_option('sequential_prefix');
		$number = absint($number);
		$postfix = $this->get('settings')->get_option('sequential_postfix');

		$formatted_number = $prefix . $number . $postfix;

		return apply_filters('mprm_format_payment_number', $formatted_number, $prefix, $number, $postfix);
	}

	public function get_next_payment_number() {

		if (!$this->get('settings')->get_option('enable_sequential')) {
			return false;
		}

		$number = get_option('mprm_last_payment_number');
		$start = $this->get('settings')->get_option('sequential_start', 1);
		$increment_number = true;

		if (false !== $number) {

			if (empty($number)) {

				$number = $start;
				$increment_number = false;

			}

		} else {

			// This case handles the first addition of the new option, as well as if it get's deleted for any reason
			$payments = new \EDD_Payments_Query(array('number' => 1, 'order' => 'DESC', 'orderby' => 'ID', 'output' => 'posts', 'fields' => 'ids'));
			$last_payment = $payments->get_payments();

			if (!empty($last_payment)) {

				$number = $this->get_payment_number($last_payment[0]);

			}

			if (!empty($number) && $number !== (int)$last_payment[0]) {

				$number = $this->remove_payment_prefix_postfix($number);

			} else {

				$number = $start;
				$increment_number = false;
			}

		}

		$increment_number = apply_filters('mprm_increment_payment_number', $increment_number, $number);

		if ($increment_number) {
			$number++;
		}

		return apply_filters('mprm_get_next_payment_number', $number);
	}

	public function remove_payment_prefix_postfix($number) {

		$prefix = $this->get('settings')->get_option('sequential_prefix');
		$postfix = $this->get('settings')->get_option('sequential_postfix');

		// Remove prefix
		$number = preg_replace('/' . $prefix . '/', '', $number, 1);

		// Remove the postfix
		$length = strlen($number);
		$postfix_pos = strrpos($number, $postfix);
		if (false !== $postfix_pos) {
			$number = substr_replace($number, '', $postfix_pos, $length);
		}

		// Ensure it's a whole number
		$number = intval($number);

		return apply_filters('mprm_remove_payment_prefix_postfix', $number, $prefix, $postfix);

	}

	public function payment_amount($payment_id = 0) {
		$amount = $this->get_payment_amount($payment_id);
		return $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($amount), $this->get_payment_currency_code($payment_id));
	}

	public function get_payment_amount($payment_id) {
		$payment = new Order($payment_id);

		return apply_filters('mprm_payment_amount', floatval($payment->total), $payment_id);
	}

	public function payment_subtotal($payment_id = 0) {
		$subtotal = $this->get_payment_subtotal($payment_id);

		return $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($subtotal), $this->get_payment_currency_code($payment_id));
	}

	public function get_payment_subtotal($payment_id = 0) {
		$payment = new Order($payment_id);

		return $payment->subtotal;
	}

	public function payment_tax($payment_id = 0, $payment_meta = false) {
		$tax = $this->get_payment_tax($payment_id, $payment_meta);
		return $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($tax), $this->get_payment_currency_code($payment_id));
	}

	public function get_payment_tax($payment_id = 0, $payment_meta = false) {
		$payment = new Order($payment_id);

		return $payment->tax;
	}


	public function get_payment_item_tax($payment_id = 0, $cart_key = false) {
		$payment = new Order($payment_id);
		$item_tax = 0;

		$cart_details = $payment->cart_details;

		if (false !== $cart_key && !empty($cart_details) && array_key_exists($cart_key, $cart_details)) {
			$item_tax = !empty($cart_details[$cart_key]['tax']) ? $cart_details[$cart_key]['tax'] : 0;
		}

		return $item_tax;

	}


	public function get_payment_fees($payment_id = 0, $type = 'all') {
		$payment = new Order($payment_id);
		return $payment->get_fees($type);
	}


	public function get_payment_transaction_id($payment_id = 0) {
		$payment = new Order($payment_id);
		return $payment->transaction_id;
	}


	public function set_payment_transaction_id($payment_id = 0, $transaction_id = '') {

		if (empty($payment_id) || empty($transaction_id)) {
			return false;
		}

		$transaction_id = apply_filters('mprm_set_payment_transaction_id', $transaction_id, $payment_id);

		return $this->update_payment_meta($payment_id, '_mprm_order_transaction_id', $transaction_id);
	}


	public function get_purchase_id_by_key($key) {
		global $wpdb;

		$purchase = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_mprm_order_purchase_key' AND meta_value = %s LIMIT 1", $key));

		if ($purchase != NULL)
			return $purchase;

		return 0;
	}


	public function get_purchase_id_by_transaction_id($key) {
		global $wpdb;

		$purchase = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_mprm_order_transaction_id' AND meta_value = %s LIMIT 1", $key));

		if ($purchase != NULL)
			return $purchase;

		return 0;
	}


	public function get_payment_notes($payment_id = 0, $search = '') {

		if (empty($payment_id) && empty($search)) {
			return false;
		}

		remove_action('pre_get_comments', 'edd_hide_payment_notes', 10);
		remove_filter('comments_clauses', 'edd_hide_payment_notes_pre_41', 10);

		$notes = get_comments(array('post_id' => $payment_id, 'order' => 'ASC', 'search' => $search));

		add_action('pre_get_comments', 'edd_hide_payment_notes', 10);
		add_filter('comments_clauses', 'edd_hide_payment_notes_pre_41', 10, 2);

		return $notes;
	}


	public function insert_payment_note($payment_id = 0, $note = '') {
		if (empty($payment_id))
			return false;

		do_action('edd_pre_insert_payment_note', $payment_id, $note);

		$note_id = wp_insert_comment(wp_filter_comment(array(
			'comment_post_ID' => $payment_id,
			'comment_content' => $note,
			'user_id' => is_admin() ? get_current_user_id() : 0,
			'comment_date' => current_time('mysql'),
			'comment_date_gmt' => current_time('mysql', 1),
			'comment_approved' => 1,
			'comment_parent' => 0,
			'comment_author' => '',
			'comment_author_IP' => '',
			'comment_author_url' => '',
			'comment_author_email' => '',
			'comment_type' => 'mprm_order_note'

		)));

		do_action('edd_insert_payment_note', $note_id, $payment_id, $note);

		return $note_id;
	}


	public function delete_payment_note($comment_id = 0, $payment_id = 0) {
		if (empty($comment_id))
			return false;

		do_action('edd_pre_delete_payment_note', $comment_id, $payment_id);
		$ret = wp_delete_comment($comment_id, true);
		do_action('edd_post_delete_payment_note', $comment_id, $payment_id);

		return $ret;
	}

	public function get_payment_note_html($note, $payment_id = 0) {

		if (is_numeric($note)) {
			$note = get_comment($note);
		}

		if (!empty($note->user_id)) {
			$user = get_userdata($note->user_id);
			$user = $user->display_name;
		} else {
			$user = __('EDD Bot', 'mp-restaurant-menu');
		}

		$date_format = get_option('date_format') . ', ' . get_option('time_format');

		$delete_note_url = wp_nonce_url(add_query_arg(array(
			'mprm-action' => 'delete_payment_note',
			'note_id' => $note->comment_ID,
			'payment_id' => $payment_id
		)), 'edd_delete_payment_note_' . $note->comment_ID);

		$note_html = '<div class="mprm-payment-note" id="mprm-payment-note-' . $note->comment_ID . '">';
		$note_html .= '<p>';
		$note_html .= '<strong>' . $user . '</strong>&nbsp;&ndash;&nbsp;' . date_i18n($date_format, strtotime($note->comment_date)) . '<br/>';
		$note_html .= $note->comment_content;
		$note_html .= '&nbsp;&ndash;&nbsp;<a href="' . esc_url($delete_note_url) . '" class="mprm-delete-payment-note" data-note-id="' . absint($note->comment_ID) . '" data-payment-id="' . absint($payment_id) . '" title="' . __('Delete this payment note', 'mp-restaurant-menu') . '">' . __('Delete', 'mp-restaurant-menu') . '</a>';
		$note_html .= '</p>';
		$note_html .= '</div>';

		return $note_html;

	}

	public function hide_payment_notes($query) {
		global $wp_version;

		if (version_compare(floatval($wp_version), '4.1', '>=')) {
			$types = isset($query->query_vars['type__not_in']) ? $query->query_vars['type__not_in'] : array();
			if (!is_array($types)) {
				$types = array($types);
			}
			$types[] = 'mprm_order_note';
			$query->query_vars['type__not_in'] = $types;
		}
	}


	public function hide_payment_notes_pre_41($clauses, $wp_comment_query) {
		global $wpdb, $wp_version;

		if (version_compare(floatval($wp_version), '4.1', '<')) {
			$clauses['where'] .= ' AND comment_type != "mprm_order_note"';
		}
		return $clauses;
	}


	public function hide_payment_notes_from_feeds($where, $wp_comment_query) {
		global $wpdb;

		$where .= $wpdb->prepare(" AND comment_type != %s", 'mprm_order_note');
		return $where;
	}


	public function remove_payment_notes_in_comment_counts($stats, $post_id) {
		global $wpdb, $pagenow;

		if ('index.php' != $pagenow) {
			return $stats;
		}

		$post_id = (int)$post_id;

		if (apply_filters('mprm_count_payment_notes_in_comments', false))
			return $stats;

		$stats = wp_cache_get("comments-{$post_id}", 'counts');

		if (false !== $stats)
			return $stats;

		$where = 'WHERE comment_type != "mprm_order_note"';

		if ($post_id > 0)
			$where .= $wpdb->prepare(" AND comment_post_ID = %d", $post_id);

		$count = $wpdb->get_results("SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A);

		$total = 0;
		$approved = array('0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed');
		foreach ((array)$count as $row) {
			// Don't count post-trashed toward totals
			if ('post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'])
				$total += $row['num_comments'];
			if (isset($approved[$row['comment_approved']]))
				$stats[$approved[$row['comment_approved']]] = $row['num_comments'];
		}

		$stats['total_comments'] = $total;
		foreach ($approved as $key) {
			if (empty($stats[$key]))
				$stats[$key] = 0;
		}

		$stats = (object)$stats;
		wp_cache_set("comments-{$post_id}", $stats, 'counts');

		return $stats;
	}


	public function filter_where_older_than_week($where = '') {
		// Payments older than one week
		$start = date('Y-m-d', strtotime('-7 days'));
		$where .= " AND post_date <= '{$start}'";
		return $where;
	}
}

add_filter('wp_count_comments', 'edd_remove_payment_notes_in_comment_counts', 10, 2);
add_filter('comment_feed_where', 'edd_hide_payment_notes_from_feeds', 10, 2);
add_filter('comments_clauses', 'edd_hide_payment_notes_pre_41', 10, 2);
add_action('pre_get_comments', 'edd_hide_payment_notes', 10);