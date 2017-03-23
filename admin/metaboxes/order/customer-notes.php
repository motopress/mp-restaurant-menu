<?php global $post;
$order = mprm_get_order_object($post);
$order_id = $order->ID;
$customer_note = esc_attr($order->customer_note);
?>
<div id="mprm-customer-order-notes" class="">
	<textarea name="mprm-customer-note" id="mprm-customer-order-note" class="large-text"><?php echo $customer_note ?></textarea>
	<div class="mprm-clear"></div>
</div>
