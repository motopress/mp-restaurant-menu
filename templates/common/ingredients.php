<?php
if (empty($ingredients)) {
	$ingredients = mprm_get_ingredients();
}
if ($ingredients) { ?>
	<div class="mprm-ingredients">
		<?php if (is_single() && apply_filters('mprm-show-title-ingredients', (empty($mprm_title_ingredients) ? true : false))) : ?>
			<h3 class="mprm-title"><?php _e('Ingredients', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>

		<p class="mprm-list">
			<?php foreach ($ingredients as $ingredient):
				if (!is_object($ingredient)) {
					continue;
				} ?>
				<span class="mprm-ingredient"><?php echo $ingredient->name ?></span>
				<span class="mprm-ingredients-delimiter"><?php echo apply_filters('mprm_ingredients_delimiter', '/'); ?></span>
			<?php endforeach; ?>
		</p>

	</div>
	<?php
}