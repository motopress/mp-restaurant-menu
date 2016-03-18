<?php global $mprm_view_args, $mprm_term;
$title = !empty($mprm_term) ? $mprm_term->name : '';
$icon = mprm_get_category_icon();
if ('none' !== $mprm_view_args['categ_name']) {
	if (mprm_has_category_image() && ('with_img' == $mprm_view_args['categ_name'])) {
		?>
		<div class="mprm-header with-image" style="background-image: url('<?php echo (mprm_has_category_image()) ? mprm_get_category_image('large') : 'none'; ?>')">
			<div class="mprm-header-content">
				<h1 class="mprm-title"><?php if (!empty($icon)): ?><i class="mprm-icon <?php echo $icon ?>"></i><?php endif; ?><?php echo $title ?></h1>
			</div>
		</div>
	<?php } else { ?>
		<div class="mprm-header only-text">
			<h1 class="mprm-title" ><?php echo $title ?></h1>
		</div>
		<?php
	}
}