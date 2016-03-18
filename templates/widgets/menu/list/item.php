<?php
/**
 * mprm_before_menu_item_list hook
 *
 * @hooked mprm_before_menu_item_list_header - 10
 * @hooked mprm_before_menu_item_list_footer - 20
 */
do_action('mprm_before_widget_menu_item_list');
/**
 * mprm_menu_item_list hook
 *
 * @hooked mprm_menu_item_list_header - 5
 * @hooked mprm_menu_item_list_image - 10
 * @hooked mprm_menu_item_list_right_header - 15
 * @hooked mprm_menu_item_list_tags - 20
 * @hooked mprm_menu_item_list_title - 30
 * @hooked mprm_menu_item_list_ingredients - 40
 * @hooked mprm_menu_item_list_excerpt - 50
 * @hooked mprm_menu_item_list_price - 60
 * @hooked mprm_menu_item_list_right_footer - 65
 * @hooked mprm_menu_item_list_footer - 70
 */
do_action('mprm_widget_menu_item_list');
/**
 * mprm_after_menu_item_list hook
 *
 * @hooked mprm_after_menu_item_list_header - 10
 * @hooked mprm_after_menu_item_list_footer - 20
 */
do_action('mprm_after_widget_menu_item_list');
