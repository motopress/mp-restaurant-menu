<?php global $post;
$term_data = mprm_get_term_menu_items();
?>
<div class="<?php echo apply_filters('mprm-shortcode-items-wrapper-class', 'mprm-container mprm-shortcode-items mprm-view-' . $view . mprm_popular_theme_class()) ?>">

	<?php foreach ($term_data as $term => $data) {
		if (in_array($view, array('list', 'grid'))) {
			if (!empty($data['posts']) && !empty($data['term'])) {
				mprm_set_current_term($data['term']);
				mprm_get_template('common/item-taxonomy-header');
			}
			list($post, $i) = create_grid_by_posts($data, $col);
		} elseif ($view == 'flat-list') {
			$last_key = array_search(end($term_data), $term_data);
			if (($term % $col) === 0 && !empty($data['term'])) {
				$i = 1;
				?>
				<div class="mprm-row">
			<?php }

		if ((empty($current_term) || $current_term != $data['term']) && !empty($data['term'])) { ?>
			<div class=" <?php echo get_column_class($col); ?>">
		<?php }

			if (!empty($data['posts']) && !empty($data['term'])) {
				mprm_set_current_term($data['term']);
				mprm_get_template('common/item-taxonomy-header');
			}
			if (empty($data['term'])) {
				list($post, $i) = create_grid_by_posts($data, $col);
			} else {
				foreach ($data['posts'] as $key => $post) :
					setup_postdata($post); ?>
					<div <?php post_class(); ?>>
						<?php
						mprm_set_menu_item($post->ID);
						render_current_html();
						wp_reset_postdata();
						?>
					</div>
				<?php endforeach;
			}

		if ((empty($current_term) || $current_term != $data['term']) && !empty($data['term'])) { ?>
			</div>
			<?php $current_term = $data['term'];
		}
			if ((($i % $col) === 0 || $last_key === $term) && !empty($data['term'])) {
				?>
				</div>
			<?php }
			$i++;
		}
	} ?>

</div>