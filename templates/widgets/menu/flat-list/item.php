<?php
/**
 * mprm_before_menu_item_flat_list hook
 *
 * @hooked mprm_before_menu_item_flat_list_header - 10
 * @hooked mprm_before_menu_item_flat_list_footer - 20
 */
do_action('mprm_before_widget_menu_item_flat_list');


do_action('mprm_widget_menu_item_flat_list');


/**
 * mprm_after_menu_item_flat_list hook
 *
 * @hooked mprm_after_menu_item_flat_list_header - 10
 * @hooked mprm_after_menu_item_flat_list_footer - 20
 */
do_action('mprm_after_widget_menu_item_flat_list');
