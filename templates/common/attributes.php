<?php
if (empty($attributes)) {
	$attributes = mprm_get_attributes();
}
$template_mode = mprm_get_template_mode();
$template_mode_class = ($template_mode == "theme") ? 'mprm-content-container' : '';

if ($attributes) { ?>
	<div class="mprm-proportions <?php echo $template_mode_class?>">
		<?php if (is_single() && apply_filters('mprm-show-title-attributes', (empty($mprm_title_attributes) ? true : false))) : ?>
			<h3 class="mprm-title"><?php _e('Portion Size', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>
		<?php if ($template_mode == "theme") {
			foreach ($attributes as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<div class="mprm-proportion"><?php echo $info['val']; ?></div>
				<?php endif; ?>
			<?php endforeach;
		} else { ?>
			<ul class="mprm-list">
				<?php foreach ($attributes as $info): ?>
					<?php if (!empty($info['val'])): ?>
						<li class="mprm-proportion"><?php echo $info['val']; ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php } ?>
	</div>
	<?php
}