<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\models\parents\Store_item;
use mp_restaurant_menu\classes\modules\Post;
use mp_restaurant_menu\classes\View;

/**
 * Class Menu_item
 * @package mp_restaurant_menu\classes\models
 */
class Menu_item extends Store_item {
	protected static $instance;
	private $bundled_menu_items;
	private $sales;
	private $earnings;
	private $type;
	private $notes;
	private $ID;
	private $sku;
	
	/**
	 * Menu_item constructor.
	 *
	 * @param bool $_id
	 * @param array $_args
	 */
	public function __construct($_id = false, $_args = array()) {
		parent::__construct();
		if ($_id) {
			$menu_item = get_post($_id);
			
			return $this->setup_menu_item($menu_item);
		}
	}
	
	/**
	 * @param $menu_item
	 *
	 * @return bool
	 */
	private function setup_menu_item($menu_item) {
		if ($this->is_menu_item($menu_item)) {
			foreach ($menu_item as $key => $value) {
				switch ($key) {
					default:
						$this->$key = $value;
						break;
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 *  Is menu item object
	 *
	 * @param $post
	 *
	 * @return bool
	 */
	public function is_menu_item($post) {
		if (!is_a($post, 'WP_Post')) {
			return false;
		}
		if ('mp_menu_item' !== $post->post_type) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @return Menu_item
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Init metaboxes
	 */
	public function init_metaboxes() {
		$metabox_array = Core::get_instance()->get_config("metaboxes");
		Post::get_instance()->set_metaboxes($metabox_array);
	}
	
	/**
	 * @return mixed
	 */
	public function get_ID() {
		return $this->ID;
	}
	
	/**
	 * @return mixed
	 */
	public function get_sku() {
		if (!isset($this->sku)) {
			$this->sku = get_post_meta($this->ID, 'mprm_sku', true);
			if (empty($this->sku)) {
				$this->sku = '-';
			}
		}
		
		return apply_filters('mprm_get_menu_item_sku', $this->sku, $this->ID);
	}
	
	/**
	 * @return string
	 */
	public function get_notes() {
		if (!isset($this->notes)) {
			$this->notes = get_post_meta($this->ID, 'mprm_product_notes', true);
		}
		
		return (string)apply_filters('mprm_product_notes', $this->notes, $this->ID);
	}
	
	/**
	 * Render meta box hook
	 *
	 * @param \WP_Post $post
	 * @param array $params
	 */
	public function render_meta_box(\WP_Post $post, array $params) {
		// add nonce field
		wp_nonce_field('mp-restaurant-menu' . '_nonce', 'mp-restaurant-menu' . '_nonce_box');
		// render Metabox html
		$name = !empty($params[ 'args' ][ 'name' ]) ? $params[ 'args' ][ 'name' ] : $params[ 'id' ];
		$description = !empty($params[ 'args' ][ 'description' ]) ? $params[ 'args' ][ 'description' ] : '';
		$data[ 'name' ] = $name;
		$data[ 'title' ] = $params[ 'title' ];
		$data[ 'value' ] = get_post_meta($post->ID, $name, true);
		$data[ 'description' ] = $description;
		View::get_instance()->render_html("../admin/metaboxes/{$name}", $data);
	}
	
	/**
	 * Get gallery
	 *
	 * @param integer $id
	 *
	 * @return array
	 */
	public function get_gallery($id) {
		// get Image gallery
		$ids = get_post_meta($id, 'mp_menu_gallery', true);
		$gallery = array();
		if (!empty($ids)) {
			foreach (explode(',', $ids) as $id) {
				if (get_post($id)) {
					$gallery[] = $id;
				}
			}
		}
		
		return $gallery;
	}
	
	/**
	 * @return bool
	 */
	public function is_bundled_menu_item() {
		return 'bundle' === $this->get_type();
	}
	
	/**
	 * @return mixed
	 */
	public function get_type() {
		
		if (!isset($this->type)) {
			
			$this->type = get_post_meta($this->ID, '_mprm_product_type', true);
			
			if (empty($this->type)) {
				$this->type = 'default';
			}
		}
		
		return apply_filters('mprm_get_menu_item_type', $this->type, $this->ID);
		
	}
	
	/**
	 * Get around items
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function get_related_items($id) {
		// get Menu category
		$menu_category = wp_get_object_terms($id, $this->get_tax_name('menu_category'));
		$posts = array();
		$terms = array();
		
		if (!empty($menu_category)) {
			foreach ($menu_category as $category) {
				$terms[] = $category->term_id;
			}
			$posts = get_posts(array(
				'post_type' => $this->get_post_type('menu_item'),
				'post_status' => 'publish',
				'posts_per_page' => 3,
				'post__not_in' => array($id),
				'tax_query' => array(
					array(
						'taxonomy' => $this->get_tax_name('menu_category'),
						'field' => 'term_id',
						'terms' => $terms
					)
				),
				'orderby' => 'rand',
			));
		}
		
		return $posts;
	}
	
	
	/**
	 * @param int $menu_item_id
	 * @param $user_purchase_info
	 * @param null $amount_override
	 *
	 * @return mixed
	 */
	public function get_final_price($menu_item_id = 0, $user_purchase_info, $amount_override = null) {
		if (is_null($amount_override)) {
			$original_price = get_post_meta($menu_item_id, 'mprm_price', true);
		} else {
			$original_price = $amount_override;
		}
		if (isset($user_purchase_info[ 'discount' ]) && $user_purchase_info[ 'discount' ] != 'none') {
			// if the discount was a %, we modify the amount. Flat rate discounts are ignored
			if ($this->get('discount')->get_discount_type($this->get('discount')->get_discount_id_by_code($user_purchase_info[ 'discount' ])) != 'flat')
				$price = $this->get('discount')->get_discounted_amount($user_purchase_info[ 'discount' ], $original_price);
			else
				$price = $original_price;
		} else {
			$price = $original_price;
		}
		
		return apply_filters('mprm_final_price', $price, $menu_item_id, $user_purchase_info);
	}
	
	/**
	 * Get category menu items
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_menu_items(array $args) {
		$items = $category_ids = $tags_ids = array();
		$params = array(
			'post_type' => $this->get_post_type('menu_item'),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'tax_query' => array()
		);
		//	$params = wp_parse_args($args, $params);
		
		if (!empty($args[ 'categ' ])) {
			if (is_array($args[ 'categ' ])) {
				$category_ids = $args[ 'categ' ];
			} else {
				$category_ids = explode(',', $args[ 'categ' ]);
			}
		}
		if (!empty($args[ 'tags_list' ])) {
			if (is_array($args[ 'tags_list' ])) {
				$tags_ids = $args[ 'tags_list' ];
			} else {
				$tags_ids = explode(',', $args[ 'tags_list' ]);
			}
		}
		if (!empty($args[ 'item_ids' ])) {
			$params[ 'post__in' ] = explode(',', $args[ 'item_ids' ]);
		}
		
		if (!empty($args[ 'categ' ]) && empty($args[ 'tags_list' ])) {
			foreach ($category_ids as $id) {
				$term_params = array_merge($params, array('tax_query' =>
						array(
							array(
								'taxonomy' => $this->get_tax_name('menu_category'),
								'field' => 'id',
								'terms' => $id,
								'include_children' => true
							)
						)
					)
				);
				$items[ $id ] = array('term' => get_term($id, $this->get_tax_name('menu_category')), 'posts' => get_posts($term_params));
				$items[ $id ][ 'order' ] = $this->get('menu_category')->get_term_order($id);
			}
		}
		
		if (!empty($args[ 'tags_list' ]) && empty($args[ 'categ' ])) {
			foreach ($tags_ids as $id) {
				$term_params = array_merge($params, array('tax_query' =>
						array(
							array(
								'taxonomy' => $this->get_tax_name('menu_tag'),
								'field' => 'id',
								'terms' => $id,
								'include_children' => false
							)
						)
					)
				);
				$items[ $id ] = array('term' => get_term($id, $this->get_tax_name('menu_tag')), 'posts' => get_posts($term_params));
				$items[ $id ][ 'order' ] = $this->get('menu_category')->get_term_order($id);
			}
		}
		
		if (!empty($category_ids) && !empty($tags_ids)) {
			foreach ($category_ids as $id) {
				$term_params = array_merge($params, array('tax_query' =>
						array(
							'relation' => 'AND',
							array(
								'taxonomy' => $this->get_tax_name('menu_category'),
								'field' => 'id',
								'terms' => $id,
								'include_children' => false
							),
							array(
								'taxonomy' => $this->get_tax_name('menu_tag'),
								'field' => 'id',
								'terms' => $tags_ids,
								'include_children' => false
							),
						)
					)
				);
				$items[ $id ] = array('term' => get_term($id, $this->get_tax_name('menu_category')), 'posts' => get_posts($term_params));
				$items[ $id ][ 'order' ] = $this->get('menu_category')->get_term_order($id);
			}
		} elseif (empty($category_ids) && empty($tags_ids) && !empty($args[ 'item_ids' ])) {
			$items[ 0 ] = array('term' => '', 'posts' => get_posts($params));
		} elseif (empty($category_ids) && empty($tags_ids) && empty($args[ 'item_ids' ])) {
			$items[ 0 ] = array('term' => '', 'posts' => get_posts($params));
		}
		
		return $this->get('menu_category')->sort_category_order($items);
	}
	
	/**
	 * Get menu item options
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_menu_item_options(array $args) {
		$options = array();
		if (!empty($args[ 'posts' ])) {
			foreach ($args[ 'posts' ] as $key => $post) {
				$options[ $key ] = $this->get_menu_item_option($post);
			}
		}
		
		return $options;
	}
	
	/**
	 * Get menu item option
	 *
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function get_menu_item_option(\WP_Post $post) {
		global $mprm_view_args;
		$options = array();
		$image_id = get_post_thumbnail_id($post->ID);
		
		if (!empty($mprm_view_args[ 'view' ]) && "list" != $mprm_view_args[ 'view' ]) {
			$mprm_view_args[ 'col' ] += 0;
			if ($mprm_view_args[ 'col' ] <= 2) {
				$thumbnail_type = 'mprm-big';
			} else {
				$thumbnail_type = 'mprm-middle';
			}
		} else {
			$thumbnail_type = 'mprm-thumbnail';
		}
		
		if ($thumbnail_type) {
			$options[ 'image' ] = wp_get_attachment_image($image_id, $thumbnail_type, false, array('class' => apply_filters('mprm-item-image', "mprm-image")));
		}
		
		$options[ 'product_price' ] = $this->get_price($post->ID, false);
		$options[ 'excerpt' ] = $post->post_excerpt;
		$options[ 'ingredients' ] = wp_get_post_terms($post->ID, $this->get_tax_name('ingredient'));
		$options[ 'attributes' ] = $this->get_attributes($post);
		$options[ 'tags' ] = wp_get_post_terms($post->ID, $this->get_tax_name('menu_tag'));
		
		return $options;
	}
	
	
	/**
	 * Purchase link
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function get_purchase_link($args) {
		global $post, $mprm_displayed_form_ids;
		
		if (!$this->is_menu_item($post)) {
			return false;
		}
		if ('publish' !== $post->post_status && !current_user_can('edit_product', $post->ID)) {
			return false; // Product not published or user doesn't have permission to view drafts
		}
		$purchase_page = $this->get('settings')->get_option('purchase_page', false);
		if (!$purchase_page || $purchase_page == 0) {
			$this->get('errors')->set_error('set_checkout', sprintf(__('No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'mp-restaurant-menu'), admin_url('edit.php?post_type=mp_menu_item&page=mprm-settings')));
			$this->get('errors')->print_errors();
			
			return false;
		}
		$post_id = is_object($post) ? $post->ID : 0;
		$button_behavior = $this->get_button_behavior($post_id);
		
		$defaults = apply_filters('mprm_purchase_link_defaults', array(
			'menu_item_id' => $post_id,
			'price' => (bool)true,
			'price_id' => isset($args[ 'price_id' ]) ? $args[ 'price_id' ] : false,
			'direct' => $button_behavior == 'direct' ? true : false,
			'text' => ($button_behavior == 'direct') ? $this->get('settings')->get_option('buy_now_text', __('Buy Now', 'mp-restaurant-menu')) : $this->get('settings')->get_option('add_to_cart_text', __('Add to Cart', 'mp-restaurant-menu')),
			'style' => $this->get('settings')->get_option('button_style', 'button'),
			'color' => $this->get('settings')->get_option('checkout_color', 'inherit'),
			'padding' => $this->get('settings')->get_option('checkout_padding', 'mprm-inherit'),
			'class' => 'mprm-submit'
		));
		
		$args = wp_parse_args($args, $defaults);
		// Override the straight_to_gateway if the shop doesn't support it
		if ($this->get('gateways')->shop_supports_buy_now()) {
			$args[ 'direct' ] = false;
		}
		// Override color if color == inherit
		$args[ 'color' ] = ($args[ 'color' ] == 'inherit') ? '' : $args[ 'color' ];
		$options = array();
		$variable_pricing = $this->has_variable_prices($post->ID);
		$data_variable = $variable_pricing ? ' data-variable-price="yes"' : 'data-variable-price="no"';
		$type = $this->is_single_price_mode($post->ID) ? 'data-price-mode=multi' : 'data-price-mode=single';
		$show_price = $args[ 'price' ] && $args[ 'price' ] !== 'no';
		$data_price_value = 0;
		$price = false;
		if ($variable_pricing && false !== $args[ 'price_id' ]) {
			$price_id = $args[ 'price_id' ];
			$prices = $post->prices;
			$options[ 'price_id' ] = $args[ 'price_id' ];
			$found_price = isset($prices[ $price_id ]) ? $prices[ $price_id ][ 'amount' ] : false;
			$data_price_value = $found_price;
			if ($show_price) {
				$price = $found_price;
			}
		} elseif (!$variable_pricing) {
			$data_price_value = $post->price;
			if ($show_price) {
				$price = $post->price;
			}
		}
		$args[ 'display_price' ] = $data_price_value;
		$data_price = 'data-price="' . $data_price_value . '"';
		$button_text = !empty($args[ 'text' ]) ? '&nbsp;&ndash;&nbsp;' . $args[ 'text' ] : '';
		
		if (false !== $price) {
			if (0 == $price) {
				$args[ 'text' ] = __('Free', 'mp-restaurant-menu') . $button_text;
			} else {
				$args[ 'text' ] = apply_filters('mprm_show_price', false) ? $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price($price)) . $button_text : $args[ 'text' ];
			}
		}
		
		if ($this->get('cart')->item_in_cart($post->ID, $options) && (!$variable_pricing || !$this->is_single_price_mode($post->ID))) {
			$button_display = 'style="display:none;"';
			$checkout_display = '';
		} else {
			$button_display = '';
			$checkout_display = 'style="display:none;"';
		}
		// Collect any form IDs we've displayed already so we can avoid duplicate IDs
		if (isset($mprm_displayed_form_ids[ $post->ID ])) {
			$mprm_displayed_form_ids[ $post->ID ]++;
		} else {
			$mprm_displayed_form_ids[ $post->ID ] = 1;
		}
		$form_id = !empty($args[ 'form_id' ]) ? $args[ 'form_id' ] : 'mprm_purchase_' . $post->ID;
		// If we've already generated a form ID for this menu_item ID, apped -#
		if ($mprm_displayed_form_ids[ $post->ID ] > 1) {
			$form_id .= '-' . $mprm_displayed_form_ids[ $post->ID ];
		}
		$args = apply_filters('mprm_purchase_link_args', $args);
		$purchase_form = View::get_instance()->render_html('../admin/shop/buy-' . Media::get_instance()->get_template_mode() . '-form',
			array(
				'args' => $args,
				'form_id' => $form_id,
				'post' => $post,
				'button_display' => $button_display,
				'checkout_display' => $checkout_display,
				'data_price' => $data_price,
				'data_variable' => $data_variable,
				'variable_pricing' => $variable_pricing,
				'checkout_uri' => $this->get('checkout')->get_checkout_uri(),
				'display_tax_rate' => $this->get('taxes')->display_tax_rate(),
				'prices_include_tax' => $this->get('taxes')->prices_include_tax(),
				'tax_rate' => $this->get('taxes')->get_tax_rate(),
				'is_ajax_disabled' => $this->get('settings')->is_ajax_disabled(),
				'straight_to_checkout' => $this->get('checkout')->straight_to_checkout(),
				'is_free' => $this->is_free($args[ 'price_id' ], $post->ID),
				'type' => $type,
			), false);
		
		return apply_filters('mprm_purchase_menu_item_form', $purchase_form, $args);
	}
	
	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function get_button_behavior($post_id) {
		$button_behavior = get_post_meta($post_id, '_button_behavior', true);
		if (empty($button_behavior) || !$this->get('gateways')->shop_supports_buy_now()) {
			$button_behavior = 'add_to_cart';
		}
		
		return apply_filters('mprm_get_button_behavior', $button_behavior, $post_id);
	}
	
	/**
	 * @param int $menu_item
	 *
	 * @return array|int|null|\WP_Post
	 */
	public function get_menu_item($menu_item = 0) {
		if (is_numeric($menu_item)) {
			$menu_item = get_post($menu_item);
			if (!$menu_item || 'mp_menu_item' !== $menu_item->post_type)
				return null;
			
			return $menu_item;
		}
		$args = array(
			'post_type' => 'mp_menu_item',
			'name' => $menu_item,
			'numberposts' => 1
		);
		$menu_item = get_posts($args);
		if ($menu_item) {
			return $menu_item[ 0 ];
		}
		
		return null;
	}
	
	/**
	 * @param int $quantity
	 *
	 * @return bool|mixed
	 */
	public function increase_sales($quantity = 1) {
		$sales = $this->get_menu_item_sales_stats($this->ID);
		$quantity = absint($quantity);
		$total_sales = $sales + $quantity;
		if ($this->update_meta('_mprm_menu_item_sales', $total_sales)) {
			$this->sales = $total_sales;
			
			return $this->sales;
		}
		
		return false;
	}
	
	/**
	 * @param int $menu_item_id
	 *
	 * @return mixed
	 */
	public function get_menu_item_sales_stats($menu_item_id = 0) {
		if (!empty($menu_item_id) && is_numeric($menu_item_id)) {
			$this->sales = get_post_meta($menu_item_id, '_mprm_menu_item_sales', true);
		} elseif (empty($this->sales)) {
			$this->sales = get_post_meta($this->ID, '_mprm_menu_item_sales', true);
		}
		
		return $this->sales;
	}
	
	/**
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool
	 */
	private function update_meta($meta_key = '', $meta_value = '') {
		if (empty($meta_key) || empty($meta_value)) {
			return false;
		}
		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize($meta_value);
		
		if (update_post_meta($this->ID, $meta_key, $meta_value)) {
			clean_post_cache($this->ID);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get increase earnings
	 *
	 * @param int $menu_item_id
	 * @param $amount
	 *
	 * @return bool
	 */
	public function get_increase_earnings($menu_item_id = 0, $amount) {
		$this->setup_menu_item($menu_item_id);
		
		return $this->increase_earnings($amount);
	}
	
	/**
	 * Increase earnings
	 *
	 * @param int $amount
	 *
	 * @return bool
	 */
	public function increase_earnings($amount = 0) {
		$earnings = $this->earnings;
		$new_amount = $earnings + (float)$amount;
		if ($this->update_meta('_mprm_menu_item_earnings', $new_amount)) {
			$this->earnings = $new_amount;
			
			return $this->earnings;
		}
		
		return false;
	}
	
	/**
	 * @param int $menu_item_id
	 * @param $amount
	 *
	 * @return bool
	 */
	public function get_decrease_earnings($menu_item_id = 0, $amount) {
		$this->setup_menu_item($menu_item_id);
		
		return $this->decrease_earnings($amount);
	}
	
	/**
	 * Decrease earnings
	 *
	 * @param $amount
	 *
	 * @return bool
	 */
	public function decrease_earnings($amount) {
		$earnings = $this->earnings;
		if ($earnings > 0) {
			// Only decrease if greater than zero
			$new_amount = $earnings - (float)$amount;
			if ($this->update_meta('_mprm_menu_item_earnings', $new_amount)) {
				$this->earnings = $new_amount;
				
				return $this->earnings;
			}
		}
		
		return false;
	}
	
	/**
	 * @param int $menu_item_id
	 * @param int $quantity
	 *
	 * @return bool|mixed
	 */
	public function decrease_purchase_count($menu_item_id = 0, $quantity = 1) {
		$this->setup_menu_item($menu_item_id);
		
		return $this->decrease_sales($quantity);
	}
	
	/**
	 * @param int $quantity
	 *
	 * @return bool|mixed
	 */
	public function decrease_sales($quantity = 1) {
		$sales = $this->get_menu_item_sales_stats($this->ID);
		// Only decrease if not already zero
		if ($sales > 0) {
			$quantity = absint($quantity);
			$total_sales = $sales - $quantity;
			if ($this->update_meta('_mprm_menu_item_sales', $total_sales)) {
				$this->sales = $total_sales;
				
				return $this->sales;
			}
		}
		
		return false;
	}
	
	/**
	 * @param int $menu_item_id
	 *
	 * @return mixed
	 */
	public function get_menu_item_type($menu_item_id = 0) {
		$this->setup_menu_item($menu_item_id);
		
		return $this->type;
	}
	
	/**
	 * @param int $menu_item_id
	 *
	 * @return mixed
	 */
	public function get_bundled_products($menu_item_id = 0) {
		$this->setup_menu_item($menu_item_id);
		
		return $this->bundled_menu_items;
	}
	
	/**
	 * @param int $menu_item_id
	 *
	 * @return mixed
	 */
	public function get_sales_stats($menu_item_id = 0) {
		$this->setup_menu_item($menu_item_id);
		
		return $this->sales;
	}
	
	/**
	 * @return array
	 */
	public function get_search_params() {
		$search_params = array(
			'sku'
		);
		
		return $search_params;
	}
	
	/**
	 * @param bool $lowercase
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_label($lowercase = false, $type = 'singular') {
		$labels = array(
			'singular' => __('Menu item', 'mp-restaurant-menu'),
			'plural' => __('Menu items', 'mp-restaurant-menu')
		);
		
		return ($lowercase) ? strtolower($labels[ $type ]) : $labels[ $type ];
	}
}
