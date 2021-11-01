<div class="notice is-dismissible notice-warning">
	<p><strong><?php esc_html_e('Restaurant Menu Plugin - eCommerce Setup', 'mp-restaurant-menu'); ?></strong></p>
	<p><?php esc_html_e('Press "Activate eCommerce" button to activate eCommerce option and create Checkout pages. Press "Skip" if you do not want to sell food and beverages online.', 'mp-restaurant-menu'); ?></p>
	<p>
		<a href="<?php echo esc_url( add_query_arg(array('controller' => 'settings', 'mprm_action' => 'create_pages'), admin_url('admin.php')) ); ?>" class="button-primary"><?php esc_html_e('Activate eCommerce', 'mp-restaurant-menu'); ?></a>
		<a class="skip button" href="<?php echo esc_url( add_query_arg(array('controller' => 'settings', 'mprm_action' => 'skip_create_pages'), admin_url('admin.php')) ); ?>"><?php esc_html_e('Skip', 'mp-restaurant-menu'); ?></a>
	</p>
</div>


