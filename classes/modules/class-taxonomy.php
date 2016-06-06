<?php
namespace mp_restaurant_menu\classes\modules;

use mp_restaurant_menu\classes\Module;
use mp_restaurant_menu\classes\View;

class Taxonomy extends Module {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register taxonomy
	 *
	 * @param $params
	 */
	public function register(array $params, $plugin_name = 'mp-restaurant-menu') {
		$args = array(
			'label' => ucfirst($params['titles']['many']),
			'labels' => $this->get_labels($params, $plugin_name),
			'parent_item' => __('Parent ' . $params['titles']['single'], $plugin_name),
			'parent_item_colon' => __('Parent ' . $params['titles']['single'], $plugin_name),
			'public' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_tagcloud' => true,
			'show_in_quick_edit' => true,
			'hierarchical' => true,
			'update_count_callback' => '',
			'rewrite' => (!empty($params['slug'])) ? array(
				'slug' => $params['slug'],
				'with_front' => true,
				'hierarchical' => true
			) : false,
			'capabilities' => array(),
			'meta_box_cb' => null,
			'show_admin_column' => false,
			'_builtin' => false,
		);
		register_taxonomy($params['taxonomy'], $params['object_type'], $args);
	}

	/**
	 * Render html for filter taxonomy link
	 *
	 * @param $post
	 * @param $tax_name
	 *
	 * @return string
	 */
	public function get_the_term_filter_list($post, $tax_name) {
		$taxonomies = wp_get_post_terms($post->ID, $tax_name);
		$taxonomies_html = "";
		foreach ($taxonomies as $tax) {
			$data["wp"] = $tax;
			$data["filter_link"] = admin_url('edit.php?post_type=' . $post->post_type . '&' . $tax->taxonomy . '=' . $tax->slug);
			$taxonomies_html .= View::get_instance()->render_html("../admin/taxonomies/taxonomy-link", $data, false);
		}
		return (!empty($taxonomies_html)) ? $taxonomies_html : "—";
	}

	public function get_terms($name) {
		return get_terms($name);
	}
}
