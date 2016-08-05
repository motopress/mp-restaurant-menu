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
	$ingredients = mprm_get_ingredients();
	if (!empty($ingredients)) { ?>
		<h3 class=" "><?php _e('Ingredients', 'mp-restaurant-menu'); ?></h3>
		<ul>
			<?php foreach ($ingredients as $ingredient) { ?>
				<li>
					<?php echo $ingredient->name ?>
				</li>
			<?php } ?>
		</ul>
	<?php }
}

/**
 * Nutritional template part
 */
function get_nutritional_theme_view() {
	$nutritional = mprm_get_nutritional();

	if (!empty($nutritional)) { ?>
		<h3 class=" "><?php _e('Nutritional', 'mp-restaurant-menu'); ?></h3>
		<ul>
			<?php foreach ($nutritional as $nutrition) { ?>
				<li> <?php echo $nutrition['title'] . ': ' . $nutrition['val'] ?></li>
			<?php } ?>
		</ul>
	<?php }
}

/**
 * Related items template part
 */
function get_related_items_theme_view() {
	$related_items = mprm_get_related_items();
	if (!empty($related_items)) { ?>
		<h3 class=" "><?php _e('You might also like', 'mp-restaurant-menu'); ?></h3>
		<ul>
			<?php foreach ($related_items as $related_item) { ?>
				<li>
					<a href="<?php echo get_permalink($related_item) ?>">
						<?php if (has_post_thumbnail($related_item)):
							echo get_the_post_thumbnail($related_item, apply_filters('mprm-related-item-image-size', 'thumbnail'));
						endif; ?>
						<p><?php echo get_the_title($related_item) ?></p>
					</a>
				</li>
			<?php } ?>
		</ul>

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