<?php

namespace mp_restaurant_menu\classes\models\parents;

use mp_restaurant_menu\classes\Model;

class Term extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get terms
	 * 
	 * @param type $taxonomy
	 * @param type $ids
	 * @return type
	 */
	public function get_terms($taxonomy, $ids = false) {
		global $mprm_view_args;
		$terms = array();
		if (!empty($ids)) {
			if (!is_array($ids)) {
				$cat_ids = explode(',', $ids);
			} else {
				$cat_ids = $ids;
			}
			foreach ($cat_ids as $id) {
				$terms[$id] = get_term_by('id', (int) ($id), $taxonomy);
			}
		} else if (empty($mprm_view_args['categ']) && empty($mprm_view_args['tags_list'])) {
			$terms = get_terms($taxonomy);
		}
		return $terms;
	}

}
