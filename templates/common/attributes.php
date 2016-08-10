<?php
if (empty($attributes)) {
	$attributes = mprm_get_attributes();
}
if ($attributes) { ?>
	<div class="mprm-proportions">
		<?php if (is_single() && apply_filters('mprm-show-title-attributes', (empty($mprm_title_attributes) ? true : false))) : ?>
			<h3 class="mprm-title"><p><?php _e('Portion Size', 'mp-restaurant-menu') ?></p></h3>
		<?php endif; ?>
		<ul class="mprm-list">
			<?php foreach ($attributes as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<li class="mprm-proportion"><?php echo $info['val']; ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}