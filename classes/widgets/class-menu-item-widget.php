<?php
namespace mp_restaurant_menu\classes\widgets;

use mp_restaurant_menu\classes\View;
use mp_restaurant_menu\classes\modules\Taxonomy;

class Menu_item_widget extends \WP_Widget {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->widget_cssclass = 'mprm_widget';
		$this->widget_description = __('Display menu items.', 'mp-restaurant-menu');
		$this->widget_id = 'mprm_menu_item';
		$this->widget_name = __('Restaurant Menu Items', 'mp-restaurant-menu');
		$widget_ops = array(
			'classname' => $this->widget_cssclass,
			'description' => $this->widget_description
		);
		parent::__construct($this->widget_id, $this->widget_name, $widget_ops);
	}

	/**
	 * Get default data
	 *
	 * @param type $instance
	 *
	 * @return string
	 */
	function get_data($instance) {
		if (!empty($instance)) {
			$data = $instance;
		} else {
			//default configuration
			$data = array(
				'view' => 'grid',
				'col' => '1',
				'sort' => 'name',
				'categ_name' => 'only_text',
				'feat_img' => '1',
				'excerpt' => '1',
				'price' => '1',
				'tags' => '1',
				'ingredients' => '1',
				'link_item' => '1',
				'show_attributes' => '1'
			);
		}
		return $data;
	}

	/**
	 *
	 * @param array $instance
	 */
	public function form($instance) {
		$data = $this->get_data($instance);
		$data['categories'] = Taxonomy::get_instance()->get_terms('mp_menu_category');
		$data['menu_tags'] = Taxonomy::get_instance()->get_terms('mp_menu_tag');
		$data['widget_object'] = $this;
		$data['categ'] = !empty($instance['categ']) ? $instance['categ'] : array();
		$data['tags_list'] = !empty($instance['tags_list']) ? $instance['tags_list'] : array();
		View::get_instance()->render_html('../admin/widgets/menu/form', $data, true);
	}

	/**
	 * Display widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
		$data = $this->get_data($instance);
		global $mprm_view_args, $mprm_widget_args;
		$mprm_view_args = $data;
		$mprm_view_args['action_path'] = "widgets/menu/{$data['view']}/item";
		$mprm_widget_args = $args;
		View::get_instance()->render_html("widgets/menu/index", $data, true);
	}
}
