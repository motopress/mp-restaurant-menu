<?php use mp_restaurant_menu\classes\View as View;

$customer = mprm_get_customer($id);
?>
<div class="wrap">
	<h1><?php _e('Delete customer', 'mp-restaurant-menu'); ?></h1>
	<?php do_action('mprm_customer_delete_top', $customer); ?>

	<div class="info-wrapper customer-section">

		<form id="delete-customer" method="post" action="<?php echo admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=delete&id=' . $customer->id); ?>">

			<div class="mprm-item-notes-header">
				<?php echo get_avatar($customer->email, 30); ?> <span><?php echo $customer->name; ?></span>
			</div>

			<div class="customer-info delete-customer">

				<span class="delete-customer-options">
					<p>
						<?php View::get_instance()->render_html('../admin/settings/checkbox', array('name' => 'mprm-customer-delete-confirm')); ?>
						<label for="mprm-customer-delete-confirm"><?php _e('Are you sure you want to delete this customer?', 'mp-restaurant-menu'); ?></label>
					</p>

					<p>
						<?php View::get_instance()->render_html('../admin/settings/checkbox', array('name' => 'mprm-customer-delete-records', 'options' => array('disabled' => true))); ?>
						<label for="mprm-customer-delete-records"><?php _e('Delete all associated payments and records?', 'mp-restaurant-menu'); ?></label>
					</p>

					<?php do_action('mprm_customer_delete_inputs', $customer); ?>
				</span>

				<span id="customer-edit-actions">
					<input type="hidden" name="customer_id" value="<?php echo $customer->id; ?>"/>
					<?php wp_nonce_field('delete-customer', '_wpnonce', false, true); ?>
					<input type="hidden" name="mprm_action" value="delete"/>
					<input type="hidden" name="controller" value="customer"/>
					<input type="submit" disabled="disabled" id="mprm-delete-customer" class="button-primary" value="<?php _e('Delete Customer', 'mp-restaurant-menu'); ?>"/>
					<a id="mprm-delete-customer-cancel" href="<?php echo admin_url('edit.php?post_type=mp_menu_item&page=mprm-customers&view=overview&id=' . $customer->id); ?>" class="delete"><?php _e('Cancel', 'mp-restaurant-menu'); ?></a>
				</span>

			</div>

		</form>
	</div>

	<?php do_action('mprm_customer_delete_bottom', $customer); ?>
</div>