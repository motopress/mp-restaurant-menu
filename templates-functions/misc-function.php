<?php
use mp_restaurant_menu\classes;
use mp_restaurant_menu\classes\Core;

/**
 * Add class shortcode/widget wrapper class
 * @return string
 */
function mprm_popular_theme_class() {
	$template = get_option('template');
	switch ($template) {
		case 'twentyeleven' :
			$class = ' twentyeleven';
			break;
		case 'twentytwelve' :
			$class = ' twentytwelve';
			break;
		case 'twentythirteen' :
			$class = ' twentythirteen';
			break;
		case 'twentyfourteen' :
			$class = ' twentyfourteen';
			break;
		case 'twentyfifteen' :
			$class = ' twentyfifteen';
			break;
		case 'twentysixteen' :
			$class = ' twentysixteen';
			break;
		default :
			$class = '';
			break;
	}
	
	return $class;
}

/**
 * Filter post class
 *
 * @param $classes
 * @param string $class
 * @param string $post_id
 *
 * @return mixed
 */
function mprm_post_class($classes, $class = '', $post_id = '') {
	if (!$post_id || 'mp_menu_item' !== get_post_type($post_id)) {
		return $classes;
	}
	if (classes\Media::get_instance()->get_template_mode() == 'plugin' || (!is_single() && !is_tax())) {
		if (!is_search() && !is_tax('mp_ingredient') && !is_author()) {
			if (false !== ($key = array_search('hentry', $classes))) {
				unset($classes[ $key ]);
			}
		}
	}
	
	if (in_array('mprm-remove-hentry', $classes)) {
		if (false !== ($key = array_search('hentry', $classes))) {
			unset($classes[ $key ]);
		}
		if (!empty($custom_class)) {
			if (false !== ($key = array_search($custom_class, $classes))) {
				unset($classes[ $key ]);
			}
		}
	}
	
	$custom_class = 'mprm-' . classes\Media::get_instance()->get_template_mode() . '-mode';
	
	$classes[] = $custom_class;
	$classes[] = 'mp-menu-item';
	
	return $classes;
}

/**
 * Get post meta with default settings
 *
 * @param $post_id
 * @param $key
 * @param bool $single
 * @param bool $default
 *
 * @return bool|mixed
 */
function mprm_get_post_meta($post_id, $key, $single = false, $default = false) {
	$post_meta = get_post_meta($post_id, $key, $single);
	
	return empty($post_meta) ? $default : $post_meta;
}

/**
 * Get post menu restaurant
 *
 * @param $type
 *
 * @return bool|string
 */
function mprm_get_post_type($type) {
	if (empty($type)) {
		return false;
	}
	
	return Core::get_instance()->get_post_type($type);
}

/**
 * @return mixed
 */
function mprm_grid_row_class() {
	return apply_filters('mprm-row-class', 'mprm-row');
}

/**
 * Cut string by length
 *
 * @param $length
 * @param $text
 *
 * @return string
 */
function mprm_cut_str($length, $text) {
	$length = empty($length) ? -1 : $length;
	
	if (strlen($text) <= $length || $length < 0) {
		return $text;
	}
	
	$string = mb_substr($text, 0, $length);
	
	return empty($string) ? $string : $string . '...';
}

/**
 * Add class wrapper
 */
function mprm_theme_wrapper_before() {
	$template = get_option('template');
	switch ($template) {
		case 'twentyeleven' :
			echo '<div id="primary"><div id="content" role="main" class="twentyeleven">';
			break;
		case 'twentytwelve' :
			echo '<div id="primary" class="site-content"><div id="content" role="main" class="twentytwelve">';
			break;
		case 'twentythirteen' :
			echo '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">';
			break;
		case 'twentyfourteen' :
			echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfmp">';
			break;
		case 'twentyfifteen' :
			echo '<div id="primary" role="main" class="content-area twentyfifteen"><div id="main" class="site-main t15mp">';
			break;
		case 'twentysixteen' :
			echo '<div id="primary" class="content-area twentysixteen"><main id="main" class="site-main" role="main">';
			break;
		default :
			echo '<div id="container"><div id="content" role="main">';
			break;
	}
}

/**
 * Theme wrapper after
 */
function mprm_theme_wrapper_after() {
	$template = get_option('template');
	switch ($template) {
		case 'twentyeleven' :
			echo '</div></div>';
			break;
		case 'twentytwelve' :
			echo '</div></div>';
			break;
		case 'twentythirteen' :
			echo '</div></div>';
			break;
		case 'twentyfourteen' :
			echo '</div></div></div>';
			get_sidebar('content');
			break;
		case 'twentyfifteen' :
			echo '</div></div>';
			break;
		case 'twentysixteen' :
			echo '</div></main>';
			break;
		default :
			echo '</div></div>';
			break;
	}
}

/**
 * Grid mprm-columns class
 *
 * @param int $type
 * @param string $view
 *
 * @return string
 */
