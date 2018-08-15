<?php
if (!empty($extensions)) {
	foreach ($extensions as $key => $extension) { 

		$extension_link = add_query_arg( array(
			'utm_source' => 'restaurant-menu-customer-website-dashboard',
			'utm_medium' => 'textlink-in-dashboard',
			'utm_campaign' => 'restaurant-menu-addons',
		), $extension->link );
		
		?><div class="mprm-extension">
			<a href="<?php echo esc_url($extension_link) ?>" title="<?php echo $extension->title ?>">
				<?php if ($extension->thumbnail): ?>
					<img width="320" height="200" src="<?php echo $extension->thumbnail ?>" class="attachment-showcase wp-post-image" alt="<?php echo $extension->title ?>" title="<?php echo $extension->title ?>">
				<?php endif; ?>
			</a>
			<h3><?php echo esc_html($extension->title) ?></h3>
			<p><?php echo esc_html($extension->excerpt); ?></p>
			<a href="<?php echo esc_url($extension_link) ?>" title="<?php echo $extension->title ?>" class="button button-secondary"><?php _e('Get this Extension', 'mp-restaurant-menu') ?></a>
		</div><?php }
} ?>
