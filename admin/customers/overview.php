<?php
$customer = mprm_get_customer($id);
$customer_edit_role = apply_filters('mprm_edit_customers_role', 'edit_shop_payments');

if (isset($customer->user_id) && $customer->user_id > 0) :
	$address = get_user_meta($customer->user_id, '_mprm_user_address', true);
	$defaults = array(
		'line1' => '',
		'line2' => '',
		'city' => '',
		'state' => '',
		'country' => '',
		'zip' => ''
	);

	$address = wp_parse_args($address, $defaults);
endif;
?>

<div class="wrap">
	<h1><?php _e('Customer Details', 'mp-restaurant-menu'); ?></h1>
	<?php do_action('mprm_customers_detail_top'); ?>

	<div class="postbox">

		<div class="mprm-avatar-wrap left" id="mprm-customer-avatar">
			<?php echo get_avatar($customer->email); ?><br/>

			<?php if (current_user_can($customer_edit_role)): ?>
				<span class="info-item editable customer-edit-link"><a title="<?php _e('Edit Customer', 'mp-restaurant-menu'); ?>" href="#" id="edit-customer"><?php _e('Edit Customer', 'mp-restaurant-menu'); ?></a></span>
			<?php endif; ?>
		</div>
		<div class="mprm-details-wrap left" id="mprm-customer-details">
			<p class="mprm-class-id">
				<label for="mprm-customer-id">
					<b><?php echo '# ' . $customer->id ?></b>
				</label>
			</p>
			<p class="mprm-class-">
				<label for="mprm-customer-email">
					<?php _e('Email:', 'mp-restaurant-menu'); ?>
					<span><b><?php echo $customer->email; ?></b></span>
				</label>
			</p>
			<p class="mprm-class-name">
				<label for="">
					<?php _e('Name:', 'mp-restaurant-menu'); ?>
					<span><b><?php echo $customer->name; ?></b></span>
				</label>

			</p>
			<p class="mprm-class-telephone">
				<label for="">
					<?php _e('Name:', 'mp-restaurant-menu'); ?>
					<span><b><?php echo $customer->telephone; ?></b></span>
				</label>
			</p>
			<p class="mprm-class-user-id">
				<label for="">
					<?php _e('User ID:', 'mp-restaurant-menu'); ?>
					<span><b><?php echo $customer->user_id; ?></b></span>
				</label>
			</p>
			<p class="mprm-class-user-id">
				<label for="">
					<?php _e('Customer since:', 'mp-restaurant-menu'); ?>
					<span><b><?php echo date_i18n(get_option('date_format'), strtotime($customer->date_created)) ?></b></span>
				</label>
			</p>
		</div>

		<form id="mprm-customers-details-form" method="post" style="display: none">
			<?php
			if (!empty($customer)):
				foreach ($customer as $key => $value): ?>
					<p class="mprm-class-<?php echo $key ?>">
						<label for="">

						</label>
						<input name="">
					</p>
					<?php
				endforeach;
			endif; ?>
		</form>
		<div class="mprm-clear"></div>
	</div>

	<?php do_action('mprm_customers_detail_bottom'); ?>
</div>