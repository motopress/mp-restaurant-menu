<?php
namespace mp_restaurant_menu\classes\models;
use mp_restaurant_menu\classes\models\parents\Term;

/**
 * Class Menu_tag
 * @package mp_restaurant_menu\classes\models
 */
class Menu_tag extends Term {
	protected static $instance;

	/**
	 * @return Menu_tag
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Get tags by ids
	 *
	 * @param array $ids
	 *
	 * @return parents\object
	 */
	public function get_tags_by_ids(array $ids = array()) {
		$taxonomy = $this->get_tax_name('menu_tag');
		$terms = $this->get_terms($taxonomy, $ids);
		return $terms;
	}
}
