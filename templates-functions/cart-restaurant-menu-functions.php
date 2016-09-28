<?php
use mp_restaurant_menu\classes\models;
use mp_restaurant_menu\classes\View as View;

/**
 * @return mixed|void
 */
function mprm_is_cart_saving_disabled() {
	return models\Cart::get_instance()->is_cart_saving_disabled();
}

/**
 * @return mixed|void
 */
function mprm_get_cart_quantity() {
	return models\Cart::get_instance()->get_cart_quantity();
}

/**
 * @return float
 */
function mprm_get_cart_total() {
	return models\Cart::get_instance()->get_cart_total();
}

/**
 * @return mixed|void
 */
function mprm_get_cart_tax() {
	return models\Cart::get_instance()->get_cart_tax();
}

/**
 * @return mixed|void
 */
function mprm_get_cart_subtotal() {
	return models\Cart::get_instance()->get_cart_subtotal();
}

/**
 * @param $cart_key
 * @param $item
 * @param bool $ajax
 *
 * @return mixed|void
 */
function mprm_get_cart_item_template($cart_key, $item, $ajax = false) {
	$id = is_array($item) ? $item['id'] : $item;
	$remove_url = mprm_remove_item_url($cart_key);
	$title = get_the_title($id);
	$options = !empty($item['options']) ? $item['options'] : array();
	$quantity = mprm_get_cart_item_quantity($id, $options, $cart_key);
	$price = mprm_get_cart_item_price($id, $options);

	if (!empty($options)) {
		$title .= (mprm_has_variable_prices($item['id'])) ? ' <span class="mprm-cart-item-separator">-</span> ' . mprm_get_price_name($id, $item['options']) : mprm_get_price_name($id, $item['options']);
	}

	$item = View::get_instance()->render_html('widgets/cart/cart-item', array('item' => $item, 'id' => $id), false);

	$item = str_replace('{item_title}', $title, $item);
	$item = str_replace('{item_amount}', mprm_currency_filter(mprm_format_amount($price)), $item);
	$item = str_replace('{cart_item_id}', absint($cart_key), $item);
	$item = str_replace('{item_id}', absint($id), $item);
	$item = str_replace('{item_quantity}', absint($quantity), $item);
	$item = str_replace('{remove_url}', $remove_url, $item);
	$subtotal = '';
	if ($ajax) {
		$subtotal = mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal()));
	}
	$item = str_replace('{subtotal}', $subtotal, $item);

	return apply_filters('mprm_cart_item', $item, $id);
}

/**
 * @param int $menu_item_id
 * @param array $options
 * @param int /null $options
 *
 * @return mixed|void
 */
function mprm_get_cart_item_quantity($menu_item_id = 0, $options = array(), $position = NULL) {
	return models\Cart::get_instance()->get_cart_item_quantity($menu_item_id, $options, $position);
}

/**
 * @param int $menu_item_id
 * @param array $options
 * @param bool $remove_tax_from_inclusive
 *
 * @return mixed|void
 */
function mprm_get_cart_item_price($menu_item_id = 0, $options = array(), $remove_tax_from_inclusive = false) {
	return models\Cart::get_instance()->get_cart_item_price($menu_item_id, $options, $remove_tax_from_inclusive);
}

/**
 * @param $cart_key
 *
 * @return mixed|void
 */
function mprm_remove_item_url($cart_key) {
	return models\Cart::get_instance()->remove_item_url($cart_key);
}

/**
 * @param int $menu_item_id
 * @param array $options
 *
 * @return mixed|void
 */
function mprm_get_price_name($menu_item_id = 0, $options = array()) {
	$return = false;
	if (mprm_has_variable_prices($menu_item_id) && !empty($options)) {
		$prices = mprm_get_variable_prices($menu_item_id);
		$name = false;
		if ($prices) {
			if (isset($prices[$options['price_id']]))
				$name = $prices[$options['price_id']]['name'];
		}
		$return = $name;
	}
	return apply_filters('mprm_get_price_name', $return, $menu_item_id, $options);
}

