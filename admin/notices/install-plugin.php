<div class="notice is-dismissible notice-warning">
	<p><strong><?php _e('Restaurant Menu plugin', 'mp-restaurant-menu'); ?></strong></p>
	<p><?php _e('Checkout, Purchase History, Success and Fail pages are required to sell food and beverages online. Press "Install Pages" button to create these pages. Dismiss this notice if you have these pages installed.', 'mp-restaurant-menu'); ?></p>
	<p>
		<a href="<?php echo add_query_arg(array('controller' => 'settings', 'mprm_action' => 'create_pages'), admin_url('admin.php')); ?>" class="button-primary"><?php _e('Install Pages', 'mp-restaurant-menu'); ?></a>
		<a class="skip button" href="<?php echo add_query_arg(array('controller' => 'settings', 'mprm_action' => 'skip_create_pages'), admin_url('admin.php')); ?>"><?php _e('Skip', 'mp-restaurant-menu'); ?></a>
	</p>
</div>


