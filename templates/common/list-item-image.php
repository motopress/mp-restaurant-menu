<?php global $post;
if (!empty($image)): ?>
	<div class="mprm-side mprm-left-side">
		<?php echo wp_get_attachment_image(get_post_thumbnail_id($post->ID), 'mprm-thumbnail', false, array('class' => apply_filters('mprm-item-image', "mprm-image"))); ?>
	</div>
<?php endif; ?>