/**
 * @param int $menu_item_id
 *
 * @return bool|mixed|void
 */
function mprm_get_variable_prices($menu_item_id = 0) {

	if (empty($menu_item_id)) {
		return false;
	}
	$menu_item = new models\Menu_item($menu_item_id);
	return $menu_item->get_prices($menu_item_id);
}

/**
 * @return mixed|void
 */
function mprm_get_cart_items() {
	return models\Cart::get_instance()->get_cart_contents();
}

/**
 * @param array $item
 *
 * @return null
 */
function mprm_get_cart_item_price_id($item = array()) {
	if (isset($item['item_number'])) {
		$price_id = isset($item['item_number']['options']['price_id']) ? $item['item_number']['options']['price_id'] : null;
	} else {
		$price_id = isset($item['options']['price_id']) ? $item['options']['price_id'] : null;
	}
	return $price_id;
}

/**
 * Cart empty
 */
function mprm_cart_empty() {
	$cart_contents = models\Cart::get_instance()->get_cart_contents();
	if (empty($cart_contents)) {
		echo apply_filters('mprm_empty_cart_message', '<span class="mprm_empty_cart">' . __('Your cart is empty.', 'mp-restaurant-menu') . '</span>');
	}
}

/**
 * Cart button
 */
function mprm_update_cart_button() {
	if (!models\Cart::get_instance()->item_quantities_enabled()) {
		return;
	}
	$color = mprm_get_option('checkout_color', 'blue');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$color = ($color == 'inherit') ? '' : $color;
	?>
	<input type="submit" name="mprm_update_cart_submit" class="mprm-submit <?php echo mprm_is_cart_saving_disabled() ? ' mprm-no-js' : ''; ?> mprm-button<?php echo ' ' . $color . ' ' . $padding; ?>" style="display: none" value="<?php _e('Update Cart', 'mp-restaurant-menu'); ?>"/>
	<input type="hidden" name="mprm_action" value="update_cart"/>
	<?php
}

/**
 * save cart button
 */
function mprm_save_cart_button() {
	if (mprm_is_cart_saving_disabled()) {
		return;
	}
	$color = mprm_get_option('checkout_color', 'blue');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$color = ($color == 'inherit') ? '' : $color;
	if (models\Cart::get_instance()->is_cart_saved()) : ?>
		<a class="mprm-cart-saving-button mprm-submit mprm-button<?php echo ' ' . $color . ' ' . $padding; ?>" id="mprm-restore-cart-button" href="<?php echo esc_url(add_query_arg(array('mprm_action' => 'restore_cart', 'mprm_cart_token' => models\Cart::get_instance()->get_cart_token()))); ?>"><?php _e('Restore Previous Cart', 'mp-restaurant-menu'); ?></a>
	<?php endif; ?>
	<a class="mprm-cart-saving-button mprm-submit mprm-button<?php echo ' ' . $color . ' ' . $padding; ?>" id="mprm-save-cart-button" href="<?php echo esc_url(add_query_arg('mprm_action', 'save_cart')); ?>"><?php _e('Save Cart', 'mp-restaurant-menu'); ?></a>
	<?php
}

/**
 * @param $ID
 * @param $options
 *
 * @return bool
 */
function mprm_item_in_cart($ID, $options) {
	return models\Cart::get_instance()->item_in_cart($ID, $options);
}

/**
 * Check is  cart taxed
 * @return bool
 */
function mprm_is_cart_taxed() {

	return models\Taxes::get_instance()->is_cart_taxed();
}

/**
 * Get cart columns
 *
 * @return mixed|void
 */
function mprm_get_checkout_cart_columns() {
	return models\Cart::get_instance()->checkout_cart_columns();
}

/**
 * Get cart contents
 *
 * @return mixed|void
 */
function mprm_get_cart_contents() {
	return models\Cart::get_instance()->get_cart_contents();
}