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
			<span class="mprm-payment-mode-label"><?php _e('Select Payment Method', 'easy-digital-downloads'); ?></span><br/>
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
	<div id="mprm_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->


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
	<input type="submit" name="gateway_submit" id="mprm_next_button" class="mprm-submit <?php echo $color; ?> <?php echo $style; ?>" value="<?php _e('Next', 'easy-digital-downloads'); ?>"/>
	<?php
	return apply_filters('mprm_checkout_button_next', ob_get_clean());
}

function mprm_purchase_form() {
}

function mprm_cart_empty() {
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
	if (models\Gateways::get_instance()->show_gateways() && did_action('mprm_payment_mode_top')) {
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

				$image = mprm_locate_template('images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.gif', false);
				$content_dir = WP_CONTENT_DIR;

				if (function_exists('wp_normalize_path')) {

					// Replaces backslashes with forward slashes for Windows systems
					$image = wp_normalize_path($image);
					$content_dir = wp_normalize_path($content_dir);

				}

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


?>