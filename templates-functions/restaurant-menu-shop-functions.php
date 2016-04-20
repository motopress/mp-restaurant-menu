<?php use \mp_restaurant_menu\classes\models; ?>
<?php

function mprm_before_purchase_form() {
}

function mprm_after_purchase_form() {
}

function mprm_checkout_form_top() {
}

function mprm_checkout_form_bottom() {
}

function mprm_payment_mode_select() {
	$gateways = models\Gateways::get_instance()->get_enabled_payment_gateways(true);
	$page_URL = models\Misc::get_instance()->get_current_page_url();

	do_action('mprm_payment_mode_top'); ?>

	<?php if (models\Settings::get_instance()->is_ajax_disabled()) { ?>
		<form id="mprm_payment_mode" action="<?php echo $page_URL; ?>" method="GET">
	<?php } ?>
	<fieldset id="mprm_payment_mode_select">
		<?php do_action('mprm_payment_mode_before_gateways_wrap'); ?>
		<div id="mprm-payment-mode-wrap">
			<span class="mprm-payment-mode-label"><?php _e('Select Payment Method', 'mp-restaurant-menu'); ?></span><br/>
			<?php

			do_action('mprm_payment_mode_before_gateways');

			foreach ($gateways as $gateway_id => $gateway) :

				$checked = checked($gateway_id, models\Gateways::get_instance()->get_default_gateway(), false);
				$checked_class = $checked ? ' mprm-gateway-option-selected' : '';
				echo '<label for="mprm-gateway-' . esc_attr($gateway_id) . '" class="mprm-gateway-option' . $checked_class . '" id="mprm-gateway-option-' . esc_attr($gateway_id) . '">';
				echo '<input type="radio" name="payment-mode" class="mprm-gateway" id="mprm-gateway-' . esc_attr($gateway_id) . '" value="' . esc_attr($gateway_id) . '"' . $checked . '>' . esc_html($gateway['checkout_label']);
				echo '</label>';
			endforeach;

			do_action('mprm_payment_mode_after_gateways');

			?>
		</div>
		<?php do_action('mprm_payment_mode_after_gateways_wrap'); ?>

	</fieldset>
	<fieldset id="mprm_payment_mode_submit" class="mprm-no-js">
		<p id="mprm-next-submit-wrap">
			<?php echo mprm_checkout_button_next(); ?>
		</p>
	</fieldset>
	<?php if (models\Settings::get_instance()->is_ajax_disabled()) { ?>
		</form>
	<?php } ?>

	<div id="mprm_purchase_form_wrap"></div>

	<?php do_action('mprm_payment_mode_bottom');
}


function mprm_checkout_button_next() {
	$color = models\Settings::get_instance()->get_option('checkout_color', 'blue');
	$color = ($color == 'inherit') ? '' : $color;
	$style = models\Settings::get_instance()->get_option('button_style', 'button');
	$purchase_page = models\Settings::get_instance()->get_option('purchase_page', '0');

	ob_start();
	?>
	<input type="hidden" name="mprm_action" value="gateway_select"/>
	<input type="hidden" name="page_id" value="<?php echo absint($purchase_page); ?>"/>
	<input type="submit" name="gateway_submit" id="mprm_next_button" class="mprm-submit <?php echo $color; ?> <?php echo $style; ?>" value="<?php _e('Next', 'mp-restaurant-menu'); ?>"/>
	<?php
	return apply_filters('mprm_checkout_button_next', ob_get_clean());
}

function mprm_checkout_button_purchase() {
	$color = models\Settings::get_instance()->get_option('checkout_color', 'blue');
	$color = ($color == 'inherit') ? '' : $color;
	$style = models\Settings::get_instance()->get_option('button_style', 'button');
	$label = models\Settings::get_instance()->get_option('checkout_label', '');

	if (models\Cart::get_instance()->get_cart_total()) {
		$complete_purchase = !empty($label) ? $label : __('Purchase', 'easy-digital-downloads');
	} else {
		$complete_purchase = !empty($label) ? $label : __('Free Download', 'easy-digital-downloads');
	}

	ob_start();
	?>
	<input type="submit" class="edd-submit <?php echo $color; ?> <?php echo $style; ?>" id="edd-purchase-button" name="edd-purchase" value="<?php echo $complete_purchase; ?>"/>
	<?php
	return apply_filters('mprm_checkout_button_purchase', ob_get_clean());
}

function mprm_purchase_form() {
	$payment_mode = models\Gateways::get_instance()->get_chosen_gateway();

	/**
	 * Hooks in at the top of the purchase form
	 *
	 * @since 1.4
	 */
	do_action('edd_purchase_form_top');

	if (models\Checkout::get_instance()->can_checkout()) {

		do_action('edd_purchase_form_before_register_login');

		$show_register_form = models\Settings::get_instance()->get_option('show_register_form', 'none');
		if (($show_register_form === 'registration' || ($show_register_form === 'both' && !isset($_GET['login']))) && !is_user_logged_in()) : ?>
			<div id="edd_checkout_login_register">

				<?php do_action('edd_purchase_form_register_fields'); ?>

			</div>
		<?php elseif (($show_register_form === 'login' || ($show_register_form === 'both' && isset($_GET['login']))) && !is_user_logged_in()) : ?>
			<div id="edd_checkout_login_register">

				<?php do_action('edd_purchase_form_login_fields'); ?>

			</div>
		<?php endif; ?>

		<?php if ((!isset($_GET['login']) && is_user_logged_in()) || !isset($show_register_form) || 'none' === $show_register_form || 'login' === $show_register_form) {
			do_action('edd_purchase_form_after_user_info');
		}

		/**
		 * Hooks in before Credit Card Form
		 *
		 * @since 1.4
		 */
		do_action('edd_purchase_form_before_cc_form');

		if (models\Cart::get_instance()->get_cart_total() > 0) {

			// Load the credit card form and allow gateways to load their own if they wish
			if (has_action('edd_' . $payment_mode . '_cc_form')) {
				do_action('edd_' . $payment_mode . '_cc_form');
			} else {
				do_action('edd_cc_form');
			}
		}

		/**
		 * Hooks in after Credit Card Form
		 *
		 * @since 1.4
		 */
		do_action('edd_purchase_form_after_cc_form');

	} else {
		// Can't checkout
		do_action('edd_purchase_form_no_access');
	}

	/**
	 * Hooks in at the bottom of the purchase form
	 *
	 * @since 1.4
	 */
	do_action('edd_purchase_form_bottom');
}

