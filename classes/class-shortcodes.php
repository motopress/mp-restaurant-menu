<?php
namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\Core;

class Shortcodes extends Core {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * install shortcodes
	 */
	public static function install() {
		// include all core controllers
		Core::include_all(MP_RM_CLASSES_PATH . 'shortcodes/');
	}

//	/**
//	 * Get taxonomy by id string
//	 *
//	 * @param $taxonomies
//	 *
//	 * @return array|bool
//	 */
//	public function get_taxonomies($taxonomies) {
//		$taxonomy_terms = array();
//		if (empty($taxonomies)) {
//			return false;
//		} else {
//			$taxonomies = explode(',', $taxonomies);
//			foreach ($taxonomies as $key => $taxonomy_id) {
//				$taxonomy_terms[$key] = get_term($taxonomy_id);
//			}
//			return $taxonomy_terms;
//		}
//	}
}