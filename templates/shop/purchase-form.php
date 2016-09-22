<?php
use mp_restaurant_menu\classes\models;

$payment_mode = models\Gateways::get_instance()->get_chosen_gateway();

/**
 * Hooks in at the top of the purchase form
 *
 * @since 1.4
 */
do_action('mprm_purchase_form_top');

if (models\Checkout::get_instance()->can_checkout()) {
	do_action('mprm_purchase_form_before_register_login');
	$show_register_form = mprm_get_option('show_register_form', 'none');
	if (($show_register_form === 'registration' || ($show_register_form === 'both' && !isset($_GET['login']))) && !is_user_logged_in()) : ?>
		<div id="mprm_checkout_login_register">
			<?php do_action('mprm_purchase_form_register_fields'); ?>
		</div>
	<?php elseif (($show_register_form === 'login' || ($show_register_form === 'both' && isset($_GET['login']))) && !is_user_logged_in()) : ?>
		<div id="mprm_checkout_login_register">
			<?php do_action('mprm_purchase_form_login_fields'); ?>
		</div>
	<?php endif; ?>
	<?php if ((!isset($_GET['login']) && is_user_logged_in()) || !isset($show_register_form) || 'none' === $show_register_form || 'login' === $show_register_form) {
		do_action('mprm_purchase_form_after_user_info');
	}
	/**
	 * Hooks in before Credit Card Form
	 *
	 * @since 1.4
	 */
	do_action('mprm_purchase_form_before_cc_form');
	if (mprm_get_cart_total() > 0) {
		if (has_action('mprm_' . $payment_mode . '_cc_form')) {
			do_action('mprm_' . $payment_mode . '_cc_form');
		} else {
			do_action('mprm_cc_form');
		}
	}
	/**
	 * Hooks in after Credit Card Form
	 *
	 * @since 1.4
	 */
	do_action('mprm_purchase_form_after_cc_form');
} else {
	do_action('mprm_purchase_form_no_access');
}

do_action('mprm_purchase_form_bottom');