<?php
before_mprm_widget();

the_mprm_widget_title();

$term_data = mprm_get_term_menu_items(); ?>

<?php global $post; ?>

	<div class="<?php echo apply_filters('mprm-widget-items-wrapper-class', 'mprm-container mprm-widget-items mprm-view-' . $view . mprm_popular_theme_class()) ?>">
		<?php if ($view == 'simple-list'){ ?>
		<div class="mprm-columns-count-<?php echo $col ?>">
			<?php } ?>
			<?php foreach ($term_data as $term => $data) {

				if (in_array($view, array('list', 'grid'))) {
					$last_key = array_search(end($data['posts']), $data['posts']);

					render_term_header($data);

					list($post, $i) = create_grid_by_posts($data, $col);

				} elseif ($view == 'simple-list') {

					if (empty($data['term'])) {
						foreach ($data['posts'] as $key => $post) :
							setup_postdata($post);
							mprm_set_menu_item($post->ID); ?>
							<div class="<?php echo apply_filters('mprm-simple-view-column', 'mprm-simple-view-column') ?>">
								<?php render_current_html(); ?>
							</div>
							<?php
							wp_reset_postdata();
						endforeach;
					} else { ?>
						<div class="<?php echo apply_filters('mprm-simple-view-column', 'mprm-simple-view-column') ?>">
							<?php
							render_term_header($data);

							foreach ($data['posts'] as $key => $post) :

								setup_postdata($post);
								mprm_set_menu_item($post->ID);
								render_current_html();
								wp_reset_postdata();

							endforeach;
							?>
						</div>
					<?php }
				}
			} ?>
			<div class="mprm-clear"></div>
			<?php if ($view == 'simple-list'){ ?>
		</div>
	<?php } ?>
	</div>

<?php after_mprm_widget() ?>