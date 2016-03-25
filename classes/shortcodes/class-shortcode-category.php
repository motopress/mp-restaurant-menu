<?php

namespace mp_restaurant_menu\classes\shortcodes;

use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\View;

class Shortcode_Category extends Shortcodes {

	protected static $instance;

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
	 * @return \mp_restaurant_menu\classes\type|string
	 */
	public function render_shortcode($args) {
		global $mprm_view_args;
		$mprm_view_args = $args;
		$mprm_view_args['categories_terms'] = array();
		$mprm_view_args['action_path'] = "shortcodes/category/{$args['view']}/item";
		return View::get_instance()->render_html("shortcodes/category/index", $args, false);
	}

	/**
	 * Integration in motopress
	 * @param $motopressCELibrary
	 */
	public function integration_motopress($motopressCELibrary) {
//		$columns = $this->create_list_motopress(Column::get_instance()->get_all_column());
//		$events = $this->create_list_motopress(Events::get_instance()->get_all_events());
//		$categories = get_terms('mp-event_category', 'orderby=count&hide_empty=0');
//		$categories = $this->create_list_motopress($categories, 'term');

		$attributes = array(
//			'col' => array(
//				'type' => 'select-multiple',
//				'label' => __('Column','mp-restaurant-menu'),
//				'list' => $columns),
//			'events' => array(
//				'type' => 'select-multiple',
//				'label' => __('Events','mp-restaurant-menu'),
//				'list' => $events),
//			'event_categ' => array(
//				'type' => 'select-multiple',
//				'label' => __('Event categories','mp-restaurant-menu'),
//				'list' => $categories),
//			'increment' => array(
//				'type' => 'select',
//				'label' => __('Hour measure','mp-restaurant-menu'),
//				'list' => array('1' => __('Hour (1h)','mp-restaurant-menu'), '0.5' => __('Half hour (30min)','mp-restaurant-menu'), '0.25' => __('Quarter hour (15min)','mp-restaurant-menu'))),
//			'view' => array(
//				'type' => 'select',
//				'label' => __('Filter style','mp-restaurant-menu'),
//				'list' => array('dropdown_list' => __('Dropdown list','mp-restaurant-menu'), 'tabs' => __('Tabs','mp-restaurant-menu'))
//			),
//			'label' => array(
//				'type' => 'text',
//				'label' => __('Filter label','mp-restaurant-menu'),
//				'default' => __('All Events','mp-restaurant-menu')
//			),
//			'hide_label' => array(
//				'type' => 'select',
//				'label' => __("Hide 'All Events' view",'mp-restaurant-menu'),
//				'list' => array('0' => __('No','mp-restaurant-menu'), '1' => __('Yes','mp-restaurant-menu'))
//			),
//			'hide_hrs' => array(
//				'type' => 'select',
//				'label' => __('Hide first (hours) column','mp-restaurant-menu'),
//				'list' => array('0' => __('No','mp-restaurant-menu'), '1' => __('Yes','mp-restaurant-menu'))
//			),
//			'hide_empty_rows' => array(
//				'type' => 'select',
//				'label' => __('Hide empty rows','mp-restaurant-menu'),
//				'list' => array('1' => __('Yes','mp-restaurant-menu'), '0' => __('No','mp-restaurant-menu')),
//				'default' => 1
//			),
//			'title' => array(
//				'type' => 'radio-buttons',
//				'label' => __('Title','mp-restaurant-menu'),
//				'default' => 1,
//				'list' => array('1' => __('Yes','mp-restaurant-menu'), '0' => __('No','mp-restaurant-menu')),
//			),
//			'time' => array(
//				'type' => 'radio-buttons',
//				'label' => __('Time','mp-restaurant-menu'),
//				'default' => 1,
//				'list' => array('1' => __('Yes','mp-restaurant-menu'), '0' => __('No','mp-restaurant-menu')),
//			),
//			'sub-title' => array(
//				'type' => 'radio-buttons',
//				'label' => __('Subtitle','mp-restaurant-menu'),
//				'default' => 1,
//				'list' => array('1' => __('Yes','mp-restaurant-menu'), '0' => __('No','mp-restaurant-menu')),
//			),
//			'description' => array(
//				'type' => 'radio-buttons',
//				'label' => __('Description','mp-restaurant-menu'),
//				'default' => 0,
//				'list' => array('1' => __('Yes','mp-restaurant-menu'), '0' => __('No','mp-restaurant-menu')),
//			),
//			'user' => array(
//				'type' => 'radio-buttons',
//				'label' => __('User','mp-restaurant-menu'),
//				'default' => 0,
//				'list' => array('1' => __('Yes','mp-restaurant-menu'), '0' => __('No','mp-restaurant-menu')),
//			),
//			'disable_event_url' => array(
//				'type' => 'select',
//				'label' => __('Disable event URL','mp-restaurant-menu'),
//				'list' => array('0' => __('No','mp-restaurant-menu'), '1' => __('Yes','mp-restaurant-menu'))
//			),
//			'text_align' => array(
//				'type' => 'select',
//				'label' => __('Text align','mp-restaurant-menu'),
//				'list' => array('center' => __('center','mp-restaurant-menu'), 'left' => __('left','mp-restaurant-menu'), 'right' => __('right','mp-restaurant-menu'))
//			),
//			'id' => array(
//				'type' => 'text',
//				'label' => __('Id','mp-restaurant-menu')
//			),
//			'row_height' => array(
//				'type' => 'text',
//				'label' => __('Row height (in px)','mp-restaurant-menu'),
//				'default' => 31
//			),
//			'responsive' => array(
//				'type' => 'select',
//				'label' => __('Responsive','mp-restaurant-menu'),
//				'list' => array('1' => __('Yes','mp-restaurant-menu'), '0' => __('No','mp-restaurant-menu')),
//				'default' => 1,
//			)
		);
		$mprm_category_shortcode = new \MPCEObject('mprm-category-shortcode', __('Restaurant Menu Categories','mp-restaurant-menu'), '', $attributes);
		$motopressCELibrary->addObject($mprm_category_shortcode, 'other');
	}
}
