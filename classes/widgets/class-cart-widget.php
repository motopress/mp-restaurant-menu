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
		parent::__construct('mprm_cart_widget', __('Menu items Cart', 'mp-restaurant-menu'), array('description' => __('Display the menu items shopping cart', 'mp-restaurant-menu')));
	}

	/**
	 * @see WP_Widget::widget
	 */
	function widget($args, $instance) {

		if (!empty($instance['hide_on_checkout']) && mprm_is_checkout()) {
			return;
		}

		$args['id'] = (isset($args['id'])) ? $args['id'] : 'mprm_cart_widget';
		$instance['title'] = (isset($instance['title'])) ? $instance['title'] : '';

		$title = apply_filters('widget_title', $instance['title'], $instance, $args['id']);

		echo $args['before_widget'];

		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action('mprm_before_cart_widget');





//		edd_shopping_cart(true);

		do_action('mprm_after_cart_widget');

		echo $args['after_widget'];
	}

	/**
	 * @see WP_Widget::update
	 */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['hide_on_checkout'] = isset($new_instance['hide_on_checkout']);

		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {

		$defaults = array(
			'title' => '',
			'hide_on_checkout' => false,
		);

		$instance = wp_parse_args((array)$instance, $defaults); ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'mp-restaurant-menu'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo $instance['title']; ?>"/>
		</p>

		<p>
			<input <?php checked($instance['hide_on_checkout'], true); ?> id="<?php echo esc_attr($this->get_field_id('hide_on_checkout')); ?>" name="<?php echo esc_attr($this->get_field_name('hide_on_checkout')); ?>" type="checkbox"/>
			<label for="<?php echo esc_attr($this->get_field_id('hide_on_checkout')); ?>"><?php _e('Hide on Checkout Page', 'mp-restaurant-menu'); ?></label>
		</p>

		<?php
	}
}


