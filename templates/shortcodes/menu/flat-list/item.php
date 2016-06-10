<?php
/**
 * mprm_before_menu_item_flat-list hook
 *
 * @hooked mprm_before_menu_item_flat-list_header - 10
 * @hooked mprm_before_menu_item_flat-list_footer - 20
 */
do_action('mprm_before_shortcode_menu_item_flat-list');


do_action('mprm_shortcode_menu_item_flat-list');


/**
 * mprm_after_menu_item_flat-list hook
 *
 * @hooked mprm_after_menu_item_flat-list_header - 10
 * @hooked mprm_after_menu_item_flat-list_footer - 20
 */
do_action('mprm_after_shortcode_menu_item_flat-list');
