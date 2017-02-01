<?php mprm_get_taxonomy();

global $mprm_view_args;

do_action('mprm-page-template-single-taxonomy-wrapper-before');

$view = mprm_get_option('display_taxonomy', 'default');

$mprm_view_args = taxonomy_settings();

$col = (int)$mprm_view_args[ 'col' ];

?>
	<div <?php post_class(apply_filters('mprm-main-wrapper-class', 'mprm-main-wrapper')) ?>>

		<div class="<?php echo apply_filters('mprm-wrapper-' . $view . '-category-class', 'mprm-taxonomy-items-' . $view . ' mprm-container mprm-category') ?> ">
			<?php
			
			/**
			 * mprm_page_template_taxonomy_header_before hook
			 *
			 * @hooked mprm_page_template_taxonomy_header_before - 10
			 */
			do_action('mprm_page_template_taxonomy_header_before');
			
			/**
			 * mprm_category_header hook
			 *
			 * @hooked mprm_category_header - 5
			 */
			do_action('mprm_page_template_taxonomy_header');
			
			/**
			 * mprm_page_template_taxonomy_header_after hook
			 *
			 * @hooked mprm_page_template_taxonomy_header_after - 10
			 */
			do_action('mprm_page_template_taxonomy_header_after');
			
			?>
			<div class="<?php echo apply_filters('mprm-shortcode-items-wrapper-class', 'mprm-container mprm-shortcode-items mprm-view-' . $view) ?>">
				<?php if ($view == 'simple-list'){ ?>
				<div class="mprm-columns-count-<?php echo $col ?>">
					<?php }
					
					foreach (mprm_get_menu_items_by_term() as $term => $data) {
						
						if (in_array($view, array('list', 'grid'))) {
							//render_term_header($data);
							create_grid_by_posts($data, $col);
						} elseif ($view == 'simple-list') {
							
							list($last_key, $first_key) = mprm_get_first_and_last_key($data);
							
							foreach ($data[ 'posts' ] as $post_key => $post) {
								
								if ($post_key === $first_key) {
									$class = ' mprm-first';
								} elseif ($post_key === $last_key) {
									$class = ' mprm-last';
								} else {
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
		</div>
	</div>

	<div class="mprm-clear"></div>
<?php do_action('mprm-page-template-taxonomy-wrapper-after');