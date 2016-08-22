<?php
if (empty($tags)) {
	$tags = mprm_get_tags();
}
$template_mode = mprm_get_template_mode();
$template_mode_class = ($template_mode == "theme") ? 'mprm-content-container' : '';


if (!empty($tags)): ?>
	<div class="mprm-tags-wrapper mprm-tags <?php echo $template_mode_class ?>">

		<?php foreach ($tags as $tag) {
			if (!is_object($tag)) {
				continue;
			}
			?>
			<span class="mprm-tag <?php echo 'mprm-tag-' . $tag->slug; ?>"><?php echo $tag->name ?></span>
		<?php } ?>

	</div>
<?php endif; ?>