<?php
if (empty($attributes)) {
	$attributes = mprm_get_attributes();
}
if ($attributes) {
	?>
	<div class="mprm-proportions">
		<?php if (is_single()) : ?>
			<h3 class="mprm-title"><?php _e('Portion Size', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>
		<ul class="mprm-list">
			<?php foreach ($attributes as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<li class="mprm-proportion"><?php echo /*mprm_get_proportion_label(strtolower($info['title'])) . apply_filters('mprm-proportions-delimiter', ': ') . */
						$info['val']; ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}