<div class="mprm-cart-content">
	<p class="mprm-cart-number-of-items"<?php echo $display // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php esc_html_e('Number of items in cart', 'mp-restaurant-menu'); ?>: <span class="mprm-cart-quantity"><?php echo esc_html( $cart_quantity ); ?></span>
	</p>
	<ul class="mprm-cart">
		<?php if ($cart_items) : ?>

			<?php foreach ($cart_items as $key => $item) : ?>
				<?php echo mprm_get_cart_item_template($key, $item, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>

			<?php if (mprm_use_taxes()) : ?>
				<li class="cart_item mprm-cart-meta mprm_subtotal"><?php echo esc_html__('Subtotal:', 'mp-restaurant-menu') . " <span class='subtotal'>" . mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
				<li class="cart_item mprm-cart-meta mprm_cart_tax"><?php esc_html_e('Estimated Tax:', 'mp-restaurant-menu'); ?> <span class="cart-tax"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_tax())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
			<?php endif; ?>
			<li class="cart_item mprm-cart-meta mprm_total"><?php esc_html_e('Total:', 'mp-restaurant-menu'); ?> <span class="cart-total"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_total())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
			<li class="cart_item mprm_checkout"><a href="<?php echo esc_url( mprm_get_checkout_uri() ); ?>"><?php esc_html_e('Checkout', 'mp-restaurant-menu'); ?></a></li>

		<?php else : ?>

			<li class="cart_item empty"><?php echo apply_filters('mprm_empty_cart_message', '<span class="mprm_empty_cart">' . esc_html__('Your cart is empty.', 'mp-restaurant-menu') . '</span>'); ?></li>
			<?php if (mprm_use_taxes()) : ?>
				<li class="cart_item mprm-cart-meta mprm_subtotal" style="display:none;"><?php echo esc_html__('Subtotal:', 'mp-restaurant-menu') . " <span class='subtotal'>" . mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
				<li class="cart_item mprm-cart-meta mprm_cart_tax" style="display:none;"><?php esc_html_e('Estimated Tax:', 'mp-restaurant-menu'); ?> <span class="cart-tax"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_tax())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
			<?php endif; ?>
			<li class="cart_item mprm-cart-meta mprm_total" style="display:none;"><?php esc_html_e('Total:', 'mp-restaurant-menu'); ?> <span class="cart-total"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_total())); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
			<li class="cart_item mprm_checkout" style="display:none;"><a href="<?php echo esc_url( mprm_get_checkout_uri() ); ?>"><?php esc_html_e('Checkout', 'mp-restaurant-menu'); ?></a></li>

		<?php endif; ?>
	</ul>
</div>
