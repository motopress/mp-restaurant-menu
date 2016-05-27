<div class="notice is-dismissible notice-warning">
	<p>
		<a href="<?php echo add_query_arg(array('controller' => 'settings', 'mprm_action' => 'create_pages'), admin_url('admin.php')); ?>" class="button-primary"><?php _e('Install MotoPress Restaurant Menu Pages', 'mp-restaurant-menu'); ?></a>
		<a class="skip button-primary" href="<?php echo add_query_arg(array('controller' => 'settings', 'mprm_action' => 'skip_create_pages'), admin_url('admin.php')); ?>"><?php _e('Skip setup', 'mp-restaurant-menu'); ?></a>
	</p>
</div>


