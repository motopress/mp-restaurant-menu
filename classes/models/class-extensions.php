<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Extension
 *
 * @package mp_restaurant_menu\classes\models
 *
 * @version 1.0.0
 */
class Extension extends Model {

	protected static $instance;
	// limit for get products
	protected $limit = 100;
	// 48 hours
	protected $cache_expire_time = 172800;
	// taxonomy name
	protected $taxonomy = 'download_category';
	// taxonomy slug
	protected $taxonomy_slug = 'restaurant-menu-addons';
	// api version v1/v2
	// http://docs.easydigitaldownloads.com/category/1130-api-reference
	private $api_version = 'v1';

	/**
	 * Get instance
	 *
	 * @return Extension
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get extensions data
	 *
	 * @return array|mixed|object
	 */
	public function get_extensions() {
		list($feed_data, $message) = $this->json_decode($this->get_add_ons_feed_data());
		if ($feed_data && is_array($feed_data)) {
			$feed_data = $this->filter_feeds($feed_data);
		}
		return $feed_data;
	}

	/**
	 * Json decode
	 *
	 * @param $json
	 *
	 * @return array  data,message
	 */
	public function json_decode($json) {

		$data = json_decode($json);
		$products = empty($data->products) ? array() : $data->products;

		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				$message = __('Error none', 'mp-restaurant-menu');
				break;
			case JSON_ERROR_DEPTH:
				$message = __('Reached the maximum stack depth', 'mp-restaurant-menu');
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$message = __('Incorrect level or match modes', 'mp-restaurant-menu');
				break;
			case JSON_ERROR_CTRL_CHAR:
				$message = __('Invalid escape character', 'mp-restaurant-menu');
				break;
			case JSON_ERROR_SYNTAX:
				$message = __('Syntax error, not a valid JSON', 'mp-restaurant-menu');
				break;
			case JSON_ERROR_UTF8:
				$message = __('Invalid UTF-8 characters, possibly incorrect coding', 'mp-restaurant-menu');
				break;
			default:
				$message = __('Unknown error', 'mp-restaurant-menu');
				break;
		}
		return array($products, $message);
	}

	/**
	 * Add-ons Get Feed
	 *
	 * Gets the add-ons page feed.
	 *
	 * @return mixed|string
	 */
	protected function get_add_ons_feed_data() {
		if (false === ($cache = get_transient('mp_restaurant_add_ons_feed'))) {
			$url = 'http://www.getmotopress.com/edd-api/' . $this->api_version . '/products/';
			$url = add_query_arg(array('number' => $this->limit), $url);
			$feed = wp_remote_get(esc_url_raw($url), array('sslverify' => false));

			if (!is_wp_error($feed)) {
				if (isset($feed['body']) && strlen($feed['body']) > 0) {
					$cache = wp_remote_retrieve_body($feed);
					set_transient('mp_restaurant_add_ons_feed', $cache, $this->cache_expire_time);
				}
			} else {
				$cache = '<div class="error"><p>' . __('There was an error retrieving the extensions list from the server. Please try again later.', 'mp-restaurant-menu') . '</div>';
			}
		}
		return $cache;
	}

	/**
	 * Filtering feeds by taxonomy slug,
	 *
	 * @param array $feeds
	 *
	 * @return array
	 */
	public function filter_feeds($feeds = array()) {
		$feeds_array = array();

		if (!empty($feeds)) {
			foreach ($feeds as $key_product => $product) {
				$categories = $product->info->category;
				if ($categories && is_array($categories)) {
					foreach ($categories as $category) {
						if (($category->slug == $this->taxonomy_slug) && ($category->taxonomy == $this->taxonomy)) {
							$feeds_array[$product->info->id] = $product->info;
							break;
						}
					}
				}
			}
			return $feeds_array;
		} else {
			return array();
		}
	}
}