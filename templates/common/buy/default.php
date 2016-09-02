<?php if (mprm_get_option('enable_ecommerce')): ?>
	<div class="mprm_menu_item_buy_button <?php echo mprm_get_option('disable_styles') ? 'mprm-no-styles' : 'mprm-plugin-styles';
	echo (mprm_is_menu_item_image() == true) ? ' mprm-without-image' : ' mprm-with-image' ?>">
		<?php echo mprm_get_purchase_link(array('menu_item_id' => get_the_ID())); ?>
	</div>
<?php endif; ?>