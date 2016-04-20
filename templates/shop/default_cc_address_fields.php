<?php
use \mp_restaurant_menu\classes\models;

function edd_default_cc_address_fields() {

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

		$user_address = get_user_meta(get_current_user_id(), '_edd_user_address', true);

		foreach ($customer['address'] as $key => $field) {

			if (empty($field) && !empty($user_address[$key])) {
				$customer['address'][$key] = $user_address[$key];
			} else {
				$customer['address'][$key] = '';
			}

		}

	}

	ob_start(); ?>
	<fieldset id="edd_cc_address" class="cc-address">
		<span><legend><?php _e('Billing Details', 'easy-digital-downloads'); ?></legend></span>
		<?php do_action('edd_cc_billing_top'); ?>
		<p id="edd-card-address-wrap">
			<label for="card_address" class="edd-label">
				<?php _e('Billing Address', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('card_address')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('The primary billing address for your credit card.', 'easy-digital-downloads'); ?></span>
			<input type="text" id="card_address" name="card_address" class="card-address edd-input<?php if (edd_field_is_required('card_address')) {
				echo ' required';
			} ?>" placeholder="<?php _e('Address line 1', 'easy-digital-downloads'); ?>" value="<?php echo $customer['address']['line1']; ?>"<?php if (edd_field_is_required('card_address')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="edd-card-address-2-wrap">
			<label for="card_address_2" class="edd-label">
				<?php _e('Billing Address Line 2 (optional)', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('card_address_2')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('The suite, apt no, PO box, etc, associated with your billing address.', 'easy-digital-downloads'); ?></span>
			<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 edd-input<?php if (edd_field_is_required('card_address_2')) {
				echo ' required';
			} ?>" placeholder="<?php _e('Address line 2', 'easy-digital-downloads'); ?>" value="<?php echo $customer['address']['line2']; ?>"<?php if (edd_field_is_required('card_address_2')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="edd-card-city-wrap">
			<label for="card_city" class="edd-label">
				<?php _e('Billing City', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('card_city')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('The city for your billing address.', 'easy-digital-downloads'); ?></span>
			<input type="text" id="card_city" name="card_city" class="card-city edd-input<?php if (edd_field_is_required('card_city')) {
				echo ' required';
			} ?>" placeholder="<?php _e('City', 'easy-digital-downloads'); ?>" value="<?php echo $customer['address']['city']; ?>"<?php if (edd_field_is_required('card_city')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="edd-card-zip-wrap">
			<label for="card_zip" class="edd-label">
				<?php _e('Billing Zip / Postal Code', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('card_zip')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('The zip or postal code for your billing address.', 'easy-digital-downloads'); ?></span>
			<input type="text" size="4" name="card_zip" class="card-zip edd-input<?php if (edd_field_is_required('card_zip')) {
				echo ' required';
			} ?>" placeholder="<?php _e('Zip / Postal Code', 'easy-digital-downloads'); ?>" value="<?php echo $customer['address']['zip']; ?>"<?php if (edd_field_is_required('card_zip')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="edd-card-country-wrap">
			<label for="billing_country" class="edd-label">
				<?php _e('Billing Country', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('billing_country')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('The country for your billing address.', 'easy-digital-downloads'); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country edd-select<?php if (edd_field_is_required('billing_country')) {
				echo ' required';
			} ?>"<?php if (edd_field_is_required('billing_country')) {
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
		<p id="edd-card-state-wrap">
			<label for="card_state" class="edd-label">
				<?php _e('Billing State / Province', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('card_state')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('The state or province for your billing address.', 'easy-digital-downloads'); ?></span>
			<?php
			$selected_state = models\Settings::get_instance()->get_shop_state();
			$states = models\Settings::get_instance()->get_shop_states($selected_country);

			if (!empty($customer['address']['state'])) {
				$selected_state = $customer['address']['state'];
			}

			if (!empty($states)) : ?>
				<select name="card_state" id="card_state" class="card_state edd-select<?php if (edd_field_is_required('card_state')) {
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
				<input type="text" size="6" name="card_state" id="card_state" class="card_state edd-input" value="<?php echo esc_attr($customer_state); ?>" placeholder="<?php _e('State / Province', 'easy-digital-downloads'); ?>"/>
			<?php endif; ?>
		</p>
		<?php do_action('edd_cc_billing_bottom'); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}

///add_action('edd_after_cc_fields', 'edd_default_cc_address_fields');