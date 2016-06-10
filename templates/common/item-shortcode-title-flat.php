<?php
global $mprm_view_args, $mprm_term;
$background_color_class = apply_filters('mprm-background-flat-view', 'mprm-background-flat-view');

$price_position_class = apply_filters('mprm-price-position-flat-view-class', 'mprm-' . mprm_get_view_price_position());
$price_wrapper_class = apply_filters('mprm-price-wrapper-flat-view-class', 'mprm-price-wrapper-flat-view-class');
if (empty($price) && !empty($mprm_view_args['price'])) {
	$price = mprm_currency_filter(mprm_format_amount(mprm_get_price()));
} else {
	$price = '';
}

?>
<?php if (!empty($mprm_view_args['link_item'])) { ?>
	<h3 class="mprm-title <?php echo $price_wrapper_class . ' ' . $price_position_class ?>">
		<span class="cell-left">
			<span class="<?php echo $background_color_class ?>"><a class="mprm-link" href="<?php echo get_permalink($mprm_menu_item->ID) ?>"><?php echo $mprm_menu_item->post_title ?></a></span>
		</span>
		<span class="cell-right <?php echo $background_color_class ?>"><?php echo $price ?></span>
		<span class="mprm-clear"></span>
	</h3>
	<?php
} else { ?>
	<h3 class="mprm-title <?php echo $price_wrapper_class . ' ' . $price_position_class ?>">
		<span class="cell-left">
			<span class="<?php echo $background_color_class ?>"><?php echo $mprm_menu_item->post_title ?></span>
		</span>
		<span class="cell-right <?php echo $background_color_class ?>"><?php echo $price ?></span>
		<span class="mprm-clear"></span>
	</h3>
	<?php
} ?>

