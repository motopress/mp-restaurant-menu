<?php
if (empty($ingredients)) {
	$ingredients = mprm_get_ingredients();
}
$template_mode = mprm_get_template_mode();
$template_mode_class = ($template_mode == "theme") ? 'mprm-content-container' : '';

if ($ingredients) { ?>
	<div class="mprm-ingredients <?php echo esc_attr( $template_mode_class ); ?>">
		<?php if (is_single() && apply_filters('mprm-show-title-ingredients', (empty($mprm_title_ingredients) ? true : false))) : ?>
			<h3 class="mprm-title"><?php esc_html_e('Ingredients', 'mp-restaurant-menu') ?></h3>
		<?php endif; ?>
		<?php if ($template_mode == "theme") {
			foreach ($ingredients as $ingredient):
				if (!is_object($ingredient)) {
					continue;
				} ?>
				<span class="mprm-ingredient"><?php echo esc_html( $ingredient->name );?></span>
				<span class="mprm-ingredients-delimiter"><?php echo esc_html( apply_filters('mprm_ingredients_delimiter', '/') ); ?></span>
			<?php endforeach;
		} else { ?>
			<ul class="mprm-list">
				<?php foreach ($ingredients as $ingredient):
					if (!is_object($ingredient)) {
						continue;
					} ?>
					<li class="mprm-ingredient"><?php echo esc_html( $ingredient->name );?></li>
					<li class="mprm-ingredients-delimiter"><?php echo esc_html( apply_filters('mprm_ingredients_delimiter', '/') ); ?></li>
				<?php endforeach; ?>
			</ul>
			<div class="mprm-clear"></div>
		<?php } ?>
	</div>
	<?php
}