<?php
namespace mp_restaurant_menu\classes\models\parents;

use mp_restaurant_menu\classes\Model;

/**
 * Class Term
 * @package mp_restaurant_menu\classes\models\parents
 */
class Term extends Model {
	protected static $instance;

	/**
	 * @return Term
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get terms
	 *
	 * @param string $taxonomy
	 * @param array /string $ids
	 *
	 * @return array
	 */
	public function get_terms($taxonomy, $ids = array()) {
		global $mprm_view_args;
		$terms = array();
		if (!empty($ids)) {
			if (!is_array($ids)) {
				$cat_ids = explode(',', $ids);
			} else {
				$cat_ids = $ids;
			}
			foreach ($cat_ids as $id) {
				$terms[$id] = get_term_by('id', (int)($id), $taxonomy);
			}
		} else if (empty($mprm_view_args['categ']) && empty($mprm_view_args['tags_list'])) {
			$terms = get_terms($taxonomy);
		}
		return $terms;
	}
}
