<?php get_header(); ?>
<?php mprm_get_taxonomy(); ?>
<?php do_action('mprm_tag_before_wrapper'); ?>
<div <?php post_class('mprm-remove-hentry ' . apply_filters('mprm-main-wrapper-class', 'mprm-main-wrapper')) ?>>
	<div class="<?php echo apply_filters('mprm-wrapper-' . get_mprm_taxonomy_view() . '-tag-class', 'mprm-taxonomy-items-' . get_mprm_taxonomy_view() . ' mprm-container mprm-tag') ?> ">
		<?php
		/**
		 * mprm_before_tag_header hook
		 *
		 * @hooked mprm_before_tag_header - 10
		 */
		do_action('mprm_before_tag_header');
		/**
		 * mprm_tag_header hook
		 *
		 * @hooked mprm_tag_header - 5
		 */
		do_action('mprm_tag_header');
		/**
		 * mprm_after_tag_header hook
		 *
		 * @hooked mprm_after_tag_header - 10
		 */
		do_action('mprm_after_tag_header');
		?>
		<?php if (is_mprm_taxonomy_grid()): ?>
			<?php foreach (mprm_get_menu_items_by_term() as $term => $data) {
				$last_key = array_search(end($data['posts']), $data['posts']);
				foreach ($data['posts'] as $key => $post):
					setup_postdata($post);
					if (($key % 3) === 0) {
						$i = 1; ?>
						<div class="mprm-row">
						<?php
					}
					do_action('mprm_before_taxonomy_grid');
					do_action('mprm_taxonomy_grid');
					do_action('mprm_after_taxonomy_grid');
					if (($i % 3) === 0 || $last_key === $key) {
						?>
						</div>
					<?php } ?>
					<?php $i++;
				endforeach;
			} ?>
		<?php else: ?>
			<?php foreach (mprm_get_menu_items_by_term() as $term => $data) {
				foreach ($data['posts'] as $key => $post):?>
					<?php setup_postdata($post); ?>
					<div <?php post_class('mprm-remove-hentry ' . 'mprm-row') ?>>
						<?php
						do_action('mprm_before_taxonomy_list');
						do_action('mprm_taxonomy_list');
						do_action('mprm_after_taxonomy_list');
						/**
						 * mprm_after_tag_list hook
						 *
						 * @hooked mprm_after_tag_list - 10
						 */
						do_action('mprm_after_tag_list'); ?>
					</div>
					<?php
				endforeach;
			} ?>
		<?php endif; ?>
	</div>
</div>
<div class="mprm-clear"></div>
<?php
do_action('mprm_tag_after_wrapper');
if (is_tax() && ('twentyfourteen' === get_option('template'))) {
	get_sidebar();
}
get_footer();

?>
