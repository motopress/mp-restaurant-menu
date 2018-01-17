<?php
namespace mp_restaurant_menu\classes\modules;

use mp_restaurant_menu\classes\models\Order;
use mp_restaurant_menu\classes\Module;

/**
 * Class Post
 * @package mp_restaurant_menu\classes\modules
 */
class Post extends Module {
	protected static $instance;

	private $metaboxes;

	/**
	 * @return Post
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register our custom post statuses, used for order status.
	 */
	public static function register_post_status() {

		register_post_status('mprm-pending', array(
			'label' => _x('Pending Payment', 'Order status', 'mp-restaurant-menu'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'mp-restaurant-menu')
		));

		register_post_status('mprm-completed', array(
			'label' => _x('Payment Complete', 'Order status', 'mp-restaurant-menu'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Payment Complete <span class="count">(%s)</span>', 'Payment Complete <span class="count">(%s)</span>', 'mp-restaurant-menu')
		));

		register_post_status('mprm-refunded', array(
			'label' => _x('Refunded', 'Order status', 'mp-restaurant-menu'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'mp-restaurant-menu')
		));

		register_post_status('mprm-failed', array(
			'label' => _x('Failed', 'Order status', 'mp-restaurant-menu'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'mp-restaurant-menu')
		));

		register_post_status('mprm-cooking', array(
			'label' => _x('Processing', 'Order status', 'mp-restaurant-menu'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'mp-restaurant-menu')
		));

		register_post_status('mprm-shipping', array(
			'label' => _x('Shipping', 'Order status', 'mp-restaurant-menu'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Shipping <span class="count">(%s)</span>', 'Shipping <span class="count">(%s)</span>', 'mp-restaurant-menu')
		));

		register_post_status('mprm-shipped', array(
			'label' => _x('Shipped', 'Order status', 'mp-restaurant-menu'),
			'public' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'mp-restaurant-menu')
		));
	}

	/**
	 * Register custom post
	 *
	 * @param array $params
	 * @param string $plugin_name
	 *
	 * @return bool
	 */
	public function register_post_type(array $params, $plugin_name = 'mp-restaurant-menu') {
		$args = array(
			'label' => $params['post_type'],
			'labels' => $this->get_labels($params, $plugin_name),
			"public" => true,
			"show_ui" => true,
			'show_in_menu' => false,
			"capability_type" => empty($params['capability_type']) ? "post" : $params['capability_type'],
			"menu_position" => 21,
			"hierarchical" => false,
			"map_meta_cap" => empty($params['map_meta_cap']) ? false : $params['map_meta_cap'],
			"rewrite" => (!empty($params['slug'])) ? array(
				'slug' => $params['slug'],
				'with_front' => true,
				'hierarchical' => true
			) : false,
			"supports" => $params['supports'],
			"show_in_admin_bar" => true
		);
		$status = register_post_type($params['post_type'], $args);
		if (!is_wp_error($status)) {
			return true;
		}
	}

	/**
	 * Set metabox params
	 *
	 * @param array $params
	 */
	public function set_metaboxes(array $params) {
		$this->metaboxes = $params;
	}

	/**
	 * Hook Add meta boxes
	 *
	 * @param string $post_type
	 */
	public function add_meta_boxes($post_type = '') {
		if (!empty($this->metaboxes) && is_array($this->metaboxes)) {
			foreach ($this->metaboxes as $metabox) {
				// add metabox to current post type
				$context = !empty($metabox['context']) ? $metabox['context'] : 'advanced';
				$callback = !empty($metabox['callback']) ? $metabox['callback'] : array($this, 'render_meta_box_content');
				$priority = !empty($metabox['priority']) ? $metabox['priority'] : 'high';
				$callback_args = !empty($metabox['callback_args']) ? $metabox['callback_args'] : array();
				$callback_args = array_merge($callback_args, array('name' => $metabox['name'], 'title' => $metabox['title']));
				add_meta_box($metabox['name'], $metabox['title'], $callback, $metabox['post_type'], $context, $priority, $callback_args);
			}
		}
		Order::get_instance()->init_metaboxes();
	}

	/**
	 * Hook Save post
	 *
	 * @param $post_id
	 *
	 * @return void|mixed
	 */
	public function save($post_id) {
		// Check nonce.
		if (!isset($_POST['mp-restaurant-menu' . '_nonce_box'])) {
			return $post_id;
		}
		$nonce = $_POST['mp-restaurant-menu' . '_nonce_box'];

		// Check correct nonce.
		if (!wp_verify_nonce($nonce, 'mp-restaurant-menu' . '_nonce')) {
			return $post_id;
		}

		// Check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		// Cher user rules
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}
		}

