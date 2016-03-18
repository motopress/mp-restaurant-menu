<?php
if (empty($tags)) {
	$tags = mprm_get_tags();
} ?>
<?php if (!empty($tags)): ?>
	<p class="mprm-tags">
		<?php if (!empty($tags)):
			foreach ($tags as $tag) {
				?>
				<span class="mprm-tag"><?php echo $tag->name ?></span>
			<?php } endif; ?>
	</p>
<?php endif; ?>