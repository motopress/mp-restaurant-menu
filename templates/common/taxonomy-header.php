<?php global $mprm_term;
if (mprm_has_category_image()) { ?>
	<div class="mprm-header with-image" style="background-image: url('<?php echo esc_url( mprm_get_category_image('large') );?>')">
		<div class="mprm-header-content"><h1 class="mprm-title"><i class="<?php echo esc_attr( mprm_get_category_icon() ); ?> mprm-icon"></i><?php echo esc_html($mprm_term->name) ?></h1></div>
	</div>
<?php } else { ?>
	<div class="mprm-header only-text"><h1 class="mprm-title"><?php echo esc_html($mprm_term->name); ?></h1></div>
	<?php
}