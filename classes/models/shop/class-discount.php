<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Discount extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function get_discount_excluded_products($code_id = null) {
		$excluded_products = get_post_meta($code_id, '_mprm_discount_excluded_products', true);

		if (empty($excluded_products) || !is_array($excluded_products)) {
			$excluded_products = array();
		}

		return (array)apply_filters('mprm_get_discount_excluded_products', $excluded_products, $code_id);
	}

	function is_discount_not_global($code_id = 0) {
		return (bool)get_post_meta($code_id, '_mprm_discount_is_not_global', true);
	}

	function get_cart_item_discount_amount($item = array()) {
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

					foreach ($reqs as $download_id) {

						if ($download_id == $item['id'] && !in_array($item['id'], $excluded_products)) {
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

	function get_discount_product_reqs($code_id = null) {
		$product_reqs = get_post_meta($code_id, '_mprm_discount_product_reqs', true);

		if (empty($product_reqs) || !is_array($product_reqs)) {
			$product_reqs = array();
		}

		return (array)apply_filters('mprm_get_discount_product_reqs', $product_reqs, $code_id);
	}

	function get_discounted_amount($code, $base_price) {
		$amount = $base_price;
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

	function get_discount_amount($code_id = null) {
		$amount = get_post_meta($code_id, '_mprm_discount_amount', true);

		return (float)apply_filters('mprm_get_discount_amount', $amount, $code_id);
	}

	function get_discount_type($code_id = null) {
		$type = strtolower(get_post_meta($code_id, '_mprm_discount_type', true));

		return apply_filters('mprm_get_discount_type', $type, $code_id);
	}

	function get_discount_id_by_code($code) {
		$discount = $this->get_discount_by_code($code);
		if ($discount) {
			return $discount->ID;
		}
		return false;
	}

}