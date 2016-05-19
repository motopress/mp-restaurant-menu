<?php
use \mp_restaurant_menu\classes\models;
use \mp_restaurant_menu\classes\View as View;

function mprm_is_cart_saving_disabled() {
	return models\Cart::get_instance()->is_cart_saving_disabled();
}

function mprm_get_cart_quantity() {
	return models\Cart::get_instance()->get_cart_quantity();
}

function mprm_get_cart_total() {
	return models\Cart::get_instance()->get_cart_total();
}

function mprm_get_cart_tax() {
	return models\Cart::get_instance()->get_cart_tax();
}

function mprm_get_cart_subtotal() {
	return models\Cart::get_instance()->get_cart_subtotal();
}

function mprm_get_cart_item_template($cart_key, $item, $ajax = false) {
	global $post;

	$id = is_array($item) ? $item['id'] : $item;

	$remove_url = mprm_remove_item_url($cart_key);
	$title = get_the_title($id);
	$options = !empty($item['options']) ? $item['options'] : array();
	$quantity = mprm_get_cart_item_quantity($id, $options);
	$price = mprm_get_cart_item_price($id, $options);

	if (!empty($options)) {
		$title .= (mprm_has_variable_prices($item['id'])) ? ' <span class="mprm-cart-item-separator">-</span> ' . edd_get_price_name($id, $item['options']) : mprm_get_price_name($id, $item['options']);
	}


	$item = View::get_instance()->render_html('widgets\cart\cart-item', array(), false);


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

function mprm_get_cart_item_quantity($menu_item_id = 0, $options = array()) {
	return models\Cart::get_instance()->get_cart_item_quantity($menu_item_id, $options);
}

function mprm_get_cart_item_price($menu_item_id = 0, $options = array(), $remove_tax_from_inclusive = false) {
	return models\Cart::get_instance()->get_cart_item_price($menu_item_id, $options, $remove_tax_from_inclusive);
}

function mprm_remove_item_url($cart_key) {
	return models\Cart::get_instance()->remove_item_url($cart_key);
}

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

function mprm_get_variable_prices($menu_item_id = 0) {

	if (empty($menu_item_id)) {
		return false;
	}

	$menu_item = new models\Menu_item($menu_item_id);
	return $menu_item->get_prices($menu_item_id);
}