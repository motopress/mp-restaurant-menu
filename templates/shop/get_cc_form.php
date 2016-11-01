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
