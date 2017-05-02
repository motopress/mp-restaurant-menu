<?php
$customer = mprm_get_customer($id);
$customer_edit_role = apply_filters('mprm_edit_customers_role', 'edit_shop_payments');
$users = apply_filters('mprm_edit_users', get_users());;

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
	<div id="mprm-customers-details-wrap" class="postbox ">
		<form id="mprm-customers-details-form" method="post">
			<p class="mprm-class-email"><label for="mprm-email">
					<?php _e('Email:', 'mp-restaurant-menu'); ?>
				</label>
				<input class="mprm-input large-text" type="email" required name="mprm-email" value="<?php echo $customer->email; ?>">
			</p>
			<p class="mprm-class-name">
				<label for="mprm-name">
					<?php _e('Full name:', 'mp-restaurant-menu'); ?>
				</label>
				<input type="text" class="mprm-input large-text" required name="mprm-name" value="<?php echo $customer->name; ?>">
			</p>
			<p class="mprm-class-telephone"><label for="mprm-telephone">
					<?php _e('Phone:', 'mp-restaurant-menu'); ?>
				</label>
				<input class="mprm-input large-text" type="text" name="mprm-telephone" value="<?php echo $customer->telephone; ?>">
			</p>
			<p class="mprm-class-wp-user">
				<label for="mprm-user">
					<?php _e('User ID:', 'mp-restaurant-menu'); ?>
				</label>

				<select class="mprm-select large-text" required name="mprm-user">
					<?php if (empty($users)) { ?>
						<option value="0"><?php _e('No available users', 'mp-restaurant-menu') ?></option>
					<?php } else { ?>
						<option value="0"><?php _e('No user selected', 'mp-restaurant-menu') ?></option>
						<?php foreach ($users as $user) { ?>
							<option value="<?php echo $user->ID ?>" <?php selected($user->ID, $customer->user_id); ?> ><?php echo $user->user_nicename ?></option>
						<?php }
					} ?>
				</select>
			</p>

			<p><?php echo mprm_get_error_html() ?></p>
			<?php submit_button(__('Update customer', 'mp-restaurant-menu'), 'primary', 'mprm-submit') ?>

			<input type="hidden" name="controller" value="customer">
			<input type="hidden" name="mprm_action" value="update_customer">
		</form>
	</div>

	<?php do_action('mprm_customers_detail_bottom'); ?>
</div>