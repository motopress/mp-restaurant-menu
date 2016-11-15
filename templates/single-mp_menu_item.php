<?php get_header();
do_action('mprm-before-main-wrapper');
while (have_posts()) : the_post(); ?>
	<div <?php post_class(apply_filters('mprm-main-wrapper-class', 'mprm-main-wrapper')) ?>>
		<?php
		do_action('mprm_before_menu_item_header');
		do_action('mprm_menu_item_header');
		do_action('mprm_after_menu_item_header');
		?>
		<div class="<?php echo apply_filters('mprm-content-wrapper-class', 'mprm-container content-wrapper') ?>">
			<div class="mprm-row">
				<div class="<?php echo apply_filters('mprm-menu-content-class', 'mprm-content mprm-eight mprm-columns') ?>">
					<?php
					do_action('mprm_before_menu_item_gallery');
					do_action('mprm_menu_item_gallery');
					do_action('mprm_after_menu_item_gallery');
					do_action('mprm_menu_item_content');
					?>
				</div>
				<div class="<?php echo apply_filters('mprm-menu-sidebar-class', 'mprm-sidebar mprm-four mprm-columns') ?>">
					<?php do_action('mprm_menu_item_slidebar'); ?>
				</div>
				<div class="mprm-clear"></div>
			</div>
		</div>
	</div>
<?php endwhile; ?>
<div class="mprm-clear"></div>
<?php do_action('mprm-after-main-wrapper');
if (is_single() && (!is_tax() || !is_archive()) && ('twentyfourteen' === get_option('template'))) {
	get_sidebar();
}

get_footer(); ?>
