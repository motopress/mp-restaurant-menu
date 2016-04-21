<?php
ob_start(); ?>
<?php do_action('edd_before_cc_fields'); ?>
	<fieldset id="edd_cc_fields" class="edd-do-validate">
		<span><legend><?php _e('Credit Card Info', 'easy-digital-downloads'); ?></legend></span>
		<?php if (is_ssl()) : ?>
			<div id="edd_secure_site_wrapper">
				<span class="padlock"></span>
				<span><?php _e('This is a secure SSL encrypted payment.', 'easy-digital-downloads'); ?></span>
			</div>
		<?php endif; ?>
		<p id="edd-card-number-wrap">
			<label for="card_number" class="edd-label">
				<?php _e('Card Number', 'easy-digital-downloads'); ?>
				<span class="edd-required-indicator">*</span>
				<span class="card-type"></span>
			</label>
			<span class="edd-description"><?php _e('The (typically) 16 digits on the front of your credit card.', 'easy-digital-downloads'); ?></span>
			<input type="text" autocomplete="off" name="card_number" id="card_number" class="card-number edd-input required" placeholder="<?php _e('Card number', 'easy-digital-downloads'); ?>"/>
		</p>
		<p id="edd-card-cvc-wrap">
			<label for="card_cvc" class="edd-label">
				<?php _e('CVC', 'easy-digital-downloads'); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e('The 3 digit (back) or 4 digit (front) value on your card.', 'easy-digital-downloads'); ?></span>
			<input type="text" size="4" maxlength="4" autocomplete="off" name="card_cvc" id="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e('Security code', 'easy-digital-downloads'); ?>"/>
		</p>
		<p id="edd-card-name-wrap">
			<label for="card_name" class="edd-label">
				<?php _e('Name on the Card', 'easy-digital-downloads'); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e('The name printed on the front of your credit card.', 'easy-digital-downloads'); ?></span>
			<input type="text" autocomplete="off" name="card_name" id="card_name" class="card-name edd-input required" placeholder="<?php _e('Card name', 'easy-digital-downloads'); ?>"/>
		</p>
		<?php do_action('edd_before_cc_expiration'); ?>
		<p class="card-expiration">
			<label for="card_exp_month" class="edd-label">
				<?php _e('Expiration (MM/YY)', 'easy-digital-downloads'); ?>
				<span class="edd-required-indicator">*</span>
			</label>
			<span class="edd-description"><?php _e('The date your credit card expires, typically on the front of the card.', 'easy-digital-downloads'); ?></span>
			<select id="card_exp_month" name="card_exp_month" class="card-expiry-month edd-select edd-select-small required">
				<?php for ($i = 1; $i <= 12; $i++) {
					echo '<option value="' . $i . '">' . sprintf('%02d', $i) . '</option>';
				} ?>
			</select>
			<span class="exp-divider"> / </span>
			<select id="card_exp_year" name="card_exp_year" class="card-expiry-year edd-select edd-select-small required">
				<?php for ($i = date('Y'); $i <= date('Y') + 30; $i++) {
					echo '<option value="' . $i . '">' . substr($i, 2) . '</option>';
				} ?>
			</select>
		</p>
		<?php do_action('edd_after_cc_expiration'); ?>
	</fieldset>
<?php
do_action('edd_after_cc_fields');
echo ob_get_clean();