<?php do_action('mprm_before_additional_information'); ?>
	<fieldset id="mprm_additional_information_fields" class="mprm-do-validate">
		<span><legend><?php _e('Additional information', 'mp-restaurant-menu'); ?></legend></span>
		<?php $temp = mprm_get_option('shipping_adress', true); ?>
		<?php if (mprm_get_option('shipping_adress', true)): ?>
			<p id="mprm-adress-wrap">
				<label for="phone_number" class="mprm-label">
					<?php _e('Shipping adress:', 'mp-restaurant-menu'); ?>
					<span class="mprm-required-indicator">*</span>
					<span class="phone-type"></span>
				</label>
				<span class="mprm-description"><?php _e('This is your primary Shipping adress.', 'mp-restaurant-menu'); ?></span>
				<input type="text" name="shipping_adress" value="" class="medium-text" required placeholder="<?php _e('Enter your adress.', 'mp-restaurant-menu'); ?>"/>
			</p>
		<?php endif; ?>

		<?php if (mprm_get_option('customer_phone', true)): ?>
			<p id="mprm-phone-number-wrap">
				<label for="phone_number" class="mprm-label">
					<?php _e('Phone Number:', 'mp-restaurant-menu'); ?>
					<span class="mprm-required-indicator">*</span>
					<span class="phone-type"></span>
				</label>
				<span class="mprm-description"><?php _e('This is your primary home phone', 'mp-restaurant-menu'); ?></span>
				<input type="text" autocomplete="off" name="phone_number" id="mprm_phone_number" class="mprm-phone-number mprm-input" required placeholder="<?php _e('Phone number', 'mp-restaurant-menu'); ?>"/>
			</p>
		<?php endif; ?>

		<p id="mprm-phone-number-wrap">
			<label for="customer_note" class="mprm-label">
				<?php _e('Customer note:', 'mp-restaurant-menu'); ?>
				<span class="phone-type"></span>
			</label>
			<span class="mprm-description"><?php _e('Order note', 'mp-restaurant-menu'); ?></span>
			<textarea type="text" name="customer_note" id="customer_note" class="phone-number mprm-input"></textarea>
		</p>
	</fieldset>
<?php
do_action('mprm_after_additional_information');