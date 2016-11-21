<?php global $post;

before_mprm_widget();
the_mprm_widget_title();
$class = '';
$term_data = mprm_get_term_menu_items(); ?>

	<div class="<?php echo apply_filters('mprm-widget-items-wrapper-class', 'mprm-container mprm-widget-items mprm-view-' . $view . mprm_popular_theme_class()) ?>">
		<?php if ($view == 'simple-list'){ ?>
		<div class="mprm-columns-count-<?php echo $col ?> <?php echo empty($data['term']) ? 'mprm-all-items' : '' ?>">
			<?php }
			foreach ($term_data as $term => $data) {
				if (in_array($view, array('list', 'grid'))) {
					$last_key = array_search(end($data['posts']), $data['posts']);
					render_term_header($data);
					create_grid_by_posts($data, $col);
				} elseif ($view == 'simple-list') {
					if (!empty($data['term'])) { ?>
						<div class="<?php echo apply_filters('mprm-simple-view-column', 'mprm-simple-view-column') ?>">
							<?php render_term_header($data); ?>
						</div>
					<?php }

					list($last_key, $first_key) = mprm_get_first_and_last_key($data);

					foreach ($data['posts'] as $post_key => $post) {

						if ($post_key === $first_key) {
							$class = ' mprm-first';
						} elseif ($post_key === $last_key) {
							$class = ' mprm-last';
						}else{
							$class = '';
						}

						setup_postdata($post);
						mprm_set_menu_item($post->ID); ?>
						<div class="<?php echo apply_filters('mprm-simple-view-column', 'mprm-simple-view-column') . $class; ?> ">
							<?php render_current_html(); ?>
						</div>
						<?php wp_reset_postdata();
					}
				}
			}
			if ($view == 'simple-list'){ ?>
		</div>
	<?php } ?>
	</div>
<?php after_mprm_widget() ?>