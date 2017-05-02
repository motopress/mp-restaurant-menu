<?php
namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\models\Session;
use mp_restaurant_menu\classes\modules\MPRM_Widget;
use mp_restaurant_menu\classes\upgrade\Upgrade;

/**
 * Class main state
 */
class Core {
	protected static $instance;
	
	public static $wpHeadFinished = false;
	
	protected $posts = array();
	
	protected $taxonomy_names = array(
		'menu_category' => 'mp_menu_category',
		'menu_tag' => 'mp_menu_tag',
		'ingredient' => 'mp_ingredient'
	);
	
	protected $post_types = array(
		'menu_item' => 'mp_menu_item',
		'order' => 'mprm_order'
	);
	/**
	 * Current state
	 */
	private $state;
	private $version;
	
	/**
	 * Core constructor.
	 */
	public function __construct() {
		
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		$this->init_plugin_version();
	}
	
	/**
	 *  Get plugin version
	 */
	public function init_plugin_version() {
		$filePath = MP_RM_PLUGIN_PATH . 'restaurant-menu.php';
		if (!$this->version) {
			$pluginObject = get_plugin_data($filePath);
			$this->version = $pluginObject[ 'Version' ];
		}
	}
	
	/**
	 * Check for ajax post
	 *
	 * @return bool
	 */
	static function is_ajax() {
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * Get post types
	 *
	 * @param string $output
	 *
	 * @return array
	 */
	public function get_post_types($output = '') {
		if ($output == 'key') {
			return array_keys($this->post_types);
		} elseif ($output == 'value') {
			return array_values($this->post_types);
		} else {
			return $this->post_types;
		}
	}
	
	/**
	 * Get post types
	 *
	 * @param string $output
	 *
	 * @return array
	 */
	public function get_taxonomy_types($output = '') {
		if ($output == 'key') {
			return array_keys($this->taxonomy_names);
		} elseif ($output == 'value') {
			return array_values($this->taxonomy_names);
		} else {
			return $this->taxonomy_names;
		}
	}
	
	
	/**
	 * Get taxonomy name
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function get_tax_name($value) {
		if (isset($this->taxonomy_names[ $value ])) {
			return $this->taxonomy_names[ $value ];
		}
		
		return false;
	}
	
	/**
	 * Get post type
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function get_post_type($value) {
		if (isset($this->post_types[ $value ])) {
			return $this->post_types[ $value ];
		}
		
		return false;
	}
	
	/**
	 * Init current plugin
	 *
	 * @param $name
	 */
	public function init_plugin($name) {
		
		load_plugin_textdomain('mp-restaurant-menu', FALSE, MP_RM_LANG_PATH);
		
		// User capability
		Upgrade::get_instance()->check_upgrade_cap_roles();
		// Check table structure
		Upgrade::get_instance()->check_upgrade_structure();
		
		// Include plugin models files
		Model::install();
		// Include plugin controllers files
		Controller::get_instance()->install();
		// Include plugin Preprocessors files
		Preprocessor::install();
		// Include plugin Modules files
		Module::install();
		// Include shortcodes
		Shortcodes::install();
		// inclide all widgets
		MPRM_Widget::install();
		// install state
		$this->install_state($name);
		// Include templates functions
		$this->include_all(MP_RM_TEMPLATES_FUNCTIONS);
		// Include templates actions
		$this->include_all(MP_RM_TEMPLATES_ACTIONS);
		// init all hooks
		Hooks::install_hooks();
		// install templates actions
		Hooks::install_templates_actions();
		
		Session::get_instance()->maybe_start_session();
		Session::get_instance()->init();
	}
	
	/**
	 * Install current state
	 *
	 * @param $name
	 */
	public function install_state($name) {
		// Include plugin state
		Core::get_instance()->set_state(new State_Factory($name));
	}
	
	/**
	 * @return Core
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Include all files from folder
	 *
	 * @param string $folder
	 * @param boolean $inFolder
	 */
	public function include_all($folder, $inFolder = true) {
		if (file_exists($folder)) {
			$includeArr = scandir($folder);
			foreach ($includeArr as $include) {
				if (!is_dir($folder . "/" . $include)) {
					$extension = pathinfo($include, PATHINFO_EXTENSION);
					if ($extension != 'php') {
						continue;
					}
					include_once($folder . "/" . $include);
				} else {
					if ($include != "." && $include != ".." && $inFolder) {
						$this->include_all($folder . "/" . $include);
					}
				}
			}
		}
	}
	
	/**
	 * Get model instance
	 *
	 * @param bool|false $type
	 *
	 * @return bool|mixed
	 */
	public function get($type = false) {
		$state = false;
		if ($type) {
			$state = $this->get_model($type);
		}
		
		return $state;
	}
	
	/**
	 * Check and return current state
	 *
	 * @param string $type
	 *
	 * @return boolean
	 */
	public function get_model($type = null) {
		return Core::get_instance()->get_state()->get_model($type);
	}
	
	
	/**
	 * Get State
	 *
	 * @return bool/Object
	 */
	public function get_state() {
		if ($this->state) {
			return $this->state;
		} else {
			return false;
		}
	}
	
	/**
	 * Set state
	 *
	 * @param object $state
	 */
	public function set_state($state) {
		$this->state = $state;
	}
	
	/**
	 * Route plugin url
	 */
	public function wp_ajax_route_url() {
		$controller = isset($_REQUEST[ "controller" ]) ? $_REQUEST[ "controller" ] : null;
		$action = isset($_REQUEST[ "mprm_action" ]) ? $_REQUEST[ "mprm_action" ] : null;
		if (!empty($action) && !empty($controller)) {
			// call controller
			Preprocessor::get_instance()->call_controller($action, $controller);
			die();
		}
	}
	
	/**
	 * Get controller
	 *
	 * @param string $type
	 *
	 * @return boolean
	 */
	public function get_controller($type) {
		return Core::get_instance()->get_state()->get_controller($type);
	}
	
	/**
	 * Get view
	 *
	 * @return object
	 */
	public function get_view() {
		return View::get_instance();
	}
	
	/**
	 * Get preprocessor
	 *
	 * @param $type
	 *
	 * @return mixed
	 */
	public function get_preprocessor($type = NULL) {
		return Core::get_instance()->get_state()->get_preprocessor($type);
	}
	
	/**
	 *  Theme mode
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function filter_template_mode($value) {
		if (current_theme_supports('mp-restaurant-menu')) {
			$value = 'plugin';
		}
		
		return $value;
	}
	
	/**
	 *  Theme mode
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function filter_button_style($value) {
		if (current_theme_supports('mp-restaurant-menu')) {
			$value = 'button';
		}
		
		return $value;
	}
	
	/**
	 * Checkout color
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function filter_checkout_color($value) {
		switch ($value) {
			case'inherit':
				$button_class = $value;
				break;
			default:
				$button_class = 'mprm-btn ' . $value;
			
		}
		
		return $button_class;
	}
	
	
	/**
	 *  Available theme
	 *
	 * @param $params
	 *
	 * @return array
	 */
	public function available_theme_mode($params) {
		if (current_theme_supports('mp-restaurant-menu')) {
			return array('plugin' => __('Plugin', 'mp-restaurant-menu'));
		}
		
		return $params;
	}
	
	/**
	 * Get data from config files
	 *`
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get_config($name) {
		if (!empty($name)) {
			return require(MP_RM_CONFIGS_PATH . "{$name}.php");
		}
		
		return array();
	}
	
	/**
	 *
	 */
	private function __clone() {
	}
}
