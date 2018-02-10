<?php
if (!empty($extensions)) {
	foreach ($extensions as $key => $extension) { ?>

		<div class="mprm-extension">
			<a href="<?php echo $extension->link ?>" title="<?php echo $extension->title ?>">
				<?php if ($extension->thumbnail): ?>
					<img width="320" height="200" src="<?php echo $extension->thumbnail ?>" class="attachment-showcase wp-post-image" alt="<?php echo $extension->title ?>" title="<?php echo $extension->title ?>">
				<?php endif; ?>
			</a>
			<p><b><?php echo $extension->title ?></b></p>
			<a href="<?php echo $extension->link ?>" title="<?php echo $extension->title ?>" class="button-secondary"><?php _e('Get this Extension', 'mp-restaurant-menu') ?></a>
		</div>

	<?php }
} ?>
