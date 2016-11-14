<?php global $post, $mprm_view_args;
if (!empty($image)): ?>
	<div class="mprm-side mprm-left-side mprm-columns mprm-five">
		<?php if (!empty($mprm_view_args['link_item'])) { ?>
		<a href="<?php echo get_permalink($post) ?>">
			<?php } ?>

			<?php echo wp_get_attachment_image(get_post_thumbnail_id($post->ID), 'mprm-big', false, array('class' => apply_filters('mprm-item-image', "mprm-image"))); ?>

			<?php if (!empty($mprm_view_args['link_item'])) { ?>
		</a>
	<?php } ?>

	</div>
<?php endif; ?>