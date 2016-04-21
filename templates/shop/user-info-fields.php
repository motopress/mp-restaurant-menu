<?php
use \mp_restaurant_menu\classes\models;

?>
	<fieldset id="mprm_checkout_user_info">
		<span><legend><?php echo apply_filters('mprm_checkout_personal_info_text', __('Personal Info', 'mp-restaurant-menu')); ?></legend></span>
		<?php do_action('mprm_purchase_form_before_email'); ?>
		<p id="mprm-email-wrap">
			<label class="mprm-label" for="mprm-email">
				<?php _e('Email Address', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_email')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('We will send the purchase receipt to this address.', 'mp-restaurant-menu'); ?></span>
			<input class="mprm-input required" type="email" name="mprm_email" placeholder="<?php _e('Email address', 'mp-restaurant-menu'); ?>" id="mprm-email" value="<?php echo esc_attr($customer['email']); ?>"/>
		</p>
		<?php do_action('mprm_purchase_form_after_email'); ?>
		<p id="mprm-first-name-wrap">
			<label class="mprm-label" for="mprm-first">
				<?php _e('First Name', 'mp-restaurant-menu'); ?>
				<?php if (models\Checkout::get_instance()->field_is_required('mprm_first')) { ?>
					<span class="mprm-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="mprm-description"><?php _e('We will use this to personalize your account experience.', 'mp-restaurant-menu'); ?></span>
			<input class="mprm-input required" type="text" name="mprm_first" placeholder="<?php _e('First name', 'mp-restaurant-menu'); ?>" id="mprm-first" value="<?php echo esc_attr($customer['first_name']); ?>"
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
			<span class="mprm-description"><?php _e('We will use this as well to personalize your account experience.', 'mp-restaurant-menu'); ?></span>
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
