<?php
use mp_restaurant_menu\classes\View as View;

global $post;
$order = mprm_get_order_object($post);
$customer_id = $order->customer_id;
$customer = mprm_get_customer($customer_id);
$phone = esc_attr($order->phone_number); ?>

<div class="column-container customer-info mprm-row">

	<?php echo mprm_customers_dropdown(array('selected' => $customer->id, 'name' => 'customer-id')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<input type="hidden" name="mprm-current-customer" value="<?php echo esc_attr( $customer->id ); ?>"/>
	<p class="mprm-customer-information">
		<?php View::get_instance()->render_html('../admin/metaboxes/order/customer-information', array('customer_id' => $customer_id)) ?>
	</p>
	<p>
		<a href="#new" class="mprm-new-customer"
	   title="<?php esc_html_e('New Customer', 'mp-restaurant-menu'); ?>"><?php esc_html_e('New Customer', 'mp-restaurant-menu'); ?></a>
	</p>
</div>

<div class="column-container new-customer mprm-row" style="display: none">
	<div class="mprm-columns mprm-four">
		<strong><?php esc_html_e('Name:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="text" name="mprm-new-customer-name" value=""/>
	</div>

	<div class="mprm-columns mprm-four">
		<strong><?php esc_html_e('Email:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="email" name="mprm-new-customer-email" value=""/>
	</div>

	<div class="mprm-columns mprm-four">
		<strong><?php esc_html_e('Phone number:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="text" name="mprm-new-phone-number" value=""/>
	</div>

	<div class="mprm-columns mprm-twelve">
		<input type="hidden" id="mprm-new-customer" name="mprm-new-customer" value="0"/>
		<a href="#save" class="mprm-new-customer-save"><?php esc_html_e('Save a customer', 'mp-restaurant-menu'); ?></a>&nbsp;|&nbsp;
		<a href="#cancel" class="mprm-new-customer-cancel mprm-delete"><?php esc_html_e('Cancel', 'mp-restaurant-menu'); ?></a>
		<p>
			<small><em>*<?php esc_html_e('Click "Save Order" to create new customer', 'mp-restaurant-menu'); ?></em></small>
		</p>
	</div>
</div>
