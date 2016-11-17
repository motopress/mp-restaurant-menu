<?php global $post;
$term_data = mprm_get_term_menu_items();
?>
<div class="<?php echo apply_filters('mprm-shortcode-items-wrapper-class', 'mprm-container mprm-shortcode-items mprm-view-' . $view . mprm_popular_theme_class()) ?>">
	<?php if ($view == 'simple-list'){ ?>

	<div class="mprm-columns-count-<?php echo $col ?> <?php echo empty($data['term']) ? 'mprm-all-items' : '' ?>">
		<?php }

		foreach ($term_data as $term => $data) {
			if (in_array($view, array('list', 'grid'))) {
				render_term_header($data);
				create_grid_by_posts($data, $col);

			} elseif ($view == 'simple-list') {

				if (!empty($data['term'])) { ?>
					<div class="<?php echo apply_filters('mprm-simple-view-column', 'mprm-simple-view-column') ?>">
						<?php render_term_header($data); ?>
					</div>
				<? }
				if (is_array($data['posts'])) {

					if (!empty($data['term'])) {
						$array_keys = array_keys($data['posts']);
						$first = reset($array_keys);
						$last = end($array_keys);
					} else {
						$class = '';
					}

					foreach ($data['posts'] as $key => $post) :
						if (isset($first) && isset($last)) {
							if ($key === $first) {
								$class = ' mprm-first';
							} elseif ($key === $last) {
								$class = ' mprm-last';
							} else {
								$class = '';
							}
						}

						setup_postdata($post);

						mprm_set_menu_item($post->ID); ?>

						<div class="<?php echo apply_filters('mprm-simple-view-column', 'mprm-simple-view-column') . $class; ?> ">
							<?php render_current_html(); ?>
						</div>

						<?php

						wp_reset_postdata();

					endforeach;
				}
			}
		}

		if ($view == 'simple-list'){ ?>
	</div>

<?php } ?>

</div>