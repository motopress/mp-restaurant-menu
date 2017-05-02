<?php $customer = mprm_get_customer($customer_id);

if (!empty($customer->telephone)) { ?>
	<span class="label"><b><?php _e('Phone:', 'mp-restaurant-menu'); ?></b></span> <span> <?php echo apply_filters('mprm_order_phone', $customer->telephone); ?></span>
	<br>
<?php } ?>
<span class="label"><b><?php _e('Email:', 'mp-restaurant-menu'); ?></b></span> <span><?php echo apply_filters('mprm_order_customer_email', $customer->email); ?></span>

