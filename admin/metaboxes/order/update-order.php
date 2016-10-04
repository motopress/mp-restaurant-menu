<?php global $post;
$order = mprm_get_order_object($post);
$order_id = $order->ID;
$number = $order->number;
$order_meta = $order->get_meta();
$transaction_id = esc_attr($order->transaction_id);
$cart_items = $order->cart_details;
$user_id = $order->user_id;
$customer_id = $order->customer_id;
$order_date = strtotime($order->date);
$address = $order->address;
$currency_code = $order->currency;
$fees = $order->fees;
$user_info = mprm_get_payment_meta_user_info($order_id);

?>
<div class="mprm-admin-box">
	<div class="mprm-admin-box-inside">
		<p>
			<span class="label"><?php _e('Status:', 'mp-restaurant-menu') ?></span>&nbsp;
			<select name="mprm-order-status" class="">
				<?php foreach (mprm_get_payment_statuses() as $key => $status) : ?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($order->status, $key); ?>><?php echo esc_html($status); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>

	<div class="mprm-admin-box-inside">
		<p>
			<span class="label"><?php _e('Date:', 'mp-restaurant-menu'); ?></span>&nbsp;
			<input type="text" name="mprm-order-date" value="<?php echo esc_attr(date('m/d/Y', $order_date)); ?>" class="mprm_datepicker hasDatepicker "/>
		</p>
	</div>

	<div class="mprm-admin-box-inside">
		<p>
			<span class="label"><?php _e('Time:', 'mp-restaurant-menu'); ?></span>
			<input type="text" maxlength="2" name="mprm-order-time-hour" value="<?php echo esc_attr(date_i18n('H', $order_date)); ?>" class="admin-time-input"/> &nbsp;: &nbsp;
			<input type="text" maxlength="2" name="mprm-order-time-min" value="<?php echo esc_attr(date('i', $order_date)); ?>" class="admin-time-input"/>
		</p>
	</div>

	<?php do_action('mprm_view_order_details_update_inner', $order_id); ?>

	<div class="mprm-order-discount mprm-admin-box-inside" style="display: none">
		<p>
			<span class="label"><?php _e('Discount Code', 'mp-restaurant-menu'); ?>:</span>&nbsp;
			<span><?php if ($order->discounts !== 'none') {
					echo '<code>' . $order->discounts . '</code>';
				} else {
					_e('None', 'mp-restaurant-menu');
				} ?></span>
		</p>
	</div>

	<?php if (!empty($fees)) : ?>
		<div class="mprm-order-fees mprm-admin-box-inside">
			<p class="strong"><?php _e('Fees', 'mp-restaurant-menu'); ?>:</p>
			<ul class="mprm-order-fees">
				<?php foreach ($fees as $fee) : ?>
					<li>
						<span class="fee-label"><?php echo $fee['label'] ?> :</span>
						<span class="fee-amount" data-fee="<?php echo esc_attr($fee['amount']) ?>"><?php echo mprm_currency_filter($fee['amount'], $currency_code); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<?php if (apply_filters('mprm_delivery_enable', true)) : ?>
		<div class="mprm-order-deliveries mprm-admin-box-inside">
			<p>
				<span class="label"><?php _e('Price of Delivery', 'mp-restaurant-menu'); ?>:</span>&nbsp;
				<input name="mprm-order-delivery-cost" class="med-text" type="text" value="<?php echo esc_attr(apply_filters('mprm_order_delivery_cost', 0)); ?>"/>
			</p>
		</div>
	<?php endif; ?>

	<?php if (mprm_use_taxes()) : ?>
		<div class="mprm-order-taxes mprm-admin-box-inside">
			<p>
				<span class="label"><?php _e('Tax', 'mp-restaurant-menu'); ?>:</span>&nbsp;
				<input name="mprm-order-tax" class="med-text" type="text" value="<?php echo esc_attr(mprm_format_amount($order->tax)); ?>"/>
			</p>
		</div>
	<?php endif; ?>


	<div class="mprm-order mprm-admin-box-inside">
		<p>
			<span class="label"><?php _e('Total Price', 'mp-restaurant-menu'); ?>:</span>&nbsp; <?php echo mprm_currency_symbol($order->currency); ?>&nbsp;<input name="mprm-order-total" type="text" class="small-text" value="<?php echo esc_attr(mprm_format_amount($order->total)); ?>"/>
		</p>
	</div>

	<div class="mprm-order-recalc-totals mprm-admin-box-inside" style="display:none">
		<p>
			<span class="label"><?php _e('Recalculate Totals', 'mp-restaurant-menu'); ?>:</span>&nbsp;
			<a href="" id="mprm-order-recalc-total" class="button button-secondary right"><?php _e('Recalculate', 'mp-restaurant-menu'); ?></a>
		</p>
	</div>
	<?php do_action('mprm_view_order_details_totals_after', $order_id); ?>

	<div class="mprm-order-update-box mprm-admin-box-inside">
		<?php do_action('mprm_view_order_details_update_before', $order_id); ?>

		<div id="delete-action">
			<a style="display: none;"
			   href="<?php echo wp_nonce_url(add_query_arg(array('mprm-action' => 'delete_order', 'purchase_id' => $order_id), admin_url('edit.php?post_type=menu_item&page=mprm-order-history')), 'mprm_order_nonce') ?>"
			   class="mprm-delete-order mprm-delete"><?php _e('Delete Order', 'mp-restaurant-menu'); ?>
			</a>
		</div>

		<p><input type="submit" class="button button-primary " value="<?php esc_attr_e('Save Order', 'mp-restaurant-menu'); ?>"/></p>

		<?php do_action('mprm_view_order_details_update_after', $order_id); ?>
		<div class="mprm-clear"></div>
	</div>
	<input type="hidden" name="mprm_update" value="1"/>
</div>
