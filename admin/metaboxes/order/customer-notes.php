<?php global $post;
use mp_restaurant_menu\classes\View as View;

$order            = mprm_get_order_object( $post );
$order_id         = $order->ID;
$customer_id      = $order->customer_id;
$customer         = mprm_get_customer( $customer_id );
$customer_note    = esc_attr( $order->customer_note );
$phone            = esc_attr( $order->phone_number );
$shipping_address = esc_attr( $order->shipping_address );
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
		<textarea name="customer-note" id="customer-order-note" placeholder="<?php _e( 'Order Notes', 'mp-restaurant-menu' ); ?>" class="widefat"><?php echo $customer_note ?></textarea>
	</p>
	<p>
		<label for="customer-shipping-address"><?php _e( 'Shipping address:', 'mp-restaurant-menu' ); ?></label>
		<input class="widefat" type="text" id="customer-shipping-address" name="shipping-address" value="<?php echo $shipping_address ?>" placeholder="<?php _e( 'Shipping address', 'mp-restaurant-menu' ); ?>">
	</p>
	<div class="column-container customer-info mprm-row">
		<div class="mprm-columns mprm-six">
			<?php echo mprm_customers_dropdown( array( 'selected' => $customer->id, 'name' => 'customer-id' ) ); ?>
			<input type="hidden" name="mprm-current-customer" value="<?php echo $customer->id; ?>"/>
			<p class="mprm-customer-information">
				<?php View::get_instance()->render_html( '../admin/metaboxes/order/customer-information', array( 'customer_id' => $customer_id ) ) ?>
			</p>
		</div>
		<div class="mprm-columns mprm-six">
			<a href="<?php echo admin_url( 'edit.php?post_type=mp_menu_item&page=mprm-customers&s=' . $order->customer_id ) ?>"><?php echo $order->first_name . ' ' . $order->last_name ?> </a> /
			<a href="#new" class="mprm-new-customer"
			   title="<?php _e( 'New Customer', 'mp-restaurant-menu' ); ?>"><?php _e( 'New Customer', 'mp-restaurant-menu' ); ?></a>
		</div>
	</div>

	<div class="column-container new-customer mprm-row" style="display: none">
		<div class="mprm-columns mprm-three">
			<strong><?php _e( 'Name:', 'mp-restaurant-menu' ); ?></strong>&nbsp;
			<input type="text" name="mprm-new-customer-name" value="" class="medium-text"/>
		</div>
		<div class="mprm-columns mprm-three">
			<strong><?php _e( 'Email:', 'mp-restaurant-menu' ); ?></strong>&nbsp;
			<input type="email" name="mprm-new-customer-email" value="" class="medium-text"/>
		</div>
		<div class="mprm-columns mprm-three">
			<strong><?php _e( 'Phone number:', 'mp-restaurant-menu' ); ?></strong>&nbsp;
			<input type="text" name="mprm-new-phone-number" value="" class="medium-text"/>
		</div>
		<div class="mprm-columns mprm-three">
			<input type="hidden" id="mprm-new-customer" name="mprm-new-customer" value="0"/>
			<a href="#save" class="mprm-new-customer-save"><?php _e( 'Save a customer', 'mp-restaurant-menu' ); ?></a>&nbsp;|&nbsp;
			<a href="#cancel" class="mprm-new-customer-cancel mprm-delete"><?php _e( 'Cancel', 'mp-restaurant-menu' ); ?></a>
			<p>
				<small><em>*<?php _e( 'Click "Save Order" to create new customer', 'mp-restaurant-menu' ); ?></em></small>
			</p>
		</div>
	</div>
</div>