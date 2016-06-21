<?php global $post;
$term_data = mprm_get_term_menu_items();
$i = 1;
?>
<div class="<?php echo apply_filters('mprm-shortcode-items-wrapper-class', 'mprm-container mprm-shortcode-items mprm-view-' . $view . mprm_popular_theme_class()) ?>">
	<div class="mprm-columns-count-<?php echo $col ?>">
		<?php foreach ($term_data as $term => $data) {
			if (in_array($view, array('list', 'grid'))) {
				render_term_header($data);
				create_grid_by_posts($data, $col);
			} elseif ($view == 'simple-list') {

				render_term_header($data);

				if (empty($data['term'])) {
					foreach ($data['posts'] as $key => $post) :
						setup_postdata($post);
						mprm_set_menu_item($post->ID); ?>
						<div class="mprm-column-count">
							<?php render_current_html(); ?>
						</div>
						<?php
						wp_reset_postdata();
					endforeach;
				} else {
					foreach ($data['posts'] as $key => $post) :
						setup_postdata($post); ?>
						<div <?php post_class("mprm-column-count"); ?>>
							<?php

							mprm_set_menu_item($post->ID);
							render_current_html();
							wp_reset_postdata();

							?>
						</div>
					<?php endforeach;
				}
			}
		} ?>
	</div>
</div>