<?php
$related_items = mprm_get_related_items();
if (!empty($related_items)) {
	?>
	<div class="mprm-related-items">
		<h3 class="mprm-title"><?php _e('You might also like', 'mp-restaurant-menu'); ?></h3>
		<ul class="mprm-related-items-list">
			<?php foreach ($related_items as $related_item): ?>
				<li class="mprm-related-item">
					<a href="<?php echo get_permalink($related_item) ?>" title="<?php echo get_the_title($related_item) ?>">
						<?php if (has_post_thumbnail($related_item)):
							echo get_the_post_thumbnail($related_item, apply_filters('mprm-related-item-image-size', 'mprm-middle'));
						endif; ?>
						<p class="mprm-related-title"><?php echo get_the_title($related_item) ?></p>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}