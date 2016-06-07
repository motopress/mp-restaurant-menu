<?php
/**
 * mprm_before_menu_item_grid hook
 *
 * @hooked mprm_before_menu_item_grid_header - 10
 * @hooked mprm_before_menu_item_grid_footer - 20
 */
do_action('mprm_before_shortcode_menu_item_grid');
/**
 * mprm_menu_item_grid hook
 *
 * @hooked mprm_menu_item_grid_header - 5
 *
 * @hooked mprm_menu_item_grid_image - 10
 * @hooked mprm_menu_item_grid_tags - 20
 * @hooked mprm_menu_item_grid_title - 30
 * @hooked mprm_menu_item_grid_ingredients - 40
 * @hooked mprm_menu_item_grid_attributes - 50
 * @hooked mprm_menu_item_grid_excerpt - 60
 * @hooked mprm_menu_item_grid_price - 70
 *
 * @hooked mprm_menu_item_grid_footer - 80
 */
do_action('mprm_shortcode_menu_item_grid');
/**
 * mprm_after_menu_item_grid hook
 *
 * @hooked mprm_after_menu_item_grid_header - 10
 * @hooked mprm_after_menu_item_grid_footer - 20
 */
do_action('mprm_after_shortcode_menu_item_grid');
