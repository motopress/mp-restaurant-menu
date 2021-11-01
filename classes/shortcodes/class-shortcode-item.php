<?php
namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\models\Menu_category;
use mp_restaurant_menu\classes\models\Menu_tag;
use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

/**
 * Class Shortcode_Item
 * @package mp_restaurant_menu\classes\shortcodes
 */
class Shortcode_Item extends Shortcodes {
	protected static $instance;

	/**
	 * @return Shortcode_Item
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init shortode item
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function render_shortcode(array $args) {
		global $mprm_view_args;
		Media::get_instance()->add_plugin_js('shortcode');
		$mprm_view_args = $args;
		$mprm_view_args['action_path'] = "shortcodes/menu/{$args['view']}/item";
		return View::get_instance()->get_template_html('shortcodes/menu/index', $args);
	}

	/**
	 * Integration in motopress
	 *
	 * @param $motopressCELibrary
	 */
	public function integration_motopress($motopressCELibrary) {
		$categories = $this->create_list_motopress(Menu_category::get_instance()->get_categories_by_ids(), 'term');
		$tags = $this->create_list_motopress(Menu_tag::get_instance()->get_tags_by_ids(), 'term');
		$attributes = array(
			'view' => array(
				'type' => 'select',
				'label' => esc_html__('View mode', 'mp-restaurant-menu'),
				'list' => array('grid' => esc_html__('Grid', 'mp-restaurant-menu'), 'list' => esc_html__('List', 'mp-restaurant-menu'), 'simple-list' => esc_html__('Simple list', 'mp-restaurant-menu')),
				'default' => 'grid'
			),
			'categ' => array(
				'type' => 'select-multiple',
				'label' => esc_html__('Categories', 'mp-restaurant-menu'),
				'list' => $categories),
			'tags_list' => array(
				'type' => 'select-multiple',
				'label' => esc_html__('Tags', 'mp-restaurant-menu'),
				'list' => $tags),
			'item_ids' => array(
				'type' => 'text',
				'label' => esc_html__('Menu item IDs', 'mp-restaurant-menu'),
			),
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
				'type' => 'select',
				'label' => esc_html__('Show category name', 'mp-restaurant-menu'),
				'list' => array(
					'only_text' => esc_html__('Only text', 'mp-restaurant-menu'),
					'with_img' => esc_html__('Title with image', 'mp-restaurant-menu'),
					'none' => esc_html__('Don`t show', 'mp-restaurant-menu'),
				),
				'default' => 'only_text'
			),
			'price_pos' => array(
				'type' => 'select',
				'label' => esc_html__('Price position', 'mp-restaurant-menu'),
				'list' => array(
					'points' => esc_html__('Dotted line and price on the right', 'mp-restaurant-menu'),
					'right' => esc_html__('Price on the right', 'mp-restaurant-menu'),
					'after_title' => esc_html__('Price next to the title', 'mp-restaurant-menu'),
				),
				'dependency' => array(
					'parameter' => 'view',
					'value' => 'simple-list'
				),
				'default' => 'right'
			),
			'show_attributes' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show attributes', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'feat_img' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show featured image', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'excerpt' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show excerpt', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'price' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show price', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'tags' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show tags', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'ingredients' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show ingredients', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'buy' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Show buy button', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'link_item' => array(
				'type' => 'radio-buttons',
				'label' => esc_html__('Link item', 'mp-restaurant-menu'),
				'default' => 1,
				'list' => array('1' => esc_html__('Yes', 'mp-restaurant-menu'), '0' => esc_html__('No', 'mp-restaurant-menu')),
			),
			'desc_length' => array(
				'type' => 'text',
				'label' => esc_html__('Excerpt length', 'mp-restaurant-menu'),
			)
		);
		$mprm_items = new \MPCEObject('mprm_items', esc_html__('Menu Items', 'mp-restaurant-menu'), '', $attributes);
		$motopressCELibrary->addObject($mprm_items, 'other');
	}
}