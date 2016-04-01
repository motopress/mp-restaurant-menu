<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Cart extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public function get_cart_content_details() {
	}

	public function get_cart_quantity() {
	}

	public function add_to_cart($download_id, $options = array()) {
	}

	public function remove_from_cart($cart_key) {
	}

	public function check_item_in_cart() {

	}

	public function save_cart() {

	}

	public function empty_cart() {
	}

	public function set_purchase_session($purchase_data = array()) {
	}

	public function get_purchase_session() {
	}

	public function get_cart_token() {

	}

	public function delete_saved_carts() {

	}

	public function generate_cart_token() {
		return apply_filters('mprm_generate_cart_token', md5(mt_rand() . time()));
	}

	public function append_purchase_link($post, $type = 'menu_item') {

	}


}
