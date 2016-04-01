<?php

namespace mp_restaurant_menu\classes\widgets;

use mp_restaurant_menu\classes\View;

class Cart_widget extends \WP_Widget {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->widget_cssclass = 'mprm_widget';
		$this->widget_description = __('Display cart.', 'mp-restaurant-menu');
		$this->widget_id = 'mprm_cart';
		$this->widget_name = __('Restaurant Menu Cart', 'mp-restaurant-menu');
		$widget_ops = array(
			'classname' => $this->widget_cssclass,
			'description' => $this->widget_description
		);
		parent::__construct($this->widget_id, $this->widget_name, $widget_ops);
	}

	/**
	 * Get default data
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	function get_data($instance) {
		if (!empty($instance)) {
			$data = $instance;
		} else {
			//default configuration
			$data = array(
				"view" => "grid",
				"col" => "1",
				"sort" => "title",
				"categ_name" => "1",
				"feat_img" => "1",
				"categ_icon" => "1",
				"categ_descr" => "1"
			);
		}
		return $data;
	}

	/**
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form($instance) {
		$data = $this->get_data($instance);

		$data['widget_object'] = $this;
		View::get_instance()->render_html('../admin/widgets/category/form', $data, true);
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

		$mprm_view_args['action_path'] = "widgets/cart/{$data['view']}/item";
		$mprm_widget_args = $args;
		View::get_instance()->render_html("widgets/cart/index", $data, true);
	}
}
