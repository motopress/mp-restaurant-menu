<?php do_action('mprm_before_additional_information'); ?>
	<fieldset id="mprm_additional_information_fields" class="mprm-do-validate">
		<span class="mprm-order-details-label"><legend><?php _e('Additional information', 'mp-restaurant-menu'); ?></legend></span>
		<?php $temp = mprm_get_option('shipping_address', false); ?>
		<?php if (mprm_get_option('shipping_address')): ?>
			<p id="mprm-address-wrap">
				<label for="phone_number" class="mprm-label">
					<?php _e('Shipping address:', 'mp-restaurant-menu'); ?>
					<span class="mprm-required-indicator">*</span>
					<span class="phone-type"></span>
				</label>
				<input type="text" name="shipping_address" value="" class="medium-text" required placeholder="<?php _e('Enter your address.', 'mp-restaurant-menu'); ?>"/>
			</p>
		<?php endif; ?>



		<p id="mprm-phone-number-wrap">
			<label for="customer_note" class="mprm-label">
				<?php _e('Order notes:', 'mp-restaurant-menu'); ?>
				<span class="phone-type"></span>
			</label>
			<textarea type="text" name="customer_note" id="customer_note" class="phone-number mprm-input"></textarea>
		</p>
	</fieldset>
<?php
do_action('mprm_after_additional_information');