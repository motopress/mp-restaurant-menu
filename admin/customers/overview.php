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
	<div id="mprm-customers-details-wrap" class="postbox ">
		<form id="mprm-customers-details-form" method="post">
			<div class="mprm-row">
				<div class="mprm-columns mprm-four">
					<p class="mprm-class-email"><label for="mprm_email">
							<?php _e('Email:', 'mp-restaurant-menu'); ?>
						</label>
						<input type="email" class="mprm-input" required name="mprm_email" value="<?php echo $customer->email; ?>">
					</p>

					<p class="mprm-class-telephone"><label for="mprm-telephone">
							<?php _e('Telephone:', 'mp-restaurant-menu'); ?>
						</label>
						<input type="tel" pattern="(\+?\d[- .]*){7,13}" class="mprm-input" name="mprm-telephone" value="<?php echo $customer->telephone; ?>">
					</p>
					<p class="mprm-class-name">
						<label for="mprm-name">
							<?php _e('Full name:', 'mp-restaurant-menu'); ?>
						</label>
						<input type="text" class="mprm-input" required name="mprm-name" value="<?php echo $customer->name; ?>">
					</p>

				</div>
			</div>

			<div class="mprm-row">
				<?php mprm_get_error_html() ?>
				<div class="mprm-columns mprm-four">
					<?php submit_button(__('Update customer', 'mp-restaurant-menu'), 'primary', 'mprm-submit') ?>
					<a href="/wp-admin/edit.php?post_type=mp_menu_item&page=mprm-customers"><?php _e('Cancel', 'mp-restaurant-menu'); ?></a>
				</div>
			</div>
			<input type="hidden" name="controller" value="customer">
			<input type="hidden" name="mprm_action" value="update_customer">
		</form>
		<div class="mprm-clear"></div>
	</div>

	<?php do_action('mprm_customers_detail_bottom'); ?>
</div>