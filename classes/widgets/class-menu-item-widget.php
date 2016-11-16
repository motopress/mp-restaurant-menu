<?php
namespace mp_restaurant_menu\classes\widgets;

use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\modules\Taxonomy;
use mp_restaurant_menu\classes\View;

/**
 * Class Menu_item_widget
 * @package mp_restaurant_menu\classes\widgets
 */
class Menu_item_widget extends \WP_Widget {

	protected static $instance;

	protected $widget_css_class;
	protected $widget_description;
	protected $widget_id;
	protected $widget_name;

	/**
	 * Menu_item_widget constructor.
	 */
	public function __construct() {
		$this->widget_css_class = 'mprm_widget';
		$this->widget_description = __('Display menu items.', 'mp-restaurant-menu');
		$this->widget_id = 'mprm_menu_item';
		$this->widget_name = __('Restaurant Menu Items', 'mp-restaurant-menu');
		$widget_ops = array(
			'classname' => $this->widget_css_class,
			'description' => $this->widget_description
		);

		parent::__construct($this->widget_id, $this->widget_name, $widget_ops);
	}

	/**
	 * @return Menu_item_widget
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
		if ($data['view'] == 'simple-list') {
			$data['feat_img'] = '';
			$data['categ_name'] = (empty($data['categ_name']) || ($data['categ_name'] == 'with_img')) ? 'only_text' : $data['categ_name'];
		}
		$data['categories'] = Taxonomy::get_instance()->get_terms('mp_menu_category');
		$data['menu_tags'] = Taxonomy::get_instance()->get_terms('mp_menu_tag');
		$data['price_pos'] = empty($data['price_pos']) ? 'points' : $data['price_pos'];
		$data['widget_object'] = $this;
		$data['categ'] = !empty($instance['categ']) ? $instance['categ'] : array();
		$data['tags_list'] = !empty($instance['tags_list']) ? $instance['tags_list'] : array();
		View::get_instance()->render_html('../admin/widgets/menu/form', $data, true);
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
				'view' => 'grid',
				'col' => '1',
				'sort' => 'name',
				'categ_name' => 'only_text',
				'feat_img' => '1',
				'excerpt' => '1',
				'price_pos' => 'points',
				'price' => '1',
				'tags' => '1',
				'ingredients' => '1',
				'buy' => '1',
				'link_item' => '1',
				'show_attributes' => '1'
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
		global $mprm_view_args, $mprm_widget_args;

		Media::get_instance()->add_plugin_js('widget');
		$data = $this->get_data($instance);

		if ($data['view'] == 'simple-list') {
			$data['feat_img'] = '';
			$data['buy'] = '0';
			$data['categ_name'] = (empty($data['categ_name']) || ($data['categ_name'] == 'with_img')) ? 'only_text' : $data['categ_name'];
		} else {
			$data['buy'] = empty($data['buy']) ? '0' : $data['buy'];
		}

		$mprm_view_args = $data;
		$mprm_view_args['action_path'] = "widgets/menu/{$data['view']}/item";
		$mprm_widget_args = $args;

		View::get_instance()->get_template("widgets/menu/index", $data);
	}
}
