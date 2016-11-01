<?php do_action('mprm_before_additional_information'); ?>
	<fieldset id="mprm_additional_information_fields" class="mprm-do-validate">
		<span class="mprm-order-details-label"><legend><?php _e('Additional information', 'mp-restaurant-menu'); ?></legend></span>
		<?php do_action('mprm_checkout_additional_information') ?>
	</fieldset>
<?php do_action('mprm_after_additional_information');