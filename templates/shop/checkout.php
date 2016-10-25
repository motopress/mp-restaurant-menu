<div id="mprm_checkout_wrap" class="<?php echo mprm_get_option('disable_styles') ? 'mprm-no-styles' : 'mprm-plugin-styles' ?>">

	<?php if ($cart_contents || $cart_has_fees) : ?>
		<p class="mprm-required"><small><?php _e('Required fields are followed by', 'mp-restaurant-menu'); ?></small></p>

		<?php
		do_action('mprm_purchase_form_cart_items_before');

		mprm_get_checkout_cart_template();

		do_action('mprm_purchase_form_cart_items_after');

		?>
		<div id="mprm_checkout_form_wrap" class="mprm-clear">
			<?php do_action('mprm_before_purchase_form'); ?>

			<form id="mprm_purchase_form" class="mprm-clear" action="<?php echo $form_action; ?>" method="POST">
				<?php
				/**
				 * Hooks in at the top of the checkout form
				 *
				 * @since 1.0
				 */
				do_action('mprm_checkout_form_top');

				if (mprm_show_gateways()) {
					do_action('mprm_payment_mode_select');
				} else {
					do_action('mprm_purchase_form');
				}

				/**
				 * Hooks in at the bottom of the checkout form
				 *
				 * @since 1.0
				 */
				do_action('mprm_checkout_form_bottom')
				?>
			</form>

			<?php do_action('mprm_after_purchase_form'); ?>

		</div>
		<?php
	else:
		/**
		 * Fires off when there is nothing in the cart
		 *
		 * @since 1.0
		 */
		do_action('mprm_cart_empty');
	endif; ?>
</div>