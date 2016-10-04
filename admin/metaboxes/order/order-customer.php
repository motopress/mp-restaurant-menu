<?php
use mp_restaurant_menu\classes\View as View;

global $post;
$order = mprm_get_order_object($post);
$customer_id = $order->customer_id;
$customer = mprm_get_customer($customer_id);
$phone = esc_attr($order->phone_number); ?>

<div class="column-container customer-info mprm-row">

	<div class="mprm-columns mprm-six">
		<?php echo mprm_customers_dropdown(array('selected' => $customer->id, 'name' => 'customer-id')); ?>
		<input type="hidden" name="mprm-current-customer" value="<?php echo $customer->id; ?>"/>
		<p class="mprm-customer-information">
			<?php View::get_instance()->render_html('../admin/metaboxes/order/customer-information', array('customer_id' => $customer_id)) ?>
		</p>
	</div>
	<div class="mprm-columns mprm-six">
		<a href="#new" class="mprm-new-customer"
		   title="<?php _e('New Customer', 'mp-restaurant-menu'); ?>"><?php _e('New Customer', 'mp-restaurant-menu'); ?></a>
	</div>
</div>

<div class="column-container new-customer mprm-row" style="display: none">
	<div class="mprm-columns mprm-three">
		<strong><?php _e('Name:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="text" name="mprm-new-customer-name" value="" class="medium-text"/>
	</div>

	<div class="mprm-columns mprm-three">
		<strong><?php _e('Email:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="email" name="mprm-new-customer-email" value="" class="medium-text"/>
	</div>

	<div class="mprm-columns mprm-three">
		<strong><?php _e('Phone number:', 'mp-restaurant-menu'); ?></strong>&nbsp;
		<input type="text" name="mprm-new-phone-number" value="" class="medium-text"/>
	</div>

	<div class="mprm-columns mprm-three">
		<input type="hidden" id="mprm-new-customer" name="mprm-new-customer" value="0"/>
		<a href="#save" class="mprm-new-customer-save"><?php _e('Save a customer', 'mp-restaurant-menu'); ?></a>&nbsp;|&nbsp;
		<a href="#cancel" class="mprm-new-customer-cancel mprm-delete"><?php _e('Cancel', 'mp-restaurant-menu'); ?></a>
		<p>
			<small><em>*<?php _e('Click "Save Order" to create new customer', 'mp-restaurant-menu'); ?></em></small>
		</p>
	</div>
</div>
