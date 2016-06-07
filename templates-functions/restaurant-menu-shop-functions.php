<?php
use \mp_restaurant_menu\classes\models;
use \mp_restaurant_menu\classes\Core as Core;
use \mp_restaurant_menu\classes\View as View;

function mprm_before_purchase_form() {
}

function mprm_after_purchase_form() {
}

function mprm_checkout_form_top() {
}

function mprm_checkout_form_bottom() {
}

function mprm_before_cc_fields() {
}

function mprm_before_cc_expiration() {
}

function mprm_after_cc_expiration() {
}

function mprm_after_cc_fields() {
}

function mprm_get_purchase_session() {
	return models\Session::get_instance()->get_session_by_key('mprm_purchase');
}

function mprm_use_skus() {
	$ret = mprm_get_option('enable_skus', false);
	return (bool)apply_filters('mprm_use_skus', $ret);
}

function mprm_get_cart_item_price_id($item = array()) {
	if (isset($item['item_number'])) {
		$price_id = isset($item['item_number']['options']['price_id']) ? $item['item_number']['options']['price_id'] : null;
	} else {
		$price_id = isset($item['options']['price_id']) ? $item['options']['price_id'] : null;
	}
	return $price_id;
}

function mprm_get_menu_item_sku($menu_item_id = 0) {
	$menu_item = new models\Menu_item($menu_item_id);
	return $menu_item->get_sku();
}

function mprm_get_menu_item_notes($menu_item_id = 0) {
	$menu_item = new models\Menu_item($menu_item_id);
	return $menu_item->get_notes();
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
			<span class="mprm-payment-mode-label"><legend><?php _e('Select Payment Method', 'mp-restaurant-menu'); ?>
					<legend></span>
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
	<div id="mprm_purchase_form_wrap" class="<?php echo mprm_get_option('disable_styles') ? 'mprm-no-styles' : 'mprm-plugin-styles' ?>"></div>
	<?php do_action('mprm_payment_mode_bottom');
}

function mprm_checkout_button_next() {
	$color = mprm_get_option('checkout_color', 'inherit');
	$color = ($color == 'inherit') ? '' : $color;
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$style = mprm_get_option('button_style', 'button');
	$purchase_page = mprm_get_option('purchase_page', '0');
	ob_start();
	?>
	<input type="hidden" name="mprm_action" value="gateway_select"/>
	<input type="hidden" name="page_id" value="<?php echo absint($purchase_page); ?>"/>
	<input type="submit" name="gateway_submit" id="mprm_next_button" class="mprm-submit <?php echo $color; ?> <?php echo $padding; ?> <?php echo $style; ?>" value="<?php _e('Next', 'mp-restaurant-menu'); ?>"/>
	<?php
	return apply_filters('mprm_checkout_button_next', ob_get_clean());
}

function mprm_checkout_button_purchase() {
	$color = mprm_get_option('checkout_color', 'inherit');
	$color = ($color == 'inherit') ? '' : $color;
	$style = mprm_get_option('button_style', 'button');
	$label = mprm_get_option('checkout_label', '');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	if (mprm_get_cart_total()) {
		$complete_purchase = !empty($label) ? $label : __('Purchase', 'mp-restaurant-menu');
	} else {
		$complete_purchase = !empty($label) ? $label : __('Free Menu item', 'mp-restaurant-menu');
	}
	ob_start();
	?>
	<input type="submit" class="mprm-submit <?php echo $color; ?> <?php echo $padding; ?> <?php echo $style; ?>" id="mprm-purchase-button" name="mprm-purchase" value="<?php echo $complete_purchase; ?>"/>
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
			// Load the credit card form and allow gateways to load their own if they wish
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
		// Can't checkout
		do_action('mprm_purchase_form_no_access');
	}
	/**
	 * Hooks in at the bottom of the purchase form
	 *
	 * @since 1.4
	 */
	do_action('mprm_purchase_form_bottom');
}

function mprm_show_gateways() {
	return models\Gateways::get_instance()->show_gateways();
}

