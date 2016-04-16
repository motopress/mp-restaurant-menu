<div id="mprm_checkout_wrap">
	<?php if ($cart_contents || $cart_has_fees) :

		//edd_checkout_cart();
		mprm_get_checkout_cart_template();
		?>
		<div id="mprm_checkout_form_wrap" class="mprm_clearfix">
			<?php do_action('mprm_before_purchase_form'); ?>
			<form id="mprm_purchase_form" class="mprm_form" action="<?php echo $form_action; ?>" method="POST">
				<?php
				/**
				 * Hooks in at the top of the checkout form
				 *
				 * @since 1.0
				 */
				do_action('edd_checkout_form_top');

				if (\mp_restaurant_menu\classes\models\Gateways::get_instance()->show_gateways()) {
					do_action('edd_payment_mode_select');
				} else {
					do_action('edd_purchase_form');
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
		</div><!--end #edd_checkout_form_wrap-->
		<?php
	else:
		/**
		 * Fires off when there is nothing in the cart
		 *
		 * @since 1.0
		 */
		do_action('edd_cart_empty');
	endif; ?>
</div>