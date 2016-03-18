<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\models\parents\Term;
use mp_restaurant_menu\classes\View;

class Menu_category extends Term {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add form field hook
	 *
	 * @param type $params
	 */
	public function add_form_fields() {
		$data = array();
		$data['placeholder'] = MP_RM_MEDIA_URL . 'img/placeholder.png';
		$category_name = $this->get_tax_name('menu_category');
		View::get_instance()->render_html("../admin/taxonomies/{$category_name}/add_form_fields", $data);
	}

	/**
	 * Edit form field
	 *
	 * @param type $term
	 */
	public function edit_form_fields($term) {
		// get tern data
		$data = $this->get_term_params($term->term_id);
		if (empty($data)) {
			$data = array(
				'iconname' => '',
				'thumbnail_id' => '',
			);
		}
		$data['placeholder'] = MP_RM_MEDIA_URL . 'img/placeholder.png';
		$category_name = $this->get_tax_name('menu_category');
		View::get_instance()->render_html("../admin/taxonomies/{$category_name}/edit_form_fields", $data);
	}

	/**
	 * Get term params
	 *
	 * @param type $term_id
	 *
	 * @return type
	 */
	public function get_term_params($term_id) {
		$term_meta = get_option("mprm_taxonomy_{$term_id}");
		// thumbnail value
		if (!empty($term_meta['thumbnail_id'])) {
			$term_meta['thumb_url'] = wp_get_attachment_thumb_url($term_meta['thumbnail_id']);
			$term_meta['full_url'] = wp_get_attachment_url($term_meta['thumbnail_id']);
			$attachment_image_src = wp_get_attachment_image_src($term_meta['thumbnail_id'], 'mprm-big');
			$term_meta['image'] = $attachment_image_src[0];
		}
		return $term_meta;
	}

	public function get_term_image($term_id, $size = 'mprm-big') {
		$term_meta = get_option("mprm_taxonomy_{$term_id}");
		if (!empty($term_meta['thumbnail_id'])) {
			$attachment_image_src = wp_get_attachment_image_src($term_meta['thumbnail_id'], $size);
			$image = $attachment_image_src[0];
			return $image;
		} else {
			return false;
		}
	}

	public function get_term_icon($term_id) {
		$term_meta = get_option("mprm_taxonomy_{$term_id}");
		return $term_meta['iconname'];

	}

	public function has_category_image($term_id) {
		$term_meta = get_option("mprm_taxonomy_{$term_id}");
		if (!empty($term_meta['thumbnail_id'])) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Save menu category
	 *
	 * @param type $term_id
	 */
	public function save_menu_category($term_id) {
		if (!empty($_POST['term_meta'])) {
			$term_meta = $_POST['term_meta'];
			if (isset($term_meta) && is_array($term_meta)) {
				update_option("mprm_taxonomy_$term_id", $term_meta);
			}
		}

	}

	/**
	 * Get categories by ids
	 *
	 * @param type $ids
	 *
	 * @return type
	 */
	public function get_categories_by_ids($ids = false) {
		$taxonomy = $this->get_tax_name('menu_category');
		$terms = $this->get_terms($taxonomy, $ids);
		return $terms;
	}

	/**
	 * Get category options
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_categories_options(array $args) {
		$options = array();
		foreach ($args['terms'] as $key => $term) {
			$args['cat_id'] = $term->term_id;
			$option = $this->get_term_params($term->term_id);
			$options[$key] = $option;
			$options[$key]['posts'] = $args['posts'] = $this->get('menu_item')->get_menu_items($args);
			$options[$key]['posts_options'] = $this->get('menu_item')->get_menu_item_options($args);
		}
		return $options;
	}

}
