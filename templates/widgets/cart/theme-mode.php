<div class="mprm-cart-content">
	<ul class="mprm-cart <?php echo empty($cart_items) ? 'mprm-empty-cart' : 'mprm-cart-items' ?>">

		<?php if ($cart_items) : ?>

			<?php foreach ($cart_items as $key => $item) : ?>
				<?php echo mprm_get_cart_item_template($key, $item, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>
			<li class="mprm-cart-item mprm-cart-meta mprm_subtotal"><?php echo esc_html__('Subtotal:', 'mp-restaurant-menu') ?> <span class='mprm_cart_subtotal_amount subtotal'><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
			<li class="mprm-cart-item mprm_checkout"><a href="<?php echo mprm_get_checkout_uri(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>"><?php esc_html_e('Checkout', 'mp-restaurant-menu'); ?></a></li>

		<?php else : ?>
			<li class="mprm-cart-item"><?php echo apply_filters('mprm_empty_cart_message', '<span class="mprm_empty_cart">' . esc_html__('Your cart is empty.', 'mp-restaurant-menu') . '</span>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
		<?php endif; ?>

	</ul>
</div>