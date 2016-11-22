<div class="mprm-cart-content">
	<ul class="mprm-cart <?php echo empty($cart_items) ? 'mprm-empty-cart' : 'mprm-cart-items' ?>">

		<?php if ($cart_items) : ?>

			<?php foreach ($cart_items as $key => $item) : ?>
				<?php echo mprm_get_cart_item_template($key, $item, false); ?>
			<?php endforeach; ?>
			<li class="mprm-cart-item mprm-cart-meta mprm_subtotal"><?php echo __('Subtotal:', 'mp-restaurant-menu') ?> <span class='mprm_cart_subtotal_amount subtotal'><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal())); ?></span></li>
			<li class="mprm-cart-item mprm_checkout"><a href="<?php echo mprm_get_checkout_uri(); ?>"><?php _e('Checkout', 'mp-restaurant-menu'); ?></a></li>

		<?php else : ?>
			<li class="mprm-cart-item"><?php echo apply_filters('mprm_empty_cart_message', '<span class="mprm_empty_cart">' . __('Your cart is empty.', 'mp-restaurant-menu') . '</span>'); ?></li>
		<?php endif; ?>

	</ul>
</div>