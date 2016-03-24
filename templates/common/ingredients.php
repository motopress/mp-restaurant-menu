<?php
if (empty($ingredients)) {
	$ingredients = mprm_get_ingredients();
}
if ($ingredients) {
	?>
	<div class="mprm-ingredients">
		<?php if (is_single()) : ?>
			<h3 class="mprm-title"><?php _e('Ingredients', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>
		<ul class="mprm-list">
			<?php foreach ($ingredients as $ingredient): ?>
				<li class="mprm-ingredient"><?php echo $ingredient->name ?></li>
				<li class="mprm-ingredients-delimiter"><?php echo apply_filters('mprm_ingredients_delimiter', '/'); ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="mprm-clear"></div>
	</div>
	<?php
}