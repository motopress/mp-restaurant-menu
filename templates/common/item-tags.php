<?php
if (empty($tags)) {
	$tags = mprm_get_tags();
} ?>
<?php if (!empty($tags)): ?>
	<p class="mprm-tags">
		<?php foreach ($tags as $tag) {
			if (!is_object($tag)) {
				continue;
			}
			?>
			<span class="mprm-tag <?php echo 'mprm-tag-' . $tag->slug; ?>"><?php echo $tag->name ?></span>
		<?php } ?>
	</p>
<?php endif; ?>