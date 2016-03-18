<?php global $post; ?>

<div class="<?php echo apply_filters('mprm-shortcode-items-wrapper-class', 'mprm-container mprm-shortcode-items mprm-view-' . $view . mprm_popular_theme_class()) ?>">
	<?php foreach (mprm_get_term_menu_items() as $term => $data) {
		$last_key = array_search(end($data['posts']), $data['posts']);
		if (!empty($data['posts']) && !empty($data['term'])) {
			mprm_set_current_term($data['term']);
			mprm_get_template('common/item-taxonomy-header');
		}
		foreach ($data['posts'] as $key => $post) :
			setup_postdata($post);
			if (($key % $col) === 0) {
				$i = 1;
				?>
				<div class="mprm-row">
			<?php }

			mprm_set_menu_item($post->ID);
			render_current_html();

			if (($i % $col) === 0 || $last_key === $key) {
				?>
				</div>
			<?php }
			$i++;
			wp_reset_postdata();
		endforeach;
	} ?>
</div>