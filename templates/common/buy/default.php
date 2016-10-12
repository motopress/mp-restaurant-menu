<?php if (mprm_get_option('enable_ecommerce')):
	$styles_class = mprm_get_option('disable_styles') ? 'mprm-no-styles' : 'mprm-plugin-styles';
	$image_class = (mprm_is_menu_item_image() == true) ? ' mprm-without-image' : ' mprm-with-image';
	?>

	<div class="mprm_menu_item_buy_button <?php echo $styles_class . $image_class ?>">

		<?php mprm_get_view()->render_html('common/notice', array('menu_item_id' => get_the_ID())) ?>

		<?php echo mprm_get_purchase_link(array('menu_item_id' => get_the_ID())); ?>

	</div>

<?php endif; ?>