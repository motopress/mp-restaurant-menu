<div class="mprm-notice mprm-hidden">
	<div class="mprm-success">
		<span class="mprm-text mprm-notice-text"><span class="mprm-notice-title">“<?php echo esc_html( get_the_title($menu_item_id) ); ?>”</span>
			<span class="mprm-notice-text"><?php esc_html_e('has been added to your cart.', 'mp-restaurant-menu') ?></span></span>
		<span class="mprm-notice-actions"><a href="<?php echo esc_attr(mprm_get_checkout_uri()) ?>" class=""><?php esc_html_e('View cart', 'mp-restaurant-menu') ?></a></span>
	</div>
	<div class="mprm-error">
		<span class="mprm-notice-text"><?php esc_html_e('An error occurred. Please try again later.', 'mp-restaurant-menu') ?></span>
	</div>
</div>