<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\libs\WP_Session;

/**
 * MPRM_Session Class
 *
 * @since 1.5
 */
class Session extends Core {
	protected static $instance;

	/**
	 * @return Session
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 1.5
	 */
	private $session;
	/**
	 * Whether to use PHP $_SESSION or WP_Session
	 *
	 * @var bool
	 * @access private
	 * @since 1.5,1
	 */
	private $use_php_sessions = false;
	/**
	 * Session index prefix
	 *
	 * @var string
	 * @access private
	 * @since 2.3
	 */
	private $prefix = '';

	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 1.5
	 */
	public function __construct() {
		parent::__construct();
		$this->use_php_sessions = $this->use_php_sessions();
		if ($this->use_php_sessions) {
			if (is_multisite()) {
				$this->prefix = '_' . get_current_blog_id();
			}
		} else {
			// Use WP_Session (default)
			if (!defined('WP_SESSION_COOKIE')) {
				define('WP_SESSION_COOKIE', 'mprm_wp_session');
			}
			if (!class_exists('Recursive_ArrayAccess')) {
				require_once MP_RM_LIBS_PATH . '/wp_session/class-recursive-arrayaccess.php';
			}
			if (!class_exists('WP_Session')) {
				require_once MP_RM_LIBS_PATH . 'wp_session/class-wp-session.php';
				require_once MP_RM_LIBS_PATH . 'wp_session/class-wp-session-utils.php';
				require_once MP_RM_LIBS_PATH . 'wp_session/wp-session.php';
			}
			add_filter('wp_session_expiration_variant', array($this, 'set_expiration_variant_time'), 99999);
			add_filter('wp_session_expiration', array($this, 'set_expiration_time'), 99999);
		}
	}

	/**
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @since 1.5
	 * @return WP_Session
	 */
	public function init() {
		if ($this->use_php_sessions) {
			$this->session = isset($_SESSION['mprm' . $this->prefix]) && is_array($_SESSION['mprm' . $this->prefix]) ? $_SESSION['mprm' . $this->prefix] : array();
		} else {
			$this->session = WP_Session::get_instance();
		}
		$use_cookie = $this->use_cart_cookie();
		$cart = $this->get_variable('mprm_cart');
		$purchase = $this->get_variable('mprm_purchase');
		if ($use_cookie) {
			if (!empty($cart) || !empty($purchase)) {
				$this->set_cart_cookie();
			} else {
				$this->set_cart_cookie(false);
			}
		}
		return $this->session;
	}

	/**
	 * Retrieve session ID
	 *
	 * @access public
	 * @since 1.6
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}

	/**
	 * @param $key
	 *
	 * @return bool|mixed
	 */
	public function get_session_by_key($key) {
		$key = sanitize_key($key);
		return isset($this->session[$key]) ? maybe_unserialize($this->session[$key]) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @since 1.5
	 *
	 * @param string $key Session key
	 * @param integer $value Session variable
	 *
	 * @return string Session variable
	 */
	public function set($key, $value) {
		$key = sanitize_key($key);
		if (is_array($value)) {
			$this->session[$key] = serialize($value);
		} else {
			$this->session[$key] = $value;
		}
		if ($this->use_php_sessions) {
			$_SESSION['mprm' . $this->prefix] = $this->session;
		}
		return $this->session[$key];
	}

	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 1.5
	 *
	 * @param string $key Session key
	 *
	 * @return string Session variable
	 */
	public function get_variable($key) {
		$key = sanitize_key($key);
		return isset($this->session[$key]) ? maybe_unserialize($this->session[$key]) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @since 1.5
	 *
	 * @param string $key Session key
	 * @param integer $value Session variable
	 *
	 * @return string Session variable
	 */
	public function set_variable($key, $value) {
		$key = sanitize_key($key);
		if (is_array($value)) {
			$this->session[$key] = serialize($value);
		} else {
			$this->session[$key] = $value;
		}
		if ($this->use_php_sessions) {
			$_SESSION['mprm' . $this->prefix] = $this->session;
		}
		return $this->session[$key];
	}

	/**
	 * Set a cookie to identify whether the cart is empty or not
	 *
	 * This is for hosts and caching plugins to identify if caching should be disabled
	 *
	 * @access public
	 * @since 1.8
	 *
	 * @param bool /string $set Whether to set or destroy
	 *
	 * @return void
	 */
	public function set_cart_cookie($set = true) {
		if (!headers_sent()) {
			if ($set) {
				@setcookie('mprm_items_in_cart', '1', time() + 30 * 60, COOKIEPATH, COOKIE_DOMAIN, false);
			} else {
				if (isset($_COOKIE['mprm_items_in_cart'])) {
					@setcookie('mprm_items_in_cart', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false);
				}
			}
		}
	}

	/**
	 * Force the cookie expiration variant time to 23 hours
	 *
	 * @access public
	 * @since 2.0
	 *
	 * @param int $exp Default expiration (1 hour)
	 *
	 * @return int
	 */
	public function set_expiration_variant_time($exp) {
		return (30 * 60 * 23);
	}

	/**
	 * Force the cookie expiration time to 24 hours
	 *
	 * @access public
	 * @since 1.9
	 *
	 * @param int $exp Default expiration (1 hour)
	 *
	 * @return int
	 */
	public function set_expiration_time($exp) {
		return (30 * 60 * 24);
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * @return boolean
	 * Checks to see if the server supports PHP sessions
	 * or if the MPRM_USE_PHP_SESSIONS constant is defined
	 *
	 * @access public
	 * @since 2.1
	 * @author Daniel J Griffiths
	 * @return boolean $ret True if we are using PHP sessions, false otherwise
	 */
	public function use_php_sessions() {
		$ret = false;
		// If the database variable is already set, no need to run autodetection
		$mprm_use_php_sessions = (bool)get_option('mprm_use_php_sessions');

		if (!$mprm_use_php_sessions) {
			// Attempt to detect if the server supports PHP sessions
			if (function_exists('session_start') && !ini_get('safe_mode')) {
				$this->set_variable('mprm_use_php_sessions', 1);
				if ($this->get_variable('mprm_use_php_sessions')) {
					$ret = true;
					// Set the database option
					update_option('mprm_use_php_sessions', true);
				}
			}
		} else {
			$ret = $mprm_use_php_sessions;
		}
		// Enable or disable PHP Sessions based on the MPRM_USE_PHP_SESSIONS constant
		if (defined('MPRM_USE_PHP_SESSIONS') && MPRM_USE_PHP_SESSIONS) {
			$ret = true;
		} else if (defined('MPRM_USE_PHP_SESSIONS') && !MPRM_USE_PHP_SESSIONS) {
			$ret = false;
		}
		return (bool)apply_filters('mprm_use_php_sessions', $ret);
	}

	/**
	 * Determines if a user has set the MPRM_USE_CART_COOKIE
	 *
	 * @since  2.5
	 * @return bool If the store should use the mprm_items_in_cart cookie to help avoid caching
	 */
	public function use_cart_cookie() {
		$ret = true;
		if (defined('MPRM_USE_CART_COOKIE') && !MPRM_USE_CART_COOKIE) {
			$ret = false;
		}
		return (bool)apply_filters('mprm_use_cart_cookie', $ret);
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 */
	public function maybe_start_session() {
		if (!session_id() && !headers_sent()) {
			session_start();
		}
	}
}
