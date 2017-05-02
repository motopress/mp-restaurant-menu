<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\models\parents\Term;
use mp_restaurant_menu\classes\View;

/**
 * Class Menu_category
 * @package mp_restaurant_menu\classes\models
 */
class Menu_category extends Term {
	protected static $instance;
	
	/**
	 * @return Menu_category
	 */
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
		$data[ 'placeholder' ] = MP_RM_MEDIA_URL . 'img/placeholder.png';
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
				'order' => '0',
			);
		}
		$data[ 'placeholder' ] = MP_RM_MEDIA_URL . 'img/placeholder.png';
		$data[ 'order' ] = empty($data[ 'order' ]) ? '0' : $data[ 'order' ];
		$category_name = $this->get_tax_name('menu_category');
		View::get_instance()->render_html("../admin/taxonomies/{$category_name}/edit_form_fields", $data);
	}
	
	/**
	 * Get term params
	 *
	 * @param $term_id
	 * @param $field
	 *
	 * @return mixed
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
		$defaults = array(
			'iconname' => '',
			'thumbnail_id' => '',
			'order' => '0'
		);
		$term_meta = wp_parse_args($term_meta, $defaults);
		// thumbnail value
		if (!empty($term_meta[ 'thumbnail_id' ])) {
			$term_meta[ 'thumb_url' ] = wp_get_attachment_thumb_url($term_meta[ 'thumbnail_id' ]);
			$term_meta[ 'full_url' ] = wp_get_attachment_url($term_meta[ 'thumbnail_id' ]);
			$attachment_image_src = wp_get_attachment_image_src($term_meta[ 'thumbnail_id' ], 'mprm-big');
			$term_meta[ 'image' ] = $attachment_image_src[ 0 ];
		}
		if (!empty($field)) {
			return empty($term_meta) ? false : (isset($term_meta[ $field ]) ? $term_meta[ $field ] : $term_meta[ $field ]);
		} else {
			return $term_meta;
		}
	}
	
	/**
	 * @param $mprm_term
	 * @param string $size
	 *
	 * @return bool
	 */
	public function get_term_image($mprm_term, $size = 'mprm-big') {
		if (!empty($mprm_term) && is_object($mprm_term)) {
			$term_meta = $this->get_term_params($mprm_term->term_id);
			if (!empty($term_meta[ 'thumbnail_id' ])) {
				$attachment_image_src = wp_get_attachment_image_src($term_meta[ 'thumbnail_id' ], $size);
				if (is_array($attachment_image_src)) {
					$image = $attachment_image_src[ 0 ];
					
					return $image;
				} else {
					return false;
				}
				
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * @param $mprm_term
	 *
	 * @return mixed|string
	 */
	public function get_term_icon($mprm_term) {
		if (!empty($mprm_term) && is_object($mprm_term)) {
			$icon = $this->get_term_params($mprm_term->term_id, 'iconname');
		}
		
		return (empty($icon) ? '' : $icon);
	}
	
	/**
	 * Get term order
	 *
	 * @param $mprm_term
	 *
	 * @return bool
	 */
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
		if (!empty($_POST[ 'term_meta' ])) {
			$term_meta = $_POST[ 'term_meta' ];
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
		$temp_terms = $sort_terms = array();
		$taxonomy = $this->get_tax_name('menu_category');
		$terms = $this->get_terms($taxonomy, $ids);
		
		if (!empty($terms)) {
			foreach ($terms as $key => $term) {
				$temp_terms[ $key ] = array('order' => $this->get_term_order($term), 'term' => $term);
			}
			$temp_terms = $this->sort_category_order($temp_terms);
			foreach ($temp_terms as $key_temp => $temp_term) {
				$sort_terms[ $key_temp ] = $temp_term[ 'term' ];
			}
			
			return $sort_terms;
			
		}
		
		return $terms;
	}
	
	/**
	 * Get term order
	 *
	 * @param $mprm_term
	 *
	 * @return mixed|string
	 */
	public function get_term_order($mprm_term) {
		if (!empty($mprm_term) && is_object($mprm_term)) {
			$order = $this->get_term_params($mprm_term->term_id, 'order');
		} elseif (!empty($mprm_term) && is_numeric($mprm_term)) {
			$order = $this->get_term_params($mprm_term, 'order');
		}
		
		return (empty($order) ? '0' : $order);
		
	}
	
	/**
	 * Sort category by order
	 *
	 * @param $items
	 *
	 * @return mixed
	 */
	public function sort_category_order($items) {
		usort($items, function ($a, $b) {
			if ($a[ 'order' ] == $b[ 'order' ]) {
				return 0;
			}
			
			return ($a[ 'order' ] < $b[ 'order' ]) ? -1 : 1;
		});
		
		return $items;
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
		foreach ($args[ 'terms' ] as $key => $term) {
			$args[ 'cat_id' ] = $term->term_id;
			$option = $this->get_term_params($term->term_id);
			$options[ $key ] = $option;
			$options[ $key ][ 'posts' ] = $args[ 'posts' ] = $this->get('menu_item')->get_menu_items($args);
			$options[ $key ][ 'posts_options' ] = $this->get('menu_item')->get_menu_item_options($args);
		}
		
		return $options;
	}
	
	/**
	 * @param string $the_list
	 * @param string $separator
	 * @param string $parents
	 *
	 * @return mixed
	 */
	public function create_custom_category_list($the_list = '', $separator = '', $parents = '') {
		global $post, $wp_rewrite;
		
		if (!empty($post) && $post->post_type === $this->post_types[ 'menu_item' ] && !is_admin()) {
			$the_list = '';
			
			$rel = (is_object($wp_rewrite) && $wp_rewrite->using_permalinks()) ? 'rel="category tag"' : 'rel="category"';
			$categories = get_the_terms($post->ID, $this->taxonomy_names[ 'menu_category' ]);
			
			if (!empty($categories)) {
				if ('' == $separator) {
					$the_list .= '<ul class="post-categories">';
					foreach ($categories as $category) {
						$the_list .= "\n\t<li>";
						switch (strtolower($parents)) {
							case 'multiple':
								if ($category->parent)
									$the_list .= get_category_parents($category->parent, true, $separator);
								$the_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" ' . $rel . '>' . $category->name . '</a></li>';
								break;
							case 'single':
								$the_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '"  ' . $rel . '>';
								if ($category->parent)
									$the_list .= get_category_parents($category->parent, false, $separator);
								$the_list .= $category->name . '</a></li>';
								break;
							case '':
							default:
								$the_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" ' . $rel . '>' . $category->name . '</a></li>';
						}
					}
					$the_list .= '</ul>';
				} else {
					$i = 0;
					foreach ($categories as $category) {
						if (0 < $i)
							$the_list .= $separator;
						switch (strtolower($parents)) {
							case 'multiple':
								if ($category->parent)
									$the_list .= get_category_parents($category->parent, true, $separator);
								$the_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" ' . $rel . '>' . $category->name . '</a>';
								break;
							case 'single':
								$the_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" ' . $rel . '>';
								if ($category->parent)
									$the_list .= get_category_parents($category->parent, false, $separator);
								$the_list .= "$category->name</a>";
								break;
							case '':
							default:
								$the_list .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" ' . $rel . '>' . $category->name . '</a>';
						}
						++$i;
					}
				}
			}
		}
		
		return apply_filters('mprm_the_category', $the_list, $separator, $parents);
	}
}
