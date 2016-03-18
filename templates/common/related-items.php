<?php
$related_items = mprm_get_related_items();
if (!empty($related_items)) {
	?>
	<div class="mprm-related-items">
		<h3 class="mprm-title"><?php _e('You might also like', 'mp-restaurant-menu') ?></h3>
		<ul class="mprm-related-items-list">
			<?php foreach ($related_items as $post): ?>
				<li class="mprm-related-item">
					<a href="<?php echo get_permalink($post) ?>">
						<?php if (has_post_thumbnail($post)):
							echo get_the_post_thumbnail($post, apply_filters('mprm-related-item-image-size', 'mprm-middle'));
						endif; ?>
						<p class="mprm-related-title"><?php echo get_the_title($post) ?></p>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}