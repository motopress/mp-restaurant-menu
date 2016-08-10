<?php
if (empty($tags)) {
	$tags = mprm_get_tags();
} ?>
<?php if (!empty($tags)): ?>
	<div class="mprm-tags-wrapper">
		<ul class="mprm-list mprm-tags">
			<?php foreach ($tags as $tag) {
				if (!is_object($tag)) {
					continue;
				}
				?>
				<li class="mprm-tag <?php echo 'mprm-tag-' . $tag->slug; ?>"><?php echo $tag->name ?></li>
			<?php } ?>
		</ul>
	</div>
<?php endif; ?>