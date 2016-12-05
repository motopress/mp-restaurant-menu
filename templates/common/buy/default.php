<?php global $mprm_view_args;

if (mprm_get_option('enable_ecommerce')):

	$styles_class = mprm_get_option('disable_styles') ? 'mprm-no-styles' : 'mprm-plugin-styles';
	$is_menu_item_image = mprm_is_menu_item_image();
	?>

	<div class="mprm_menu_item_buy_button <?php echo $styles_class ?>" style="<?php echo get_buy_style_view_args() ?>">

		<?php mprm_get_view()->get_template('common/notice', array('menu_item_id' => get_the_ID())) ?>

		<?php echo mprm_get_purchase_link(array('menu_item_id' => get_the_ID())); ?>

	</div>

<?php endif; ?>