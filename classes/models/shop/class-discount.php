<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Discount
 * @package mp_restaurant_menu\classes\models
 */
class Discount extends Model {
	protected static $instance;

	/**
	 * @return Discount
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param array $item
	 *
	 * @return int
	 */
	public function get_cart_item_discount_amount($item = array()) {
		global $mprm_is_last_cart_item, $mprm_flat_discount_total;
		// If we're not meeting the requirements of the $item array, return or set them
		if (empty($item) || empty($item['id'])) {
			return 0;
		}
		// Quantity is a requirement of the cart options array to determine the discounted price
		if (empty($item['quantity'])) {
			return 0;
		}
		if (!isset($item['options'])) {
			$item['options'] = array();
		}
		$amount = 0;
		$price = $this->get('cart')->get_cart_item_price($item['id'], $item['options']);
		$discounted_price = $price;
		// Retrieve all discounts applied to the cart
		$discounts = $this->get('cart')->get_cart_discounts();
		if ($discounts) {
			foreach ($discounts as $discount) {
				$code_id = $this->get_discount_id_by_code($discount);
				// Check discount exists
				if (!$code_id) {
					continue;
				}
				$reqs = $this->get_discount_product_reqs($code_id);
				$excluded_products = $this->get_discount_excluded_products($code_id);
				// Make sure requirements are set and that this discount shouldn't apply to the whole cart
				if (!empty($reqs) && $this->is_discount_not_global($code_id)) {
					// This is a product(s) specific discount
					foreach ($reqs as $menu_item_id) {
						if ($menu_item_id == $item['id'] && !in_array($item['id'], $excluded_products)) {
							$discounted_price -= $price - $this->get_discounted_amount($discount, $price);
						}
					}
				} else {
					// This is a global cart discount
					if (!in_array($item['id'], $excluded_products)) {
						if ('flat' === $this->get_discount_type($code_id)) {
							/* *
							 * In order to correctly record individual item amounts, global flat rate discounts
							 * are distributed across all cart items. The discount amount is divided by the number
							 * of items in the cart and then a portion is evenly applied to each cart item
							 */
							$items_subtotal = 0.00;
							$cart_items = $this->get('cart')->get_cart_contents();
							foreach ($cart_items as $cart_item) {
								if (!in_array($cart_item['id'], $excluded_products)) {
									$item_price = $this->get('cart')->get_cart_item_price($cart_item['id'], $cart_item['options']);
									$items_subtotal += $item_price * $cart_item['quantity'];
								}
							}
							$subtotal_percent = (($price * $item['quantity']) / $items_subtotal);
							$code_amount = $this->get_discount_amount($code_id);
							$discounted_amount = $code_amount * $subtotal_percent;
							$discounted_price -= $discounted_amount;
							$mprm_flat_discount_total += round($discounted_amount, $this->get('formatting')->currency_decimal_filter());
							if ($mprm_is_last_cart_item && $mprm_flat_discount_total < $code_amount) {
								$adjustment = $code_amount - $mprm_flat_discount_total;
								$discounted_price -= $adjustment;
							}
						} else {
							$discounted_price -= $price - $this->get_discounted_amount($discount, $price);
						}
					}
				}
			}
			$amount = ($price - apply_filters('mprm_get_cart_item_discounted_amount', $discounted_price, $discounts, $item, $price));
			if ('flat' !== $this->get_discount_type($code_id)) {
				$amount = $amount * $item['quantity'];
			}
		}
		return $amount;
	}

	/**
	 * @param $code
	 *
	 * @return bool|int
	 */
	public function get_discount_id_by_code($code) {
		$discount = $this->get_discount_by_code($code);
		if ($discount) {
			return $discount->ID;
		}
		return false;
	}

	/**
	 * @param string $code
	 *
	 * @return array|bool|null|\WP_Post
	 */
	public function get_discount_by_code($code = '') {
		if (empty($code) || !is_string($code)) {
			return false;
		}
		return $this->get_discount_by('code', $code);
	}

