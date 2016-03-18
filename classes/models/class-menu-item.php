<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\modules\Post;
use mp_restaurant_menu\classes\View;

class Menu_item extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init metaboxes
	 */
	public function init_metaboxes() {
		$metabox_array = Core::get_instance()->get_config("metaboxes");
		Post::get_instance()->set_metaboxes($metabox_array);
	}

	/**
	 * Render meta box hook
	 *
	 * @param \WP_Post $post
	 * @param array $params
	 */
	public function render_meta_box(\WP_Post $post, array $params) {
		// add nonce field
		wp_nonce_field('mp-restaurant-menu' . '_nonce', 'mp-restaurant-menu' . '_nonce_box');
		// render Metabox html
		$name = !empty($params['args']['name']) ? $params['args']['name'] : $params['id'];
		$description = !empty($params['args']['description']) ? $params['args']['description'] : '';
		$data['name'] = $name;
		$data['title'] = $params['title'];
		$data['value'] = get_post_meta($post->ID, $name, true);
		$data['description'] = $description;
		View::get_instance()->render_html("../admin/metaboxes/{$name}", $data);
	}

	/**
	 * Get gallery
	 *
	 * @param type $id
	 *
	 * @return type
	 */
	public function get_gallery($id) {
		// get Image gallery
		$ids = get_post_meta($id, 'mp_menu_gallery', true);
		$gallery = array();
		if (!empty($ids)) {
			foreach (explode(',', $ids) as $id) {
				if (get_post($id)) {
					$gallery[] = $id;
				}
			}
		}
		return $gallery;
	}

	/**
	 * Get around items
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function get_related_items($id) {
		// get Menu category
		$menu_category = wp_get_object_terms($id, $this->get_tax_name('menu_category'));
		$posts = array();
		if (!empty($menu_category)) {
			$posts = get_posts(array(
				'post_type' => $this->get_post_type('menu_item'),
				'post_status' => 'publish',
				'posts_per_page' => 3,
				'post__not_in' => array($id),
				'tax_query' => array(
					'taxonomy' => $this->get_tax_name('menu_category'),
					'field' => 'id',
					'terms' => $menu_category[0]->term_id
				),
				'orderby' => 'rand',
			));
		}
		return $posts;
	}

	/**
	 * Get attributes
	 *
	 * @param \WP_Post $post
	 *
	 * @return type
	 */
	public function get_attributes(\WP_Post $post) {
		$attributes = get_post_meta($post->ID, 'attributes', true);
		if (!$this->is_arr_values_empty($attributes)) {
			return $attributes;
		}
	}

	/**
	 * Get featured image
	 *
	 * @param \WP_Post $post
	 * @param bool $type
	 *
	 * @return false|string
	 */
	public function get_featured_image(\WP_Post $post, $type = false) {
		$id = get_post_thumbnail_id($post);
		if ($type) {
			return wp_get_attachment_image_url($id, $type, false);
		} else {
			return wp_get_attachment_url($id);
		}
	}

	/**
	 * Get menu item class
	 *
	 * @param int $id
	 * @param bool $format
	 *
	 * @return string
	 */
	public function get_price($id, $format = false) {
		$currency = $this->get('settings')->get_currency_symbol();
		$pos = $this->get('settings')->get_settings('currency_pos');
		$price = get_post_meta($id, 'price', true);
		if (!empty($currency) && $format && !empty($price)) {
			switch ($pos) {
				case 'left':
					$price = "{$currency}{$price}";
					break;
				case 'right':
					$price = "{$price}{$currency}";
					break;
				case 'left_space':
					$price = "{$currency} {$price}";
					break;
				case 'right_space':
					$price = "{$price} {$currency}";
					break;
			}
		}
		return $price;
	}

	/**
	 * Get category menu items
	 *
	 * @param type $args
	 *
	 * @return type
	 */
	public function get_menu_items(array $args) {
		$items = array();
		$params = array(
			'post_type' => $this->get_post_type('menu_item'),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'tax_query' => array()
		);
		if (!empty($args['categ'])) {
			if (is_array($args['categ'])) {
				$category_ids = $args['categ'];
			} else {
				$category_ids = explode(',', $args['categ']);
			}
		}
		if (!empty($args['tags_list'])) {
			if (is_array($args['tags_list'])) {
				$tags_ids = $args['tags_list'];
			} else {
				$tags_ids = explode(',', $args['tags_list']);
			}
		}

		if (!empty($args['item_ids'])) {
			$params['post__in'] = explode(',', $args['item_ids']);
		}
		if (!empty($args['categ']) && empty($args['tags_list'])) {
			foreach ($category_ids as $id) {
				$term_params = array_merge($params, array('tax_query' =>
						array(
							array(
								'taxonomy' => $this->get_tax_name('menu_category'),
								'field' => 'id',
								'terms' => $id,
								'include_children' => false
							)
						)
					)
				);
				$items[$id] = array('term' => get_term($id, $this->get_tax_name('menu_category')), 'posts' => get_posts($term_params));
			}
		}
		if (!empty($args['tags_list']) && empty($args['categ'])) {
			foreach ($tags_ids as $id) {
				$term_params = array_merge($params, array('tax_query' =>
						array(
							array(
								'taxonomy' => $this->get_tax_name('menu_tag'),
								'field' => 'id',
								'terms' => $id,
								'include_children' => false
							)
						)
					)
				);
				$items[$id] = array('term' => get_term($id, $this->get_tax_name('menu_tag')), 'posts' => get_posts($term_params));
			}
		}
		if (!empty($category_ids) && !empty($tags_ids)) {
			foreach ($category_ids as $id) {
				$term_params = array_merge($params, array('tax_query' =>
						array(
							'relation' => 'AND',
							array(
								'taxonomy' => $this->get_tax_name('menu_category'),
								'field' => 'id',
								'terms' => $id,
								'include_children' => false
							),
							array(
								'taxonomy' => $this->get_tax_name('menu_tag'),
								'field' => 'id',
								'terms' => $tags_ids,
								'include_children' => false
							),
						)
					)
				);
				$items[$id] = array('term' => get_term($id, $this->get_tax_name('menu_category')), 'posts' => get_posts($term_params));
			}
		} elseif (empty($category_ids) && empty($tags_ids) && !empty($args['item_ids'])) {
			$items[0] = array('term' => '', 'posts' => get_posts($params));
		} elseif (empty($category_ids) && empty($tags_ids) && empty($args['item_ids'])) {
			$items[0] = array('term' => '', 'posts' => get_posts($params));
		}
		return $items;
	}

	/**
	 * Get menu item options
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_menu_item_options(array $args) {
		$options = array();
		if (!empty($args['posts'])) {
			foreach ($args['posts'] as $key => $post) {
				$options[$key] = $this->get_menu_item_option($post);
			}
		}
		return $options;
	}

	/**
	 * Get menu item option
	 *
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function get_menu_item_option(\WP_Post $post) {
		global $mprm_view_args;
		$options = array();
		$image_id = get_post_thumbnail_id($post->ID);
		if ("list" != $mprm_view_args['view']) {

			$mprm_view_args['col'] += 0;
			if ($mprm_view_args['col'] <= 2) {
				$thumbnail_type = 'mprm-big';
			} else {
				$thumbnail_type = 'mprm-middle';
			}
		} else {
			$thumbnail_type = 'mprm-thumbnail';
		}
		if ($thumbnail_type) {
			$options['image'] = wp_get_attachment_image($image_id, $thumbnail_type, false, array('class' => apply_filters('mprm-item-image', "mprm-image")));
		}
		$options['product_price'] = $this->get_price($post->ID, true);
		$options['excerpt'] = $post->post_excerpt;
		$options['ingredients'] = wp_get_post_terms($post->ID, $this->get_tax_name('ingredient'));
		$options['attributes'] = $this->get_attributes($post);
		$options['tags'] = wp_get_post_terms($post->ID, $this->get_tax_name('menu_tag'));
		return $options;
	}

	/**
	 * Is nutritional empty
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function is_arr_values_empty($data) {
		if ($data === false || $data == NULL)
			return true;

		if (is_array($data) && !empty($data)) {
			$empty_count = 0;
			foreach ($data as $item) {
				if (!empty($item) && is_array($item)) {
					foreach ($item as $key => $value) {
						if ($key == 'val' && empty($value)) {
							$empty_count++;
						}
					}
				}
			}
			if (count($data) == $empty_count) {
				return true;
			}
		}
		return false;
	}
}