function get_column_class($type, $view = 'default') {
	
	if ($view == 'simple-list') {
		$column_class = apply_filters('mprm-grid-column-class-simple-list', 'mprm-simple-view-column');
	} else {
		$column_class = apply_filters('mprm-grid-column-class', 'mprm-columns');
	}
	
	switch ($type) {
		case '1':
			$class = $column_class . ' mprm-twelve';
			break;
		case '2':
			$class = $column_class . ' mprm-six';
			break;
		case '3':
			$class = $column_class . ' mprm-four';
			break;
		case '4':
			$class = $column_class . ' mprm-three';
			break;
		case '6':
			$class = $column_class . ' mprm-two';
			break;
		default :
			$class = $column_class . ' mprm-twelve';
			break;
	}
	
	return $class;
}

/**
 * Get template mode
 *
 * @return mixed|string
 */
function mprm_get_template_mode() {
	return classes\Media::get_instance()->get_template_mode();
}

/**
 * Get first and last key array
 *
 * @param $data
 *
 * @return array
 */
function mprm_get_first_and_last_key(array $data) {
	$array_keys = array_keys($data[ 'posts' ]);
	$last_key = end($array_keys);
	reset($array_keys);
	$first_key = key($array_keys);
	
	return array($last_key, $first_key);
}

/**
 * Get errors
 * @return bool|mixed
 */
function mprm_get_errors() {
	return classes\models\Session::get_instance()->get_session_by_key('mprm_errors');
}

/**
 * Check is mode
 *
 * @return bool
 */
function mprm_is_test_mode() {
	return classes\models\Misc::get_instance()->is_test_mode();
}

/**
 * Get purchase summary
 *
 * @param $purchase_data
 * @param bool $email
 *
 * @return mixed
 */
function mprm_get_purchase_summary($purchase_data, $email = true) {
	return classes\models\Cart::get_instance()->get_purchase_summary($purchase_data, $email);
}

/**
 * Insert payment
 *
 * @param $payment
 *
 * @return bool
 */
function mprm_insert_payment($payment) {
	return classes\models\Payments::get_instance()->insert_payment($payment);
}

/**
 * @param $payment_id
 * @param $new_status
 *
 * @return bool
 */
function mprm_update_payment_status($payment_id, $new_status) {
	return classes\models\Payments::get_instance()->update_payment_status($payment_id, $new_status);
}

/**
 * @param int $payment_id
 * @param string $note
 *
 * @return bool|false|int
 */
function mprm_insert_payment_note($payment_id, $note) {
	return classes\models\Payments::get_instance()->insert_payment_note($payment_id, $note);
}

/**
 * Get Taxonomy settings
 *
 * @return array
 */
function taxonomy_settings() {
	
	$default_settings = array(
		'show_attributes' => '',
		'feat_img' => '',
		'excerpt' => '',
		'tags' => '',
		'ingredients' => '',
		'buy' => '',
		'grid_desc_length' => ''
	);
	$data = array(
		'taxonomy_grid' => array(
			'col' => '3',
			'categ_name' => 'only_text',
			'feat_img' => '1',
			'ingredients' => '1',
			'link_item' => '1',
		),
		'taxonomy_list' => array(
			'col' => '2',
			'categ_name' => 'only_text',
			'feat_img' => '1',
			'ingredients' => '1',
			'link_item' => '1',
		),
		'taxonomy_simple_list' => array(
			'col' => '1',
			'price_pos' => 'points',
			'categ_name' => 'only_text',
			'price' => '1',
			'link_item' => '1',
		));
	
	$taxonomy_view_settings = array();
	$taxonomy_view = mprm_get_option('display_taxonomy', 'default');
	
	if ($taxonomy_view !== 'default') {
		
		$settings_key = 'taxonomy_' . preg_replace('/-/', '_', $taxonomy_view);
		
		$taxonomy_settings = mprm_get_option($settings_key, array());
		if (empty($taxonomy_settings)) {
			$taxonomy_view_settings = array_merge($default_settings, $data[ $settings_key ], $taxonomy_settings);
		} else {
			$taxonomy_view_settings = array_merge($default_settings, $taxonomy_settings);
		}
		
		$taxonomy_view_settings[ 'action_path' ] = "common/page-parts/{$taxonomy_view}-item";
		$taxonomy_view_settings[ 'view' ] = $taxonomy_view;
		
		
		return $taxonomy_view_settings;
	}
	
	return $taxonomy_view_settings;
}

/**
 * Get theme template
 *
 * @return mixed|string
 */
function mprm_get_theme_template() {
	if (!function_exists('get_page_templates')) {
		include(ABSPATH . '/wp-admin/includes/theme.php');
	}
	
	$theme_templates = mprm_get_option('theme_templates', '');
	$templates = get_page_templates();
	
	if (empty($templates) || !array_key_exists($theme_templates, array_flip($templates))) {
		$theme_templates = 'page.php';
	}
	
	return $theme_templates;
	
}
