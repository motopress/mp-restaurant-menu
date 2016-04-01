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

	public function get_error($type) {
		switch ($type) {
			case 'purchase_page':

				break;
			default:
				break;
		}
	}

	public function get_append_purchase_link() {
		global $post;
		$data = array(
			'ID' => $post->ID,
			'template' => 'default',
			'error' => false,
			'price' => Menu_item::get_instance()->get_price($post->id),
			'direct' => false,
			'text' => __('Purchase', 'mp-restaurant-menu'),
			'style' => get_option('mprm_button_style', 'button'),
			'color' => get_option('mprm_checkout_color', 'blue'),
			'class' => 'mprm-submit'
		);
		$purchase_page = get_option('mprm_purchase_page', false);

		if (!$purchase_page || $purchase_page == 0) {
			$data['error'] = true;
			$data['error_message'] = $this->get_error('purchase_page');
			return false;
		}

		if (empty($post->ID)) {
			return false;
		}
		if ('publish' !== $post->post_status && !current_user_can('edit_product', $post->ID)) {
			return false;
		}
		return $data;

	}
}
