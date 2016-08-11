<?php
if (empty($attributes)) {
	$attributes = mprm_get_attributes();
}
if ($attributes) { ?>
	<div class="mprm-proportions">
		<?php if (is_single() && apply_filters('mprm-show-title-attributes', (empty($mprm_title_attributes) ? true : false))) : ?>
			<h3 class="mprm-title"><?php _e('Portion Size', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>
		<p class="mprm-list">
			<?php foreach ($attributes as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<span class="mprm-proportion"><?php echo $info['val']; ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</p>
	</div>
	<?php
}