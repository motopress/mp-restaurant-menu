<?php
if (empty($nutritional)) {
	$nutritional = mprm_get_nutritional();
}

$template_mode = mprm_get_template_mode();
$template_mode_class = ($template_mode == "theme") ? 'mprm-content-container' : '';

if ($nutritional) { ?>
	<div class="mprm-nutrition <?php echo $template_mode_class ?>">
		<?php if (is_single() && apply_filters('mprm-show-title-nutritional', (empty($mprm_title_nutritional) ? true : false))) : ?>
			<h3 class="mprm-title"><?php _e('Nutrition Facts', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>
		<?php if ($template_mode == "theme") { ?>
			<?php foreach ($nutritional as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<span class="mprm-nutrition-item"><?php echo mprm_get_nutrition_label(strtolower($info['title'])) . apply_filters('mprm-nutritional-delimiter', ': ') . $info['val']; ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php } else { ?>
			<ul class="mprm-list">
				<?php foreach ($nutritional as $info): ?>
					<?php if (!empty($info['val'])): ?>
						<li class="mprm-nutrition-item"><?php echo mprm_get_nutrition_label(strtolower($info['title'])) . apply_filters('mprm-nutritional-delimiter', ': ') . $info['val']; ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php } ?>
	</div>
	<?php
}