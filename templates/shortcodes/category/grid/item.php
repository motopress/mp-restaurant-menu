<?php
global $mprm_view_args, $mprm_term;
$icon = mprm_get_category_icon();
$featured_image = mprm_get_feat_image();
?>
<div class="mprm-menu-category mprm-effect-hover <?php echo get_column_class($mprm_view_args['col']); ?> " style="background-image: <?php echo (mprm_has_category_image() && $featured_image) ? "url('" . mprm_get_category_image('large') . "')" : 'none'; ?>">
	<a class="mprm-link" href="<?php echo get_term_link($mprm_term); ?>">
		<div class="mprm-effect-hover"></div>
		<div class="mprm-category-content">
			<h2 class="mprm-title"><?php if (!empty($icon) && !empty($mprm_view_args['categ_icon'])): ?><i class="<?php echo $icon ?> mprm-icon"></i><?php endif;
				if (!empty($mprm_view_args['categ_name'])) :
					echo $mprm_term->name;
				endif; ?></h2>
			<?php
			if (!empty($mprm_view_args['categ_descr'])) {
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