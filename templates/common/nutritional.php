<?php
if (empty($nutritional)) {
	$nutritional = mprm_get_nutritional();
}
if ($nutritional) {
	?>
	<div class="mprm-nutrition">
		<?php if (is_single() && apply_filters('mprm-show-title-nutritional', (empty($mprm_title_nutritional) ? true : false))) : ?>
			<h3 class="mprm-title"><?php _e('Nutrition Facts', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>
		<ul class="mprm-list">
			<?php foreach ($nutritional as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<li class="mprm-nutrition-item"><?php echo mprm_get_nutrition_label(strtolower($info['title'])) . apply_filters('mprm-nutritional-delimiter', ': ') . $info['val']; ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}