	/**
	 * @param string $field
	 * @param string $value
	 *
	 * @return array|bool|null|\WP_Post
	 */
	public function get_discount_by($field = '', $value = '') {
		if (empty($field) || empty($value)) {
			return false;
		}
		if (!is_string($field)) {
			return false;
		}
		switch (strtolower($field)) {
			case 'code':
				$discount = $this->get_discounts(array(
					'meta_key' => '_mprm_discount_code',
					'meta_value' => $value,
					'posts_per_page' => 1,
					'post_status' => 'any'
				));
				if ($discount) {
					$discount = $discount[0];
				}
				break;
			case 'id':
				$discount = $this->get_discount($value);
				break;
			case 'name':
				$discount = get_posts(array(
					'post_type' => 'mprm_discount',
					'name' => $value,
					'posts_per_page' => 1,
					'post_status' => 'any'
				));
				if ($discount) {
					$discount = $discount[0];
				}
				break;
			default:
				return false;
		}
		if (!empty($discount)) {
			return $discount;
		}
		return false;
	}

	/**
	 * @param array $args
	 *
	 * @return array|bool
	 */
	public function get_discounts($args = array()) {
		$defaults = array(
			'post_type' => 'mprm_discount',
			'posts_per_page' => 30,
			'paged' => null,
			'post_status' => array('active', 'inactive', 'expired')
		);
		$args = wp_parse_args($args, $defaults);
		$discounts = get_posts($args);
		if ($discounts) {
			return $discounts;
		}
		if (!$discounts && !empty($args['s'])) {
			// If no discounts are found and we are searching, re-query with a meta key to find discounts by code
			$args['meta_key'] = '_mprm_discount_code';
			$args['meta_value'] = $args['s'];
			$args['meta_compare'] = 'LIKE';
			unset($args['s']);
			$discounts = get_posts($args);
		}
		if ($discounts) {
			return $discounts;
		}
		return false;
	}

	/**
	 * @param int $discount_id
	 *
	 * @return array|bool|null|\WP_Post
	 */
	public function get_discount($discount_id = 0) {
		if (empty($discount_id)) {
			return false;
		}
		$discount = get_post($discount_id);
		if (get_post_type($discount_id) != 'mprm_discount') {
			return false;
		}
		return $discount;
	}