function mprm_get_cc_form() {
	ob_start(); ?>
	<?php do_action('mprm_before_cc_fields'); ?>
	<fieldset id="mprm_cc_fields" class="mprm-do-validate">
		<span><legend><?php _e('Credit Card Info', 'mp-restaurant-menu'); ?></legend></span>
		<?php if (is_ssl()) : ?>
			<div id="mprm_secure_site_wrapper">
				<span class="padlock"></span>
				<span><?php _e('This is a secure SSL encrypted payment.', 'mp-restaurant-menu'); ?></span>
			</div>
		<?php endif; ?>
		<p id="mprm-card-number-wrap">
			<label for="card_number" class="mprm-label">
				<?php _e('Card Number', 'mp-restaurant-menu'); ?>
				<span class="mprm-required-indicator">*</span>
				<span class="card-type"></span>
			</label>
			<span class="mprm-description"><?php _e('The (typically) 16 digits on the front of your credit card.', 'mp-restaurant-menu'); ?></span>
			<input type="text" autocomplete="off" name="card_number" id="card_number" class="card-number mprm-input required" placeholder="<?php _e('Card number', 'mp-restaurant-menu'); ?>"/>
		</p>
		<p id="mprm-card-cvc-wrap">
			<label for="card_cvc" class="mprm-label">
				<?php _e('CVC', 'mp-restaurant-menu'); ?>
				<span class="mprm-required-indicator">*</span>
			</label>
			<span class="mprm-description"><?php _e('The 3 digit (back) or 4 digit (front) value on your card.', 'mp-restaurant-menu'); ?></span>
			<input type="text" size="4" maxlength="4" autocomplete="off" name="card_cvc" id="card_cvc" class="card-cvc mprm-input required" placeholder="<?php _e('Security code', 'mp-restaurant-menu'); ?>"/>
		</p>
		<p id="mprm-card-name-wrap">
			<label for="card_name" class="mprm-label">
				<?php _e('Name on the Card', 'mp-restaurant-menu'); ?>
				<span class="mprm-required-indicator">*</span>
			</label>
			<span class="mprm-description"><?php _e('The name printed on the front of your credit card.', 'mp-restaurant-menu'); ?></span>
			<input type="text" autocomplete="off" name="card_name" id="card_name" class="card-name mprm-input required" placeholder="<?php _e('Card name', 'mp-restaurant-menu'); ?>"/>
		</p>
		<?php do_action('mprm_before_cc_expiration'); ?>
		<p class="card-expiration">
			<label for="card_exp_month" class="mprm-label">
				<?php _e('Expiration (MM/YY)', 'mp-restaurant-menu'); ?>
				<span class="mprm-required-indicator">*</span>
			</label>
			<span class="mprm-description"><?php _e('The date your credit card expires, typically on the front of the card.', 'mp-restaurant-menu'); ?></span>
			<select id="card_exp_month" name="card_exp_month" class="card-expiry-month mprm-select mprm-select-small required">
				<?php for ($i = 1; $i <= 12; $i++) {
					echo '<option value="' . $i . '">' . sprintf('%02d', $i) . '</option>';
				} ?>
			</select>
			<span class="exp-divider"> / </span>
			<select id="card_exp_year" name="card_exp_year" class="card-expiry-year mprm-select mprm-select-small required">
				<?php for ($i = date('Y'); $i <= date('Y') + 30; $i++) {
					echo '<option value="' . $i . '">' . substr($i, 2) . '</option>';
				} ?>
			</select>
		</p>
		<?php do_action('mprm_after_cc_expiration'); ?>
	</fieldset>
	<?php
	do_action('mprm_after_cc_fields');
	echo ob_get_clean();
}

