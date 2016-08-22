<?php if (!empty($excerpt)) {
	$template_mode = mprm_get_template_mode();
	$template_mode_class = ($template_mode == "theme") ? 'mprm-content-container' : '';

	?>
	<div class="mprm-excerpt <?php echo $template_mode_class ?>"><?php echo $excerpt ?></div>
<?php } ?>