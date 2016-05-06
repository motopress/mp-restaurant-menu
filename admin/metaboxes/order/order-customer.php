<div id="edd-customer-details" class="postbox">
	<h3 class="hndle">
		<span><?php _e( 'Customer Details', 'easy-digital-downloads' ); ?></span>
	</h3>
	<div class="inside edd-clearfix">

		<?php $customer = new EDD_Customer( $customer_id ); ?>

		<div class="column-container customer-info">
			<div class="column">
				<?php echo EDD()->html->customer_dropdown( array( 'selected' => $customer->id, 'name' => 'customer-id' ) ); ?>
			</div>
			<div class="column">
				<input type="hidden" name="edd-current-customer" value="<?php echo $customer->id; ?>" />
			</div>
			<div class="column">
				<?php if( ! empty( $customer->id ) ) : ?>
					<?php $customer_url = admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $customer->id ); ?>
					<a href="<?php echo $customer_url; ?>" title="<?php _e( 'View Customer Details', 'easy-digital-downloads' ); ?>"><?php _e( 'View Customer Details', 'easy-digital-downloads' ); ?></a>
					&nbsp;|&nbsp;
				<?php endif; ?>
				<a href="#new" class="edd-payment-new-customer" title="<?php _e( 'New Customer', 'easy-digital-downloads' ); ?>"><?php _e( 'New Customer', 'easy-digital-downloads' ); ?></a>
			</div>
		</div>

		<div class="column-container new-customer" style="display: none">
			<div class="column">
				<strong><?php _e( 'Name:', 'easy-digital-downloads' ); ?></strong>&nbsp;
				<input type="text" name="edd-new-customer-name" value="" class="medium-text"/>
			</div>
			<div class="column">
				<strong><?php _e( 'Email:', 'easy-digital-downloads' ); ?></strong>&nbsp;
				<input type="email" name="edd-new-customer-email" value="" class="medium-text"/>
			</div>
			<div class="column">
				<input type="hidden" id="edd-new-customer" name="edd-new-customer" value="0" />
				<a href="#cancel" class="edd-payment-new-customer-cancel edd-delete"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
			</div>
			<div class="column">
				<small><em>*<?php _e( 'Click "Save Payment" to create new customer', 'easy-digital-downloads' ); ?></em></small>
			</div>
		</div>

		<?php
		// The edd_payment_personal_details_list hook is left here for backwards compatibility
		do_action( 'edd_payment_personal_details_list', $payment_meta, $user_info );
		do_action( 'edd_payment_view_details', $payment_id );
		?>

	</div><!-- /.inside -->
</div><!-- /#edd-customer-details -->