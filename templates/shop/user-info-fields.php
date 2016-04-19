<?php
$customer = EDD()->session->get('customer');
$customer = wp_parse_args($customer, array('first_name' => '', 'last_name' => '', 'email' => ''));

if (is_user_logged_in()) {
	$user_data = get_userdata(get_current_user_id());
	foreach ($customer as $key => $field) {

		if ('email' == $key && empty($field)) {
			$customer[$key] = $user_data->user_email;
		} elseif (empty($field)) {
			$customer[$key] = $user_data->$key;
		}

	}
}

$customer = array_map('sanitize_text_field', $customer);
?>
	<fieldset id="edd_checkout_user_info">
		<span><legend><?php echo apply_filters('edd_checkout_personal_info_text', __('Personal Info', 'easy-digital-downloads')); ?></legend></span>
		<?php do_action('edd_purchase_form_before_email'); ?>
		<p id="edd-email-wrap">
			<label class="edd-label" for="edd-email">
				<?php _e('Email Address', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('edd_email')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('We will send the purchase receipt to this address.', 'easy-digital-downloads'); ?></span>
			<input class="edd-input required" type="email" name="edd_email" placeholder="<?php _e('Email address', 'easy-digital-downloads'); ?>" id="edd-email" value="<?php echo esc_attr($customer['email']); ?>"/>
		</p>
		<?php do_action('edd_purchase_form_after_email'); ?>
		<p id="edd-first-name-wrap">
			<label class="edd-label" for="edd-first">
				<?php _e('First Name', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('edd_first')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('We will use this to personalize your account experience.', 'easy-digital-downloads'); ?></span>
			<input class="edd-input required" type="text" name="edd_first" placeholder="<?php _e('First name', 'easy-digital-downloads'); ?>" id="edd-first" value="<?php echo esc_attr($customer['first_name']); ?>"<?php if (edd_field_is_required('edd_first')) {
				echo ' required ';
			} ?>/>
		</p>
		<p id="edd-last-name-wrap">
			<label class="edd-label" for="edd-last">
				<?php _e('Last Name', 'easy-digital-downloads'); ?>
				<?php if (edd_field_is_required('edd_last')) { ?>
					<span class="edd-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="edd-description"><?php _e('We will use this as well to personalize your account experience.', 'easy-digital-downloads'); ?></span>
			<input class="edd-input<?php if (edd_field_is_required('edd_last')) {
				echo ' required';
			} ?>" type="text" name="edd_last" id="edd-last" placeholder="<?php _e('Last name', 'easy-digital-downloads'); ?>" value="<?php echo esc_attr($customer['last_name']); ?>"<?php if (edd_field_is_required('edd_last')) {
				echo ' required ';
			} ?>/>
		</p>
		<?php do_action('edd_purchase_form_user_info'); ?>
		<?php do_action('edd_purchase_form_user_info_fields'); ?>
	</fieldset>
<?php

add_action('edd_purchase_form_after_user_info', 'edd_user_info_fields');
add_action('edd_register_fields_before', 'edd_user_info_fields');