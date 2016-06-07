<?php
global $post;
$order = mprm_get_order_object($post);
$order_id = $order->ID;
$gateway = $order->gateway;
$transaction_id = esc_attr($order->transaction_id);
$phone = esc_attr($order->phone_number);
?>
<div id="mprm-order-details" class="mprm-order-data">
	<div class="mprm-admin-box">

		<?php do_action('mprm_view_order_details_payment_meta_before', $order_id); ?>

		<?php
		if ($gateway) : ?>
			<div class="mprm-order-gateway mprm-admin-box-inside">
				<p>
					<span class="label"><?php _e('Gateway:', 'mp-restaurant-menu'); ?></span>&nbsp;
					<?php echo mprm_get_gateway_admin_label($gateway); ?>
				</p>
			</div>
		<?php endif; ?>

		<div class="mprm-order-payment-key mprm-admin-box-inside">
			<p>
				<span class="label"><?php _e('Key:', 'mp-restaurant-menu'); ?></span>&nbsp;
				<span><?php echo $order->key; ?></span>
			</p>
		</div>

		<div class="mprm-order-ip mprm-admin-box-inside">
			<p>
				<span class="label"><?php _e('IP:', 'mp-restaurant-menu'); ?></span>&nbsp;
				<span><?php echo esc_attr($order->ip); ?></span>
			</p>
		</div>

		<?php if ($transaction_id) : ?>
			<div class="mprm-order-tx-id mprm-admin-box-inside">
				<p>
					<span class="label"><?php _e('Transaction ID:', 'mp-restaurant-menu'); ?></span>&nbsp;
					<span><?php echo apply_filters('mprm_payment_details_transaction_id-' . $gateway, $transaction_id, $order_id); ?></span>
				</p>
			</div>
		<?php endif; ?>

		<?php if ($phone) : ?>
			<div class="mprm-order-tx-phone mprm-admin-box-inside">
				<p>
					<span class="label"><?php _e('Contact phone:', 'mp-restaurant-menu'); ?></span>&nbsp;
					<span><?php echo apply_filters('mprm_order_phone', $phone, $order_id); ?></span>
				</p>
			</div>
		<?php endif; ?>

		<?php do_action('mprm_view_order_details_payment_meta_after', $order_id); ?>
	</div>
</div>