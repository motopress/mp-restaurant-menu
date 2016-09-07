<?php
global $mprm_receipt_args;
use mp_restaurant_menu\classes\models\Cart as Cart;
use mp_restaurant_menu\classes\models\Gateways as Gateways;
use mp_restaurant_menu\classes\models\Misc as Misc;
use mp_restaurant_menu\classes\models\Payments as Payments;
use mp_restaurant_menu\classes\models\Taxes as Taxes;

// No key found
if (empty($payment_key)) { ?>
	<p class="mprm-alert mprm-alert-error"><?php echo $mprm_receipt_args['error'] ?></p>
	<?php
	return;
}
if (isset($can_view) && $can_view && !empty($mprm_receipt_args['error'])) {
	?>
	<p class="mprm-alert mprm-alert-error"><?php echo $mprm_receipt_args['error'] ?></p>
	<?php
	return;
}
if (empty($order)) : ?>
	<div class="mprm-errors mprm-alert mprm-alert-error">
		<?php _e('The specified receipt ID appears to be invalid', 'mp-restaurant-menu'); ?>
	</div>
	<?php
	return;
endif;

if (isset($need_login) && $need_login) {
	echo empty($login_from) ? '' : $login_from;
}
?>

<table id="mprm_purchase_receipt">
	<thead>
	<?php do_action('mprm_payment_receipt_before', $order, $receipt_args); ?>
	<?php if (filter_var($receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<th><strong><?php _e('Payment', 'mp-restaurant-menu'); ?>:</strong></th>
			<th><?php echo Payments::get_instance()->get_payment_number($order->ID); ?></th>
		</tr>
	<?php endif; ?>
	</thead>
	<tbody>
	<tr>
		<td class="mprm_receipt_payment_status"><strong><?php _e('Payment Status', 'mp-restaurant-menu'); ?>:</strong></td>
		<td class="mprm_receipt_payment_status <?php echo strtolower($status); ?>"><?php echo $status; ?></td>
	</tr>
	<?php if (filter_var($receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<td><strong><?php _e('Payment Key', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo get_post_meta($order->ID, '_mprm_order_purchase_key', true); ?></td>
		</tr>
	<?php endif; ?>
	<?php if (filter_var($receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<td><strong><?php _e('Payment Method', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo Gateways::get_instance()->get_gateway_checkout_label(Payments::get_instance()->get_payment_gateway($order->ID)); ?></td>
		</tr>
	<?php endif; ?>
	<?php if (filter_var($receipt_args['date'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<td><strong><?php _e('Date', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo date_i18n(get_option('date_format'), strtotime($meta['date'])); ?></td>
		</tr>
	<?php endif; ?>
	<?php if (($fees = Payments::get_instance()->get_payment_fees($order->ID, 'fee'))) : ?>
		<tr>
			<td><strong><?php _e('Fees', 'mp-restaurant-menu'); ?>:</strong></td>
			<td>
				<ul class="mprm_receipt_fees">
					<?php foreach ($fees as $fee) : ?>
						<li>
							<span class="mprm_fee_label"><?php echo esc_html($fee['label']); ?></span>
							<span class="mprm_fee_sep">&nbsp;&ndash;&nbsp;</span>
							<span class="mprm_fee_amount"><?php echo mprm_currency_filter(mprm_format_amount($fee['amount'])); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</td>
		</tr>
	<?php endif; ?>
	<?php if (filter_var($receipt_args['discount'], FILTER_VALIDATE_BOOLEAN) && isset($user['discount']) && $user['discount'] != 'none') : ?>
		<tr>
			<td><strong><?php _e('Discount(s)', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo $user['discount']; ?></td>
		</tr>
	<?php endif; ?>
	<?php if (Taxes::get_instance()->use_taxes()) : ?>
		<tr>
			<td><strong><?php _e('Tax', 'mp-restaurant-menu'); ?></strong></td>
			<td><?php echo Payments::get_instance()->payment_tax($order->ID); ?></td>
		</tr>
	<?php endif; ?>
	<?php if (filter_var($receipt_args['price'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<td><strong><?php _e('Subtotal', 'mp-restaurant-menu'); ?></strong></td>
			<td>
				<?php echo Payments::get_instance()->payment_subtotal($order->ID); ?>
			</td>
		</tr>
		<tr>
			<td><strong><?php _e('Total Price', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo Payments::get_instance()->payment_amount($order->ID); ?></td>
		</tr>
	<?php endif; ?>

	<?php if (!empty($order->customer_note)) : ?>
		<tr>
			<td><strong><?php _e('Order Notes', 'mp-restaurant-menu'); ?></strong></td>
			<td><?php echo $order->customer_note; ?></td>
		</tr>
	<?php endif; ?>

	<?php if (!empty($order->shipping_address)) : ?>
		<tr>
			<td><strong><?php _e('Shipping address', 'mp-restaurant-menu'); ?></strong></td>
			<td><?php echo $order->shipping_address; ?></td>
		</tr>
	<?php endif; ?>

	<?php do_action('mprm_payment_receipt_after', $order, $receipt_args); ?>
	</tbody>
</table>
<?php do_action('mprm_payment_receipt_after_table', $order, $receipt_args); ?>

<?php if (filter_var($receipt_args['products'], FILTER_VALIDATE_BOOLEAN)) : ?>
	<h3><?php echo apply_filters('mprm_payment_receipt_products_title', __('Products', 'mp-restaurant-menu')); ?></h3>
	<table id="mprm_purchase_receipt_products">
		<thead>
		<th><?php _e('Name', 'mp-restaurant-menu'); ?></th>
		<?php if (Misc::get_instance()->use_skus()) { ?>
			<th><?php _e('SKU', 'mp-restaurant-menu'); ?></th>
		<?php } ?>
		<?php if (Cart::get_instance()->item_quantities_enabled()) : ?>
			<th><?php _e('Quantity', 'mp-restaurant-menu'); ?></th>
		<?php endif; ?>
		<th><?php _e('Price', 'mp-restaurant-menu'); ?></th>
		</thead>
		<tbody>
		<?php if ($cart) : ?>
			<?php foreach ($cart as $key => $item) : ?>
				<?php if (!apply_filters('mprm_user_can_view_receipt_item', true, $item)) : ?>
					<?php continue; // Skip this item if can't view it ?>
				<?php endif; ?>

				<?php do_action('mprm-success-page-cart-item-before', $item['id'], $order) ?>

				<?php if (empty($item['in_bundle'])) : ?>

					<tr>
						<td>
							<?php $price_id = Cart::get_instance()->get_cart_item_price_id($item); ?>
							<div class="mprm_purchase_receipt_product_name mprm-post-<?php echo get_post_type($item['id']) ?>">
								<?php echo esc_html($item['name']); ?>
								<?php if (mprm_has_variable_prices($item['id']) && !is_null($price_id)) : ?>
									<span class="mprm_purchase_receipt_price_name ">&nbsp;&ndash;&nbsp;<?php echo mprm_get_price_option_name($item['id'], $price_id, $order->ID); ?></span>
								<?php endif; ?>
							</div>
							<?php if ($receipt_args['notes']) : ?>
								<div class="mprm_purchase_receipt_product_notes"><?php echo wpautop(mprm_get_menu_item_notes($item['id'])); ?></div>
							<?php endif; ?>
						</td>
						<?php if (Misc::get_instance()->use_skus()) : ?>
							<td><?php echo mprm_get_menu_item_sku($item['id']); ?></td>
						<?php endif; ?>
						<?php if (Cart::get_instance()->item_quantities_enabled()) { ?>
							<td><?php echo $item['quantity']; ?></td>

						<?php } ?>
						<td>
							<?php if (empty($item['in_bundle'])) : // Only show price when product is not part of a bundle ?>
								<?php echo mprm_currency_filter(mprm_format_amount($item['price'])); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php do_action('mprm-success-page-cart-item-after', $item['id'], $order) ?>

			<?php endforeach; ?>
		<?php endif; ?>
		<?php if (($fees = Payments::get_instance()->get_payment_fees($order->ID, 'item'))) : ?>
			<?php foreach ($fees as $fee) : ?>
				<tr>
					<td class="mprm_fee_label"><?php echo esc_html($fee['label']); ?></td>
					<?php if (Cart::get_instance()->item_quantities_enabled()) : ?>
						<td></td>
					<?php endif; ?>
					<td class="mprm_fee_amount"><?php echo mprm_currency_filter(mprm_format_amount($fee['amount'])) ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
<?php endif; ?>
