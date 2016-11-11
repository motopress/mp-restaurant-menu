<?php global $mprm_view_args, $mprm_term;
$icon = mprm_get_category_icon();
$featured_image = mprm_get_feat_image();
$image_is_available = mprm_has_category_image() && !empty($mprm_view_args['feat_img']);
$category_image_src = mprm_get_category_image('large');

?>
<div class="mprm-menu-category <?php echo get_column_class($mprm_view_args['col']) ?>">
	<a href="<?php echo get_term_link($mprm_term); ?>" class="mprm-link">

		<?php if ($image_is_available && $category_image_src): ?>
			<img class="mprm-category-list-image mprm-columns mprm-five" src="<?php echo $category_image_src ?>">
		<?php endif; ?>

		<div class="mprm-category-content <?php echo ($image_is_available && $category_image_src)? 'mprm-columns mprm-seven' : '' ?>">
			<h2 class="mprm-title">
				<?php if (!empty($icon) && !empty($mprm_view_args['categ_icon'])): ?><i class="mprm-icon  <?php echo $icon ?>"></i><?php endif;
				if (!empty($mprm_view_args['categ_name'])) : echo $mprm_term->name; endif; ?>
			</h2>
			<?php if (!empty($mprm_view_args['categ_descr'])) {
				$desc_length = isset($mprm_view_args['desc_length']) ? $mprm_view_args['desc_length'] : -1;
				$description = mprm_cut_str($desc_length, $mprm_term->description);
				if (!empty($description)) { ?>
					<p class="mprm-category-description">
						<?php echo $description; ?>
					</p>
				<?php }
			}
			?>
		</div>

	</a>
</div>