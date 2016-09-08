<?php

global $post;
$order = mprm_get_order_object($post);

?>
<?php do_action('mprm_delivery_details_before') ?>

	<textarea name="mprm-order-delivery" id="mprm-order-delivery" class="large-text"><?php echo $order->shipping_address ?></textarea>

<?php do_action('mprm_delivery_details_after') ?>