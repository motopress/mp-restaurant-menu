<?php global $post;
$order = mprm_get_order_object($post);
$customer_id = $order->customer_id;
$customer = mprm_get_customer($customer_id); ?>

<div class="column-container customer-info mprm-row">

	<div class="mprm-columns mprm-four">
		<?php echo EDD()->html->customer_dropdown(array('selected' => $customer->id, 'name' => 'customer-id')); ?>
	</div>

	<div class="mprm-columns mprm-four">
		<input type="hidden" name="mprm-current-customer" value="<?php echo $customer->id; ?>"/>
	</div>

	<div class="mprm-columns mprm-four">
		<?php if (!empty($customer->id)) : ?>
			<?php $customer_url = admin_url('edit.php?post_type=menu_item&page=mprm-customers&view=overview&id=' . $customer->id); ?>
			<a href="<?php echo $customer_url; ?>" title="<?php _e('View Customer Details', 'mp-restaurant-menu'); ?>"><?php _e('View Customer Details', 'mp-restaurant-menu'); ?></a>
			&nbsp;|&nbsp;
		<?php endif; ?>
		<a href="#new" class="mprm-payment-new-customer" title="<?php _e('New Customer', 'mp-restaurant-menu'); ?>"><?php _e('New Customer', 'mp-restaurant-menu'); ?></a>
	</div>

</div>

<div class="column-container new-customer mprm-row" style="display: none">
	<div class="mprm-columns mprm-three">
		<strong><?php _e('Name:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="text" name="mprm-new-customer-name" value="" class="medium-text"/>
	</div>

	<div class="mprm-columns mprm-three">
		<strong><?php _e('Email:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="email" name="mprm-new-customer-email" value="" class="medium-text"/>
	</div>

	<div class="mprm-columns mprm-three">
		<input type="hidden" id="mprm-new-customer" name="mprm-new-customer" value="0"/>
		<a href="#cancel" class="mprm-payment-new-customer-cancel mprm-delete"><?php _e('Cancel', 'mp-restaurant-menu'); ?></a>
	</div>

	<div class="mprm-columns mprm-three">
		<small><em>*<?php _e('Click "Save Payment" to create new customer', 'mp-restaurant-menu'); ?></em></small>
	</div>
</div>
