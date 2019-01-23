<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Cart
 * @package mp_restaurant_menu\classes\models
 */
class Cart extends Model {

	protected static $instance;

	/**
	 * @return Cart
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return mixed
	 */
	public function get_cart_quantity() {
		$total_quantity = 0;
		$cart = $this->get_cart_contents();
		if (!empty($cart)) {
			$quantities = wp_list_pluck($cart, 'quantity');
			$total_quantity = absint(array_sum($quantities));
		}

		return apply_filters('mprm_get_cart_quantity', $total_quantity, $cart);
	}

	/**
	 * @return mixed
	 */
	public function get_cart_contents() {
		$cart = $this->get('session')->get_session_by_key('mprm_cart');
		$cart = !empty($cart) ? array_values($cart) : array();
		return apply_filters('mprm_cart_contents', $cart);
	}

	/**
	 * Add item to cart
	 *
	 * @param $item_id
	 * @param array $options
	 *
	 * @return bool|int
	 */
	public function add_to_cart($item_id, $options = array()) {
		$menu_item = get_post($item_id);
		if (!$this->get('menu_item')->is_menu_item($menu_item)) {
			return false;
		}
		if (!current_user_can('edit_post', $menu_item->ID) && $menu_item->post_status != 'publish') {
			return false;
		}
		do_action('mprm_pre_add_to_cart', $item_id, $options);

		$cart = apply_filters('mprm_pre_add_to_cart_contents', $this->get_cart_contents());

		if ($this->get('menu_item')->has_variable_prices($item_id) && !isset($options['price_id'])) {
			// Forces to the first price ID if none is specified and menu_item has variable prices
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

		// If the price IDs are a string and is a coma separated list, make it an array (allows custom add to cart URLs)
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

		if (!empty($items)) {
			foreach ($items as $item) {

				$to_add = apply_filters('mprm_add_to_cart_item', $item);

				if (!is_array($to_add)) {
					return false;
				}

				if (!isset($to_add['id']) || empty($to_add['id'])) {
					return false;
				}
				$position_key_cart = $this->get_item_position_in_cart($to_add['id'], $to_add['options']);

				if (is_numeric($position_key_cart) && $this->item_quantities_enabled() && apply_filters('mprm_item_quantities_cart', TRUE)) {

					if (is_array($quantity)) {
						$cart[$position_key_cart]['quantity'] += $quantity[$position_key_cart];
					} else {
						$cart[$position_key_cart]['quantity'] += $quantity;
					}

				} else {
					$cart[] = $to_add;
				}
			}
		}

		$this->get('session')->set('mprm_cart', $cart);

		do_action('mprm_post_add_to_cart', $item_id, $options);

		// Clear all errors
		$this->get('errors')->clear_errors();
		return count($cart) - 1;
	}

	/**
	 * Position in cart
	 *
	 * @param int $item_id
	 * @param array $options
	 *
	 * @return bool|int|string
	 */
	public function get_item_position_in_cart($item_id = 0, $options = array()) {
		$position = false;
		$cart_items = $this->get_cart_contents();

		if (!is_array($cart_items)) {
			return false;
		} else {

			foreach ($cart_items as $cart_position => $item) {
				
				if (($item['id'] == $item_id) && apply_filters('mprm_in_cart_position', $item_id, $item)) {
					if (isset($options['price_id']) && isset($item['options']['price_id'])) {
						if ((int)$options['price_id'] == (int)$item['options']['price_id']) {
							$position = $cart_position;
						}
					} else {
						$position = $cart_position;
					}
				}
			}
			
		}

		return $position;
	}

	/**
	 * is Quantities enabled
	 * @return bool
	 */
	public function item_quantities_enabled() {
		$ret = $this->get('settings')->get_option('item_quantities', false);
		return (bool)apply_filters('mprm_item_quantities_enabled', $ret);
	}

	/**
	 * Item in cart
	 *
	 * @param $post_id
	 * @param $options
	 *
	 * @return bool
	 */
	public function item_in_cart($post_id, $options = array()) {
		$ret = is_numeric($this->get_item_position_in_cart($post_id, $options));
		return (bool)apply_filters('mprm_item_in_cart', $ret, $post_id, $options);
	}

	/**
	 *
	 * Remove from cart
	 *
	 * @param $cart_key
	 *
	 * @return bool|mixed
	 */
	public function remove_from_cart($cart_key) {
		$cart = $this->get_cart_contents();
		do_action('mprm_pre_remove_from_cart', $cart_key);
		if (!is_array($cart)) {
			return true; // Empty cart
		} else {
			$item_id = isset($cart[$cart_key]['id']) ? $cart[$cart_key]['id'] : null;
			unset($cart[$cart_key]);
		}
		$this->get('session')->set('mprm_cart', $cart);
		do_action('mprm_post_remove_from_cart', $cart_key, $item_id);
		// Clear all the checkout errors, if any
		$this->get('errors')->clear_errors();
		return $cart; // The updated cart items
	}

	/**
	 * Save cart (need change)
	 *
	 * @return bool
	 */
	public function save_cart() {
		if ($this->is_cart_saving_disabled())
			return false;
		$user_id = get_current_user_id();
		$cart = $this->get('session')->get_session_by_key('mprm_cart');
		$token = $this->generate_cart_token();
		if (is_user_logged_in()) {
			update_user_meta($user_id, 'mprm_saved_cart', $cart, false);
			update_user_meta($user_id, 'mprm_cart_token', $token, false);
		} else {
			$cart = json_encode($cart);
			setcookie('mprm_saved_cart', $cart, time() + 3600 * 24 * 7, COOKIEPATH, COOKIE_DOMAIN);
			setcookie('mprm_cart_token', $token, time() + 3600 * 24 * 7, COOKIEPATH, COOKIE_DOMAIN);
		}
		$messages = $this->get('session')->get_session_by_key('mprm_cart_messages');
		if (!$messages)
			$messages = array();
		$messages['mprm_cart_save_successful'] = sprintf(
			'<strong>%1$s</strong>: %2$s',
			__('Success', 'mp-restaurant-menu'),
			__('Cart saved successfully. You can restore your cart using this URL:', 'mp-restaurant-menu') . ' ' . '<a href="' . $this->get('checkout')->get_checkout_uri() . '?mprm_action=restore_cart&mprm_cart_token=' . $token . '">' . $this->get('checkout')->get_checkout_uri() . '?mprm_action=restore_cart&mprm_cart_token=' . $token . '</a>'
		);
		$this->get('session')->set('mprm_cart_messages', $messages);
		if ($cart) {
			return true;
		}
		return false;
	}

	/**
	 * is Cart saving disabled
	 * @return mixed
	 */
	public function is_cart_saving_disabled() {
		$ret = $this->get('settings')->get_option('enable_cart_saving', false);
		return apply_filters('mprm_cart_saving_disabled', !$ret);
	}

	/**
	 * Generate cart token
	 * @return mixed
	 */
	public function generate_cart_token() {
		return apply_filters('mprm_generate_cart_token', md5(mt_rand() . time()));
	}

	/**
	 *  Clear cart
	 */
	public function empty_cart() {
		$this->get('session')->set('mprm_cart', NULL);
		$this->get('session')->set('mprm_cart_fees', NULL);
		$this->get('discount')->unset_all_cart_discounts();
		do_action('mprm_empty_cart');
	}

	/**
	 * @param array $purchase_data
	 *
	 * @return mixed
	 */
	public function set_purchase_session($purchase_data = array()) {
		return $this->get('session')->set('mprm_purchase', $purchase_data);
	}

	/**
	 * @return mixed
	 */
	public function get_purchase_session() {
		return $this->get('session')->get_session_by_key('mprm_purchase');
	}

	/**
	 * Get cart token
	 *
	 * @return mixed
	 */
	public function get_cart_token() {
		$user_id = get_current_user_id();
		if (is_user_logged_in()) {
			$token = get_user_meta($user_id, 'mprm_cart_token', true);
		} else {
			$token = isset($_COOKIE['mprm_cart_token']) ? $_COOKIE['mprm_cart_token'] : false;
		}
		return apply_filters('mprm_get_cart_token', $token, $user_id);
	}

	/**
	 * Delete saved carts
	 */
	public function delete_saved_carts() {
		global $wpdb;
		$start = date('Y-m-d', strtotime('-7 days'));
		$carts = $wpdb->get_results("SELECT user_id, meta_key, FROM_UNIXTIME(meta_value, '%Y-%m-%d') AS date FROM {$wpdb->usermeta} HERE meta_key = 'mprm_cart_token' ", ARRAY_A);
		if ($carts) {
			foreach ($carts as $cart) {
				$user_id = $cart['user_id'];
				$meta_value = $cart['date'];
				if (strtotime($meta_value) < strtotime('-1 week')) {
					$wpdb->delete(
						$wpdb->usermeta,
						array(
							'user_id' => $user_id,
							'meta_key' => 'mprm_cart_token'
						)
					);
					$wpdb->delete(
						$wpdb->usermeta,
						array(
							'user_id' => $user_id,
							'meta_key' => 'mprm_saved_cart'
						)
					);
				}
			}
		}
	}

	/**
	 * Purchase link
	 *
	 * @return array|bool
	 */
	public function get_append_purchase_link() {
		global $post;
		$data = array(
			'ID' => $post->ID,
			'template' => 'default',
			'error' => false,
			'price' => Menu_item::get_instance()->get_price($post->ID),
			'direct' => false,
			'text' => __('Purchase', 'mp-restaurant-menu'),
			'style' => $this->get('settings')->get_option('mprm_button_style', 'button'),
			'color' => $this->get('settings')->get_option('mprm_checkout_color', 'inherit'),
			'padding' => $this->get('settings')->get_option('checkout_padding', 'mprm-inherit'),
			'class' => 'mprm-submit'
		);
		$purchase_page = $this->get('settings')->get_option('purchase_page', false);
		if (!$purchase_page || $purchase_page == 0) {
			$data['error'] = true;
			$data['error_message'] = $this->get('errors')->get_error('purchase_page');
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

	/**
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function cart_has_fees($type = 'all') {
		return $this->get('fees')->has_fees($type);
	}

	/**
	 * Cart item name
	 *
	 * @param array $item
	 *
	 * @return mixed
	 */
	public function get_cart_item_name($item = array()) {
		$item_title = get_the_title($item['id']);
		if (empty($item_title)) {
			$item_title = $item['id'];
		}
		if ($this->get('menu_item')->has_variable_prices($item['id']) && false !== $this->get_cart_item_price_id($item)) {
			$item_title .= ' - ' . $this->get_cart_item_price_name($item);
		}
		return apply_filters('mprm_get_cart_item_name', $item_title, $item['id'], $item);
	}

	/**
	 * Get cart item price id
	 *
	 * @param array $item
	 *
	 * @return null
	 */
	public function get_cart_item_price_id($item = array()) {
		if (isset($item['item_number'])) {
			$price_id = isset($item['item_number']['options']['price_id']) ? $item['item_number']['options']['price_id'] : null;
		} else {
			$price_id = isset($item['options']['price_id']) ? $item['options']['price_id'] : null;
		}
		return $price_id;
	}


	/**
	 * Get cart item price name
	 *
	 * @param array $item
	 *
	 * @return mixed
	 */
	public function get_cart_item_price_name($item = array()) {
		$price_id = (int)$this->get_cart_item_price_id($item);
		$prices = $this->get('menu_item')->get_prices($item['id']);
		$name = !empty($prices[$price_id]) ? $prices[$price_id]['name'] : '';
		return apply_filters('mprm_get_cart_item_price_name', $name, $item['id'], $price_id, $item);
	}

	/**
	 * Cart item price
	 *
	 * @param int $item_id
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function cart_item_price($item_id = 0, $options = array()) {
		$price = $this->get_cart_item_price($item_id, $options);
		$label = '';
		$price_id = isset($options['price_id']) ? $options['price_id'] : false;
		if (!$this->get('menu_item')->is_free($item_id, $price_id) && !$this->get('taxes')->menu_item_is_tax_exclusive($item_id)) {

			if ($this->get('taxes')->prices_show_tax_on_checkout() && !$this->get('taxes')->prices_include_tax()) {
				$price += $this->get_cart_item_tax($item_id, $options, $price);
			}
			if (!$this->get('taxes')->prices_show_tax_on_checkout() && $this->get('taxes')->prices_include_tax()) {
				$price -= $this->get_cart_item_tax($item_id, $options, $price);
			}
			if ($this->get('taxes')->display_tax_rate()) {
				$label = '&nbsp;&ndash;&nbsp;';
				if ($this->get('taxes')->prices_show_tax_on_checkout()) {
					$label .= sprintf(__('includes %s tax', 'mp-restaurant-menu'), $this->get('taxes')->get_formatted_tax_rate());
				} else {
					$label .= sprintf(__('excludes %s tax', 'mp-restaurant-menu'), $this->get('taxes')->get_formatted_tax_rate());
				}
				$label = apply_filters('mprm_cart_item_tax_description', $label, $item_id, $options);
			}
		}
		if (!empty($price) && $price_id === false) {
			if ($this->get('taxes')->prices_show_tax_on_checkout() && !$this->get('taxes')->prices_include_tax()) {
				$price += $this->get_cart_item_tax($item_id, $options, $price);
			}
			if (!$this->get('taxes')->prices_show_tax_on_checkout() && $this->get('taxes')->prices_include_tax()) {
				$price -= $this->get_cart_item_tax($item_id, $options, $price);
			}
			if ($this->get('taxes')->display_tax_rate()) {
				$label = '&nbsp;&ndash;&nbsp;';
				if ($this->get('taxes')->prices_show_tax_on_checkout()) {
					$label .= sprintf(__('includes %s tax', 'mp-restaurant-menu'), $this->get('taxes')->get_formatted_tax_rate());
				} else {
					$label .= sprintf(__('excludes %s tax', 'mp-restaurant-menu'), $this->get('taxes')->get_formatted_tax_rate());
				}
				$label = apply_filters('mprm_cart_item_tax_description', $label, $item_id, $options);
			}
		}
		$price = $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($price));
		return apply_filters('mprm_cart_item_price_label', $price . $label, $item_id, $options);
	}

	/**
	 * Get cart item price
	 *
	 * @param int $menu_item_id
	 * @param array $options
	 * @param bool $remove_tax_from_inclusive
	 *
	 * @return mixed
	 */
	public function get_cart_item_price($menu_item_id = 0, $options = array(), $remove_tax_from_inclusive = false) {
		$price = 0;
		$variable_prices = $this->get('menu_item')->has_variable_prices($menu_item_id);
		if ($variable_prices) {
			$prices = $this->get('menu_item')->get_prices($menu_item_id);
			if ($prices) {
				if (!empty($options)) {
					$price = isset($prices[$options['price_id']]) ? $prices[$options['price_id']]['amount'] : false;
				} else {
					$price = false;
				}
			}
		}
		if (!$variable_prices || false === $price) {
			// Get the standard Menu item price if not using variable prices
			$price = $this->get('menu_item')->get_price($menu_item_id);
		}
		if ($remove_tax_from_inclusive && $this->get('taxes')->prices_include_tax()) {
			$price -= $this->get_cart_item_tax($menu_item_id, $options, $price);
		}
		return apply_filters('mprm_cart_item_price', $price, $menu_item_id, $options);
	}

	/**
	 * Get cart item tax
	 *
	 * @param int $menu_item_id
	 * @param array $options
	 * @param string $subtotal
	 *
	 * @return mixed
	 */
	public function get_cart_item_tax($menu_item_id = 0, $options = array(), $subtotal = '') {
		$tax = 0;
		if (!$this->get('taxes')->menu_item_is_tax_exclusive($menu_item_id)) {
			$country = !empty($_POST['billing_country']) ? $_POST['billing_country'] : false;
			$state = !empty($_POST['card_state']) ? $_POST['card_state'] : false;
			$tax = $this->get('taxes')->calculate_tax($subtotal, $country, $state);
		}
		return apply_filters('mprm_get_cart_item_tax', $tax, $menu_item_id, $options, $subtotal);
	}

	/**
	 * @param $cart_key
	 *
	 * @return mixed
	 */
	public function remove_item_url($cart_key) {
		if (defined('DOING_AJAX')) {
			$current_page = $this->get('checkout')->get_checkout_uri();
		} else {
			$current_page = $this->get('misc')->get_current_page_url();
		}
		$remove_url = $this->get('misc')->add_cache_busting(add_query_arg(array('cart_item' => $cart_key, 'mprm_action' => 'remove', 'controller' => 'cart'), $current_page));
		return apply_filters('mprm_remove_item_url', $remove_url);
	}

	/**
	 * @param string $fee_id
	 *
	 * @return mixed
	 */
	public function remove_cart_fee_url($fee_id = '') {

		if (defined('DOING_AJAX')) {
			$current_page = $this->get('checkout')->get_checkout_uri();
		} else {
			$current_page = $this->get('misc')->get_current_page_url();
		}
		$remove_url = add_query_arg(array('fee' => $fee_id, 'mprm_action' => 'remove_fee', 'nocache' => 'true'), $current_page);

		return apply_filters('mprm_remove_fee_url', $remove_url);
	}

	/**
	 * Checkout cart columns
	 * @return mixed
	 */
	public function checkout_cart_columns() {
		$head_first = did_action('mprm_checkout_table_header_first');
		$head_last = did_action('mprm_checkout_table_header_last');
		$default = 3;
		return apply_filters('mprm_checkout_cart_columns', $head_first + $head_last + $default);
	}

	/**
	 * Get cart subtotal
	 * @return mixed
	 */
	public function cart_subtotal() {
		$price = $this->get('menu_item')->currency_filter(mprm_format_amount($this->get_cart_subtotal()));
		return $price;
	}

	/**
	 * Get cart total
	 *
	 * @return mixed
	 */
	public function get_cart_subtotal() {
		$items = $this->get_cart_content_details();
		$subtotal = $this->get_cart_items_subtotal($items);
		return apply_filters('mprm_get_cart_subtotal', $subtotal);
	}

	/**
	 * Cart content details
	 *
	 * @return array|bool
	 */
	public function get_cart_content_details() {
		global $mprm_is_last_cart_item, $mprm_flat_discount_total;

		$cart_items = $this->get_cart_contents();

		if (empty($cart_items)) {
			return false;
		}
		$details = array();
		$length = count($cart_items) - 1;

		foreach ($cart_items as $key => $item) {

			if ($key >= $length) {
				$mprm_is_last_cart_item = true;
			}

			$item['quantity'] = $this->item_quantities_enabled() ? absint($item['quantity']) : 1;
			$item_price = $this->get_cart_item_price($item['id'], $item['options']);

			$discount = $this->get('discount')->get_cart_item_discount_amount($item);
			$discount = apply_filters('mprm_get_cart_content_details_item_discount_amount', $discount, $item);

			$quantity = $this->get_cart_item_quantity($item['id'], $item['options'], $key);
			$fees = $this->get_cart_fees('fee', $item['id']);

			$subtotal = $item_price * $quantity;

			$tax = $this->get_cart_item_tax($item['id'], $item['options'], $subtotal - $discount);

			if ($this->get('taxes')->prices_include_tax()) {
				$subtotal -= round($tax, $this->get('formatting')->currency_decimal_filter());
			}

			$total = $subtotal - $discount + $tax;
			// Do not allow totals to go negative
			if ($total < 0) {
				$total = 0;
			}
			$details[$key] = array(
				'name' => get_the_title($item['id']),
				'id' => $item['id'],
				'item_number' => $item,
				'item_price' => round($item_price, $this->get('formatting')->currency_decimal_filter()),
				'quantity' => $quantity,
				'discount' => round($discount, $this->get('formatting')->currency_decimal_filter()),
				'subtotal' => round($subtotal, $this->get('formatting')->currency_decimal_filter()),
				'tax' => round($tax, $this->get('formatting')->currency_decimal_filter()),
				'fees' => $fees,
				'price' => round($total, $this->get('formatting')->currency_decimal_filter())
			);
			if ($mprm_is_last_cart_item) {
				$mprm_is_last_cart_item = false;
				$mprm_flat_discount_total = 0.00;
			}
		}
		return apply_filters('mprm_cart_content_details', $details);
	}

	/**
	 * Cart item quantity
	 *
	 * @param int $menu_item_id
	 * @param array $options
	 * @param int /null $position
	 *
	 * @return mixed
	 */
	public function get_cart_item_quantity($menu_item_id = 0, $options = array(), $position = NULL) {
		$cart = $this->get_cart_contents();
		if (is_null($position)) {
			$key = $this->get_item_position_in_cart($menu_item_id, $options);
		} else {
			$key = $position;
		}
		$quantity = isset($cart[$key]['quantity']) && $this->item_quantities_enabled() ? $cart[$key]['quantity'] : 1;
		if ($quantity < 1)
			$quantity = 1;
		return apply_filters('mprm_get_cart_item_quantity', $quantity, $menu_item_id, $options);
	}

	/**
	 * Cart fees
	 *
	 * @param string $type
	 * @param int $menu_item_id
	 *
	 * @return mixed
	 */
	public function get_cart_fees($type = 'all', $menu_item_id = 0) {
		return $this->get('fees')->get_fees($type, $menu_item_id);
	}

	/**
	 * Cart items subtotal
	 *
	 * @param $items
	 *
	 * @return mixed
	 */
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

	/**
	 * Cart has discounts
	 *
	 * @return mixed
	 */
	public function cart_has_discounts() {
		$ret = false;
		if ($this->get_cart_discounts()) {
			$ret = true;
		}
		return apply_filters('mprm_cart_has_discounts', $ret);
	}

	/**
	 * Cart Discounts
	 *
	 * @return array|bool
	 */
	public function get_cart_discounts() {
		$discounts = $this->get('session')->get_session_by_key('cart_discounts');
		$discounts = !empty($discounts) ? explode('|', $discounts) : false;
		return $discounts;
	}

	/**
	 * Cart total
	 *
	 * @param bool $echo
	 *
	 * @return mixed
	 */
	public function cart_total($echo = true) {
		$total = apply_filters('mprm_cart_total', $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($this->get_cart_total(), true)));
		if (!$echo) {
			return $total;
		}
		echo $total;
	}

	/**
	 * Cart total
	 *
	 * @param bool $discounts
	 *
	 * @return float
	 */
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

	/**
	 * @param bool $discounts
	 *
	 * @return mixed
	 */
	public function get_cart_discounted_amount($discounts = false) {
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

	/**
	 * @return mixed
	 */
	public function get_cart_tax() {
		$cart_tax = 0;
		$items = $this->get_cart_content_details();
		if ($items) {
			$taxes = wp_list_pluck($items, 'tax');
			if (is_array($taxes)) {
				$cart_tax = array_sum($taxes);
			}
		}
		$cart_tax += $this->get_cart_fee_tax();
		return apply_filters('mprm_get_cart_tax', $this->get('formatting')->sanitize_amount($cart_tax));
	}

	/**
	 * @return mixed
	 */
	public function get_cart_fee_tax() {
		$tax = 0;
		$fees = $this->get_cart_fees();
		if ($fees) {
			foreach ($fees as $fee_id => $fee) {
				if (!empty($fee['no_tax'])) {
					continue;
				}
				// Fees must (at this time) be exclusive of tax
				add_filter('mprm_prices_include_tax', '__return_false');
				$tax += $this->get('taxes')->calculate_tax($fee['amount']);
				remove_filter('mprm_prices_include_tax', '__return_false');
			}
		}
		return apply_filters('mprm_get_cart_fee_tax', $tax);
	}

	/**
	 * @param bool $echo
	 *
	 * @return mixed
	 */
	public function cart_tax($echo = false) {
		$cart_tax = 0;
		if ($this->get('taxes')->is_cart_taxed()) {
			$cart_tax = $this->get_cart_tax();
			$cart_tax = $this->get('menu_item')->currency_filter($this->get('formatting')->format_amount($cart_tax));
		}
		$tax = apply_filters('mprm_cart_tax', $cart_tax);
		if (!$echo) {
			return $tax;
		}
		echo $tax;
	}

	/**
	 * @return bool
	 */
	public function is_cart_saved() {
		if ($this->is_cart_saving_disabled()) {
			return false;
		}
		if (is_user_logged_in()) {
			$saved_cart = get_user_meta(get_current_user_id(), 'mprm_saved_cart', true);
			// Check that a cart exists
			if (!$saved_cart)
				return false;
			// Check that the saved cart is not the same as the current cart
			if ($saved_cart === $this->get('session')->get_session_by_key('mprm_cart'))
				return false;
			return true;
		} else {
			// Check that a saved cart exists
			if (!isset($_COOKIE['mprm_saved_cart']))
				return false;
			// Check that the saved cart is not the same as the current cart
			if (json_decode(stripslashes($_COOKIE['mprm_saved_cart']), true) === $this->get('session')->get_session_by_key('mprm_cart'))
				return false;
			return true;
		}
	}

	/**
	 * @param $purchase_data
	 * @param bool $email
	 *
	 * @return mixed
	 */
	function get_purchase_summary($purchase_data, $email = true) {
		$summary = '';
		if ($email) {
			$summary .= $purchase_data['user_email'] . ' - ';
		}
		if (!empty($purchase_data['menu_items'])) {
			foreach ($purchase_data['menu_items'] as $menu_item) {
				$summary .= get_the_title($menu_item['id']) . ', ';
			}
			$summary = substr($summary, 0, -2);
		}
		return apply_filters('mprm_get_purchase_summary', $summary, $purchase_data, $email);
	}

	/**
	 * @param int $menu_item_id
	 * @param int $quantity
	 * @param array $options
	 * @param int /null $position
	 *
	 * @return mixed
	 */
	function set_cart_item_quantity($menu_item_id = 0, $quantity = 1, $options = array(), $position = NULL) {
		$cart = $this->get_cart_contents();

		if (is_null($position)) {
			$key = $this->get_item_position_in_cart($menu_item_id, $options);
		} else {
			$key = $position;
		}

		if ($quantity < 1) {
			$quantity = 1;
		}

		$cart[$key]['quantity'] = $quantity;
		$this->get('session')->set('mprm_cart', $cart);
		return $cart;
	}
}