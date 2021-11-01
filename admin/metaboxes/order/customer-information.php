<?php $customer = mprm_get_customer($customer_id);

if (!empty($customer->telephone)) { ?>
	<span class="label"><b><?php esc_html_e('Phone:', 'mp-restaurant-menu'); ?></b></span> <span> <?php echo esc_html( apply_filters('mprm_order_phone', $customer->telephone ) ); ?></span>
	<br>
<?php } ?>
<span class="label"><b><?php esc_html_e('Email:', 'mp-restaurant-menu'); ?></b></span> <span><?php echo esc_html( apply_filters('mprm_order_customer_email', $customer->email ) ); ?></span>

