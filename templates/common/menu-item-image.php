<?php
global $post;
if (has_post_thumbnail($post)) {
	?>
	<div class="mprm-item-image">
		<?php if (has_post_thumbnail($post)) {
			echo get_the_post_thumbnail($post, apply_filters('mprm-related-item-image-size', 'mprm-middle'));
		} ?>
	</div>
	<?php
}