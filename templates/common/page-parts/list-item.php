<?php
/**
 * mprm_page_template_menu_item_list_before hook
 */
do_action('mprm_page_template_menu_item_list_before');

/**
 * mprm_page_template_menu_item_list hook
 *
 * Menu page template item list
 *
 * @hooked mprm_page_template_menu_item_list - 10
 *
 * @see  mprm_menu_item_grid_header - 10
 * @see  mprm_menu_item_before_content - 15
 * @see  mprm_menu_item_grid_image - 20
 * @see  mprm_menu_item_grid_title - 30
 * @see  mprm_menu_item_grid_ingredients - 40
 * @see  mprm_menu_item_grid_attributes - 50
 * @see  mprm_menu_item_grid_excerpt - 60
 * @see  mprm_menu_item_grid_tags - 70
 * @see  mprm_menu_item_grid_price - 80);
 * @see  mprm_menu_item_after_content - 85
 * @see  mprm_get_purchase_template - 90
 * @see  mprm_menu_item_grid_footer - 95
 *
 */
do_action('mprm_page_template_menu_item_list');

/**
 * mprm_page_template_menu_item_list_after hook
 *
 */
do_action('mprm_page_template_menu_item_list_after');
