<?php before_mprm_widget() ?>

<?php the_mprm_widget_title() ?>

<div class="<?php echo apply_filters('mprm-widget-category-wrapper-class', 'mprm-container mprm-widget-categories mprm-view-' . $view . mprm_popular_theme_class()) ?>">
	<?php $categories = mprm_get_categories();
	$last_key = array_search(end($categories), $categories);
	foreach (array_values($categories) as $key => $term):
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
	<div class="mprm-clear"></div>
</div>

<?php after_mprm_widget() ?>
