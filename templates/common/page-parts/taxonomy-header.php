<?php global $mprm_term;

$taxonomy_settings = taxonomy_settings();

if ('none' !== $taxonomy_settings[ 'categ_name' ]) {
	if (mprm_has_category_image() && ('with_img' == $taxonomy_settings[ 'categ_name' ])) { ?>
		<div class="mprm-header with-image" style="background-image: url('<?php echo mprm_get_category_image('large') ?>')">
			<div class="mprm-header-content"><h1 class="mprm-title"><i class="<?php echo mprm_get_category_icon(); ?> mprm-icon"></i><?php echo esc_html($mprm_term->name) ?></h1></div>
		</div>
	<?php } else { ?>
		<div class="mprm-header only-text"><h1 class="mprm-title"><?php echo esc_html($mprm_term->name); ?></h1></div>
		<?php
	}
}