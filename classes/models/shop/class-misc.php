<?php

namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Capabilities;
use mp_restaurant_menu\classes\Model;

class Misc extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function get_current_page_url($nocache = false) {

		global $wp;

		if (get_option('permalink_structure')) {

			$base = trailingslashit(home_url($wp->request));

		} else {

			$base = add_query_arg($wp->query_string, '', trailingslashit(home_url($wp->request)));
			$base = remove_query_arg(array('post_type', 'name'), $base);

		}

		$scheme = is_ssl() ? 'https' : 'http';
		$uri = set_url_scheme($base, $scheme);

		if (is_front_page()) {
			$uri = home_url('/');
		} elseif ($this->get('checkout')->is_checkout(array(), false)) {
			$uri = $this->get('checkout')->get_checkout_uri();
		}

		$uri = apply_filters('mprm_get_current_page_url', $uri);

		if ($nocache) {
			$uri = $this->add_cache_busting($uri);
		}

		return $uri;
	}

	/**
	 * Adds the 'nocache' parameter to the provided URL
	 *
	 * @since  2.4.4
	 *
	 * @param  string $url The URL being requested
	 *
	 * @return string      The URL with cache busting added or not
	 */
	function add_cache_busting($url = '') {

		$no_cache_checkout = $this->get('settings')->get_option('no_cache_checkout', false);

		if (Capabilities::get_instance()->is_caching_plugin_active() || ($this->get('checkout')->is_checkout() && $no_cache_checkout)) {
			$url = add_query_arg('nocache', 'true', $url);
		}

		return $url;
	}
}