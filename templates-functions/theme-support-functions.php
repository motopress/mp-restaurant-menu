<?php

/**
 * Price template part
 */
function get_price_theme_view() {
	$price = mprm_get_price();
	if (!empty($price)) { ?>
		<p><?php _e('Price', 'mp-restaurant-menu'); ?>: <span><b><?php echo mprm_currency_filter(mprm_format_amount($price)) ?></b></span></p>
	<?php }
}

/**
 * Ingredients template part
 */
function get_ingredients_theme_view() {
	$ingredients = mprm_get_ingredients(); ?>
	<?php if (!empty($ingredients)) { ?>
		<div class="mprm-ingredients mprm-content-container">
			<div class="mprm-content-container mprm-title-big"><b><?php _e('Ingredients', 'mp-restaurant-menu'); ?></b></div>
			<?php foreach ($ingredients as $ingredient):
				if (!is_object($ingredient)) {
					continue;
				} ?>
				<span class="mprm-ingredient"><?php echo $ingredient->name ?></span>
				<span class="mprm-ingredients-delimiter"><?php echo apply_filters('mprm_ingredients_delimiter', '/'); ?></span>
			<?php endforeach; ?>
		</div>
	<?php }
}

/**
 * Attributes template part
 */
function get_attributes_theme_view() {
	$attributes = mprm_get_attributes();
	if ($attributes) { ?>
		<div class="mprm-proportions mprm-content-container">
			<div class="mprm-content-container mprm-title-big"><b><?php _e('Portion Size', 'mp-restaurant-menu'); ?></b></div>
			<?php foreach ($attributes as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<div class="mprm-proportion"><?php echo $info['val']; ?></div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
}

/**
 * Nutritional template part
 */
function get_nutritional_theme_view() {
	$nutritional = mprm_get_nutritional();
	if (!empty($nutritional)) { ?>
		<div class="mprm-nutrition mprm-content-container">
			<div class="mprm-content-container mprm-title-big"><b><?php _e('Nutritional', 'mp-restaurant-menu'); ?></b></div>
			<?php foreach ($nutritional as $info): ?>
				<?php if (!empty($info['val'])): ?>
					<span class="mprm-nutrition-item"><?php echo mprm_get_nutrition_label(strtolower($info['title'])) . apply_filters('mprm-nutritional-delimiter', ': ') . $info['val']; ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php }
}

/**
 * Related items template part
 */
function get_related_items_theme_view() {
	$related_items = mprm_get_related_items();
	if (!empty($related_items)) { ?>
		<div class="mprm-related-items">
			<h3><?php _e('You might also like', 'mp-restaurant-menu'); ?></h3>
			<p>
				<?php foreach ($related_items as $related_item) { ?>
					<span>
						<a href="<?php echo get_permalink($related_item) ?>" title="<?php echo get_the_title($related_item) ?>">
							<?php
							if (has_post_thumbnail($related_item)) {
								echo wp_get_attachment_image(get_post_thumbnail_id($related_item), apply_filters('mprm-related-item-image-size', 'thumbnail'));
							} else { ?>
								<span><?php echo get_the_title($related_item) ?></span>
							<?php } ?>
						</a>
					</span>
				<?php } ?>
			</p>
		</div>
	<?php }
}

/**
 * Gallery template part
 */
function get_gallery_theme_view() {
	$gallery = mprm_get_gallery();
	if (!empty($gallery)) {
		$args = apply_filters('mprm-gallery-settings', array('ids' => $gallery, 'link' => 'file', 'columns' => '3'));
		echo gallery_shortcode($args);
	}
}

/**
 * @return mixed|string|void
 */
function mprm_get_template_mode() {
	return \mp_restaurant_menu\classes\Media::get_instance()->template_mode();
}