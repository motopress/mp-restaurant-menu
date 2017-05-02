<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model as Model;

/**
 * Class Query
 *
 * @package mp_restaurant_menu\classes\models
 */
class Query extends Model {
	
	protected static $instance;
	
	/**
	 * @return Query
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Is restaurant menu query
	 *
	 * @return mixed
	 */
	public function is_restaurant_menu_query() {
		global $wp_query;
		
		if ($wp_query->is_tag && (array)$wp_query->get('post_type') != array($this->get_post_type('menu_item'))) {
			$types = $wp_query->get('post_type');
			if (empty($types)) {
				$types = array('post');
			}
			if (is_array($types) && $wp_query->is_main_query()) {
				$types[] = $this->get_post_type('menu_item');
			} elseif ($wp_query->is_main_query()) {
				if (is_string($types)) {
					$types = array($types, $this->get_post_type('menu_item'));
				} else {
					if ($types != 'any') {
						$types = array('post', $this->get_post_type('menu_item'));
					}
				}
			}
			$wp_query->set('post_type', $types);
		}
		
		$types = (!empty($wp_query->query_vars[ 'post_type' ]) ? (array)$wp_query->query_vars[ 'post_type' ] : array());
		
		//check if any possibility of this being an menu_item  category,tag,ingredient taxonomy
		
		$wp_query->mprm_is_category = !empty ($wp_query->query_vars[ $this->get_tax_name('menu_category') ])
			? true // it was an event category
			: false;
		
		$wp_query->mprm_is_tag = !empty ($wp_query->query_vars[ $this->get_tax_name('menu_tag') ])
			? true // it was an event category
			: false;
		
		$wp_query->mprm_is_ingredient = !empty ($wp_query->query_vars[ $this->get_tax_name('ingredient') ])
			? true // it was an event category
			: false;
		
		$wp_query->mprm_is_menu_item = (in_array(array_values($this->post_types), $types))
			? true // it was an event venue
			: false;
		
		
		$wp_query->mprm_is_restaurant_query = (
			$wp_query->mprm_is_category
			|| $wp_query->mprm_is_tag
			|| $wp_query->mprm_is_ingredient
			|| $wp_query->mprm_is_menu_item)
			? true // this is an event query of some type
			: false; // move along, this is not the query you are looking for
		
		
		return apply_filters('mprm_is_restaurant_menu_query', $wp_query->mprm_is_restaurant_query);
	}
	
	/**
	 * Restore the original query after spoofing it.
	 */
	public function restoreQuery() {
		global $wp_query;
		
		// If the query hasn't been spoofed we need take no action
		if (!isset($wp_query->spoofed) || !$wp_query->spoofed) {
			return;
		}
		
		// Remove the spoof post and fix the post count
		array_pop($wp_query->posts);
		$wp_query->post_count = count($wp_query->posts);
		
		// If we have other posts besides the spoof, rewind and reset
		if ($wp_query->post_count > 0) {
			$wp_query->rewind_posts();
			wp_reset_postdata();
		} // If there are no other posts, unset the $post property
		elseif (0 === $wp_query->post_count) {
			$wp_query->current_post = -1;
			unset($wp_query->post);
		}
		
		// Don't do this again
		unset($wp_query->spoofed);
	}
	
	/**
	 * Query is complete: stop the loop from repeating.
	 */
	public function endQuery() {
		global $wp_query;
		
		$wp_query->current_post = -1;
		$wp_query->post_count = 0;
	}
	
	/**
	 * @param $wp
	 */
	public function mprm_search_custom_fields($wp) {
		global $pagenow, $wpdb;
		
		if ('edit.php' != $pagenow || empty($wp->query_vars[ 's' ]) || !in_array($wp->query_vars[ 'post_type' ], array_values($this->post_types))) {
			return;
		}
		switch ($wp->query_vars[ 'post_type' ]) {
			case'mp_menu_item':
				$search_params = $this->get('menu_item')->get_search_params();
				$search_fields = array_map('mprm_clean', apply_filters('mprm_menu_item_search_fields', $search_params));
				break;
			case'mprm_order':
				$search_params = $this->get('order')->get_search_params();
				$search_fields = array_map('mprm_clean', apply_filters('mprm_order_search_fields', $search_params));
				break;
			default:
				break;
		}
		
		$search_order_id = preg_replace('/[a-z# ]/i', '', $_GET[ 's' ]);
		
		// Search orders
		if (is_numeric($search_order_id)) {
			$post_ids = array_unique(array_merge(
				$wpdb->get_col(
					$wpdb->prepare("SELECT DISTINCT p1.post_id FROM {$wpdb->postmeta} p1 WHERE p1.meta_key IN ('" . implode("','", array_map('esc_sql', $search_fields)) . "') AND p1.meta_value LIKE '%%%d%%';", absint($search_order_id))
				),
				array(absint($search_order_id))
			));
		} else {
			$post_ids = array_unique(array_merge(
				$wpdb->get_col(
					$wpdb->prepare("
						SELECT DISTINCT p1.post_id
						FROM {$wpdb->postmeta} p1
						INNER JOIN {$wpdb->postmeta} p2 ON p1.post_id = p2.post_id
						WHERE		( p1.meta_key IN ('" . implode("','", array_map('esc_sql', $search_fields)) . "') AND p1.meta_value LIKE '%%%s%%' )	",
						mprm_clean($_GET[ 's' ]), mprm_clean($_GET[ 's' ]), mprm_clean($_GET[ 's' ])
					)
				), $wpdb->get_col($wpdb->prepare("SELECT *  FROM {$wpdb->posts} WHERE `post_title` LIKE '%%%s%%'", mprm_clean($_GET[ 's' ])))
			));
		}
		
		// Remove s - we don't want to search order name
		unset($wp->query_vars[ 's' ]);
		
		// so we know we're doing this
		$wp->query_vars[ 'mprm_order_search' ] = true;
		
		// Search by found posts
		$wp->query_vars[ 'post__in' ] = array_filter($post_ids);
	}
	
	/**
	 * @param $public_query_vars
	 *
	 * @return array
	 */
	public function add_custom_query_var($public_query_vars) {
		$public_query_vars[] = 'sku';
		$public_query_vars[] = 'mprm_order_search';
		
		return $public_query_vars;
	}
}