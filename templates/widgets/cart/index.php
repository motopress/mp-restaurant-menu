<?php do_action('mprm_before_cart');

$cart_items = mprm_get_cart_items();
$cart_quantity = mprm_get_cart_quantity();
$cart_total = mprm_get_cart_total();
$template_mode = mprm_get_template_mode();
$display = $cart_quantity > 0 ? '' : ' style="display:none;"';

mprm_get_template('widgets/cart/' . $template_mode . '-mode',
	array(
		'cart_total' => $cart_total,
		'cart_quantity' => $cart_quantity,
		'cart_items' => $cart_items,
		'display' => $display)
);

do_action('mprm_after_cart');