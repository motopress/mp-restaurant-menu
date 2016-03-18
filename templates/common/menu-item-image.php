<?php
global $post;
if (has_post_thumbnail($post)) {
	?>
	<div class="mprm-item-image">
		<a href="<?php the_permalink() ?>">
			<?php if (has_post_thumbnail($post)) {
				echo get_the_post_thumbnail($post, apply_filters('mprm-related-item-image-size', 'mprm-middle'));
			} ?>
		</a>
	</div>
	<?php
}