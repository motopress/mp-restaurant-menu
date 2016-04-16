<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Cart extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public function get_cart_content_details() {
		global $edd_is_last_cart_item, $edd_flat_discount_total;

		$cart_items = $this->get_cart_contents();

		if (empty($cart_items)) {
			return false;
		}

		$details = array();
		$length = count($cart_items) - 1;

		foreach ($cart_items as $key => $item) {

			if ($key >= $length) {
				$edd_is_last_cart_item = true;
			}

			$item['quantity'] = $this->item_quantities_enabled() ? absint($item['quantity']) : 1;

			$item_price = edd_get_cart_item_price($item['id'], $item['options']);
			$discount = edd_get_cart_item_discount_amount($item);
			$discount = apply_filters('edd_get_cart_content_details_item_discount_amount', $discount, $item);
			$quantity = edd_get_cart_item_quantity($item['id'], $item['options']);
			$fees = edd_get_cart_fees('fee', $item['id']);
			$subtotal = $item_price * $quantity;
			$tax = edd_get_cart_item_tax($item['id'], $item['options'], $subtotal - $discount);

			if (edd_prices_include_tax()) {
				$subtotal -= round($tax, edd_currency_decimal_filter());
			}

			$total = $subtotal - $discount + $tax;

			// Do not allow totals to go negatve
			if ($total < 0) {
				$total = 0;
			}

			$details[$key] = array(
				'name' => get_the_title($item['id']),
				'id' => $item['id'],
				'item_number' => $item,
				'item_price' => round($item_price, edd_currency_decimal_filter()),
				'quantity' => $quantity,
				'discount' => round($discount, edd_currency_decimal_filter()),
				'subtotal' => round($subtotal, edd_currency_decimal_filter()),
				'tax' => round($tax, edd_currency_decimal_filter()),
				'fees' => $fees,
				'price' => round($total, edd_currency_decimal_filter())
			);

			if ($edd_is_last_cart_item) {

				$edd_is_last_cart_item = false;
				$edd_flat_discount_total = 0.00;
			}

		}

		return $details;
	}

	public function get_cart_quantity() {
	}


	public function add_to_cart($item_id, $options = array()) {
		$menu_item = get_post($item_id);
		if (!$this->get('menu_item')->is_menu_item($menu_item)) {
			return;
		}
		if (!current_user_can('edit_post', $menu_item->ID) && $menu_item->post_status != 'publish') {
			return; // Do not allow draft/pending to be purchased if can't edit. Fixes #1056
		}

		do_action('mprm_pre_add_to_cart', $item_id, $options);

		$cart = apply_filters('edd_pre_add_to_cart_contents', $this->get_cart_contents());

		if ($this->get('menu_item')->has_variable_prices($item_id) && !isset($options['price_id'])) {
			// Forces to the first price ID if none is specified and download has variable prices
			$options['price_id'] = '0';
		}

		if (isset($options['quantity'])) {
			if (is_array($options['quantity'])) {

				$quantity = array();
				foreach ($options['quantity'] as $q) {
					$quantity[] = absint(preg_replace('/[^0-9\.]/', '', $q));
				}

			} else {

				$quantity = absint(preg_replace('/[^0-9\.]/', '', $options['quantity']));

			}

			unset($options['quantity']);
		} else {
			$quantity = 1;
		}

		// If the price IDs are a string and is a coma separted list, make it an array (allows custom add to cart URLs)
		if (isset($options['price_id']) && !is_array($options['price_id']) && false !== strpos($options['price_id'], ',')) {
			$options['price_id'] = explode(',', $options['price_id']);
		}

		if (isset($options['price_id']) && is_array($options['price_id'])) {

			// Process multiple price options at once
			foreach ($options['price_id'] as $key => $price) {

				$items[] = array(
					'id' => $item_id,
					'options' => array(
						'price_id' => preg_replace('/[^0-9\.-]/', '', $price)
					),
					'quantity' => $quantity[$key],
				);

			}

		} else {

			// Sanitize price IDs
			foreach ($options as $key => $option) {

				if ('price_id' == $key) {
					$options[$key] = preg_replace('/[^0-9\.-]/', '', $option);
				}

			}

			// Add a single item
			$items[] = array(
				'id' => $item_id,
				'options' => $options,
				'quantity' => $quantity
			);
		}

		foreach ($items as $item) {
			$to_add = apply_filters('mprm_add_to_cart_item', $item);
			if (!is_array($to_add))
				return;

			if (!isset($to_add['id']) || empty($to_add['id']))
				return;

			if ($this->item_in_cart($to_add['id'], $to_add['options']) && $this->item_quantities_enabled()) {

				$key = $this->get_item_position_in_cart($to_add['id'], $to_add['options']);

				if (is_array($quantity)) {
					$cart[$key]['quantity'] += $quantity[$key];
				} else {
					$cart[$key]['quantity'] += $quantity;
				}


			} else {

				$cart[] = $to_add;

			}
		}

		$this->get('session')->set('mprm_cart', $cart);

		do_action('mprm_post_add_to_cart', $item_id, $options);

		// Clear all the checkout errors, if any
		$this->get('errors')->clear_errors();

		return count($cart) - 1;
	}

	public function item_quantities_enabled() {
		$ret = $this->get('settings')->get_option('item_quantities', false);
		return (bool)apply_filters('mprm_item_quantities_enabled', $ret);
	}

	public function get_item_position_in_cart($menu_item_id = 0, $options = array()) {
		$cart_items = $this->get_cart_contents();
		if (!is_array($cart_items)) {
			return false; // Empty cart
		} else {
			foreach ($cart_items as $position => $item) {
				if ($item['id'] == $menu_item_id) {
					if (isset($options['price_id']) && isset($item['options']['price_id'])) {
						if ((int)$options['price_id'] == (int)$item['options']['price_id']) {
							return $position;
						}
					} else {
						return $position;
					}
				}
			}
		}
		return false; // Not found
	}

	public function remove_from_cart($cart_key) {
	}

	public function check_item_in_cart() {

	}

	public function item_in_cart($post_id, $options) {
		$cart_items = $this->get_cart_contents();

		$ret = false;

		if (is_array($cart_items)) {
			foreach ($cart_items as $item) {
				if ($item['id'] == $post_id) {
					if (isset($options['price_id']) && isset($item['options']['price_id'])) {
						if ($options['price_id'] == $item['options']['price_id']) {
							$ret = true;
							break;
						}
					} else {
						$ret = true;
						break;
					}
				}
			}
		}

		return (bool)apply_filters('mprm_item_in_cart', $ret, $post_id, $options);
	}

	public function get_cart_contents() {
		$cart = $this->get('session')->get_session_by_key('mprm_cart');
		$cart = !empty($cart) ? array_values($cart) : array();

		return apply_filters('mprm_cart_contents', $cart);
	}

	public function save_cart() {

	}

	public function empty_cart() {
	}

	public function set_purchase_session($purchase_data = array()) {
	}

	public function get_purchase_session() {
	}

	public function get_cart_token() {

	}

	public function get_cart_items_subtotal($items) {
		$subtotal = 0.00;

		if (is_array($items) && !empty($items)) {

			$prices = wp_list_pluck($items, 'subtotal');

			if (is_array($prices)) {
				$subtotal = array_sum($prices);
			} else {
				$subtotal = 0.00;
			}

			if ($subtotal < 0) {
				$subtotal = 0.00;
			}

		}

		return apply_filters('mprm_get_cart_items_subtotal', $subtotal);
	}


	public function get_cart_subtotal() {
		$items = $this->get_cart_content_details();
		$subtotal = $this->get_cart_items_subtotal($items);

		return apply_filters('mprm_get_cart_subtotal', $subtotal);
	}

	public function delete_saved_carts() {

	}

	public function generate_cart_token() {
		return apply_filters('mprm_generate_cart_token', md5(mt_rand() . time()));
	}

	public function get_error($type) {
		switch ($type) {
			case 'purchase_page':

				break;
			default:
				break;
		}
	}

	public function get_append_purchase_link() {
		global $post;
		$data = array(
			'ID' => $post->ID,
			'template' => 'default',
			'error' => false,
			'price' => Menu_item::get_instance()->get_price($post->id),
			'direct' => false,
			'text' => __('Purchase', 'mp-restaurant-menu'),
			'style' => get_option('mprm_button_style', 'button'),
			'color' => get_option('mprm_checkout_color', 'blue'),
			'class' => 'mprm-submit'
		);
		$purchase_page = get_option('mprm_purchase_page', false);

		if (!$purchase_page || $purchase_page == 0) {
			$data['error'] = true;
			$data['error_message'] = $this->get_error('purchase_page');
			return false;
		}

		if (empty($post->ID)) {
			return false;
		}
		if ('publish' !== $post->post_status && !current_user_can('edit_product', $post->ID)) {
			return false;
		}
		return $data;

	}

	public function cart_has_fees($type = 'all') {
		return $this->get('fees')->has_fees($type);
	}

	public function get_cart_total($discounts = false) {
		$subtotal = (float)$this->get_cart_subtotal();
		$discounts = (float)$this->get_cart_discounted_amount();
		$cart_tax = (float)$this->get_cart_tax();
		$fees = (float)$this->get('fees')->total();
		$total = $subtotal - $discounts + $cart_tax + $fees;

		if ($total < 0)
			$total = 0.00;

		return (float)apply_filters('mprm_get_cart_total', $total);
	}

	function get_cart_discounted_amount($discounts = false) {

		$amount = 0.00;
		$items = $this->get_cart_content_details();

		if ($items) {

			$discounts = wp_list_pluck($items, 'discount');

			if (is_array($discounts)) {
				$discounts = array_map('floatval', $discounts);
				$amount = array_sum($discounts);
			}

		}

		return apply_filters('mprm_get_cart_discounted_amount', $amount);
	}

	function get_cart_tax() {

		$cart_tax = 0;
		$items = $this->get_cart_content_details();

		if ($items) {

			$taxes = wp_list_pluck($items, 'tax');

			if (is_array($taxes)) {
				$cart_tax = array_sum($taxes);
			}

		}

		$cart_tax += edd_get_cart_fee_tax();

		return apply_filters('edd_get_cart_tax', edd_sanitize_amount($cart_tax));
	}

	function is_cart_saving_disabled() {
		$ret = $this->get('settings')->get_option('enable_cart_saving', false);
		return apply_filters('mprm_cart_saving_disabled', !$ret);
	}

}
