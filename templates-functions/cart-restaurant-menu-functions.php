<?php
use mp_restaurant_menu\classes\models;
use mp_restaurant_menu\classes\View as View;

/**
 * @return mixed
 */
function mprm_is_cart_saving_disabled() {
	return models\Cart::get_instance()->is_cart_saving_disabled();
}

/**
 * @return mixed
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
 * @return mixed
 */
function mprm_get_cart_tax() {
	return models\Cart::get_instance()->get_cart_tax();
}

/**
 * @return mixed
 */
function mprm_get_cart_subtotal() {
	return models\Cart::get_instance()->get_cart_subtotal();
}

/**
 * @param $cart_key
 * @param $item
 * @param bool $ajax
 *
 * @return mixed
 */
function mprm_get_cart_item_template($cart_key, $item, $ajax = false) {
	$id = is_array($item) ? $item['id'] : $item;
	$template_mode = mprm_get_template_mode();
	$remove_url = mprm_remove_item_url($cart_key);
	$title = get_the_title($id);
	$options = !empty($item['options']) ? $item['options'] : array();
	$quantity = mprm_get_cart_item_quantity($id, $options, $cart_key);
	$price = mprm_get_cart_item_price($id, $options);

	if (!empty($options)) {
		$title .= (mprm_has_variable_prices($item['id'])) ? ' <span class="mprm-cart-item-separator">-</span> ' . mprm_get_price_name($id, $item['options']) : mprm_get_price_name($id, $item['options']);
	}

	$item = View::get_instance()->get_template_html('widgets/cart/cart-item-' . $template_mode, array('item' => $item, 'id' => $id));

	$item = str_replace('{item_title}', $title, $item);
	$item = str_replace('{cart_item_url}', get_permalink($id), $item);
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
 * Cart item quantity
 *
 * @param int $menu_item_id
 * @param array $options
 * @param int /null $options
 *
 * @return mixed
 */
function mprm_get_cart_item_quantity($menu_item_id = 0, $options = array(), $position = NULL) {
	return models\Cart::get_instance()->get_cart_item_quantity($menu_item_id, $options, $position);
}

/**
 * Cart item price
 *
 * @param int $menu_item_id
 * @param array $options
 * @param bool $remove_tax_from_inclusive
 *
 * @return mixed
 */
function mprm_get_cart_item_price($menu_item_id = 0, $options = array(), $remove_tax_from_inclusive = false) {
	return models\Cart::get_instance()->get_cart_item_price($menu_item_id, $options, $remove_tax_from_inclusive);
}

/**
 * Cart item remove url
 *
 * @param $cart_key
 *
 * @return mixed
 */
function mprm_remove_item_url($cart_key) {
	return models\Cart::get_instance()->remove_item_url($cart_key);
}

/**
 * @param int $menu_item_id
 * @param array $options
 *
 * @return mixed
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
 * @return bool|mixed
 */
function mprm_get_variable_prices($menu_item_id = 0) {

	if (empty($menu_item_id)) {
		return false;
	}
	$menu_item = new models\Menu_item($menu_item_id);
	return $menu_item->get_prices($menu_item_id);
}

/**
 * @return mixed
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
	$color = mprm_get_option('checkout_color', 'mprm-btn blue');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$color = ($color == 'inherit') ? '' : $color;
	?>
	<input type="submit" name="mprm_update_cart_submit" class="mprm-submit <?php echo mprm_is_cart_saving_disabled() ? ' mprm-no-js' : ''; ?> button<?php echo ' ' . $color . ' ' . $padding; ?>" style="display: none" value="<?php _e('Update Cart', 'mp-restaurant-menu'); ?>"/>
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
	$color = mprm_get_option('checkout_color', 'mprm-btn blue');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$color = ($color == 'inherit') ? '' : $color;
	if (models\Cart::get_instance()->is_cart_saved()) : ?>
		<a class="mprm-cart-saving-button mprm-submit button<?php echo ' ' . $color . ' ' . $padding; ?>" id="mprm-restore-cart-button" href="<?php echo esc_url(add_query_arg(array('mprm_action' => 'restore_cart', 'mprm_cart_token' => models\Cart::get_instance()->get_cart_token()))); ?>"><?php _e('Restore Previous Cart', 'mp-restaurant-menu'); ?></a>
	<?php endif; ?>
	<a class="mprm-cart-saving-button mprm-submit button<?php echo ' ' . $color . ' ' . $padding; ?>" id="mprm-save-cart-button" href="<?php echo esc_url(add_query_arg('mprm_action', 'save_cart')); ?>"><?php _e('Save Cart', 'mp-restaurant-menu'); ?></a>
	<?php
}

/**
 * Item in cart
 *
 * @param $ID
 * @param $options
 *
 * @return bool
 */
function mprm_item_in_cart($ID, $options = array()) {
	return models\Cart::get_instance()->item_in_cart($ID, $options);
}

/**
 * Check is cart taxed
 * @return bool
 */
function mprm_is_cart_taxed() {
	return models\Taxes::get_instance()->is_cart_taxed();
}

/**
 * Get cart columns
 *
 * @return mixed
 */
function mprm_get_checkout_cart_columns() {
	return models\Cart::get_instance()->checkout_cart_columns();
}

/**
 * Get cart contents
 *
 * @return mixed
 */
function mprm_get_cart_contents() {
	return models\Cart::get_instance()->get_cart_contents();
}

/**
 * Success cart cart item
 *
 * @param $item
 * @param $order
 *
 * @return bool/mixed
 */
function mprm_success_page_cart_item($item, $order) {
	$menu_item_notes = mprm_get_menu_item_notes($item['id']);
	$post_type = get_post_type($item['id']);

	if ($post_type != mprm_get_post_type('menu_item')) {
		return true;
	}

	?>
	<tr>
		<td>
			<?php $price_id = models\Cart::get_instance()->get_cart_item_price_id($item); ?>

			<div class="mprm_purchase_receipt_product_name mprm-post-<?php echo $post_type ?>">
				<?php echo esc_html($item['name']); ?>
				<?php if (mprm_has_variable_prices($item['id']) && !is_null($price_id)) : ?>
					<span class="mprm_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;<?php echo mprm_get_price_option_name($item['id'], $price_id, $order->ID); ?></span>
				<?php endif; ?>
			</div>

			<?php if (!empty($menu_item_notes)) : ?>
				<div class="mprm_purchase_receipt_product_notes"><?php echo wpautop($menu_item_notes); ?></div>
			<?php endif; ?>

		</td>

		<?php if (models\Misc::get_instance()->use_skus()) : ?>
			<td><?php echo mprm_get_menu_item_sku($item['id']); ?></td>
		<?php endif; ?>

		<?php if (models\Cart::get_instance()->item_quantities_enabled()) { ?>
			<td class="mprm-success-page-quantity"><?php echo $item['quantity']; ?></td>
		<?php } ?>
		<td>
			<?php if (empty($item['in_bundle'])) : ?>
				<?php echo mprm_currency_filter(mprm_format_amount($item['item_price'])); ?>
			<?php endif; ?>
		</td>
	</tr>
<?php }