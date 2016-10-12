<?php do_action('mprm_before_cart');

$cart_items = mprm_get_cart_items();
$cart_quantity = mprm_get_cart_quantity();
$display_quantity = $cart_quantity > 0 ? '' : 'mprm-hidden ';
$cart_total = mprm_get_cart_total();
?>
	<div class="mprm-cart-content">
		<p class="mprm-cart-number-of-items <?php echo $display_quantity; ?>">
			<?php _e('Number of items in cart', 'mp-restaurant-menu'); ?>: <span class="mprm-cart-quantity"><?php echo $cart_quantity; ?></span>
		</p>
		<ul class="mprm-cart">

			<?php if ($cart_items) : ?>

				<?php foreach ($cart_items as $key => $item) : ?>
					<?php echo mprm_get_cart_item_template($key, $item, false); ?>
				<?php endforeach; ?>

				<?php if (mprm_use_taxes()) : ?>
					<li class="mprm-cart-item mprm-cart-meta mprm_subtotal"><?php echo __('Subtotal:', 'mp-restaurant-menu') ?> <span class='mprm_cart_subtotal_amount subtotal'><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal())); ?></span></li>
					<li class="mprm-cart-item mprm-cart-meta mprm_cart_tax"><?php _e('Estimated Tax:', 'mp-restaurant-menu'); ?> <span class="mprm_cart_tax_amount cart-tax"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_tax())); ?></span></li>
				<?php endif; ?>

				<?php do_action('mprm_cart_total_widget_before') ?>
				<li class="mprm-cart-item mprm-cart-meta mprm_total"><span class="mprm-bold"><?php _e('Total:', 'mp-restaurant-menu'); ?></span> <span class="cart-total mprm_cart_amount"><?php echo mprm_currency_filter(mprm_format_amount($cart_total)); ?></span></li>

				<?php do_action('mprm_cart_total_widget_after', $cart_total) ?>
				<li class="mprm-cart-item mprm_checkout"><a href="<?php echo mprm_get_checkout_uri(); ?>"><?php _e('Checkout', 'mp-restaurant-menu'); ?></a></li>

			<?php else : ?>
				<li class="mprm-cart-item empty"><?php echo apply_filters('mprm_empty_cart_message', '<span class="mprm_empty_cart">' . __('Your cart is empty.', 'mp-restaurant-menu') . '</span>'); ?></li>

				<?php if (mprm_use_taxes()) : ?>
					<li class="mprm-cart-item mprm-cart-meta mprm_subtotal" style="display:none;"><?php echo __('Subtotal:', 'mp-restaurant-menu'); ?> <span class='mprm_cart_subtotal_amount subtotal'><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal())); ?></span></li>
					<li class="mprm-cart-item mprm-cart-meta mprm_cart_tax" style="display:none;"><?php _e('Estimated Tax:', 'mp-restaurant-menu'); ?> <span class="mprm_cart_tax_amount cart-tax"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_tax())); ?></span></li>
				<?php endif; ?>

				<?php do_action('mprm_cart_total_widget_before') ?>

				<li class="mprm-cart-item mprm-cart-meta mprm_total" style="display:none;"><span class="mprm-bold"><?php _e('Total:', 'mp-restaurant-menu'); ?></span>
					<span class="cart-total mprm_cart_amount"><?php echo mprm_currency_filter(mprm_format_amount($cart_total)); ?></span>
				</li>

				<?php do_action('mprm_cart_total_widget_after', $cart_total) ?>

				<li class="mprm-cart-item mprm_checkout" style="display:none;"><a href="<?php echo mprm_get_checkout_uri(); ?>"><?php _e('Checkout', 'mp-restaurant-menu'); ?></a></li>
			<?php endif; ?>

		</ul>
	</div>

<?php do_action('mprm_after_cart');