<?php
namespace mp_restaurant_menu\classes\widgets;

use mp_restaurant_menu\classes\modules\Taxonomy;
use mp_restaurant_menu\classes\View;

/**
 * Class Category_widget
 * @package mp_restaurant_menu\classes\widgets
 */
class Category_widget extends \WP_Widget {

	protected static $instance;
	protected $widget_css_class;
	protected $widget_description;
	protected $widget_id;
	protected $widget_name;

	/**
	 * Category_widget constructor.
	 */
	public function __construct() {
		$this->widget_css_class = 'mprm_widget';
		$this->widget_description = __('Display categories.', 'mp-restaurant-menu');
		$this->widget_id = 'mprm_category';
		$this->widget_name = __('Restaurant Menu Categories', 'mp-restaurant-menu');
		$widget_ops = array(
			'classname' => $this->widget_css_class,
			'description' => $this->widget_description
		);

		parent::__construct($this->widget_id, $this->widget_name, $widget_ops);
	}

	/**
	 * @return Category_widget
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form($instance) {
		$data = $this->get_data($instance);
		$data['categories'] = Taxonomy::get_instance()->get_terms('mp_menu_category');
		$data['tags'] = Taxonomy::get_instance()->get_terms('mp_menu_tag');
		$data['categ'] = !empty($instance['categ']) ? $instance['categ'] : array();
		$data['widget_object'] = $this;
		View::get_instance()->render_html('../admin/widgets/category/form', $data, true);
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
	 * Display widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
		$data = $this->get_data($instance);
		global $mprm_view_args, $mprm_widget_args;
		$mprm_view_args = $data;
		$mprm_view_args['action_path'] = "widgets/category/{$data['view']}/item";
		$mprm_widget_args = $args;
		View::get_instance()->get_template('widgets/category/index', $data);
	}
}
