<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\models\Menu_category;
use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

/**
 * Class Shortcode_Category
 * @package mp_restaurant_menu\classes\shortcodes
 */
class Shortcode_Category extends Shortcodes {
	protected static $instance;

	/**
	 * @return Shortcode_Category
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Main functiob for short code category
	 *
	 * @param $args
	 *
	 * @return \mp_restaurant_menu\classes\|string
	 */
	public function render_shortcode($args) {
		global $mprm_view_args;
		Media::get_instance()->add_plugin_js('shortcode');
		$mprm_view_args = $args;
		$mprm_view_args['categories_terms'] = array();
		$mprm_view_args['action_path'] = "shortcodes/category/{$args['view']}/item";

		return View::get_instance()->get_template_html('shortcodes/category/index', $args);
	}

	/**
	 * Integration in motopress
	 *
	 * @param $motopressCELibrary
	 */
	public function integration_motopress($motopressCELibrary) {
		$categories = $this->create_list_motopress(Menu_category::get_instance()->get_categories_by_ids(), 'term');
		$attributes = array(
			'view' => array(
				'type' => 'select',
				'label' => __('View mode', 'mp-restaurant-menu'),
				'list' => array('grid' => __('Grid', 'mp-restaurant-menu'), 'list' => __('List', 'mp-restaurant-menu')),
				'default' => 'grid'
			),
			'categ' => array(
				'type' => 'select-multiple',
				'label' => __('Categories', 'mp-restaurant-menu'),
				'list' => $categories),
			'col' => array(
				'type' => 'select',
				'label' => __('Columns', 'mp-restaurant-menu'),
				'list' => array(
					'1' => __('1 column', 'mp-restaurant-menu'),
					'2' => __('2 columns', 'mp-restaurant-menu'),
					'3' => __('3 columns', 'mp-restaurant-menu'),
					'4' => __('4 columns', 'mp-restaurant-menu'),
					'6' => __('6 columns', 'mp-restaurant-menu')),
				'default' => 1
			),
			'categ_name' => array(
				'type' => 'radio-buttons',
				'label' => __('Show category name', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => __('Yes', 'mp-restaurant-menu'), '0' => __('No', 'mp-restaurant-menu')),
			),
			'feat_img' => array(
				'type' => 'radio-buttons',
				'label' => __('Show category featured image', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => __('Yes', 'mp-restaurant-menu'), '0' => __('No', 'mp-restaurant-menu')),
			),
			'categ_icon' => array(
				'type' => 'radio-buttons',
				'label' => __('Show category icon', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => __('Yes', 'mp-restaurant-menu'), '0' => __('No', 'mp-restaurant-menu')),
			),
			'categ_descr' => array(
				'type' => 'radio-buttons',
				'label' => __('Show category description', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => __('Yes', 'mp-restaurant-menu'), '0' => __('No', 'mp-restaurant-menu')),
			),
			'desc_length' => array(
				'type' => 'text',
				'label' => __('Description length', 'mp-restaurant-menu'),
			)
		);
		$mprm_categories = new \MPCEObject('mprm_categories', __('Menu Categories', 'mp-restaurant-menu'), '', $attributes);

		$motopressCELibrary->addObject($mprm_categories, 'other');
	}
}
