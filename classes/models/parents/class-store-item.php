<?php
namespace mp_restaurant_menu\classes\models\parents;

use mp_restaurant_menu\classes\Model;

/**
 * Class Store_item
 */
class Store_item extends Model {
	/**
	 * @return Store_item
	 */
	private static $_instance;
	
	/**
	 * @return Store_item
	 */
	public static function get_instance() {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	/**
	 * @param string $price
	 * @param string $currency
	 *
	 * @return mixed|string|void
	 */
	public function currency_filter($price = '', $currency = '') {
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
					$formatted = $symbol . ' ' . $price;
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
					$formatted = $price . ' ' . $symbol;
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
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function is_single_price_mode($post_id) {
		$ret = get_post_meta($post_id, '_mprm_price_options_mode', true);
		
		return (bool)apply_filters('mprm_single_price_option_mode', $ret, $post_id);
	}
	
	/**
	 * @param int $item_id
	 * @param int $price_id
	 * @param int $payment_id
	 *
	 * @return mixed
	 */
	public function get_price_option_name($item_id = 0, $price_id = 0, $payment_id = 0) {
		$prices = $this->get_variable_prices($item_id);
		$price_name = '';
		if ($prices && is_array($prices)) {
			if (isset($prices[ $price_id ]))
				$price_name = $prices[ $price_id ][ 'name' ];
		}
		
		return apply_filters('mprm_get_price_option_name', $price_name, $item_id, $payment_id, $price_id);
	}
	
	/**
	 * @param int $item_id
	 *
	 * @return bool|mixed
	 */
	public function get_variable_prices($item_id = 0) {
		if (empty($item_id)) {
			return false;
		}
		
		return $this->get_prices($item_id);
	}
	
	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function get_prices($post_id) {
		$prices = get_post_meta($post_id, 'mprm_variable_prices', true);
		
		return apply_filters('mprm_get_' . get_post_type($post_id) . '_variable_prices', $prices, $post_id);
	}
	
	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function get_disabled_checkout($post_id) {
		$disabled_checkout = get_post_meta($post_id, '_disabled_checkout', true);
		
		return apply_filters('mprm_get_disabled_checkout', $disabled_checkout, $post_id);
	}
	
	/**
	 * @param bool $price_id
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function is_free($price_id = false, $post_id) {
		global $post;
		if (empty($post)) {
			$post = get_post($post_id);
		}
		$is_free = false;
		$variable_pricing = $this->has_variable_prices($post->ID);
		if ($variable_pricing && !is_null($price_id) && $price_id !== false) {
			$price = $this->get_price_option_amount($post->ID, $price_id);
		} elseif ($variable_pricing && $price_id === false) {
			$lowest_price = (float)$this->get_price_option($post->ID, 'min');
			$highest_price = (float)$this->get_price_option($post->ID, 'max');
			if ($lowest_price === 0.00 && $highest_price === 0.00) {
				$price = 0;
			}
		} elseif (!$variable_pricing) {
			$price = get_post_meta($post->ID, 'price', true);
		}
		if (isset($price) && (float)$price == 0) {
			$is_free = true;
		}
		
		return (bool)apply_filters('mprm_is_free_' . $post->post_type, $is_free, $post->ID, $price_id);
	}
	
	/**
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function has_variable_prices($post_id) {
		$ret = get_post_meta($post_id, '_variable_pricing', true);
		
		return (bool)apply_filters('mprm_' . get_post_type($post_id) . 'has_variable_prices', $ret, $post_id);
	}
	
	/**
	 * @param int $item_id
	 * @param int $price_id
	 *
	 * @return mixed
	 */
	public function get_price_option_amount($item_id = 0, $price_id = 0) {
		$prices = $this->get_variable_prices($item_id);
		$amount = 0.00;
		if ($prices && is_array($prices)) {
			if (isset($prices[ $price_id ]))
				$amount = $prices[ $price_id ][ 'amount' ];
		}
		
		return apply_filters('mprm_get_price_option_amount', $this->get('formatting')->sanitize_amount($amount), $item_id, $price_id);
	}
	
	/**
	 * @param int $item_id
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_price_option($item_id = 0, $type = 'max') {
		$price_type = 0.00;
		$max = 0;
		$id = 0;
		if (empty($item_id))
			$item_id = get_the_ID();
		if (!$this->has_variable_prices($item_id)) {
			return $this->get_price($item_id);
		}
		$prices = $this->get_variable_prices($item_id);
		
		if (!empty($prices)) {
			foreach ($prices as $key => $price) {
				if (empty($price[ 'amount' ])) {
					continue;
				}
				if ($type == 'min') {
					if (!isset($min)) {
						$min = $price[ 'amount' ];
					} else {
						$min = min($min, $price[ 'amount' ]);
					}
					if ($price[ 'amount' ] == $min) {
						$id = $key;
					}
				} elseif ($type == 'max') {
					$max = max($max, $price[ 'amount' ]);
					if ($price[ 'amount' ] == $max) {
						$id = $key;
					}
				}
			}
			$price_type = $prices[ $id ][ 'amount' ];
		}
		
		return $this->get('formatting')->sanitize_amount($price_type);
	}
	
	/**
	 * Get item class
	 *
	 * @param int $id
	 * @param bool $format
	 *
	 * @return string
	 */
	public function get_price($id, $format = false) {
		$price = get_post_meta($id, 'price', true);
		
		$price = floatval(str_replace(',', '.', $price));
		
		if ($format) {
			$price = $this->get_formatting_price($price);
		}
		
		return $price;
	}
	
	/**
	 * @param $amount
	 * @param bool $decimals
	 *
	 * @return mixed
	 */
	public function get_formatting_price($amount, $decimals = true) {
		return $this->get('formatting')->format_amount($amount, $decimals);
	}
	
	/**
	 * Get attributes
	 *
	 * @param \WP_Post $post
	 *
	 * @return array/void
	 */
	public function get_attributes(\WP_Post $post) {
		if (empty($post)) {
			return array();
		}
		$attributes = get_post_meta($post->ID, 'attributes', true);
		if (!$this->is_arr_values_empty($attributes)) {
			return $attributes;
		} else {
			return array();
		}
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
	
	
}