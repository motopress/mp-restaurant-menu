<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Ingredient extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get post ingredients
	 *
	 * @param type $id
	 */
	public function get_ingredients($id) {
		return wp_get_object_terms($id, $this->get_tax_name('ingredient'));
	}

}