		if (isset($_POST['mprm_update'])) {
			if (($_POST['post_type'] == $this->post_types['order']) && (bool)$_POST['mprm_update']) {
				$this->get('payments')->update_payment_details($_POST);
			}
		}

		foreach ($this->metaboxes as $metabox) {
			// update post if current post type
			if ($_POST['post_type'] == $metabox['post_type']) {

				$value = empty($_POST[$metabox['name']]) ? false : $_POST[$metabox['name']];

				if ($metabox['name'] == 'price') {
					$value = floatval(str_replace(',', '.', $value));
				}

				if (is_array($value)) {
					$mydata = $value;
				} else {
					$mydata = sanitize_text_field($value);
				}
				update_post_meta($post_id, $metabox['name'], $mydata);
			}
		}
	}

	/**
	 * Edit form after title
	 */
	public function edit_form_after_title() {
		global $post, $wp_meta_boxes;
		unset($wp_meta_boxes[get_post_type($post)]['normal']['core']['authordiv']);
	}

	/**
	 * Add custom taxonomy columns
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function init_menu_columns($columns) {
		$columns = array_slice($columns, 0, 1, true) + array('mprm-thumb' => __("Image", 'mp-restaurant-menu')) + array_slice($columns, 1, count($columns) - 1, true);
		$columns = array_slice($columns, 0, 3, true) + array($this->get_tax_name('menu_tag') => __("Tags", 'mp-restaurant-menu')) + array_slice($columns, 3, count($columns) - 1, true);
		$columns = array_slice($columns, 0, 3, true) + array($this->get_tax_name('menu_category') => __("Categories", 'mp-restaurant-menu')) + array_slice($columns, 3, count($columns) - 1, true);
		$columns = array_slice($columns, 0, 3, true) + array('mprm-price' => __("Price", 'mp-restaurant-menu')) + array_slice($columns, 3, count($columns) - 1, true);
		return $columns;
	}

	/**
	 * Add content to custom column
	 *
	 * @param $column
	 * @param $post_ID
	 */
	public function show_menu_columns($column, $post_ID) {
		global $post;
		$data = array();
		$category_name = $this->get_tax_name('menu_category');
		$tag_name = $this->get_tax_name('menu_tag');

		switch ($column) {
			case $category_name:
				echo Taxonomy::get_instance()->get_the_term_filter_list($post, $category_name);
				break;
			case $tag_name:
				echo Taxonomy::get_instance()->get_the_term_filter_list($post, $tag_name);
				break;
			case 'mprm-thumb':
				echo '<a href="' . get_edit_post_link($post->ID) . '">' . get_the_post_thumbnail($post_ID, 'thumbnail', array('width' => 50, 'height' => 50)) . '</a>' . '<div class=mprm-clear></div>';
				break;
			case 'mprm-price':

				$data['nutritional'] = get_post_meta($post->ID, 'nutritional', true);
				$data['attributes'] = get_post_meta($post->ID, 'attributes', true);
				$data['price'] = get_post_meta($post->ID, 'price', true);
				$data['sku'] = get_post_meta($post->ID, 'sku', true);

				$this->get_view()->render_html('../admin/quick-edit/hidden-data', $data);

				if (!empty($post->price)) {
					echo mprm_currency_filter(mprm_format_amount($post->price));
				} else {
					echo '—';
				}
				break;
		}
	}
}