function mprm_cart_empty() {
	echo apply_filters('mprm_empty_cart_message', '<span class="mprm_empty_cart">' . __('Your cart is empty.', 'mp-restaurant-menu') . '</span>');
}

function mprm_checkout_table_header_first() {
}

function mprm_checkout_table_header_last() {
}

function mprm_cart_items_before() {
}

function mprm_checkout_table_body_first() {
}

function mprm_checkout_cart_item_title_after() {
}

function mprm_checkout_cart_item_price_after() {
}

function mprm_cart_actions() {
}

function mprm_checkout_table_body_last() {
}

function mprm_cart_items_middle() {
}

function mprm_cart_items_after() {
}

function mprm_cart_footer_buttons() {
}

function mprm_checkout_table_discount_first() {
}

function mprm_checkout_table_discount_last() {
}

function mprm_checkout_table_footer_first() {
}

function mprm_checkout_table_footer_last() {
}

function mprm_payment_mode_top() {
	if (models\Gateways::get_instance()->show_gateways() && did_action('mprm_payment_mode_top') > 1) {
		return;
	}

	$payment_methods = models\Settings::get_instance()->get_option('accepted_cards', array());

	if (empty($payment_methods)) {
		return;
	}

	echo '<div class="mprm-payment-icons">';

	foreach ($payment_methods as $key => $card) {

		if (models\Settings::get_instance()->string_is_image_url($key)) {

			echo '<img class="payment-icon" src="' . esc_url($key) . '"/>';

		} else {

			$card = strtolower(str_replace(' ', '', $card));

			if (has_filter('mprm_accepted_payment_' . $card . '_image')) {

				$image = apply_filters('mprm_accepted_payment_' . $card . '_image', '');

			} else {

				$image = MP_RM_MEDIA_URL . 'img/' . 'icons/' . $card . '.gif';
				$content_dir = WP_CONTENT_DIR;

				$image = str_replace($content_dir, content_url(), $image);

			}

			if (models\Settings::get_instance()->is_ssl_enforced() || is_ssl()) {

				$image = models\Checkout::get_instance()->enforced_ssl_asset_filter($image);

			}

			echo '<img class="payment-icon" src="' . esc_url($image) . '"/>';
		}

	}

	echo '</div>';
}

function mprm_add_body_classes($class) {
	$classes = (array)$class;

	if (models\Checkout::get_instance()->is_checkout()) {
		$classes[] = 'mprm-checkout';
		$classes[] = 'mprm-page';
	}

	if (models\Checkout::get_instance()->is_success_page()) {
		$classes[] = 'mprm-success';
		$classes[] = 'mprm-page';
	}

	if (models\Checkout::get_instance()->is_failed_transaction_page()) {
		$classes[] = 'mprm-failed-transaction';
		$classes[] = 'mprm-page';
	}

	if (models\Checkout::get_instance()->is_purchase_history_page()) {
		$classes[] = 'mprm-purchase-history';
		$classes[] = 'mprm-page';
	}

	if (models\Misc::get_instance()->is_test_mode()) {
		$classes[] = 'mprm-test-mode';
		$classes[] = 'mprm-page';
	}

	return array_unique($classes);
}

function mprm_update_cart_button() {
	if (!models\Cart::get_instance()->item_quantities_enabled())
		return;

	$color = models\Settings::get_instance()->get_option('checkout_color', 'blue');
	$color = ($color == 'inherit') ? '' : $color;
	?>
	<input type="submit" name="mprm_update_cart_submit" class="mprm-submit mprm-no-js button<?php echo ' ' . $color; ?>" value="<?php _e('Update Cart', 'easy-digital-downloads'); ?>"/>
	<input type="hidden" name="mprm_action" value="update_cart"/>
	<?php
}

function mprm_save_cart_button() {
	if (models\Settings::get_instance()->is_cart_saving_disabled())
		return;

	$color = models\Settings::get_instance()->get_option('checkout_color', 'blue');
	$color = ($color == 'inherit') ? '' : $color;

	if (models\Cart::get_instance()->is_cart_saved()) : ?>
		<a class="edd-cart-saving-button edd-submit button<?php echo ' ' . $color; ?>" id="edd-restore-cart-button" href="<?php echo esc_url(add_query_arg(array('mprm_action' => 'restore_cart', 'mprm_cart_token' => edd_get_cart_token()))); ?>"><?php _e('Restore Previous Cart', 'easy-digital-downloads'); ?></a>
	<?php endif; ?>
	<a class="edd-cart-saving-button edd-submit button<?php echo ' ' . $color; ?>" id="edd-save-cart-button" href="<?php echo esc_url(add_query_arg('mprm_action', 'save_cart')); ?>"><?php _e('Save Cart', 'easy-digital-downloads'); ?></a>
	<?php
}

function mprm_user_info_fields() {
	$customer = models\Customer::get_instance()->get_session_customer();
	mprm_get_template('/shop/user-info-fields', array('customer' => $customer));
}

?>