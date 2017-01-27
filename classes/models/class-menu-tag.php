<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\models\parents\Term;

/**
 * Class Menu_tag
 *
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
	
	/**
	 * @param $tags
	 * @param string $before
	 * @param string $sep
	 * @param string $after
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function create_custom_tags_list($tags, $before = '', $sep = '', $after = '', $id = 0) {
		global $post;
		if ($post->post_type === $this->post_types[ 'menu_item' ]) {
			$id = ($id === 0) ? $post->id : $id;
			$_tags = get_the_term_list($id, $this->taxonomy_names[ 'menu_tag' ], $before, $sep, $after);
			$tags = apply_filters('mprm_the_tags', $_tags, $tags);
		}
		
		return $tags;
	}
}
