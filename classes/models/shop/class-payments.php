<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\models\parents\Parent_query;
use mp_restaurant_menu\classes\View as View;

/**
 * Class Payments
 * @package mp_restaurant_menu\classes\models
 */
class Payments extends Parent_query {
	
	protected static $instance;
	
	/**
	 * @return Payments
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Get payment by param
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return array|bool|mixed
	 */
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
					$payment = $payment->setup_payment($payment[ 0 ]);
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
					$payment = $payment->setup_payment($payment[ 0 ]);
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
	
	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_payments($args = array()) {
		$args = apply_filters('mprm_get_payments_args', $args);
		$args[ 'post_type' ] = $this->get_post_type('order');
		$this->setup_args($args);
		
		return $this->get_posts();
	}
	
	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_posts($args = array()) {
		do_action('mprm_pre_get_order', $this);
		$query = new \WP_Query($this->args);
		
		$custom_output = array(
			'orders'
		);
		if (isset($this->args[ 'output' ])) {
			if (in_array($this->args[ 'output' ], $custom_output)) {
				return $query->posts;
			}
		}
		
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				
				$payment_id = get_post()->ID;
				$payment = new Order($payment_id);
				
				if ($this->get('settings')->get_option('enable_sequential')) {
					// Backwards Compatibility, needs to set `payment_number` attribute
					$payment->payment_number = $payment->number;
				}
				
				$this->posts[] = apply_filters('mprm_payment', $payment, $payment_id, $this);
			}
			
