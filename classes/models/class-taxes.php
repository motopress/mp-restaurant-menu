<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Taxes extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function use_taxes() {
		$ret = $this->get('settings')->get_option('enable_taxes', false);
		return (bool)apply_filters('mprm_use_taxes', $ret);
	}

}