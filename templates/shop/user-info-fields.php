<?php use mp_restaurant_menu\classes\models; ?>
	<fieldset id="mprm_checkout_user_info">
		<span class="mprm-payment-details-label"><legend><?php echo apply_filters('mprm_checkout_personal_info_text', __('Billing Details', 'mp-restaurant-menu')); ?></legend></span>
		<?php do_action('mprm_purchase_form_before_email'); ?>
		<p id="mprm-email-wrap">
			<label class="mprm-label" for="mprm-email">
				<?php _e('Email Address', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_email')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="mprm-input mprm-required" type="email" name="mprm_email" placeholder="<?php _e('Email address', 'mp-restaurant-menu'); ?>" id="mprm-email" value="<?php echo esc_attr($customer['email']); ?>"
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_email')) {
					echo ' required ';
				} ?>/>
		</p>

		<?php if (mprm_get_option('customer_phone')): ?>
			<p id="mprm-phone-number-wrap">
				<label for="phone_number" class="mprm-label">
					<?php _e('Phone Number', 'mp-restaurant-menu'); ?>
					<span class="mprm-required-indicator">*</span>
					<span class="phone-type"></span>
				</label>
				<input type="text" name="phone_number" id="mprm_phone_number" class="mprm-phone-number mprm-input" required placeholder="<?php _e('Phone number', 'mp-restaurant-menu'); ?>"/>
			</p>
		<?php endif; ?>

		<?php do_action('mprm_purchase_form_after_email'); ?>
		<p id="mprm-first-name-wrap">
			<label class="mprm-label" for="mprm-first">
				<?php _e('First Name', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_first')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="mprm-input mprm-required" type="text" name="mprm_first" placeholder="<?php _e('First name', 'mp-restaurant-menu'); ?>" id="mprm-first" value="<?php echo esc_attr($customer['first_name']); ?>"
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_first')) {
					echo ' required ';
				} ?>/>
		</p>

		<p id="mprm-last-name-wrap">
			<label class="mprm-label" for="mprm-last">
				<?php _e('Last Name', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_last')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<input class="mprm-input<?php if (models\Checkout::get_instance()->field_is_required('mprm_last')) {
				echo ' required';
			} ?>" type="text" name="mprm_last" id="mprm-last" placeholder="<?php _e('Last name', 'mp-restaurant-menu'); ?>" value="<?php echo esc_attr($customer['last_name']); ?>"
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_last')) {
					echo ' required ';
				} ?>/>
		</p>
		<?php do_action('mprm_purchase_form_user_info'); ?>
		<?php do_action('mprm_purchase_form_user_info_fields'); ?>
	</fieldset>
<?php