function mprm_get_register_fields() {
	$show_register_form = mprm_get_option('show_register_form', 'none');
	ob_start(); ?>
	<fieldset id="mprm_register_fields">
		<?php if ($show_register_form == 'both') { ?>
			<p id="mprm-login-account-wrap"><?php _e('Already have an account?', 'mp-restaurant-menu'); ?> <a href="<?php echo esc_url(add_query_arg('login', 1)); ?>" class="mprm_checkout_register_login" data-action="checkout_login"><?php _e('Login', 'mp-restaurant-menu'); ?></a></p>
		<?php } ?>
		<?php do_action('mprm_register_fields_before'); ?>
		<fieldset id="mprm_register_account_fields">
			<span>
				<legend><?php _e('Create an account', 'mp-restaurant-menu');
					if (!mprm_is_no_guest_checkout()) {
						echo ' ' . __('(optional)', 'mp-restaurant-menu');
					} ?></legend>
			</span>
			<?php do_action('mprm_register_account_fields_before'); ?>
			<p id="mprm-user-login-wrap">
				<label for="mprm_user_login">
					<?php _e('Username', 'mp-restaurant-menu'); ?>
					<?php if (mprm_is_no_guest_checkout()) { ?>
						<span class="mprm-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="mprm-description"><?php _e('The username you will use to log into your account.', 'mp-restaurant-menu'); ?></span>
				<input name="mprm_user_login" id="mprm_user_login" class="mprm-input"
					<?php if (mprm_is_no_guest_checkout()) {
						echo 'required ';
					} ?> type="text" placeholder="<?php _e('Username', 'mp-restaurant-menu'); ?>" title="<?php _e('Username', 'mp-restaurant-menu'); ?>"/>
			</p>
			<p id="mprm-user-pass-wrap">
				<label for="mprm_user_pass">
					<?php _e('Password', 'mp-restaurant-menu'); ?>
					<?php if (mprm_is_no_guest_checkout()) { ?>
						<span class="mprm-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="mprm-description"><?php _e('The password used to access your account.', 'mp-restaurant-menu'); ?></span>
				<input name="mprm_user_pass" id="mprm_user_pass" class="mprm-input"
					<?php if (mprm_is_no_guest_checkout()) {
						echo 'required ';
					} ?> placeholder="<?php _e('Password', 'mp-restaurant-menu'); ?>" type="password"/>
			</p>
			<p id="mprm-user-pass-confirm-wrap" class="mprm_register_password">
				<label for="mprm_user_pass_confirm">
					<?php _e('Password Again', 'mp-restaurant-menu'); ?>
					<?php if (mprm_is_no_guest_checkout()) { ?>
						<span class="mprm-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="mprm-description"><?php _e('Confirm your password.', 'mp-restaurant-menu'); ?></span>
				<input name="mprm_user_pass_confirm" id="mprm_user_pass_confirm" class="mprm-input" <?php if (mprm_is_no_guest_checkout()) {
					echo 'required ';
				} ?> placeholder="<?php _e('Confirm password', 'mp-restaurant-menu'); ?>" type="password"/>
			</p>
			<?php do_action('mprm_register_account_fields_after'); ?>
		</fieldset>
		<?php do_action('mprm_register_fields_after'); ?>
		<input type="hidden" name="mprm-purchase-var" value="needs-to-register"/>
		<?php do_action('mprm_purchase_form_user_info'); ?>
		<?php do_action('mprm_purchase_form_user_register_fields'); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}

function mprm_purchase_form_before_cc_form() {
}

function mprm_is_no_guest_checkout() {
	return models\Misc::get_instance()->no_guest_checkout();
}

function mprm_checkout_tax_fields() {
	if (models\Taxes::get_instance()->cart_needs_tax_address_fields() && mprm_get_cart_total()) {
		mprm_default_cc_address_fields();
	}
}

function mprm_default_cc_address_fields() {
	$logged_in = is_user_logged_in();
	$customer = models\Session::get_instance()->get_session_by_key('customer');
	$customer = wp_parse_args($customer, array('address' => array(
		'line1' => '',
		'line2' => '',
		'city' => '',
		'zip' => '',
		'state' => '',
		'country' => ''
	)));
	$customer['address'] = array_map('sanitize_text_field', $customer['address']);
	if ($logged_in) {
		$user_address = get_user_meta(get_current_user_id(), '_mprm_user_address', true);
		foreach ($customer['address'] as $key => $field) {
			if (empty($field) && !empty($user_address[$key])) {
				$customer['address'][$key] = $user_address[$key];
			} else {
				$customer['address'][$key] = '';
			}
		}
	}
	ob_start(); ?>
	<fieldset id="mprm_cc_address" class="cc-address">
		<span><legend><?php _e('Billing Details', 'mp-restaurant-menu'); ?></legend></span>
		<?php do_action('mprm_cc_billing_top'); ?>
		<p id="mprm-card-address-wrap">
			<label for="card_address" class="mprm-label">
				<?php _e('Billing Address', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('card_address')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('The primary billing address for your credit card.', 'mp-restaurant-menu'); ?></span>
			<input type="text" id="card_address" name="card_address" class="card-address mprm-input<?php if (models\Checkout::get_instance()->field_is_required('card_address')) {
				echo ' required';
			} ?>" placeholder="<?php _e('Address line 1', 'mp-restaurant-menu'); ?>" value="<?php echo $customer['address']['line1']; ?>"<?php if (models\Checkout::get_instance()->field_is_required('card_address')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="mprm-card-address-2-wrap">
			<label for="card_address_2" class="mprm-label">
				<?php _e('Billing Address Line 2 (optional)', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('card_address_2')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('The suite, apt no, PO box, etc, associated with your billing address.', 'mp-restaurant-menu'); ?></span>
			<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 mprm-input<?php if (models\Checkout::get_instance()->field_is_required('card_address_2')) {
				echo ' required';
			} ?>" placeholder="<?php _e('Address line 2', 'mp-restaurant-menu'); ?>" value="<?php echo $customer['address']['line2']; ?>"<?php if (models\Checkout::get_instance()->field_is_required('card_address_2')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="mprm-card-city-wrap">
			<label for="card_city" class="mprm-label">
				<?php _e('Billing City', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('card_city')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('The city for your billing address.', 'mp-restaurant-menu'); ?></span>
			<input type="text" id="card_city" name="card_city" class="card-city mprm-input<?php if (models\Checkout::get_instance()->field_is_required('card_city')) {
				echo ' required';
			} ?>" placeholder="<?php _e('City', 'mp-restaurant-menu'); ?>" value="<?php echo $customer['address']['city']; ?>"<?php if (models\Checkout::get_instance()->field_is_required('card_city')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="mprm-card-zip-wrap">
			<label for="card_zip" class="mprm-label">
				<?php _e('Billing Zip / Postal Code', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('card_zip')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('The zip or postal code for your billing address.', 'mp-restaurant-menu'); ?></span>
			<input type="text" size="4" name="card_zip" class="card-zip mprm-input<?php if (models\Checkout::get_instance()->field_is_required('card_zip')) {
				echo ' required';
			} ?>" placeholder="<?php _e('Zip / Postal Code', 'mp-restaurant-menu'); ?>" value="<?php echo $customer['address']['zip']; ?>"<?php if (models\Checkout::get_instance()->field_is_required('card_zip')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="mprm-card-country-wrap">
			<label for="billing_country" class="mprm-label">
				<?php _e('Billing Country', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('billing_country')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('The country for your billing address.', 'mp-restaurant-menu'); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country mprm-select<?php if (models\Checkout::get_instance()->field_is_required('billing_country')) {
				echo ' required';
			} ?>"<?php if (models\Checkout::get_instance()->field_is_required('billing_country')) {
				echo ' required ';
			} ?>>
				<?php
				$selected_country = models\Settings::get_instance()->get_shop_country();
				if (!empty($customer['address']['country']) && '*' !== $customer['address']['country']) {
					$selected_country = $customer['address']['country'];
				}
				$countries = models\Settings::get_instance()->get_country_list();
				foreach ($countries as $country_code => $country) {
					echo '<option value="' . esc_attr($country_code) . '"' . selected($country_code, $selected_country, false) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>
		<p id="mprm-card-state-wrap">
			<label for="card_state" class="mprm-label">
				<?php _e('Billing State / Province', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('card_state')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('The state or province for your billing address.', 'mp-restaurant-menu'); ?></span>
			<?php
			$selected_state = models\Settings::get_instance()->get_shop_state();
			$states = models\Settings::get_instance()->get_shop_states($selected_country);
			if (!empty($customer['address']['state'])) {
				$selected_state = $customer['address']['state'];
			}
			if (!empty($states)) : ?>
				<select name="card_state" id="card_state" class="card_state mprm-select<?php if (models\Checkout::get_instance()->field_is_required('card_state')) {
					echo ' required';
				} ?>">
					<?php
					foreach ($states as $state_code => $state) {
						echo '<option value="' . $state_code . '"' . selected($state_code, $selected_state, false) . '>' . $state . '</option>';
					}
					?>
				</select>
			<?php else : ?>
				<?php $customer_state = !empty($customer['address']['state']) ? $customer['address']['state'] : ''; ?>
				<input type="text" size="6" name="card_state" id="card_state" class="card_state mprm-input" value="<?php echo esc_attr($customer_state); ?>" placeholder="<?php _e('State / Province', 'mp-restaurant-menu'); ?>"/>
			<?php endif; ?>
		</p>
		<?php do_action('mprm_cc_billing_bottom'); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}

function mprm_checkout_submit() { ?>
	<fieldset id="mprm_purchase_submit">
		<?php do_action('mprm_purchase_form_before_submit'); ?>
		<?php mprm_checkout_hidden_fields(); ?>
		<?php echo mprm_checkout_button_purchase(); ?>
		<?php do_action('mprm_purchase_form_after_submit'); ?>
		<!--		--><?php //if (models\Settings::get_instance()->is_ajax_disabled()) { ?>
		<!--			<p class="mprm-cancel"><a href="--><?php //echo models\Checkout::get_instance()->get_checkout_uri(); ?><!--">--><?php //_e('Go back', 'mp-restaurant-menu'); ?><!--</a></p>-->
		<!--		--><?php //} ?>
	</fieldset>
	<?php
}

function mprm_terms_agreement() {
	if (mprm_get_option('show_agree_to_terms', false)) {
		$agree_text = mprm_get_option('agree_text', '');
		$agree_label = mprm_get_option('agree_label', __('Agree to Terms?', 'mp-restaurant-menu'));
		?>
		<fieldset id="mprm_terms_agreement">
			<div id="mprm_terms" style="display:none;">
				<?php
				do_action('mprm_before_terms');
				echo wpautop(stripslashes($agree_text));
				do_action('mprm_after_terms');
				?>
			</div>
			<div id="mprm_show_terms">
				<a href="#" class="mprm_terms_links"><?php _e('Show Terms', 'mp-restaurant-menu'); ?></a>
				<a href="#" class="mprm_terms_links" style="display:none;"><?php _e('Hide Terms', 'mp-restaurant-menu'); ?></a>
			</div>
			<div class="mprm-terms-agreement">
				<input name="mprm_agree_to_terms" class="required" required="required" type="checkbox" id="mprm_agree_to_terms" value="1"/>
				<label for="mprm_agree_to_terms"><?php echo stripslashes($agree_label); ?></label>
			</div>
		</fieldset>
		<?php
	}
}

function mprm_checkout_additional_information() {
	View::get_instance()->render_html('/shop/checkout-additional-information');
}

function mprm_print_errors() {
	models\Errors::get_instance()->print_errors();
}

function mprm_checkout_final_total() {
	?>
	<p id="mprm_final_total_wrap">
		<strong><?php _e('Purchase Total:', 'mp-restaurant-menu'); ?></strong>
		<span class="mprm_cart_amount" data-subtotal="<?php echo models\Cart::get_instance()->get_cart_subtotal(); ?>" data-total="<?php echo models\Cart::get_instance()->get_cart_subtotal(); ?>"><?php models\Cart::get_instance()->cart_total(); ?></span>
	</p>
	<?php
}

function mprm_purchase_form_after_submit() {
}

function mprm_checkout_hidden_fields() {
	?>
	<?php if (is_user_logged_in()) { ?>
		<input type="hidden" name="mprm-user-id" value="<?php echo get_current_user_id(); ?>"/>
	<?php } ?>
	<input type="hidden" name="mprm_action" value="purchase"/>
	<input type="hidden" name="controller" value="cart"/>
	<input type="hidden" name="mprm-gateway" value="<?php echo models\Gateways::get_instance()->get_chosen_gateway(); ?>"/>
	<?php
}

function mprm_get_login_fields() {
	$color = mprm_get_option('checkout_color', 'inherit');
	$color = ($color == 'inherit') ? '' : $color;
	$style = mprm_get_option('button_style', 'button');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$show_register_form = mprm_get_option('show_register_form', 'none');
	ob_start(); ?>
	<fieldset id="mprm_login_fields">
		<?php if ($show_register_form == 'both') { ?>
			<p id="mprm-new-account-wrap">
				<?php _e('Need to create an account?', 'mp-restaurant-menu'); ?>
				<a href="<?php echo esc_url(remove_query_arg('login')); ?>" class="mprm_checkout_register_login" data-action="checkout_register">
					<?php _e('Register', 'mp-restaurant-menu');
					if (!mprm_is_no_guest_checkout()) {
						echo ' ' . __('or checkout as a guest.', 'mp-restaurant-menu');
					} ?>
				</a>
			</p>
		<?php } ?>
		<?php do_action('mprm_checkout_login_fields_before'); ?>
		<p id="mprm-user-login-wrap">
			<label class="mprm-label" for="mprm-username">
				<?php _e('Username', 'mp-restaurant-menu'); ?>
				<?php if (mprm_is_no_guest_checkout()) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="<?php if (mprm_is_no_guest_checkout()) {
				echo 'required ';
			} ?>mprm-input" type="text" name="mprm_user_login" id="mprm_user_login" value="" placeholder="<?php _e('Your username', 'mp-restaurant-menu'); ?>"/>
		</p>
		<p id="mprm-user-pass-wrap" class="mprm_login_password">
			<label class="mprm-label" for="mprm-password">
				<?php _e('Password', 'mp-restaurant-menu'); ?>
				<?php if (mprm_is_no_guest_checkout()) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="<?php if (mprm_is_no_guest_checkout()) {
				echo 'required ';
			} ?>mprm-input" type="password" name="mprm_user_pass" id="mprm_user_pass" placeholder="<?php _e('Your password', 'mp-restaurant-menu'); ?>"/>
			<?php if (mprm_is_no_guest_checkout()) : ?>
				<input type="hidden" name="mprm-purchase-var" value="needs-to-login"/>
			<?php endif; ?>
			<input type="hidden" name="redirect" value="<?php echo mprm_get_checkout_uri(); ?>"/>
			<input type="hidden" name="mprm_login_nonce" value="<?php echo wp_create_nonce('mprm-login-nonce'); ?>"/>

		</p>
		<p id="mprm-user-login-submit">
			<input type="submit" class="mprm-submit <?php echo $color; ?> <?php echo $style; ?> <?php echo $padding; ?>" name="mprm_login_submit" value="<?php _e('Login', 'mp-restaurant-menu'); ?>"/>
		</p>
		<?php do_action('mprm_checkout_login_fields_after'); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}

function mprm_register_account_fields_before() {
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
	$payment_methods = mprm_get_option('accepted_cards', array());
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
	$color = mprm_get_option('checkout_color', 'inherit');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$color = ($color == 'inherit') ? '' : $color;
	?>
	<input type="submit" name="mprm_update_cart_submit" class="mprm-submit mprm-no-js button<?php echo ' ' . $color . ' ' . $padding; ?>" value="<?php _e('Update Cart', 'mp-restaurant-menu'); ?>"/>
	<input type="hidden" name="mprm_action" value="update_cart"/>
	<?php
}

function mprm_save_cart_button() {
	if (mprm_is_cart_saving_disabled())
		return;
	$color = mprm_get_option('checkout_color', 'inherit');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$color = ($color == 'inherit') ? '' : $color;
	if (models\Cart::get_instance()->is_cart_saved()) : ?>
		<a class="mprm-cart-saving-button mprm-submit button<?php echo ' ' . $color . ' ' . $padding; ?>" id="mprm-restore-cart-button" href="<?php echo esc_url(add_query_arg(array('mprm_action' => 'restore_cart', 'mprm_cart_token' => models\Cart::get_instance()->get_cart_token()))); ?>"><?php _e('Restore Previous Cart', 'mp-restaurant-menu'); ?></a>
	<?php endif; ?>
	<a class="mprm-cart-saving-button mprm-submit button<?php echo ' ' . $color . ' ' . $padding; ?>" id="mprm-save-cart-button" href="<?php echo esc_url(add_query_arg('mprm_action', 'save_cart')); ?>"><?php _e('Save Cart', 'mp-restaurant-menu'); ?></a>
	<?php
}

function mprm_user_info_fields() {
	$customer = models\Customer::get_instance()->get_session_customer();
	mprm_get_template('/shop/user-info-fields', array('customer' => $customer));
}

function mprm_purchase_form_before_register_login() {
}

function mprm_purchase_form_top() { ?>
	<?php
	$class = '';
	if (models\Settings::get_instance()->is_ajax_disabled()) {
		$class = 'mprm-no-js';
	} ?>

	<form id="mprm_purchase_form" class="mprm_form <?php echo $class ?>"  method="POST">
	<?php
}

function mprm_purchase_form_bottom() {
	?>
	</form>
	<?php
}

function mprm_purchase_form_before_email() {
}

function mprm_purchase_form_after_email() {
}

function mprm_purchase_form_user_info() {
}

function mprm_purchase_form_user_info_fields() {
}

function mprm_filter_where_older_than_week($where = '') {
	// Payments older than one week
	$start = date('Y-m-d', strtotime('-7 days'));
	$where .= " AND post_date <= '{$start}'";
	return $where;
}

function mprm_increase_purchase_count($menu_item_id = 0, $quantity = 1) {
	$quantity = (int)$quantity;
	$menu_item = new models\Menu_item($menu_item_id);
	return $menu_item->increase_sales($quantity);
}

function mprm_currency_filter($price = '', $currency = '') {
	return models\Menu_item::get_instance()->currency_filter($price, $currency);
}

function mprm_format_amount($amount, $decimals = true) {
	return models\Formatting::get_instance()->format_amount($amount, $decimals);
}

function mprm_get_payment_amount($payment_id) {
	return models\Payments::get_instance()->get_payment_amount($payment_id);
}

function mprm_get_label_plural($lowercase = false) {
	return models\Menu_item::get_instance()->get_label($lowercase, 'plural');
}

function mprm_get_label_singular($lowercase = false) {
	return models\Menu_item::get_instance()->get_label($lowercase, 'singular');
}

function mprm_is_success_page() {
	return models\Checkout::get_instance()->is_success_page();
}

function mprm_filter_success_page_content($content) {
	if (isset($_GET['payment-confirmation']) && mprm_is_success_page()) {
		if (has_filter('mprm_payment_confirm_' . $_GET['payment-confirmation'])) {
			$content = apply_filters('mprm_payment_confirm_' . $_GET['payment-confirmation'], $content);
		}
	}
	return $content;
}

function mprm_get_plugin_version() {
	return Core::get_instance()->get_version();
}

function mprm_get_success_page_uri() {
	$page_id = mprm_get_option('success_page', 0);
	$page_id = absint($page_id);
	return apply_filters('mprm_get_success_page_uri', get_permalink($page_id));
}

function mprm_get_payment_key($payment_id = 0) {
	return models\Payments::get_instance()->get_payment_key($payment_id);
}

function mprm_get_payment_number($payment_id = 0) {
	return models\Payments::get_instance()->get_payment_number($payment_id);
}

function mprm_get_payment_status($payment, $return_label = false) {
	return models\Payments::get_instance()->get_payment_status($payment, $return_label);
}

function mprm_get_payment_meta($payment_id = 0, $meta_key = '_mprm_order_meta', $single = true) {
	return models\Payments::get_instance()->get_payment_meta($payment_id, $meta_key, $single);
}

function mprm_get_users_purchases($user = 0, $number = 20, $pagination = false, $status = 'mprm-complete') {
	return models\Customer::get_instance()->get_users_purchases($user, $number, $pagination, $status);
}

function mprm_item_quantities_enabled() {
	return models\Cart::get_instance()->item_quantities_enabled();
}

function mprm_user_pending_verification($user_id = 0) {
	return models\Customer::get_instance()->user_pending_verification($user_id);
}

function mprm_is_bundled_product($menu_item_id = 0) {
	$menu_item = new models\Menu_item($menu_item_id);
	return $menu_item->is_bundled_menu_item();
}

function mprm_count_purchases_of_customer() {
	if (empty($user)) {
		$user = get_current_user_id();
	}
	$stats = !empty($user) ? models\Customer::get_instance()->get_purchase_stats_by_user($user) : false;
	return isset($stats['purchases']) ? $stats['purchases'] : 0;
}

function mprm_get_user_verification_request_url($user_id = 0) {
	return models\Customer::get_instance()->get_user_verification_request_url($user_id);
}

function mprm_get_payment_statuses() {
	return models\Payments::get_instance()->get_payment_statuses();
}

/**
 * @param WP_Post $post
 *
 * @return models\Order
 */
function mprm_get_order_object(\WP_Post $post) {
	return new models\Order($post->ID);
}

function mprm_use_taxes() {
	return models\Taxes::get_instance()->use_taxes();
}

function mprm_currency_symbol($currency = '') {
	return models\Settings::get_instance()->get_currency_symbol($currency);
}

function mprm_get_payment_meta_user_info($order_id) {
	return models\Payments::get_instance()->get_payment_meta_user_info($order_id);
}

function mprm_get_gateway_admin_label($gateway) {
	return models\Gateways::get_instance()->get_gateway_admin_label($gateway);
}

function mprm_get_cart_items() {
	return models\Cart::get_instance()->get_cart_contents();
}

function mprm_get_menu_item_final_price($menu_item_id = 0, $user_purchase_info, $amount_override = null) {
	return models\Menu_item::get_instance()->get_final_price($menu_item_id, $user_purchase_info, $amount_override);
}

function mprm_has_variable_prices($menu_item_id) {
	return models\Menu_item::get_instance()->has_variable_prices($menu_item_id);
}

function mprm_get_price_option_name($menu_item_id = 0, $price_id = 0, $payment_id = 0) {
	return models\Menu_item::get_instance()->get_price_option_name($menu_item_id, $price_id, $payment_id);
}

function mprm_is_payment_complete($payment_id) {
	return models\Payments::get_instance()->is_payment_complete($payment_id);
}

function mprm_clean($var) {
	return is_array($var) ? array_map('mprm_clean', $var) : sanitize_text_field($var);
}

function mprm_menu_item_dropdown($data) {
	$menu_items = mprm_get_menu_items(array('orderby' => 'title', 'order' => 'ASC', 'post_type' => 'mp_menu_item'));

	if ($menu_items) {
		$options[0] = sprintf(__('Select a %s', 'mp-restaurant-menu'), mprm_get_label_singular());
		foreach ($menu_items as $product) {
			$options[absint($product->ID)] = esc_html($product->post_title);
		}
	} else {
		$options[0] = __('No products found', 'mp-restaurant-menu');
	}
	$data['options'] = $options;
	$data['show_option_all'] = false;
	$data['show_option_none'] = false;
	$data['placeholder'] = __('Select a Menu item', 'mp-restaurant-menu');

	return View::get_instance()->render_html('../admin/settings/select', $data, false);
}

function mprm_customers_dropdown($data) {
	$options = array(__('No customer attached', 'mp-restaurant-menu'));
	$argc = array(
		'name' => 'customer-id',
		'show_option_all' => false,
		'show_option_none' => false);
	$data = wp_parse_args($data, $argc);

	$customers = models\Customer::get_instance()->get_customers();
	if (!empty($customers)) {
		foreach ($customers as $customer) {
			$options[$customer->id] = $customer->name;
		}
	}
	$data['options'] = $options;
	$data['placeholder'] = __('Select a Customer', 'mp-restaurant-menu');

	return View::get_instance()->render_html('../admin/settings/select', $data, false);
}

function mprm_text($data) {
	return View::get_instance()->render_html('../admin/settings/text', array('args' => $data), false);
}

function mprn_select($data) {
	return View::get_instance()->render_html('../admin/settings/select', $data, false);
}

function mprm_sanitize_key($key) {
	return models\Formatting::get_instance()->sanitize_key($key);
}

function mprm_get_country_list() {
	return models\Settings::get_instance()->get_country_list();
}

function mprm_get_shop_states($country = null) {
	return models\Settings::get_instance()->get_shop_states($country);
}

function mprm_get_menu_items(array $args) {
	$menu_items = models\Menu_item::get_instance()->get_menu_items($args);
	return $menu_items[0]['posts'];
}

function mprm_get_payment_notes($payment_id = 0, $search = '') {
	return models\Payments::get_instance()->get_payment_notes($payment_id, $search);
}

function mprm_get_payment_note_html($note, $payment_id = 0) {
	return models\Payments::get_instance()->get_payment_note_html($note, $payment_id);
}

/**
 * @param $customer_id
 *
 * @return models\Customer
 */
function mprm_get_customer($customer_id) {
	return new models\Customer(array('field' => 'id', 'value' => $customer_id));
}

function mprm_sanitize_amount($amount) {
	return models\Formatting::get_instance()->sanitize_amount($amount);
}

function mprm_get_currency() {
	return models\Settings::get_instance()->get_currency();
}

/**
 * Get option by key
 *
 * @param $key
 * @param bool $default
 *
 * @return mixed|void
 */
function mprm_get_option($key, $default = false) {
	return models\Settings::get_instance()->get_option($key, $default);
}

function mprm_currency_decimal_filter() {
	return models\Formatting::get_instance()->currency_decimal_filter();
}

function mprm_get_purchase_history_page() {
	$history_page_url = get_permalink(mprm_get_option('purchase_history_page'));
	return empty($history_page_url) ? '' : $history_page_url;
}

function mprm_get_default_sale_notification_email() {

	$default_email_body = __('A new purchase has been made!', 'mp-restaurant-menu') . "\n\n";
	$default_email_body .= __('Purchased products:', 'mp-restaurant-menu') . "\n";
	$default_email_body .= '{menu_item_list}' . "\n\n";
	$default_email_body .= __('Purchased by: ', 'mp-restaurant-menu') . " " . "{fullname}" . "\n";
	$default_email_body .= __('Amount: ', 'mp-restaurant-menu') . " " . "{price}" . "\n";
	$default_email_body .= __('Payment Method: ', 'mp-restaurant-menu') . " " . "{payment_method}" . "\n\n";
	$default_email_body .= __('Thank you', 'mp-restaurant-menu');

	$message = mprm_get_option('sale_notification', false);
	$message = !empty($message) ? $message : $default_email_body;

	return $message;
}
