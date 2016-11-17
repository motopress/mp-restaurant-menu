<?php
global $mprm_view_args, $mprm_term;
$price_position_class = apply_filters('mprm-price-position-simple-view-class', 'mprm-' . mprm_get_view_price_position());
$price_wrapper_class = apply_filters('mprm-price-wrapper-simple-view-class', 'mprm-flex-container-simple-view');

if (empty($price) && !empty($mprm_view_args['price'])) {
	$price = mprm_currency_filter(mprm_format_amount(mprm_get_price()));
} else {
	$price = '';
}
?>
<ul class="mprm-list <?php echo $price_wrapper_class . ' ' . $price_position_class ?>">
	<?php if (!empty($mprm_view_args['link_item'])) { ?>
		<li class="mprm-flex-item mprm-title"><a href="<?php echo get_permalink($mprm_menu_item) ?>"><?php echo $mprm_menu_item->post_title ?></a></li>
	<?php } else { ?>
		<li class="mprm-flex-item mprm-title"><?php echo $mprm_menu_item->post_title ?></li>
	<?php } ?>
	<li class="mprm-flex-item mprm-dots"></li>
	<li class="mprm-flex-item mprm-price"><?php echo $price ?></li>
</ul>



