<?php

/**
 * Price template part
 */
function get_price_theme_view() {
	$price = mprm_get_price();
	
	do_action('mprm_price_theme_view_before', $price);
	
	if (!empty($price)) { ?>
		<p><?php esc_html_e('Price', 'mp-restaurant-menu'); ?>: <span><b><?php echo esc_html( mprm_currency_filter(mprm_format_amount($price)) );?></b></span></p>
	<?php }
	
	do_action('mprm_price_theme_view_after', $price);
}

/**
 * Ingredients template part
 */
function get_ingredients_theme_view() {
	$ingredients = mprm_get_ingredients();
	do_action('mprm_ingredients_theme_view_before', $ingredients);
	
	if (!empty($ingredients)) { ?>
		<h3><?php esc_html_e('Ingredients', 'mp-restaurant-menu'); ?></h3>
		<ul>
			<?php foreach ($ingredients as $ingredient):
				if (!is_object($ingredient)) {
					continue;
				} ?>
				<li><?php echo esc_html( $ingredient->name );?></li>
			<?php endforeach; ?>
		</ul>
	<?php }
	
	do_action('mprm_ingredients_theme_view_after', $ingredients);
}

/**
 * Attributes template part
 */
function get_attributes_theme_view() {
	$attributes = mprm_get_attributes();
	do_action('mprm_attributes_theme_view_before', $attributes);
	
	if ($attributes) { ?>
		<h3><?php esc_html_e('Portion Size', 'mp-restaurant-menu'); ?></h3>
		<ul>
			<?php foreach ($attributes as $info): ?>
				<?php if (!empty($info[ 'val' ])): ?>
					<li><?php echo esc_html( $info[ 'val' ] ); ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<?php
	}
	
	do_action('mprm_attributes_theme_view_after', $attributes);
}

/**
 * Nutritional template part
 */
function get_nutritional_theme_view() {
	$nutritional = mprm_get_nutritional();
	do_action('mprm_nutritional_theme_view_before', $nutritional);
	
	if (!empty($nutritional)) { ?>
		<h3><?php esc_html_e('Nutritional', 'mp-restaurant-menu'); ?></h3>
		<ul>
			<?php foreach ($nutritional as $info): ?>
				<?php if (!empty($info[ 'val' ])): ?>
					<li><?php echo esc_html( mprm_get_nutrition_label(strtolower($info[ 'title' ])) .
						apply_filters('mprm-nutritional-delimiter', ': ') . $info[ 'val' ] ); ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	<?php }
	
	do_action('mprm_nutritional_theme_view_after', $nutritional);
}

/**
 * Related items template part
 */
function get_related_items_theme_view() {
	$related_items = mprm_get_related_items();
	do_action('mprm_related_items_theme_view_before', $related_items);
	
	if (!empty($related_items)) { ?>
		<div class="mprm-related-items">
			<h3><?php esc_html_e('You might also like', 'mp-restaurant-menu'); ?></h3>
			<?php foreach ($related_items as $related_item) { ?>
				<a href="<?php echo esc_url( get_permalink($related_item) );?>" title="<?php echo esc_attr( get_the_title($related_item) );?>">
					<?php
					if (has_post_thumbnail($related_item)) {
						echo wp_get_attachment_image(get_post_thumbnail_id($related_item), apply_filters('mprm-related-item-image-size', 'thumbnail')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else { ?>
						<span><?php echo esc_html( get_the_title($related_item) );?></span>
					<?php } ?>
				</a>
			<?php } ?>
		</div>
	<?php }
	
	do_action('mprm_related_items_theme_view_after', $related_items);
}

/**
 * Gallery template part
 */
function get_gallery_theme_view() {
	$gallery = mprm_get_gallery();
	if (!empty($gallery)) {
		$args = apply_filters('mprm-gallery-settings', array('ids' => $gallery, 'link' => 'file', 'columns' => '3'));
		echo gallery_shortcode($args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

function mprm_page_template_taxonomy_header(){
	mprm_get_template('common/page-parts/taxonomy-header');
}