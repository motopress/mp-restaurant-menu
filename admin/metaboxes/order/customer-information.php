<?php $customer = mprm_get_customer( $customer_id );

if ( ! empty( $customer->telephone ) ) { ?>
	<span class="label"><b><?php _e( 'Contact phone:', 'mp-restaurant-menu' ); ?></b></span> <span> <?php echo apply_filters( 'mprm_order_phone', $customer->telephone ); ?></span>
	<br>
<?php }
if ( ! empty( $customer->telephone ) ) { ?>
	<span class="label"><b><?php _e( 'Customer email:', 'mp-restaurant-menu' ); ?></b></span> <span><?php echo apply_filters( 'mprm_order_customer_email', $customer->email ); ?></span>
<?php } ?>