	/**
	 * @param null $code_id
	 *
	 * @return array
	 */
	public function get_discount_product_reqs($code_id = null) {
		$product_reqs = get_post_meta($code_id, '_mprm_discount_product_reqs', true);
		if (empty($product_reqs) || !is_array($product_reqs)) {
			$product_reqs = array();
		}
		return (array)apply_filters('mprm_get_discount_product_reqs', $product_reqs, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return array
	 */
	public function get_discount_excluded_products($code_id = null) {
		$excluded_products = get_post_meta($code_id, '_mprm_discount_excluded_products', true);
		if (empty($excluded_products) || !is_array($excluded_products)) {
			$excluded_products = array();
		}
		return (array)apply_filters('mprm_get_discount_excluded_products', $excluded_products, $code_id);
	}

	/**
	 * @param int $code_id
	 *
	 * @return bool
	 */
	public function is_discount_not_global($code_id = 0) {
		return (bool)get_post_meta($code_id, '_mprm_discount_is_not_global', true);
	}

	/**
	 * @param $code
	 * @param $base_price
	 *
	 * @return mixed
	 */
	public function get_discounted_amount($code, $base_price) {
		$discount_id = $this->get_discount_id_by_code($code);
		if ($discount_id) {
			$type = $this->get_discount_type($discount_id);
			$rate = $this->get_discount_amount($discount_id);
			if ($type == 'flat') {
				// Set amount
				$amount = $base_price - $rate;
				if ($amount < 0) {
					$amount = 0;
				}
			} else {
				// Percentage discount
				$amount = $base_price - ($base_price * ($rate / 100));
			}
		} else {
			$amount = $base_price;
		}
		return apply_filters('mprm_discounted_amount', $amount);
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function get_discount_type($code_id = null) {
		$type = strtolower(get_post_meta($code_id, '_mprm_discount_type', true));
		return apply_filters('mprm_get_discount_type', $type, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return float
	 */
	public function get_discount_amount($code_id = null) {
		$amount = get_post_meta($code_id, '_mprm_discount_amount', true);
		return (float)apply_filters('mprm_get_discount_amount', $amount, $code_id);
	}

	public function unset_all_cart_discounts() {
		$this->get('session')->set('cart_discounts', null);
	}

	/**
	 * @param $code
	 *
	 * @return bool|int
	 */
	public function decrease_discount_usage($code) {
		$id = $this->get_discount_id_by_code($code);
		if (false === $id) {
			return false;
		}
		$uses = $this->get_discount_uses($id);
		if ($uses) {
			$uses--;
		}
		if ($uses < 0) {
			$uses = 0;
		}
		update_post_meta($id, '_mprm_discount_uses', $uses);
		do_action('mprm_discount_decrease_use_count', $uses, $id, $code);
		return $uses;
	}

	/**
	 * @param null $code_id
	 *
	 * @return int
	 */
	public function get_discount_uses($code_id = null) {
		$uses = get_post_meta($code_id, '_mprm_discount_uses', true);
		return (int)apply_filters('mprm_get_discount_uses', $uses, $code_id);
	}

	/**
	 * @param $code
	 *
	 * @return bool|int
	 */
	public function increase_discount_usage($code) {
		$id = $this->get_discount_id_by_code($code);
		if (false === $id) {
			return false;
		}
		$uses = $this->get_discount_uses($id);
		if ($uses) {
			$uses++;
		}
		if ($uses < 0) {
			$uses = 0;
		}
		update_post_meta($id, '_mprm_discount_uses', $uses);
		do_action('mprm_discount_increase_use_count', $uses, $id, $code);
		return $uses;
	}

	/**
	 * @param string $code
	 * @param string $user
	 * @param bool $set_error
	 *
	 * @return mixed
	 */
	public function is_discount_valid($code = '', $user = '', $set_error = true) {

		$return = false;
		$discount_id = $this->get_discount_id_by_code($code);
		$user = trim($user);
		if ($this->get('cart')->get_cart_contents()) {
			if ($discount_id) {
				if (
					$this->is_discount_active($discount_id) &&
					$this->is_discount_started($discount_id) &&
					!$this->is_discount_maxed_out($discount_id) &&
					!$this->is_discount_used($code, $user, $discount_id) &&
					$this->discount_is_min_met($discount_id) &&
					$this->discount_product_reqs_met($discount_id)
				) {
					$return = true;
				}
			} elseif ($set_error) {
				$this->get('errors')->set_error('mprm-discount-error', __('This discount is invalid.', 'mp-restaurant-menu'));
			}
		}
		return apply_filters('mprm_is_discount_valid', $return, $discount_id, $code, $user);
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function is_discount_active($code_id = null) {
		$discount = $this->get_discount($code_id);
		$return = false;
		if ($discount) {
			if ($this->is_discount_expired($code_id)) {
				if (defined('DOING_AJAX')) {
					$this->get('errors')->set_error('mprm-discount-error', __('This discount is expired.', 'mp-restaurant-menu'));
				}
			} elseif ($discount->post_status == 'active') {
				$return = true;
			} else {
				if (defined('DOING_AJAX')) {
					$this->get('errors')->set_error('mprm-discount-error', __('This discount is not active.', 'mp-restaurant-menu'));
				}
			}
		}
		return apply_filters('mprm_is_discount_active', $return, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function is_discount_expired($code_id = null) {
		$discount = $this->get_discount($code_id);
		$return = false;
		if ($discount) {
			$expiration = $this->get_discount_expiration($code_id);
			if ($expiration) {
				$expiration = strtotime($expiration);
				if ($expiration < current_time('timestamp')) {
					// Discount is expired
					$this->update_discount_status($code_id, 'inactive');
					$return = true;
				}
			}
		}
		return apply_filters('mprm_is_discount_expired', $return, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function get_discount_expiration($code_id = null) {
		$expiration = get_post_meta($code_id, '_mprm_discount_expiration', true);
		return apply_filters('mprm_get_discount_expiration', $expiration, $code_id);
	}

	/**
	 * @param int $code_id
	 * @param string $new_status
	 *
	 * @return bool
	 */
	public function update_discount_status($code_id = 0, $new_status = 'active') {
		$discount = $this->get_discount($code_id);
		if ($discount) {
			do_action('mprm_pre_update_discount_status', $code_id, $new_status, $discount->post_status);
			wp_update_post(array('ID' => $code_id, 'post_status' => $new_status));
			do_action('mprm_post_update_discount_status', $code_id, $new_status, $discount->post_status);
			return true;
		}
		return false;
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function is_discount_started($code_id = null) {
		$discount = $this->get_discount($code_id);
		$return = false;
		if ($discount) {
			$start_date = $this->get_discount_start_date($code_id);
			if ($start_date) {
				$start_date = strtotime($start_date);
				if ($start_date < current_time('timestamp')) {
					// Discount has pased the start date
					$return = true;
				} else {
					$this->get('errors')->set_error('mprm-discount-error', __('This discount is not active yet.', 'mp-restaurant-menu'));
				}
			} else {
				// No start date for this discount, so has to be true
				$return = true;
			}
		}
		return apply_filters('mprm_is_discount_started', $return, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function get_discount_start_date($code_id = null) {
		$start_date = get_post_meta($code_id, '_mprm_discount_start', true);
		return apply_filters('mprm_get_discount_start_date', $start_date, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function is_discount_maxed_out($code_id = null) {
		$discount = $this->get_discount($code_id);
		$return = false;
		if ($discount) {
			$uses = $this->get_discount_uses($code_id);
			// Large number that will never be reached
			$max_uses = $this->get_discount_max_uses($code_id);
			// Should never be greater than, but just in case
			if ($uses >= $max_uses && !empty($max_uses)) {
				// Discount is maxed out
				$this->get('errors')->set_error('mprm-discount-error', __('This discount has reached its maximum usage.', 'mp-restaurant-menu'));
				$return = true;
			}
		}
		return apply_filters('mprm_is_discount_maxed_out', $return, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return int
	 */
	public function get_discount_max_uses($code_id = null) {
		$max_uses = get_post_meta($code_id, '_mprm_discount_max_uses', true);
		return (int)apply_filters('mprm_get_discount_max_uses', $max_uses, $code_id);
	}

	/**
	 * @param null $code
	 * @param string $user
	 * @param int $code_id
	 *
	 * @return bool|mixed
	 */
	public function is_discount_used($code = null, $user = '', $code_id = 0) {
		$return = false;
		if (empty($code_id)) {
			$code_id = $this->get_discount_id_by_code($code);
			if (empty($code_id)) {
				return false; // No discount was found
			}
		}
		if ($this->discount_is_single_use($code_id)) {
			$payments = array();
			$user_found = false;
			if (is_email($user)) {
				$user_found = true; // All we need is the email
				$key = '_mprm_order_user_email';
				$value = $user;
			} else {
				$user_data = get_user_by('login', $user);
				if ($user_data) {
					$user_found = true;
					$key = '_mprm_order_user_id';
					$value = $user_data->ID;
				}
			}

			if ($user_found) {
				$query_args = array(
					'post_type' => 'mprm_order',
					'meta_query' => array(
						array(
							'key' => $key,
							'value' => $value,
							'compare' => '='
						)
					),
					'fields' => 'ids'
				);
				$payments = get_posts($query_args); // Get all payments with matching email
			}
			if ($payments) {
				foreach ($payments as $payment) {
					$payment = new Menu_item($payment);
					if (empty($payment->discounts)) {
						continue;
					}
					if (in_array($payment->status, array('abandoned', 'mprm-failed'))) {
						continue;
					}
					$discounts = explode(',', $payment->discounts);
					if (is_array($discounts)) {
						if (in_array(strtolower($code), $discounts)) {
							$this->get('errors')->set_error('mprm-discount-error', __('This discount has already been redeemed.', 'mp-restaurant-menu'));
							$return = true;
							break;
						}
					}
				}
			}
		}
		return apply_filters('mprm_is_discount_used', $return, $code, $user);
	}

	/**
	 * @param int $code_id
	 *
	 * @return bool
	 */
	public function discount_is_single_use($code_id = 0) {
		$single_use = get_post_meta($code_id, '_mprm_discount_is_single_use', true);
		return (bool)apply_filters('mprm_is_discount_single_use', $single_use, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return mixed
	 */
	public function discount_is_min_met($code_id = null) {
		$discount = $this->get_discount($code_id);
		$return = false;
		if ($discount) {
			$min = $this->get_discount_min_price($code_id);
			$cart_amount = $this->get_cart_discountable_subtotal($code_id);
			if ((float)$cart_amount >= (float)$min) {
				// Minimum has been met
				$return = true;
			} else {
				$this->get('errors')->set_error('mprm-discount-error', sprintf(__('Minimum order of %s not met.', 'mp-restaurant-menu'), $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($min))));
			}
		}
		return apply_filters('mprm_is_discount_min_met', $return, $code_id);
	}

	/**
	 * @param null $code_id
	 *
	 * @return float
	 */
	public function get_discount_min_price($code_id = null) {
		$min_price = get_post_meta($code_id, '_mprm_discount_min_price', true);
		return (float)apply_filters('mprm_get_discount_min_price', $min_price, $code_id);
	}

	/**
	 * @param $code_id
	 *
	 * @return mixed
	 */
	public function get_cart_discountable_subtotal($code_id) {
		$cart_items = $this->get('cart')->get_cart_content_details();
		$items = array();
		$excluded_products = $this->get_discount_excluded_products($code_id);
		if ($cart_items) {
			foreach ($cart_items as $item) {
				if (!in_array($item['id'], $excluded_products)) {
					$items[] = $item;
				}
			}
		}
		$subtotal = $this->get('cart')->get_cart_items_subtotal($items);
		return apply_filters('mprm_get_cart_discountable_subtotal', $subtotal);
	}

	/**
	 * @param null $code_id
	 *
	 * @return bool
	 */
	public function discount_product_reqs_met($code_id = null) {
		$product_reqs = $this->get_discount_product_reqs($code_id);
		$condition = $this->get_discount_product_condition($code_id);
		$excluded_ps = $this->get_discount_excluded_products($code_id);
		$cart_items = $this->get('cart')->get_cart_contents();
		$cart_ids = $cart_items ? wp_list_pluck($cart_items, 'id') : null;
		$ret = false;
		if (empty($product_reqs) && empty($excluded_ps)) {
			$ret = true;
		}
		// Normalize our data for product requiremetns, exlusions and cart data
		// First absint the items, then sort, and reset the array keys
		$product_reqs = array_map('absint', $product_reqs);
		asort($product_reqs);
		$product_reqs = array_values($product_reqs);
		$excluded_ps = array_map('absint', $excluded_ps);
		asort($excluded_ps);
		$excluded_ps = array_values($excluded_ps);
		$cart_ids = array_map('absint', $cart_ids);
		asort($cart_ids);
		$cart_ids = array_values($cart_ids);
		// Ensure we have requirements before proceeding
		if (!$ret && !empty($product_reqs)) {
			switch ($condition) {
				case 'all' :
					// Default back to true
					$ret = true;
					foreach ($product_reqs as $menu_item_id) {
						if (!$this->get('cart')->item_in_cart($menu_item_id)) {
							$this->get('errors')->set_error('mprm-discount-error', __('The product requirements for this discount are not met.', 'mp-restaurant-menu'));
							$ret = false;
							break;
						}
					}
					break;
				default : // Any
					foreach ($product_reqs as $menu_item_id) {
						if ($this->get('cart')->item_in_cart($menu_item_id)) {
							$ret = true;
							break;
						}
					}
					if (!$ret) {
						$this->get('errors')->set_error('mprm-discount-error', __('The product requirements for this discount are not met.', 'mp-restaurant-menu'));
					}
					break;
			}
		} else {
			$ret = true;
		}
		if (!empty($excluded_ps)) {
			// Check that there are products other than excluded ones in the cart
			if ($cart_ids == $excluded_ps) {
				$this->get('errors')->set_error('mprm-discount-error', __('This discount is not valid for the cart contents.', 'mp-restaurant-menu'));
				$ret = false;
			}
		}
		return (bool)apply_filters('mprm_is_discount_products_req_met', $ret, $code_id, $condition);
	}

	/**
	 * @param int $code_id
	 *
	 * @return mixed
	 */
	public function get_discount_product_condition($code_id = 0) {
		return get_post_meta($code_id, '_mprm_discount_product_condition', true);
	}

	/**
	 * @param string $code
	 *
	 * @return array|bool
	 */
	public function set_cart_discount($code = '') {
		if ($this->multiple_discounts_allowed()) {
			// Get all active cart discounts
			$discounts = $this->get('cart')->get_cart_discounts();
		} else {
			$discounts = false;
		}
		if ($discounts) {
			$key = array_search(strtolower($code), array_map('strtolower', $discounts));
			if (false !== $key) {
				unset($discounts[$key]);
			}
			$discounts[] = $code;
		} else {
			$discounts = array();
			$discounts[] = $code;
		}
		$this->get('session')->set('cart_discounts', implode('|', $discounts));
		return $discounts;
	}

	/**
	 * @return bool
	 */
	public function multiple_discounts_allowed() {
		$ret = $this->get('settings')->get_option('allow_multiple_discounts', false);
		return (bool)apply_filters('mprm_multiple_discounts_allowed', $ret);
	}

	public function init_action() {
		add_action('mprm_discount_decrease_use_count', 'mprm_discount_decrease_use_count', 10, 3);
		add_action('mprm_discount_increase_use_count', 'mprm_discount_increase_use_count', 10, 3);
		add_action('mprm_pre_update_discount_status', 'mprm_pre_update_discount_status', 10, 3);
		add_action('mprm_post_update_discount_status', 'mprm_post_update_discount_status', 10, 3);
	}
}