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
	 * @param object $term
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
	 * @param $term_id
	 * @param $field
	 *
	 * @return mixed|void
	 */
	public function get_term_params($term_id, $field = '') {
		global $wp_version;
		if ($wp_version < 4.4) {
			$term_meta = get_option("mprm_taxonomy_{$term_id}");
		} else {
			$term_meta = get_term_meta($term_id, "mprm_taxonomy_$term_id", true);
		}
		// if update version wordpress  get old data
		if ($wp_version >= 4.4 && empty($term_meta)) {
			$term_meta = get_option("mprm_taxonomy_{$term_id}");
		}
		// thumbnail value
		if (!empty($term_meta['thumbnail_id'])) {
			$term_meta['thumb_url'] = wp_get_attachment_thumb_url($term_meta['thumbnail_id']);
			$term_meta['full_url'] = wp_get_attachment_url($term_meta['thumbnail_id']);
			$attachment_image_src = wp_get_attachment_image_src($term_meta['thumbnail_id'], 'mprm-big');
			$term_meta['image'] = $attachment_image_src[0];
		}
		if (!empty($field)) {
			return empty($term_meta) ? false : $term_meta[$field];
		} else {
			return $term_meta;
		}
	}
	public function get_term_image($mprm_term, $size = 'mprm-big') {
		if (!empty($mprm_term->term_id)) {
			$term_meta = $this->get_term_params($mprm_term->term_id);
			if (!empty($term_meta['thumbnail_id'])) {
				$attachment_image_src = wp_get_attachment_image_src($term_meta['thumbnail_id'], $size);
				$image = $attachment_image_src[0];
				return $image;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	public function get_term_icon($mprm_term) {
		if (!empty($mprm_term)) {
			$icon = $this->get_term_params($mprm_term->term_id, 'iconname');
			return (empty($icon) ? '' : $icon);
		} else {
			return '';
		}
	}
	public function has_category_image($mprm_term) {
		if (!empty($mprm_term->term_id)) {
			$thumbnail_id = $this->get_term_params($mprm_term->term_id, 'thumbnail_id');
			if (!empty($thumbnail_id)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * Save menu category
	 *
	 * @param int $term_id
	 * @param array $term_meta
	 */
	public function save_menu_category($term_id, $term_meta = array()) {
		global $wp_version;
		if (!empty($_POST['term_meta'])) {
			$term_meta = $_POST['term_meta'];
		}
		if (!empty($term_meta) && is_array($term_meta)) {
			if ($wp_version < 4.4) {
				update_option("mprm_taxonomy_$term_id", $term_meta);
			} else {
				update_term_meta($term_id, "mprm_taxonomy_$term_id", $term_meta);
			}
		}
	}
	/**
	 * Get categories by ids
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function get_categories_by_ids($ids = array()) {
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
