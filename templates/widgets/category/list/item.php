<?php global $mprm_view_args, $mprm_term;
$icon = mprm_get_category_icon()
?>
<div class="mprm-menu-category <?php echo get_column_class($mprm_view_args['col']) ?>">
	<?php if (mprm_has_category_image() && !empty($mprm_view_args['feat_img'])): ?>
		<img class="mprm-category-list-image" src="<?php echo mprm_get_category_image('thumbnail') ?>">
	<?php endif; ?>

	<div class="mprm-category-content">
			<h2 class="mprm-title">
				<a href="<?php echo get_term_link($mprm_term); ?>" class="mprm-link"><?php if (!empty($icon) && !empty($mprm_view_args['categ_icon'])): ?><i class="mprm-icon  <?php echo $icon ?>"></i><?php endif;
				if (!empty($mprm_view_args['categ_name'])) :
					echo $mprm_term->name;
				endif; ?></a></h2>
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
</div>