<div class="<?php echo apply_filters('mprm-shortcode-category-wrapper-class', 'mprm-container mprm-shortcode-categories mprm-view-' . $view . mprm_popular_theme_class()) ?>">
	<?php $categories = mprm_get_categories();
	$categories = array_values($categories);
	$last_key = array_search(end($categories), $categories);
	foreach ($categories as $key => $term):
		if (($key % $col) === 0) {
			$i = 1; ?>
			<div class="mprm-row">
		<?php }
		mprm_set_current_term($term);
		render_current_html();
		if (($i % $col) === 0 || $last_key === $key) { ?>
			</div>
		<?php }
		$i++;
	endforeach; ?>
</div>