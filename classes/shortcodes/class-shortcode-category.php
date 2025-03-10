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

		$args = shortcode_atts(
			array(
				'view' => 'grid',
				'categ' => '',
				'col' => '1',
				'categ_name' => '',
				'feat_img' => '',
				'categ_icon' => '',
				'categ_descr' => '',
				'desc_length' => '',
		), $args );

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
				'label' => esc_html__('View mode', 'mp-restaurant-menu'),
				'list' => array('grid' => esc_html__('Grid', 'mp-restaurant-menu'), 'list' => esc_html__('List', 'mp-restaurant-menu')),
				'default' => 'grid'
			),
			'categ' => array(
				'type' => 'select-multiple',
				'label' => esc_html__('Categories', 'mp-restaurant-menu'),
				'list' => $categories),
			'col' => array(
				'type' => 'select',
				'label' => esc_html__('Columns', 'mp-restaurant-menu'),
				'list' => array(
					'1' => esc_html__('1 column', 'mp-restaurant-menu'),
					'2' => esc_html__('2 columns', 'mp-restaurant-menu'),
					'3' => esc_html__('3 columns', 'mp-restaurant-menu'),
					'4' => esc_html__('4 columns', 'mp-restaurant-menu'),
					'6' => esc_html__('6 columns', 'mp-restaurant-menu')),
				'default' => 1
			),
			'categ_name' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show category name', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'feat_img' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show category featured image', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'categ_icon' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show category icon', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'categ_descr' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show category description', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'desc_length' => array(
				'type' => 'text',
				'label' => esc_html__('Description length', 'mp-restaurant-menu'),
			)
		);
		$mprm_categories = new \MPCEObject('mprm_categories', esc_html__('Menu Categories', 'mp-restaurant-menu'), '', $attributes);

		$motopressCELibrary->addObject($mprm_categories, 'other');
	}
}
