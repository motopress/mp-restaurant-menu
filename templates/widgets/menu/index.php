<?php before_mprm_widget() ?>
<?php the_mprm_widget_title(); ?>
<?php global $post; ?>
	<div class="<?php echo apply_filters('mprm-widget-items-wrapper-class', 'mprm-container mprm-widget-items mprm-view-' . $view . mprm_popular_theme_class()) ?>">
		<?php foreach (mprm_get_term_menu_items() as $term => $data) {
			$last_key = array_search(end($data['posts']), $data['posts']);
			if (!empty($data['posts']) && !empty($data['term'])) {
				mprm_set_current_term($data['term']);
				mprm_get_template('common/item-taxonomy-header');
			}
			list($post, $i) = create_grid_by_posts($data, $col);
		} ?>
		<div class="mprm-clear"></div>
	</div>
<?php after_mprm_widget() ?>