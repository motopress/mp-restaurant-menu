<?php use \mp_restaurant_menu\classes\models\Gateways as Gateways; ?>
<div id="mprm_checkout_wrap">
	<?php if ($cart_contents || $cart_has_fees) :

		//edd_checkout_cart();
		mprm_get_checkout_cart_template();
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

				if (Gateways::get_instance()->show_gateways()) {
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