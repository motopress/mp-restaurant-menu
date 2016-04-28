<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\modules\Post;
use mp_restaurant_menu\classes\View;

class Menu_item extends Model {
	protected static $instance;
	private $bundled_menu_items;


	private $sales;

	private $earnings;
	private $type;
	private $notes;
	private $ID;
	private $sku;

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

	public function __construct($_id = false, $_args = array()) {

		$menu_item = \WP_Post::get_instance($_id);

		return $this->setup_menu_item($menu_item);

	}

	public function get_ID() {
		return $this->ID;
	}

	public function get_sku() {
		if (!isset($this->sku)) {

			$this->sku = get_post_meta($this->ID, 'mprm_sku', true);

			if (empty($this->sku)) {
				$this->sku = '-';
			}

		}

		return apply_filters('mprm_get_menu_item_sku', $this->sku, $this->ID);
	}

	public function get_notes() {

		if (!isset($this->notes)) {
			$this->notes = get_post_meta($this->ID, 'mprm_product_notes', true);
		}

		return (string)apply_filters('mprm_product_notes', $this->notes, $this->ID);

	}


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
		$name = !empty($params['args']['name']) ? $params['args']['name'] : $params['id'];
		$description = !empty($params['args']['description']) ? $params['args']['description'] : '';
		$data['name'] = $name;
		$data['title'] = $params['title'];
		$data['value'] = get_post_meta($post->ID, $name, true);
		$data['description'] = $description;
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
		if (!empty($menu_category)) {
			$posts = get_posts(array(
				'post_type' => $this->get_post_type('menu_item'),
				'post_status' => 'publish',
				'posts_per_page' => 3,
				'post__not_in' => array($id),
				'tax_query' => array(
					'taxonomy' => $this->get_tax_name('menu_category'),
					'field' => 'id',
					'terms' => $menu_category[0]->term_id
				),
				'orderby' => 'rand',
			));
		}
		return $posts;
	}

	/**
	 * Get attributes
	 *
	 * @param \WP_Post $post
	 *
	 * @return array/void
	 */
	public function get_attributes(\WP_Post $post) {
		$attributes = get_post_meta($post->ID, 'attributes', true);
		if (!$this->is_arr_values_empty($attributes)) {
			return $attributes;
		} else {
			return array();
		}
	}

	/**
	 * Get featured image
	 *
	 * @param \WP_Post $post
	 * @param bool $type
	 *
	 * @return false|string
	 */
	public function get_featured_image(\WP_Post $post, $type = false) {
		$id = get_post_thumbnail_id($post);
		if ($type) {
			return wp_get_attachment_image_url($id, $type, false);
		} else {
			return wp_get_attachment_url($id);
		}
	}

	/**
	 * Get menu item class
	 *
	 * @param int $id
	 * @param bool $format
	 *
	 * @return string
	 */
	public function get_price($id, $format = false) {
		$price = get_post_meta($id, 'price', true);
		if ($format) {
			$price = $this->get_formatting_price($price);
		}
		return $price;
	}

	function get_final_price($menu_item_id = 0, $user_purchase_info, $amount_override = null) {
		if (is_null($amount_override)) {
			$original_price = get_post_meta($menu_item_id, 'mprm_price', true);
		} else {
			$original_price = $amount_override;
		}
		if (isset($user_purchase_info['discount']) && $user_purchase_info['discount'] != 'none') {
			// if the discount was a %, we modify the amount. Flat rate discounts are ignored
			if ($this->get('discount')->get_discount_type($this->get('discount')->get_discount_id_by_code($user_purchase_info['discount'])) != 'flat')
				$price = $this->get('discount')->get_discounted_amount($user_purchase_info['discount'], $original_price);
			else
				$price = $original_price;
		} else {
			$price = $original_price;
		}
		return apply_filters('mprm_final_price', $price, $menu_item_id, $user_purchase_info);
	}

	public function get_formatting_price($amount, $decimals = true) {
		$thousands_sep = $this->get('settings')->get_option('thousands_separator', ',');
		$decimal_sep = $this->get('settings')->get_option('decimal_separator', '.');
		// Format the amount
		if ($decimal_sep == ',' && false !== ($sep_found = strpos($amount, $decimal_sep))) {
			$whole = substr($amount, 0, $sep_found);
			$part = substr($amount, $sep_found + 1, (strlen($amount) - 1));
			$amount = $whole . '.' . $part;
		}
		// Strip , from the amount (if set as the thousands separator)
		if ($thousands_sep == ',' && false !== ($found = strpos($amount, $thousands_sep))) {
			$amount = str_replace(',', '', $amount);
		}
		// Strip ' ' from the amount (if set as the thousands separator)
		if ($thousands_sep == ' ' && false !== ($found = strpos($amount, $thousands_sep))) {
			$amount = str_replace(' ', '', $amount);
		}
		if (empty($amount)) {
			$amount = 0;
		}
		$decimals = apply_filters('mprm_format_amount_decimals', $decimals ? 2 : 0, $amount);
		$formatted = number_format($amount, $decimals, $decimal_sep, $thousands_sep);
		return apply_filters('mprm_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep);
	}

	function currency_filter($price = '', $currency = '') {
		if (empty($currency)) {
			$currency = $this->get('settings')->get_currency();
		}
		$position = $this->get('settings')->get_option('currency_position', 'before');
		$negative = $price < 0;
		if ($negative) {
			$price = substr($price, 1); // Remove proceeding "-" -
		}
		$symbol = $this->get('settings')->get_currency_symbol($currency);
		if ($position == 'before'):
			switch ($currency):
				case "GBP" :
				case "BRL" :
				case "EUR" :
				case "USD" :
				case "AUD" :
				case "CAD" :
				case "HKD" :
				case "MXN" :
				case "NZD" :
				case "SGD" :
				case "JPY" :
					$formatted = $symbol . $price;
					break;
				default :
					$formatted = $currency . ' ' . $price;
					break;
			endswitch;
			$formatted = apply_filters('mprm_' . strtolower($currency) . '_currency_filter_before', $formatted, $currency, $price);
		else :
			switch ($currency) :
				case "GBP" :
				case "BRL" :
				case "EUR" :
				case "USD" :
				case "AUD" :
				case "CAD" :
				case "HKD" :
				case "MXN" :
				case "SGD" :
				case "JPY" :
					$formatted = $price . $symbol;
					break;
				default :
					$formatted = $price . ' ' . $currency;
					break;
			endswitch;
			$formatted = apply_filters('mprm_' . strtolower($currency) . '_currency_filter_after', $formatted, $currency, $price);
		endif;
		if ($negative) {
			// Prepend the mins sign before the currency sign
			$formatted = '-' . $formatted;
		}
		return $formatted;
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
		if (!empty($args['categ'])) {
			if (is_array($args['categ'])) {
				$category_ids = $args['categ'];
			} else {
				$category_ids = explode(',', $args['categ']);
			}
		}
		if (!empty($args['tags_list'])) {
			if (is_array($args['tags_list'])) {
				$tags_ids = $args['tags_list'];
			} else {
				$tags_ids = explode(',', $args['tags_list']);
			}
		}
		if (!empty($args['item_ids'])) {
			$params['post__in'] = explode(',', $args['item_ids']);
		}
		if (!empty($args['categ']) && empty($args['tags_list'])) {
			foreach ($category_ids as $id) {
				$term_params = array_merge($params, array('tax_query' =>
						array(
							array(
								'taxonomy' => $this->get_tax_name('menu_category'),
								'field' => 'id',
								'terms' => $id,
								'include_children' => false
							)
						)
					)
				);
				$items[$id] = array('term' => get_term($id, $this->get_tax_name('menu_category')), 'posts' => get_posts($term_params));
			}
		}
		if (!empty($args['tags_list']) && empty($args['categ'])) {
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
				$items[$id] = array('term' => get_term($id, $this->get_tax_name('menu_tag')), 'posts' => get_posts($term_params));
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
				$items[$id] = array('term' => get_term($id, $this->get_tax_name('menu_category')), 'posts' => get_posts($term_params));
			}
		} elseif (empty($category_ids) && empty($tags_ids) && !empty($args['item_ids'])) {
			$items[0] = array('term' => '', 'posts' => get_posts($params));
		} elseif (empty($category_ids) && empty($tags_ids) && empty($args['item_ids'])) {
			$items[0] = array('term' => '', 'posts' => get_posts($params));
		}
		return $items;
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
		if (!empty($args['posts'])) {
			foreach ($args['posts'] as $key => $post) {
				$options[$key] = $this->get_menu_item_option($post);
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
		if ("list" != $mprm_view_args['view']) {
			$mprm_view_args['col'] += 0;
			if ($mprm_view_args['col'] <= 2) {
				$thumbnail_type = 'mprm-big';
			} else {
				$thumbnail_type = 'mprm-middle';
			}
		} else {
			$thumbnail_type = 'mprm-thumbnail';
		}
		if ($thumbnail_type) {
			$options['image'] = wp_get_attachment_image($image_id, $thumbnail_type, false, array('class' => apply_filters('mprm-item-image', "mprm-image")));
		}
		$options['product_price'] = $this->get_price($post->ID, true);
		$options['excerpt'] = $post->post_excerpt;
		$options['ingredients'] = wp_get_post_terms($post->ID, $this->get_tax_name('ingredient'));
		$options['attributes'] = $this->get_attributes($post);
		$options['tags'] = wp_get_post_terms($post->ID, $this->get_tax_name('menu_tag'));
		return $options;
	}

	/**
	 * Is nutritional empty
	 *
	 * @param array $data
	 *
	 * @return boolean
	 */
	public function is_arr_values_empty($data) {
		if ($data === false || $data == NULL)
			return true;
		if (is_array($data) && !empty($data)) {
			$empty_count = 0;
			foreach ($data as $item) {
				if (!empty($item) && is_array($item)) {
					foreach ($item as $key => $value) {
						if ($key == 'val' && empty($value)) {
							$empty_count++;
						}
					}
				}
			}
			if (count($data) == $empty_count) {
				return true;
			}
		}
		return false;
	}

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
			$this->get('errors')->set_error('set_checkout', sprintf(__('No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'mp-restaurant-menu'), admin_url('edit.php?post_type=mp_menu_item&page=admin.php?page=mprm-settings')));
			$this->get('errors')->print_errors();
			return false;
		}
		$post_id = is_object($post) ? $post->ID : 0;
		$button_behavior = $this->get_button_behavior($post_id);
		$defaults = apply_filters('mprm_purchase_link_defaults', array(
			'menu_item_id' => $post_id,
			'price' => (bool)true,
			'price_id' => isset($args['price_id']) ? $args['price_id'] : false,
			'direct' => $button_behavior == 'direct' ? true : false,
			'text' => $button_behavior == 'direct' ? $this->get('settings')->get_option('buy_now_text', __('Buy Now', 'mp-restaurant-menu')) : $this->get('settings')->get_option('add_to_cart_text', __('Purchase', 'mp-restaurant-menu')),
			'style' => $this->get('settings')->get_option('button_style', 'button'),
			'color' => $this->get('settings')->get_option('checkout_color', 'blue'),
			'class' => 'mprm-submit'
		));
		$args = wp_parse_args($args, $defaults);
		// Override the straight_to_gateway if the shop doesn't support it
		//	if (mprm_shop_supports_buy_now()) {
		if ($this->get('gateways')->shop_supports_buy_now()) {
			$args['direct'] = false;
		}
		// Override color if color == inherit
		$args['color'] = ($args['color'] == 'inherit') ? '' : $args['color'];
		$options = array();
		$variable_pricing = $this->has_variable_prices($post->ID);
		$data_variable = $variable_pricing ? ' data-variable-price="yes"' : 'data-variable-price="no"';
		$type = $this->is_single_price_mode($post->ID) ? 'data-price-mode=multi' : 'data-price-mode=single';
		$show_price = $args['price'] && $args['price'] !== 'no';
		$data_price_value = 0;
		$price = false;
		if ($variable_pricing && false !== $args['price_id']) {
			$price_id = $args['price_id'];
			$prices = $post->prices;
			$options['price_id'] = $args['price_id'];
			$found_price = isset($prices[$price_id]) ? $prices[$price_id]['amount'] : false;
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
		$args['display_price'] = $data_price_value;
		$data_price = 'data-price="' . $data_price_value . '"';
		$button_text = !empty($args['text']) ? '&nbsp;&ndash;&nbsp;' . $args['text'] : '';
		if (false !== $price) {
			if (0 == $price) {
				$args['text'] = __('Free', 'mp-restaurant-menu') . $button_text;
			} else {
				$args['text'] = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price($price)) . $button_text;
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
		if (isset($mprm_displayed_form_ids[$post->ID])) {
			$mprm_displayed_form_ids[$post->ID]++;
		} else {
			$mprm_displayed_form_ids[$post->ID] = 1;
		}
		$form_id = !empty($args['form_id']) ? $args['form_id'] : 'mprm_purchase_' . $post->ID;
		// If we've already generated a form ID for this menu_item ID, apped -#
		if ($mprm_displayed_form_ids[$post->ID] > 1) {
			$form_id .= '-' . $mprm_displayed_form_ids[$post->ID];
		}
		$args = apply_filters('mprm_purchase_link_args', $args);
		$purchase_form = View::get_instance()->render_html('../admin/shop/buy-form',
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
				'is_free' => $this->is_free($args['price_id'], $post->ID),
				'type' => $type,
			), false);
		return apply_filters('mprm_purchase_menu_item_form', $purchase_form, $args);
	}

	public function get_button_behavior($post_id) {
		$button_behavior = get_post_meta($post_id, '_button_behavior', true);
		if (empty($button_behavior) || !$this->get('gateways')->shop_supports_buy_now()) {
			$button_behavior = 'add_to_cart';
		}
		return apply_filters('mprm_get_button_behavior', $button_behavior, $post_id);
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

	public function has_variable_prices($post_id) {
		$ret = get_post_meta($post_id, '_variable_pricing', true);
		/**
		 * Override whether the menu_item has variables prices.
		 *
		 * @since 2.3
		 *
		 * @param bool $ret Does menu_item have variable prices?
		 * @param int|string The ID of the menu_item.
		 */
		return (bool)apply_filters('mprm_has_variable_prices', $ret, $post_id);
	}

	public function is_single_price_mode($post_id) {
		$ret = get_post_meta($post_id, '_mprm_price_options_mode', true);
		/**
		 * Override the price mode for a menu_item when checking if is in single price mode.
		 *
		 * @since 2.3
		 *
		 * @param bool $ret Is menu_item in single price mode?
		 * @param int|string The ID of the menu_item.
		 */
		return (bool)apply_filters('mprm_single_price_option_mode', $ret, $post_id);
	}

	public function is_free($price_id = false, $post_id) {
		global $post;
		if (empty($post)) {
			$post = get_post($post_id);
		}
		$is_free = false;
		$variable_pricing = $this->has_variable_prices($post->ID);
		if ($variable_pricing && !is_null($price_id) && $price_id !== false) {
			$price = $this->get('menu_item')->get_price_option_amount($post->ID, $price_id);
		} elseif ($variable_pricing && $price_id === false) {
			$lowest_price = (float)$this->get_price_option($post->ID, 'min');
			$highest_price = (float)$this->get_price_option($post->ID, 'max');
			if ($lowest_price === 0.00 && $highest_price === 0.00) {
				$price = 0;
			}
		} elseif (!$variable_pricing) {
			$price = get_post_meta($post->ID, 'mprm_price', true);
		}
		if (isset($price) && (float)$price == 0) {
			$is_free = true;
		}
		return (bool)apply_filters('mprm_is_free_menu_item', $is_free, $post->ID, $price_id);
	}

	public function get_prices($post_id) {
		$prices = get_post_meta($post_id, 'mprm_variable_prices', true);
		return apply_filters('mprm_get_variable_prices', $prices, $post_id);
	}

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
			return $menu_item[0];
		}

		return null;
	}

	public function get_price_option_amount($menu_item_id = 0, $price_id = 0) {
		$prices = $this->get_variable_prices($menu_item_id);
		$amount = 0.00;

		if ($prices && is_array($prices)) {
			if (isset($prices[$price_id]))
				$amount = $prices[$price_id]['amount'];
		}

		return apply_filters('mprm_get_price_option_amount', $this->get('formatting')->sanitize_amount($amount), $menu_item_id, $price_id);
	}

	public function get_variable_prices($menu_item_id = 0) {

		if (empty($menu_item_id)) {
			return false;
		}
		return $this->get_prices($menu_item_id);
	}


	public function get_price_option($menu_item_id = 0, $type = 'max') {
		if (empty($menu_item_id))
			$menu_item_id = get_the_ID();

		if (!$this->has_variable_prices($menu_item_id)) {
			return $this->get_price($menu_item_id);
		}

		$prices = $this->get_variable_prices($menu_item_id);

		$price_type = 0.00;
		$max = 0;
		if (!empty($prices)) {

			foreach ($prices as $key => $price) {

				if (empty($price['amount'])) {
					continue;
				}
				if ($type == 'min') {
					if (!isset($min)) {
						$min = $price['amount'];
					} else {
						$min = min($min, $price['amount']);
					}

					if ($price['amount'] == $min) {
						$id = $key;
					}
				} elseif ($type == 'max') {
					$max = max($max, $price['amount']);

					if ($price['amount'] == $max) {
						$id = $key;
					}
				}
			}

			$price_type = $prices[$id]['amount'];

		}

		return $this->get('formatting')->sanitize_amount($price_type);
	}

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

	function get_menu_item_sales_stats($menu_item_id = 0) {
		if (empty($this->sales)) {
			$this->sales = get_post_meta($this->ID, '_mprm_menu_item_sales', true);
		}
		return $this->sales;
	}

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

	public function get_increase_earnings($menu_item_id = 0, $amount) {
		$this->setup_menu_item($menu_item_id);
		return $this->increase_earnings($amount);
	}

	public function increase_earnings($amount = 0) {

		$earnings = $this->earnings;
		$new_amount = $earnings + (float)$amount;

		if ($this->update_meta('_mprm_menu_item_earnings', $new_amount)) {

			$this->earnings = $new_amount;
			return $this->earnings;

		}

		return false;

	}

	function get_decrease_earnings($menu_item_id = 0, $amount) {
		$this->setup_menu_item($menu_item_id);
		return $this->decrease_earnings($amount);
	}

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

	function decrease_purchase_count($menu_item_id = 0, $quantity = 1) {
		$this->setup_menu_item($menu_item_id);
		return $this->decrease_sales($quantity);
	}

	function get_menu_item_type($menu_item_id = 0) {
		$this->setup_menu_item($menu_item_id);
		return $this->type;
	}

	function get_bundled_products($menu_item_id = 0) {
		$this->setup_menu_item($menu_item_id);
		return $this->bundled_menu_items;
	}

	function get_sales_stats($menu_item_id = 0) {
		$this->setup_menu_item($menu_item_id);
		return $this->sales;
	}

	private function update_meta($meta_key = '', $meta_value = '') {

		global $wpdb;

		if (empty($meta_key) || empty($meta_value)) {
			return false;
		}

		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize($meta_value);

		if (is_numeric($meta_value)) {
			$value_type = is_float($meta_value) ? '%f' : '%d';
		} else {
			$value_type = "'%s'";
		}

		$sql = $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key);

		if ($wpdb->query($sql)) {

			clean_post_cache($this->ID);
			return true;

		}

		return false;
	}

	function get_price_option_name($menu_item_id = 0, $price_id = 0, $payment_id = 0) {
		$prices = $this->get_variable_prices($menu_item_id);
		$price_name = '';

		if ($prices && is_array($prices)) {
			if (isset($prices[$price_id]))
				$price_name = $prices[$price_id]['name'];
		}

		return apply_filters('mprm_get_price_option_name', $price_name, $menu_item_id, $payment_id, $price_id);
	}

	public function get_label($lowercase = false, $type = 'singular') {
		$labels = array(
			'singular' => __('Menu item', 'mp-restaurant-menu'),
			'plural' => __('Menu items', 'mp-restaurant-menu')
		);
		return ($lowercase) ? strtolower($labels[$type]) : $labels[$type];
	}

}
