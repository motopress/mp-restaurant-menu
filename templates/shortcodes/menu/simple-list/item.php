<?php
/**
 * mprm_before_menu_item_simple-list hook
 *
 * @hooked mprm_before_menu_item_simple-list_header - 10
 * @hooked mprm_before_menu_item_simple-list_footer - 20
 */
do_action('mprm_before_shortcode_menu_item_simple-list');



do_action('mprm_shortcode_menu_item_simple-list');

/**
 * mprm_after_menu_item_simple-list hook
 *
 * @hooked mprm_after_menu_item_simple-list_header - 10
 * @hooked mprm_after_menu_item_simple-list_footer - 20
 */
do_action('mprm_after_shortcode_menu_item_simple-list');