			wp_reset_postdata();
		}
		
		do_action('mprm_post_get_order', $this);
		
		return $this->posts;
	}
	
	/**
	 * Insert payment
	 *
	 * @param array $payment_data
	 *
	 * @return bool
	 */
	public function insert_payment($payment_data = array()) {
		
		if (empty($payment_data)) {
			return false;
		}
		
		$payment = $this->get('order');
		$session = $this->get('session')->get_session_by_key('mprm_purchase');
		
		if (is_array($payment_data[ 'cart_details' ]) && !empty($payment_data[ 'cart_details' ])) {
			foreach ($payment_data[ 'cart_details' ] as $item) {
				$args = array(
					'quantity' => $item[ 'quantity' ],
					'price_id' => isset($item[ 'item_number' ][ 'options' ][ 'price_id' ]) ? $item[ 'item_number' ][ 'options' ][ 'price_id' ] : null,
					'tax' => $item[ 'tax' ],
					'item_price' => isset($item[ 'item_price' ]) ? $item[ 'item_price' ] : $item[ 'price' ],
					'fees' => isset($item[ 'fees' ]) ? $item[ 'fees' ] : array(),
					'discount' => isset($item[ 'discount' ]) ? $item[ 'discount' ] : 0,
				);
				$options = isset($item[ 'item_number' ][ 'options' ]) ? $item[ 'item_number' ][ 'options' ] : array();
				$payment->add_menu_item($item[ 'id' ], $args, $options);
			}
		}
		
		$payment->increase_tax($this->get('cart')->get_cart_fee_tax());
		$gateway = !empty($payment_data[ 'gateway' ]) ? $payment_data[ 'gateway' ] : '';
		$gateway = empty($gateway) && isset($_POST[ 'mprm-gateway' ]) ? $_POST[ 'mprm-gateway' ] : $gateway;
		$payment->status = !empty($payment_data[ 'status' ]) ? $payment_data[ 'status' ] : 'mprm-pending';
		$payment->currency = !empty($payment_data[ 'currency' ]) ? $payment_data[ 'currency' ] : $this->get('settings')->get_currency();
		$payment->user_info = $payment_data[ 'user_info' ];
		$payment->gateway = $gateway;
		$payment->user_id = $payment_data[ 'user_info' ][ 'id' ];
		$payment->email = $payment_data[ 'user_email' ];
		$payment->first_name = $payment_data[ 'user_info' ][ 'first_name' ];
		$payment->last_name = $payment_data[ 'user_info' ][ 'last_name' ];
		$payment->email = $payment_data[ 'user_info' ][ 'email' ];
		$payment->ip = $this->get('misc')->get_ip();
		$payment->key = $payment_data[ 'purchase_key' ];
		$payment->mode = $this->get('misc')->is_test_mode() ? 'test' : 'live';
		$payment->parent_payment = !empty($payment_data[ 'parent' ]) ? absint($payment_data[ 'parent' ]) : '';
		$payment->discounts = !empty($payment_data[ 'user_info' ][ 'discount' ]) ? $payment_data[ 'user_info' ][ 'discount' ] : array();
		
		$payment->customer_note = !empty($session[ 'customer_note' ]) ? $session[ 'customer_note' ] : '';
		$payment->shipping_address = !empty($session[ 'shipping_address' ]) ? $session[ 'shipping_address' ] : '';
		$payment->phone_number = !empty($session[ 'phone_number' ]) ? $session[ 'phone_number' ] : '';
		
		
		if (isset($payment_data[ 'post_date' ])) {
			$payment->date = $payment_data[ 'post_date' ];
		}
		
		if ($this->get('settings')->get_option('enable_sequential')) {
			$number = $this->get_next_payment_number();
			$payment->number = $this->format_payment_number($number);
			update_option('mprm_last_payment_number', $number);
		}
		
		// Clear the user's purchased cache
		delete_transient('mprm_user_' . $payment_data[ 'user_info' ][ 'id' ] . '_purchases');
		
		$payment->save();
		
		do_action('mprm_insert_payment', $payment->ID, $payment_data);
		
		if (!empty($payment->ID)) {
			return $payment->ID;
		}
		
		// Return false if no payment was inserted
		return false;
	}
	
	/**
	 * Next payment number
	 *
	 * @return bool|mixed
	 */
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
			
			$last_payment = $this->get_posts(array(
				'number' => 1,
				'order' => 'DESC',
				'orderby' => 'ID',
				'output' => 'posts',
				'fields' => 'ids'
			));
			
			if (!empty($last_payment)) {
				$number = $this->get_payment_number($last_payment[ 0 ]);
			}
			if (!empty($number) && $number !== (int)$last_payment[ 0 ]) {
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
	
	/**
	 * @param int $payment_id
	 *
	 * @return string
	 */
	public function get_payment_number($payment_id = 0) {
		$payment = new Order($payment_id);
		
		return $payment->number;
	}
	
	/**
	 * @param $number
	 *
	 * @return mixed
	 */
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
	
	/**
	 * @param $number
	 *
	 * @return int|mixed
	 */
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
	
	/**
	 * Save / edit order data
	 *
	 * @param $data
	 */
	public function update_payment_details($data) {
		unset($_POST[ 'mprm_update' ]);
		// Retrieve the payment ID
		$payment_id = absint($data[ 'post_ID' ]);
		$payment = new Order($payment_id);
		
		// Retrieve existing payment meta
		$meta = $payment->get_meta();
		$user_info = $payment->user_info;
		
		$status = $data[ 'mprm-order-status' ];
		
		$date = sanitize_text_field($data[ 'mprm-order-date' ]);
		$hour = sanitize_text_field($data[ 'mprm-order-time-hour' ]);
		
		// Restrict to our high and low
		if ($hour > 23) {
			$hour = 23;
		} elseif ($hour < 0) {
			$hour = 00;
		}
		
		$minute = sanitize_text_field($data[ 'mprm-order-time-min' ]);
		
		// Restrict to our high and low
		if ($minute > 59) {
			$minute = 59;
		} elseif ($minute < 0) {
			$minute = 00;
		}
		
		$address = array_map('trim', $data[ 'mprm-order-address' ][ 0 ]);
		
		$shipping_address = empty($data[ 'mprm-order-delivery' ]) ? '' : trim($data[ 'mprm-order-delivery' ]);
		
		$curr_total = $this->get('formatting')->sanitize_amount($payment->total);
		$new_total = $this->get('formatting')->sanitize_amount($_POST[ 'mprm-order-total' ]);
		$tax = isset($_POST[ 'mprm-order-tax' ]) ? $this->get('formatting')->sanitize_amount($_POST[ 'mprm-order-tax' ]) : 0;
		$date = date('Y-m-d', strtotime($date)) . ' ' . $hour . ':' . $minute . ':00';
		
		$curr_customer_id = sanitize_text_field($data[ 'mprm-current-customer' ]);
		$new_customer_id = sanitize_text_field($data[ 'customer-id' ]);
		
		$updated_menu_items = isset($_POST[ 'mprm-order-details' ]) ? $_POST[ 'mprm-order-details' ] : false;
		
		if ($updated_menu_items && !empty($_POST[ 'mprm-order-details' ])) {
			if (!empty($updated_menu_items) && is_array($updated_menu_items)) {
				foreach ($updated_menu_items as $menu_item) {
					
					// If this item doesn't have a log yet, add one for each quantity count
					$has_log = absint($menu_item[ 'has_log' ]);
					$has_log = empty($has_log) ? false : true;
					
					if ($has_log) {
						continue;
					}
					
					if (empty($menu_item[ 'item_price' ])) {
						$menu_item[ 'item_price' ] = 0.00;
					}
					
					$item_price = $menu_item[ 'item_price' ];
					$menu_item_id = absint($menu_item[ 'id' ]);
					$quantity = absint($menu_item[ 'quantity' ]) > 0 ? absint($menu_item[ 'quantity' ]) : 1;
					$price_id = false;
					
					if ($this->get('menu_item')->has_variable_prices($menu_item_id) && isset($menu_item[ 'price_id' ])) {
						$price_id = absint($menu_item[ 'price_id' ]);
					}
					
					// Set some defaults
					$args = array(
						'quantity' => $quantity,
						'item_price' => $item_price,
						'price_id' => empty($price_id) ? 0 : $price_id,
						'tax' => !isset($menu_item[ 'tax' ]) ? 0 : $menu_item[ 'tax' ]
					);
					
					$payment->add_menu_item($menu_item_id, $args);
					
				}
			}
			
			if (!empty($data[ 'mprm-order-removed' ])) {
				$deleted_menu_items = json_decode(stripcslashes($data[ 'mprm-order-removed' ]), true);
				
				foreach ($deleted_menu_items as $deleted_menu_item) {
					$deleted_menu_item = $deleted_menu_item[ 0 ];
					
					if (empty ($deleted_menu_item[ 'id' ])) {
						continue;
					}
					
					$price_id = empty($deleted_menu_item[ 'price_id' ]) ? 0 : (int)$deleted_menu_item[ 'price_id' ];
					
					$args = array(
						'quantity' => (int)$deleted_menu_item[ 'quantity' ],
						'price_id' => (int)$price_id,
						'item_price' => (float)$deleted_menu_item[ 'amount' ],
						'cart_index' => !isset($deleted_menu_item[ 'cart_index' ]) ? false : $deleted_menu_item[ 'cart_index' ]
					);
					
					$payment->remove_menu_item($deleted_menu_item[ 'id' ], $args);
					
					do_action('mprm_remove_menu_item_from_payment', $payment_id, $deleted_menu_item[ 'id' ]);
				}
			}
		}
		
		do_action('mprm_update_edited_purchase', $payment_id);
		
		$payment->date = $date;
		$updated = $payment->save();
		
		if (0 === $updated) {
			wp_die(__('Error Updating Payment', 'mp-restaurant-menu'), __('Error', 'mp-restaurant-menu'), array('response' => 400));
		}
		
		$customer_changed = false;
		
		if (isset($data[ 'mprm-new-customer' ]) && $data[ 'mprm-new-customer' ] == '1') {
			
			$email = isset($data[ 'mprm-new-customer-email' ]) ? sanitize_text_field($data[ 'mprm-new-customer-email' ]) : '';
			$names = isset($data[ 'mprm-new-customer-name' ]) ? sanitize_text_field($data[ 'mprm-new-customer-name' ]) : '';
			$phone = isset($data[ 'mprm-new-phone-number' ]) ? sanitize_text_field($data[ 'mprm-new-phone-number' ]) : '';
			
			if (empty($email) || empty($names)) {
				wp_die(__('New Customers require a name and email address', 'mp-restaurant-menu'));
			}
			
			$customer = new Customer(array('field' => 'email', 'value' => $email));
			if (empty($customer->id)) {
				$customer_data = array('name' => $names, 'email' => $email, 'phone' => $phone);
				$user_id = email_exists($email);
				if (false !== $user_id) {
					$customer_data[ 'user_id' ] = $user_id;
				}
				
				if (!$customer->create($customer_data)) {
					// Failed to crete the new customer, assume the previous customer
					$customer_changed = false;
					$customer = new Customer(array('field' => 'id', 'value' => $curr_customer_id));
					$this->get('errors')->set_error('mprm-order-new-customer-fail', __('Error creating new customer', 'mp-restaurant-menu'));
				}
			}
			
			$new_customer_id = $customer->id;
			
			$previous_customer = new Customer(array('field' => 'id', 'value' => $curr_customer_id));
			
			$customer_changed = true;
			
		} elseif ($curr_customer_id !== $new_customer_id) {
			
			$customer = new Customer(array('field' => 'id', 'value' => $new_customer_id));
			$email = $customer->email;
			$names = $customer->name;
			
			$previous_customer = new Customer(array('field' => 'id', 'value' => $curr_customer_id));
			
			$customer_changed = true;
			
		} else {
			
			$customer = new Customer(array('field' => 'id', 'value' => $curr_customer_id));
			$email = $customer->email;
			$names = $customer->name;
			
		}
		
		// Setup first and last name from input values
		$names = explode(' ', $names);
		$first_name = !empty($names[ 0 ]) ? $names[ 0 ] : '';
		$last_name = '';
		if (!empty($names[ 1 ])) {
			unset($names[ 0 ]);
			$last_name = implode(' ', $names);
		}
		
		if ($customer_changed) {
			
			// Remove the stats and payment from the previous customer and attach it to the new customer
			$previous_customer->remove_payment($payment_id, false);
			$customer->attach_payment($payment_id, false);
			
			// If purchase was completed and not ever refunded, adjust stats of customers
			if ('mprm-revoked' == $status || 'publish' == $status) {
				$previous_customer->decrease_purchase_count();
				$previous_customer->decrease_value($new_total);
				$customer->increase_purchase_count();
				$customer->increase_value($new_total);
			}
			$payment->customer_id = $customer->id;
		}
		
		// Set new meta values
		$payment->user_id = $customer->user_id;
		$payment->email = $customer->email;
		$payment->first_name = $first_name;
		$payment->last_name = $last_name;
		$payment->address = $address;
		
		$payment->shipping_address = $shipping_address;
		
		$payment->total = $new_total;
		$payment->tax = $tax;
		if (!empty($data[ 'mprm-customer-note' ])) {
			$payment->customer_note = sanitize_text_field($data[ 'mprm-customer-note' ]);
		}
		// Check for payment notes
		if (!empty($data[ 'mprm-order-note' ])) {
			$note = wp_kses($data[ 'mprm-order-note' ], array());
			$this->insert_payment_note($payment->ID, $note);
		}
		
		// Set new status
		$payment->status = $status;
		
		// Adjust total store earnings if the payment total has been changed
		if ($new_total !== $curr_total && ('publish' == $status || 'mprm-revoked' == $status)) {
			
			if ($new_total > $curr_total) {
				// Increase if our new total is higher
				$difference = $new_total - $curr_total;
				$this->increase_total_earnings($difference);
				
			} elseif ($curr_total > $new_total) {
				// Decrease if our new total is lower
				$difference = $curr_total - $new_total;
				$this->decrease_total_earnings($difference);
			}
		}
		$payment->save();
		do_action('mprm_updated_edited_purchase', $payment_id);
	}
	
	/**
	 * @param int $payment_id
	 * @param string $note
	 *
	 * @return bool|false|int
	 */
	public function insert_payment_note($payment_id = 0, $note = '') {
		if (empty($payment_id) || empty($note)) {
			return false;
		}
		
		do_action('mprm_pre_insert_payment_note', $payment_id, $note);
		
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
		
		do_action('mprm_insert_payment_note', $note_id, $payment_id, $note);
		
		return $note_id;
	}
	
	/**
	 * @param int $amount
	 *
	 * @return int|mixed
	 */
	public function increase_total_earnings($amount = 0) {
		$total = $this->get_total_earnings();
		$total += $amount;
		update_option('mprm_earnings_total', $total);
		
		return $total;
	}
	
	/**
	 * Total earnings
	 *
	 * @return mixed
	 */
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
					'status' => array('publish', 'mprm-revoked'),
					'fields' => 'ids',
					'output' => 'orders'
				));
				
				$payments = $this->get_payments($args);
				
				if ($payments) {
					if (did_action('mprm_update_payment_status')) {
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
	
	/**
	 * Decrease total earnings
	 *
	 * @param int $amount
	 *
	 * @return int|mixed
	 */
	public function decrease_total_earnings($amount = 0) {
		$total = $this->get_total_earnings();
		$total -= $amount;
		if ($total < 0) {
			$total = 0;
		}
		update_option('mprm_earnings_total', $total);
		
		return $total;
	}
	
	/**
	 * Update payment status
	 *
	 * @param $payment_id
	 * @param string $new_status
	 *
	 * @return bool
	 */
	public function update_payment_status($payment_id, $new_status = 'publish') {
		$payment = new Order($payment_id);
		$payment->status = $new_status;
		$updated = $payment->save();
		
		return $updated;
	}
	
	/**
	 * Delete purchase
	 *
	 * @param int $payment_id
	 * @param bool $update_customer
	 * @param bool $delete_menu_item_logs
	 */
	public function delete_purchase($payment_id = 0, $update_customer = true, $delete_menu_item_logs = false) {
		global $mprm_logs;
		$payment = new Order($payment_id);
		// Update sale counts and earnings for all purchased products
		$this->undo_purchase(false, $payment_id);
		$amount = $this->get_payment_amount($payment_id);
		$status = $payment->post_status;
		$customer_id = $this->get_payment_customer_id($payment_id);
		$customer = new Customer(array('field' => 'id', 'value' => $customer_id));
		if ($status == 'mprm-revoked' || $status == 'publish') {
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
//		$mprm_logs->delete_logs(
//			null,
//			'sale',
//			array(
//				array(
//					'key' => '_mprm_log_payment_id',
//					'value' => $payment_id
//				)
//			)
//		);
//		if ($delete_menu_item_logs) {
//			$mprm_logs->delete_logs(
//				null,
//				'file_menu_item',
//				array(
//					array(
//						'key' => '_mprm_log_payment_id',
//						'value' => $payment_id
//					)
//				)
//			);
//		}
		do_action('mprm_order_deleted', $payment_id);
	}
	
	/**
	 * Undo purchase
	 *
	 * @param bool $menu_item_id
	 * @param $payment_id
	 */
	public function undo_purchase($menu_item_id = false, $payment_id) {
		$payment = $this->get('order');
		
		$payment->setup_payment($payment_id);
		$cart_details = $payment->cart_details;
		$user_info = $payment->user_info;
		if (is_array($cart_details)) {
			foreach ($cart_details as $item) {
				// get the item's price
				$amount = isset($item[ 'price' ]) ? $item[ 'price' ] : false;
				// Decrease earnings/sales and fire action once per quantity number
				for ($i = 0; $i < $item[ 'quantity' ]; $i++) {
					// variable priced menu_items
					if (false === $amount && $this->get('menu_item')->has_variable_prices($item[ 'id' ])) {
						$price_id = isset($item[ 'item_number' ][ 'options' ][ 'price_id' ]) ? $item[ 'item_number' ][ 'options' ][ 'price_id' ] : null;
						$amount = !isset($item[ 'price' ]) && 0 !== $item[ 'price' ] ? $this->get('menu_item')->get_price_option_amount($item[ 'id' ], $price_id) : $item[ 'price' ];
					}
					if (!$amount) {
						// This function is only used on payments with near 1.0 cart data structure
						$amount = $this->get('menu_item')->get_final_price($item[ 'id' ], $user_info, $amount);
					}
				}
				$maybe_decrease_earnings = apply_filters('mprm_decrease_earnings_on_undo', true, $payment, $item[ 'id' ]);
				if (true === $maybe_decrease_earnings) {
					// decrease earnings
					$this->get('menu_item')->decrease_earnings($item[ 'id' ], $amount);
				}
				$maybe_decrease_sales = apply_filters('mprm_decrease_sales_on_undo', true, $payment, $item[ 'id' ]);
				if (true === $maybe_decrease_sales) {
					// decrease purchase count
					$this->get('menu_item')->decrease_purchase_count($item[ 'id' ], $item[ 'quantity' ]);
				}
			}
		}
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return mixed
	 */
	public function get_payment_amount($payment_id) {
		$payment = new Order($payment_id);
		
		return apply_filters('mprm_payment_amount', floatval($payment->total), $payment_id);
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return null
	 */
	public function get_payment_customer_id($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->customer_id;
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return bool
	 */
	public function check_for_existing_payment($payment_id) {
		$exists = false;
		$payment = new Order($payment_id);
		
		if ($payment_id === $payment->ID && 'publish' === $payment->status) {
			$exists = true;
		}
		
		return $exists;
	}
	
	/**
	 * Get payment status
	 *
	 * @param $payment
	 * @param bool $return_label
	 *
	 * @return bool|mixed
	 */
	public function get_payment_status($payment, $return_label = false) {
		if (!is_object($payment) || !isset($payment->post_status)) {
			return false;
		}
		$statuses = $this->get_payment_statuses();
		if (!is_array($statuses) || empty($statuses)) {
			return false;
		}
		$payment = new Order($payment->ID);
		if (array_key_exists($payment->status, $statuses)) {
			if (true === $return_label) {
				return $statuses[ $payment->status ];
			} else {
				// Account that our 'publish' status is labeled 'Complete'
				$post_status = 'publish' == $payment->status ? 'Payment Complete' : $payment->post_status;
				
				// Make sure we're matching cases, since they matter
				$post_label = array_search(strtolower($post_status), array_map('strtolower', $statuses));
				if ($post_label) {
					return $statuses[ $post_label ];
				} else {
					return !empty($statuses[ $payment->status ]) ? $statuses[ $payment->status ] : $payment->status;
				}
			}
		}
		
		return $payment->status;
	}
	
	/**
	 * @return mixed
	 */
	public function get_payment_statuses() {
		$payment_statuses = array(
			'mprm-pending' => __('Pending', 'mp-restaurant-menu'),
			'publish' => __('Payment Complete', 'mp-restaurant-menu'),
			'mprm-refunded' => __('Refunded', 'mp-restaurant-menu'),
			'mprm-failed' => __('Failed', 'mp-restaurant-menu'),
			'mprm-cooking' => __('Processing', 'mp-restaurant-menu'),
			'mprm-shipping' => __('Shipping', 'mp-restaurant-menu'),
			'mprm-shipped' => __('Shipped', 'mp-restaurant-menu'),
		);
		
		return apply_filters('mprm_payment_statuses', $payment_statuses);
	}
	
	/**
	 * @return array
	 */
	public function get_payment_status_keys() {
		$statuses = array_keys($this->get_payment_statuses());
		asort($statuses);
		
		return array_values($statuses);
	}
	
	/**
	 * @param null $day
	 * @param $month_num
	 * @param null $year
	 * @param null $hour
	 * @param bool $include_taxes
	 *
	 * @return float
	 */
	public function get_earnings_by_date($day = null, $month_num, $year = null, $hour = null, $include_taxes = true) {
		global $wpdb;
		$args = array(
			'post_type' => 'mprm_order',
			'nopaging' => true,
			'year' => $year,
			'monthnum' => $month_num,
			'post_status' => array('publish', 'mprm-revoked'),
			'fields' => 'ids',
			'update_post_term_cache' => false,
			'include_taxes' => $include_taxes,
		);
		if (!empty($day)) {
			$args[ 'day' ] = $day;
		}
		if (!empty($hour)) {
			$args[ 'hour' ] = $hour;
		}
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
	
	/**
	 * @param null $day
	 * @param null $month_num
	 * @param null $year
	 * @param null $hour
	 *
	 * @return int|mixed
	 */
	public function get_sales_by_date($day = null, $month_num = null, $year = null, $hour = null) {
		$args = array(
			'post_type' => 'mprm_order',
			'nopaging' => true,
			'year' => $year,
			'fields' => 'ids',
			'post_status' => array('publish', 'mprm-revoked'),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		);
		$show_free = apply_filters('mprm_sales_by_date_show_free', true, $args);
		if (false === $show_free) {
			$args[ 'meta_query' ] = array(
				array(
					'key' => '_mprm_order_total',
					'value' => 0,
					'compare' => '>',
					'type' => 'NUMERIC',
				),
			);
		}
		if (!empty($month_num)) {
			$args[ 'monthnum' ] = $month_num;
		}
		if (!empty($day)) {
			$args[ 'day' ] = $day;
		}
		if (!empty($hour)) {
			$args[ 'hour' ] = $hour;
		}
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
	
	/**
	 * @param int $payment_id
	 *
	 * @return mixed
	 */
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
	
	/**
	 * Total sales
	 * @return mixed
	 */
	public function get_total_sales() {
		$payments = $this->count_payments();
		
		return $payments->revoked + $payments->publish;
	}
	
	/**
	 * @param array $args
	 *
	 * @return array|object
	 */
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
		if (!empty($args[ 'user' ])) {
			if (is_email($args[ 'user' ])) {
				$field = 'email';
			} elseif (is_numeric($args[ 'user' ])) {
				$field = 'id';
			} else {
				$field = '';
			}
			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			if (!empty($field)) {
				$where .= "
				AND m.meta_key = '_mprm_order_user_{$field}'
				AND m.meta_value = '{$args['user']}'";
			}
			// Count payments for a search
		} elseif (!empty($args[ 's' ])) {
			if (is_email($args[ 's' ]) || strlen($args[ 's' ]) == 32) {
				if (is_email($args[ 's' ])) {
					$field = '_mprm_order_user_email';
				} else {
					$field = '_mprm_order_purchase_key';
				}
				
				$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
				$where .= $wpdb->prepare("
				AND m.meta_key = %s
				AND m.meta_value = %s",
					$field,
					$args[ 's' ]
				);
			} elseif ('#' == substr($args[ 's' ], 0, 1)) {
				$search = str_replace('#:', '', $args[ 's' ]);
				$search = str_replace('#', '', $search);
				$select = "SELECT p2.post_status,count( * ) AS num_posts ";
				$join = "LEFT JOIN $wpdb->postmeta m ON m.meta_key = '_mprm_log_payment_id' AND m.post_id = p.ID ";
				$join .= "INNER JOIN $wpdb->posts p2 ON m.meta_value = p2.ID ";
				$where = "WHERE p.post_type = 'mprm_log' ";
				$where .= $wpdb->prepare("AND p.post_parent = %d} ", $search);
			} elseif (is_numeric($args[ 's' ])) {
				$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
				$where .= $wpdb->prepare("
				AND m.meta_key = '_mprm_order_user_id'
				AND m.meta_value = %d",
					$args[ 's' ]
				);
			} elseif (0 === strpos($args[ 's' ], 'discount:')) {
				$search = str_replace('discount:', '', $args[ 's' ]);
				$search = 'discount.*' . $search;
				$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
				$where .= $wpdb->prepare("
				AND m.meta_key = '_mprm_order_meta'
				AND m.meta_value REGEXP %s",
					$search
				);
			} else {
				$search = $wpdb->esc_like($args[ 's' ]);
				$search = '%' . $search . '%';
				$where .= $wpdb->prepare("AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))", $search, $search);
			}
		}
		if (!empty($args[ 'menu_item' ]) && is_numeric($args[ 'menu_item' ])) {
			$where .= $wpdb->prepare(" AND p.post_parent = %d", $args[ 'menu_item' ]);
		}
		// Limit payments count by date
		if (!empty($args[ 'start-date' ]) && false !== strpos($args[ 'start-date' ], '/')) {
			$date_parts = explode('/', $args[ 'start-date' ]);
			$month = !empty($date_parts[ 0 ]) && is_numeric($date_parts[ 0 ]) ? $date_parts[ 0 ] : 0;
			$day = !empty($date_parts[ 1 ]) && is_numeric($date_parts[ 1 ]) ? $date_parts[ 1 ] : 0;
			$year = !empty($date_parts[ 2 ]) && is_numeric($date_parts[ 2 ]) ? $date_parts[ 2 ] : 0;
			$is_date = checkdate($month, $day, $year);
			if (false !== $is_date) {
				$date = new \DateTime($args[ 'start-date' ]);
				$where .= $wpdb->prepare(" AND p.post_date >= '%s'", $date->format('Y-m-d'));
			}
			// Fixes an issue with the payments list table counts when no end date is specified (partly with stats class)
			if (empty($args[ 'end-date' ])) {
				$args[ 'end-date' ] = $args[ 'start-date' ];
			}
		}
		if (!empty ($args[ 'end-date' ]) && false !== strpos($args[ 'end-date' ], '/')) {
			$date_parts = explode('/', $args[ 'end-date' ]);
			$month = !empty($date_parts[ 0 ]) ? $date_parts[ 0 ] : 0;
			$day = !empty($date_parts[ 1 ]) ? $date_parts[ 1 ] : 0;
			$year = !empty($date_parts[ 2 ]) ? $date_parts[ 2 ] : 0;
			$is_date = checkdate($month, $day, $year);
			if (false !== $is_date) {
				$date = new \DateTime($args[ 'end-date' ]);
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
		if (isset($statuses[ 'private' ]) && empty($args[ 's' ])) {
			unset($statuses[ 'private' ]);
		}
		foreach ($statuses as $state) {
			$stats[ $state ] = 0;
		}
		foreach ((array)$count as $row) {
			if ('private' == $row[ 'post_status' ] && empty($args[ 's' ])) {
				continue;
			}
			$stats[ $row[ 'post_status' ] ] = $row[ 'num_posts' ];
		}
		$stats = (object)$stats;
		wp_cache_set($cache_key, $stats, 'counts');
		
		return $stats;
	}
	
	/**
	 * @param int $payment_id
	 * @param string $meta_key
	 * @param bool $single
	 *
	 * @return mixed
	 */
	public function get_payment_meta($payment_id = 0, $meta_key = '_mprm_order_meta', $single = true) {
		$payment = new Order($payment_id);
		
		return $payment->get_meta($meta_key, $single);
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return array
	 */
	public function get_payment_meta_user_info($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->user_info;
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return array
	 */
	public function get_payment_meta_menu_items($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->menu_items;
	}
	
	/**
	 * @param $payment_id
	 * @param bool $include_bundle_files
	 *
	 * @return mixed
	 */
	public function get_payment_meta_cart_details($payment_id, $include_bundle_files = false) {
		
		$payment = new Order($payment_id);
		
		$cart_details = $payment->cart_details;
		$payment_currency = $payment->currency;
		if (!empty($cart_details) && is_array($cart_details)) {
			foreach ($cart_details as $key => $cart_item) {
				$cart_details[ $key ][ 'currency' ] = $payment_currency;
				// Ensure subtotal is set, for pre-1.9 orders
				if (!isset($cart_item[ 'subtotal' ])) {
					$cart_details[ $key ][ 'subtotal' ] = $cart_item[ 'price' ];
				}
				if ($include_bundle_files) {
					if ('bundle' != $this->get('menu_item')->get_menu_item_type($cart_item[ 'id' ])) {
						continue;
					}
					$products = $this->get('menu_item')->get_bundled_products($cart_item[ 'id' ]);
					if (empty($products)) {
						continue;
					}
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
								'id' => $cart_item[ 'id' ],
								'options' => isset($cart_item[ 'item_number' ][ 'options' ]) ? $cart_item[ 'item_number' ][ 'options' ] : array()
							)
						);
					}
				}
			}
		}
		
		return apply_filters('mprm_payment_meta_cart_details', $cart_details, $payment_id);
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return string
	 */
	public function get_payment_user_email($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->email;
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return bool
	 */
	public function is_guest_payment($payment_id) {
		$payment_user_id = $this->get_payment_user_id($payment_id);
		$is_guest_payment = !empty($payment_user_id) && $payment_user_id > 0 ? false : true;
		
		return (bool)apply_filters('mprm_is_guest_payment', $is_guest_payment, $payment_id);
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return int
	 */
	public function get_payment_user_id($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->user_id;
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return bool
	 */
	public function payment_has_unlimited_menu_items($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->has_unlimited_menu_items;
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return string
	 */
	public function get_payment_user_ip($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->ip;
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return string
	 */
	public function get_payment_completed_date($payment_id = 0) {
		$payment = new Order($payment_id);
		
		return $payment->completed_date;
	}
	
	/**
	 * @param $payment_id
	 *
	 * @return string
	 */
	public function get_payment_gateway($payment_id) {
		$payment = new Order($payment_id);
		
		return $payment->gateway;
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return mixed
	 */
	public function get_payment_currency($payment_id = 0) {
		$currency = $this->get_payment_currency_code($payment_id);
		
		return apply_filters('mprm_payment_currency', $this->get('misc')->get_currency_name($currency), $payment_id);
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return string
	 */
	public function get_payment_currency_code($payment_id = 0) {
		$payment = new Order($payment_id);
		
		return $payment->currency;
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return string
	 */
	public function get_payment_key($payment_id = 0) {
		$payment = new Order($payment_id);
		
		return $payment->key;
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return mixed
	 */
	public function payment_amount($payment_id = 0) {
		$amount = $this->get_payment_amount($payment_id);
		
		return $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($amount), $this->get_payment_currency_code($payment_id));
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return mixed
	 */
	public function payment_subtotal($payment_id = 0) {
		$subtotal = $this->get_payment_subtotal($payment_id);
		
		return $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($subtotal), $this->get_payment_currency_code($payment_id));
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return int
	 */
	public function get_payment_subtotal($payment_id = 0) {
		$payment = new Order($payment_id);
		
		return $payment->subtotal;
	}
	
	/**
	 * @param int $payment_id
	 * @param bool $payment_meta
	 *
	 * @return mixed
	 */
	public function payment_tax($payment_id = 0, $payment_meta = false) {
		$tax = $this->get_payment_tax($payment_id, $payment_meta);
		
		return $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($tax), $this->get_payment_currency_code($payment_id));
	}
	
	/**
	 * @param int $payment_id
	 * @param bool $payment_meta
	 *
	 * @return int
	 */
	public function get_payment_tax($payment_id = 0, $payment_meta = false) {
		$payment = new Order($payment_id);
		
		return $payment->tax;
	}
	
	/**
	 * @param int $payment_id
	 * @param bool $cart_key
	 *
	 * @return int
	 */
	public function get_payment_item_tax($payment_id = 0, $cart_key = false) {
		$payment = new Order($payment_id);
		$item_tax = 0;
		$cart_details = $payment->cart_details;
		if (false !== $cart_key && !empty($cart_details) && array_key_exists($cart_key, $cart_details)) {
			$item_tax = !empty($cart_details[ $cart_key ][ 'tax' ]) ? $cart_details[ $cart_key ][ 'tax' ] : 0;
		}
		
		return $item_tax;
	}
	
	/**
	 * @param int $payment_id
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function get_payment_fees($payment_id = 0, $type = 'all') {
		$payment = new Order($payment_id);
		
		return $payment->get_fees($type);
	}
	
	/**
	 * @param int $payment_id
	 *
	 * @return string
	 */
	public function get_payment_transaction_id($payment_id = 0) {
		$payment = new Order($payment_id);
		
		return $payment->transaction_id;
	}
	
	/**
	 * @param int $payment_id
	 * @param string $transaction_id
	 *
	 * @return bool|int
	 */
	public function set_payment_transaction_id($payment_id = 0, $transaction_id = '') {
		if (empty($payment_id) || empty($transaction_id)) {
			return false;
		}
		$transaction_id = apply_filters('mprm_set_payment_transaction_id', $transaction_id, $payment_id);
		
		return $this->update_payment_meta($payment_id, '_mprm_order_transaction_id', $transaction_id);
	}
	
	/**
	 * @param int $payment_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param string $prev_value
	 *
	 * @return bool|int
	 */
	public function update_payment_meta($payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '') {
		$payment = new Order($payment_id);
		
		return $payment->update_meta($meta_key, $meta_value, $prev_value);
	}
	
	/**
	 * @param $key
	 *
	 * @return int|null|string
	 */
	public function get_purchase_id_by_transaction_id($key) {
		global $wpdb;
		$purchase = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_mprm_order_transaction_id' AND meta_value = %s LIMIT 1", $key));
		if ($purchase != null) {
			return $purchase;
		}
		
		return 0;
	}
	
	/**
	 * @param int $payment_id
	 * @param string $search
	 *
	 * @return array|bool|int
	 */
	public function get_payment_notes($payment_id = 0, $search = '') {
		if (empty($payment_id) && empty($search)) {
			return false;
		}
		remove_action('pre_get_comments', array($this, 'hide_payment_notes'), 10);
		remove_filter('comments_clauses', array($this, 'hide_payment_notes_pre_41'), 10);
		$notes = get_comments(array('post_id' => $payment_id, 'order' => 'ASC', 'search' => $search));
		add_action('pre_get_comments', array($this, 'hide_payment_notes'), 10);
		add_filter('comments_clauses', array($this, 'hide_payment_notes_pre_41'), 10, 2);
		
		return $notes;
	}
	
	/**
	 * @param int $comment_id
	 * @param int $payment_id
	 *
	 * @return bool
	 */
	public function delete_payment_note($comment_id = 0, $payment_id = 0) {
		if (empty($comment_id)) {
			return false;
		}
		do_action('mprm_pre_delete_payment_note', $comment_id, $payment_id);
		$ret = wp_delete_comment($comment_id, true);
		do_action('mprm_post_delete_payment_note', $comment_id, $payment_id);
		
		return $ret;
	}
	
	/**
	 * @param $note
	 * @param int $payment_id
	 *
	 * @return mixed
	 */
	public function get_payment_note_html($note, $payment_id = 0) {
		if (is_numeric($note)) {
			$note = get_comment($note);
		}
		if (!empty($note->user_id)) {
			$user = get_userdata($note->user_id);
			$user = $user->display_name;
		} else {
			$user = __('System', 'mp-restaurant-menu');
		}
		$date_format = get_option('date_format') . ', ' . get_option('time_format');
		
		$delete_note_url = wp_nonce_url(add_query_arg(array(
			'mprm-action' => 'delete_payment_note',
			'note_id' => $note->comment_ID,
			'payment_id' => $payment_id
		)), 'mprm_delete_payment_note_' . $note->comment_ID);
		
		
		$note_html = View::get_instance()->render_html('../admin/metaboxes/order/notes', array(
			'note' => $note,
			'user' => $user,
			'delete_note_url' => $delete_note_url,
			'date_format' => $date_format,
			'payment_id' => $payment_id
		), false);
		
		return $note_html;
	}
	
	/**
	 * @param $query
	 */
	public function hide_payment_notes($query) {
		global $wp_version;
		if (version_compare(floatval($wp_version), '4.1', '>=')) {
			$types = isset($query->query_vars[ 'type__not_in' ]) ? $query->query_vars[ 'type__not_in' ] : array();
			if (!is_array($types)) {
				$types = array($types);
			}
			$types[] = 'mprm_order_note';
			$query->query_vars[ 'type__not_in' ] = $types;
		}
	}
	
	/**
	 * @param $clauses
	 * @param $wp_comment_query
	 *
	 * @return mixed
	 */
	public function hide_payment_notes_pre_41($clauses, $wp_comment_query) {
		global $wpdb, $wp_version;
		if (version_compare(floatval($wp_version), '4.1', '<')) {
			$clauses[ 'where' ] .= ' AND comment_type != "mprm_order_note"';
		}
		
		return $clauses;
	}
	
	/**
	 * @param $where
	 * @param $wp_comment_query
	 *
	 * @return string
	 */
	public function hide_payment_notes_from_feeds($where, $wp_comment_query) {
		global $wpdb;
		$where .= $wpdb->prepare(" AND comment_type != %s", 'mprm_order_note');
		
		return $where;
	}
	
	/**
	 * @param $stats
	 * @param $post_id
	 *
	 * @return bool|mixed|object
	 */
	public function remove_payment_notes_in_comment_counts($stats, $post_id) {
		global $wpdb, $pagenow;
		if ('index.php' != $pagenow) {
			return $stats;
		}
		$post_id = (int)$post_id;
		if (apply_filters('mprm_count_payment_notes_in_comments', false)) {
			return $stats;
		}
		$stats = wp_cache_get("comments-{$post_id}", 'counts');
		if (false !== $stats) {
			return $stats;
		}
		$where = 'WHERE comment_type != "mprm_order_note"';
		if ($post_id > 0) {
			$where .= $wpdb->prepare(" AND comment_post_ID = %d", $post_id);
		}
		$count = $wpdb->get_results("SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A);
		$total = 0;
		$approved = array(
			'0' => 'moderated',
			'1' => 'approved',
			'spam' => 'spam',
			'trash' => 'trash',
			'post-trashed' => 'post-trashed'
		);
		foreach ((array)$count as $row) {
			// Don't count post-trashed toward totals
			if ('post-trashed' != $row[ 'comment_approved' ] && 'trash' != $row[ 'comment_approved' ]) {
				$total += $row[ 'num_comments' ];
			}
			if (isset($approved[ $row[ 'comment_approved' ] ])) {
				$stats[ $approved[ $row[ 'comment_approved' ] ] ] = $row[ 'num_comments' ];
			}
		}
		$stats[ 'total_comments' ] = $total;
		foreach ($approved as $key) {
			if (empty($stats[ $key ])) {
				$stats[ $key ] = 0;
			}
		}
		$stats = (object)$stats;
		wp_cache_set("comments-{$post_id}", $stats, 'counts');
		
		return $stats;
	}
	
	/**
	 * @param string $where
	 *
	 * @return string
	 */
	public function filter_where_older_than_week($where = '') {
		// Payments older than one week
		$start = date('Y-m-d', strtotime('-7 days'));
		$where .= " AND post_date <= '{$start}'";
		
		return $where;
	}
	
	/**
	 * @param array $params
	 *
	 * @return bool|int|null|string
	 */
	public function get_payment_id($params = array()) {
		$payment_id = false;
		if (!empty($params)) {
			switch ($params[ 'search_key' ]) {
				case 'payment_key':
					$payment_id = $this->get_purchase_id_by_key($params[ 'value' ]);
					break;
				default:
					break;
			}
		}
		
		return $payment_id;
	}
	
	/**
	 * @param $key
	 *
	 * @return int|null|string
	 */
	public function get_purchase_id_by_key($key) {
		global $wpdb;
		$purchase = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_mprm_order_purchase_key' AND meta_value = %s LIMIT 1", $key));
		if ($purchase != null) {
			return $purchase;
		}
		
		return 0;
	}
	
	/**
	 * @param $payment_id
	 * @param $new_status
	 * @param $old_status
	 */
	public function complete_purchase($payment_id, $new_status, $old_status) {
		if ($old_status == 'publish' || $old_status == 'mprm-complete') {
			return; // Make sure that payments are only completed once
		}
		// Make sure the payment completion is only processed when new status is complete
		if ($new_status != 'publish' && $new_status != 'mprm-complete') {
			return;
		}
		$payment = new Order($payment_id);
		$creation_date = get_post_field('post_date', $payment_id, 'raw');
		$completed_date = $payment->completed_date;
		$user_info = $payment->user_info;
		$customer_id = $payment->customer_id;
		$amount = $payment->total;
		$cart_details = $payment->cart_details;
		do_action('mprm_pre_complete_purchase', $payment_id);
		if (is_array($cart_details)) {
			// Increase purchase count and earnings
			foreach ($cart_details as $cart_index => $menu_item) {
				// "bundle" or "default"
				$menu_item_type = $this->get('menu_item')->get_menu_item_type($menu_item[ 'id' ]);
				$price_id = isset($menu_item[ 'item_number' ][ 'options' ][ 'price_id' ]) ? (int)$menu_item[ 'item_number' ][ 'options' ][ 'price_id' ] : false;
				// Increase earnings and fire actions once per quantity number
				for ($i = 0; $i < $menu_item[ 'quantity' ]; $i++) {
					// Ensure these actions only run once, ever
					if (empty($completed_date)) {
						//mprm_record_sale_in_log($menu_item['id'], $payment_id, $price_id, $creation_date);
						do_action('mprm_complete_menu_item_purchase', $menu_item[ 'id' ], $payment_id, $menu_item_type, $menu_item, $cart_index);
					}
				}
				$menu_item_object = new Menu_item($menu_item[ 'id' ]);
				
				$menu_item_object->increase_earnings($menu_item[ 'price' ]);
				
				mprm_increase_purchase_count($menu_item[ 'id' ], $menu_item[ 'quantity' ]);
			}
			// Clear the total earnings cache
			delete_transient('mprm_earnings_total');
			// Clear the This Month earnings (this_monththis_month is NOT a typo)
			delete_transient(md5('mprm_earnings_this_monththis_month'));
			delete_transient(md5('mprm_earnings_todaytoday'));
		}
		// Increase the customer's purchase stats
		$customer = new Customer(array('field' => 'id', 'value' => $customer_id));
		
		$customer->increase_purchase_count();
		$customer->increase_value($amount);
		
		$this->get('payments')->increase_total_earnings($amount);

		// Ensure this action only runs once ever
		if (empty($completed_date)) {
			// Save the completed date
			$payment->completed_date = current_time('mysql');
			$payment->save();
			do_action('mprm_complete_purchase', $payment_id);
		}
		// Empty the shopping cart
		$this->get('cart')->empty_cart();
	}
	
	/**
	 * @param $payment_id
	 * @param $new_status
	 * @param $old_status
	 */
	public function record_status_change($payment_id, $new_status, $old_status) {
		// Get the list of statuses so that status in the payment note can be translated
		$status = $this->get_payment_statuses();
		$old_status = isset($status[ $old_status ]) ? $status[ $old_status ] : $old_status;
		$new_status = isset($status[ $new_status ]) ? $status[ $new_status ] : $new_status;
		$status_change = sprintf(__('Status changed from %s to %s', 'mp-restaurant-menu'), $old_status, $new_status);
		$this->get('payments')->insert_payment_note($payment_id, $status_change);
	}
	
	/**
	 * Reduces earnings and sales stats when a purchase is refunded
	 *
	 *
	 * @param int $payment_id the ID number of the payment
	 * @param string $new_status the status of the payment, probably "publish"
	 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
	 *
	 * @internal param Arguments $data passed
	 */
	public function undo_purchase_on_refund($payment_id, $new_status, $old_status) {
		$payment = new Order($payment_id);
		$payment->refund();
	}
	
	/**
	 * Flushes the current user's purchase history transient when a payment status
	 * is updated
	 *
	 *
	 * @param int $payment_id the ID number of the payment
	 * @param string $new_status the status of the payment, probably "publish"
	 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
	 */
	public function clear_user_history_cache($payment_id, $new_status, $old_status) {
		$payment = new Order($payment_id);
		if (!empty($payment->user_id)) {
			delete_transient('mprm_user_' . $payment->user_id . '_purchases');
		}
	}
	
	/**
	 * @param $data
	 */
	public function update_old_payments_with_totals($data) {
		if (!wp_verify_nonce($data[ '_wpnonce' ], 'mprm_upgrade_payments_nonce')) {
			return;
		}
		if (get_option('mprm_payment_totals_upgraded')) {
			return;
		}
		$payments = $this->get('payments')->get_payments(array(
			'offset' => 0,
			'number' => -1,
			'mode' => 'all',
		));
		if ($payments) {
			foreach ($payments as $payment) {
				$payment = new Order($payment->ID);
				$meta = $payment->get_meta();
				$payment->total = $meta[ 'amount' ];
				$payment->save();
			}
		}
		add_option('mprm_payment_totals_upgraded', 1);
	}
	
	/**
	 * Updates week-old+ 'pending' orders to 'abandoned'
	 *
	 * @since 1.6
	 * @return void
	 */
	public function mark_abandoned_orders() {
		$args = array(
			'status' => 'mprm-pending',
			'number' => -1,
			'output' => 'mprm_payments',
		);
		add_filter('posts_where', 'mprm_filter_where_older_than_week');
		$payments = $this->get_payments($args);
		remove_filter('posts_where', 'mprm_filter_where_older_than_week');
		if ($payments) {
			foreach ($payments as $payment) {
				if ('mprm-pending' === $payment->post_status) {
					$payment->status = 'abandoned';
					$payment->save();
				}
			}
		}
	}
	
	public function init_action() {
		
		add_action('mprm_insert_payment', array($this, 'insert_payment_action'), 11, 2);
		
		add_action('mprm_pre_get_order', array($this, 'date_filter_pre'));
		add_action('mprm_post_get_order', array($this, 'date_filter_post'));
		add_action('mprm_pre_get_order', array($this, 'orderby'));
		add_action('mprm_pre_get_order', array($this, 'status'));
		add_action('mprm_pre_get_order', array($this, 'month'));
		add_action('mprm_pre_get_order', array($this, 'per_page'));
		add_action('mprm_pre_get_order', array($this, 'page'));
		add_action('mprm_pre_get_order', array($this, 'user'));
		add_action('mprm_pre_get_order', array($this, 'search'));
		add_action('mprm_pre_get_order', array($this, 'mode'));
		add_action('mprm_pre_get_order', array($this, 'children'));
		
		add_action('mprm_weekly_scheduled_events', array($this, 'mark_abandoned_orders'));
		add_action('mprm_upgrade_payments', array($this, 'update_old_payments_with_totals'));
		add_action('mprm_update_payment_status', array($this, 'clear_user_history_cache'), 10, 3);
		add_action('mprm_update_payment_status', array($this, 'complete_purchase'), 100, 3);
		add_action('mprm_update_payment_status', array($this, 'record_status_change'), 100, 3);
		add_filter('wp_count_comments', array($this, 'remove_payment_notes_in_comment_counts'), 10, 2);
		add_filter('comment_feed_where', array($this, 'hide_payment_notes_from_feeds'), 10, 2);
		add_filter('comments_clauses', array($this, 'hide_payment_notes_pre_41'), 10, 2);
		add_action('pre_get_comments', array($this, 'hide_payment_notes'), 10);
	}
	
	/**
	 * Insert action
	 *
	 * @param $order_ID
	 * @param $payment_data
	 *
	 * @return bool/void
	 */
	public function insert_payment_action($order_ID, $payment_data) {
		$order = $this->get('order');
		if ($order->setup_payment($order_ID)) {
			$gateway = $order->gateway;
			if ($gateway == 'manual') {
				if ( ! $this->get('settings')->get_option('cod_process_payments_manually', false) ) {
					// autocomplete order
					$this->update_payment_status($order_ID, 'publish');
				} else {
					do_action('mprm_admin_sale_notice', $order_ID, $payment_data);
				}
			}
			
		}
		
		return true;
	}
}