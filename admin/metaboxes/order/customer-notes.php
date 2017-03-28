<?php global $post;
$order         = mprm_get_order_object( $post );
$order_id      = $order->ID;
$customer_note = esc_attr( $order->customer_note );
$phone         = esc_attr( $order->phone_number );
$shipping_address = esc_attr( $order->shipping_address )
?>
<div class="order-details">
	<p>
		<label for="customer-first-name"><?php _e( 'First Name:', 'mp-restaurant-menu' ); ?></label>
		<input class="widefat" id="customer-first-name" type="text" name="customer-first-name" value="<?php echo $order->first_name ?>" placeholder="<?php _e( 'First Name', 'mp-restaurant-menu' ); ?>">
	</p>
	<p>
		<label for="customer-last-name"><?php _e( 'Last Name:', 'mp-restaurant-menu' ); ?></label>
		<input class="widefat" id="customer-last-name" type="text" name="customer-last-name" value="<?php echo $order->last_name ?>" placeholder="<?php _e( 'Last Name', 'mp-restaurant-menu' ); ?>">
	</p>
	<p>
		<label for="customer-email"><?php _e( 'Contact email:', 'mp-restaurant-menu' ); ?></label>
		<input class="widefat" id="customer-email" type="text" name="customer-email" value="<?php echo $order->email ?>" placeholder="<?php _e( 'Customer email', 'mp-restaurant-menu' ); ?>"></p>
	<p>
		<label for="customer-phone"><?php _e( 'Contact phone:', 'mp-restaurant-menu' ); ?></label>
		<input class="widefat" type="text" id="customer-phone" name="customer-phone" value="<?php echo $phone ?>" placeholder="<?php _e( 'Phone number', 'mp-restaurant-menu' ); ?>">
	</p>
	<p>
		<label for="customer-order-note"><?php _e( 'Order Notes:', 'mp-restaurant-menu' ); ?></label>
		<textarea name="customer-note" id="customer-order-note" placeholder="<?php _e( 'Order Notes', 'mp-restaurant-menu' ); ?>" class="large-text"><?php echo $customer_note ?></textarea>
	</p>
	<p>
		<label for="customer-shipping-address"><?php _e( 'Shipping address:', 'mp-restaurant-menu' ); ?></label>
		<input class="widefat" type="text" id="customer-shipping-address" name="shipping-address" value="<?php echo $shipping_address ?>" placeholder="<?php _e( 'Shipping address', 'mp-restaurant-menu' ); ?>">
	</p>
	<p>
		<label><?php _e( 'View customer details:', 'mp-restaurant-menu' ); ?></label>
		<br>
		<a href="<?php echo admin_url( 'edit.php?post_type=mp_menu_item&page=mprm-customers&s=' . $order->customer_id ) ?>"><?php echo $order->first_name . ' ' . $order->last_name ?> </a>
	</p>
</div>