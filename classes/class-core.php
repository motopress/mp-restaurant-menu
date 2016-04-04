<?php

namespace mp_restaurant_menu\classes;

//use mp_restaurant_menu\classes\View;
//use mp_restaurant_menu\classes\Model;
//use mp_restaurant_menu\classes\Controller;
//use mp_restaurant_menu\classes\Preprocessor;
//use mp_restaurant_menu\classes\State_Factory;
//use mp_restaurant_menu\classes\Module;
//use mp_restaurant_menu\classes\Hooks;
use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\modules\Widget;


/**
 * Class main state
 */
class Core {
	/**
	 * Current state
	 */
	private $state;
	private $version;
	protected $taxonomy_names;
	protected $post_types;
	protected $posts = array();
	protected static $instance;

	public function __construct() {
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		$this->taxonomy_names = array(
			'menu_category' => 'mp_menu_category',
			'menu_tag' => 'mp_menu_tag',
			'ingredient' => 'mp_ingredient'
		);
		$this->post_types = array(
			'menu_item' => 'mp_menu_item'
		);
		$this->init_plugin_version();
	}

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_version() {
		return $this->version;
	}

	/**
	 * Get taxonomy name
	 *
	 * @param string $value
	 *
	 * @return array
	 */
	public function get_tax_name($value) {
		if (isset($this->taxonomy_names[$value])) {
			return $this->taxonomy_names[$value];
		}
	}

	/**
	 * Get post type
	 *
	 * @param type $value
	 *
	 * @return type
	 */
	public function get_post_type($value) {
		if (isset($this->post_types[$value])) {
			return $this->post_types[$value];
		}
	}

	/**
	 * Init current plugin
	 */
	public function init_plugin($name) {
		
		load_plugin_textdomain( 'mp-restaurant-menu', FALSE, MP_RM_LANG_PATH );
		
		// include plugin models files
		Model::install();
		// include plugin controllers files
		Controller::get_instance()->install();
		// include plugin Preprocessors files
		Preprocessor::install();
		// include plugin Modules files 
		Module::install();
		//include shortcodes
		Shortcodes::install();
		// inclide all widgets
		Widget::install();
		// install state
		$this->install_state($name);
		// include templates functions
		$this->include_all(MP_RM_TEMPLATES_FUNCTIONS);
		// include templates actions
		$this->include_all(MP_RM_TEMPLATES_ACTIONS);
		// init all hooks
		Hooks::install_hooks();
		// install templates actions
		Hooks::install_templates_actions();
	}

	/**
	 * Get model instace
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
	 *  Get plugin version
	 */
	public function init_plugin_version() {
		$filePath = MP_RM_PLUGIN_PATH . 'restaurant-menu.php';
		if (!$this->version) {
			$pluginObject = get_plugin_data($filePath);
			$this->version = $pluginObject['Version'];
		}
	}

	/**
	 * Load language file
	 *
	 * @param bool $domain
	 *
	 * @return bool
	 */
	public function load_language($domain = false) {
		if (empty($domain)) {
			return false;
		}
		$locale = get_option("locate", true);
		$moFile = MP_RM_LANG_PATH . "{$domain}-{$locale}.mo";
		$result = load_textdomain($domain, $moFile);
		return $result;
	}

	/**
	 * install current state
	 */
	public function install_state($name) {
		// include plugin state
		Core::get_instance()->set_state(new State_Factory($name));
	}

	/**
	 * Route plugin url
	 */
	public function wp_ajax_route_url() {
		$controller = isset($_REQUEST["controller"]) ? $_REQUEST["controller"] : null;
		$action = isset($_REQUEST["mprm_action"]) ? $_REQUEST["mprm_action"] : null;
		//$type = "defrozo";
		if (!empty($action)) {
			// call controller
			Preprocessor::get_instance()->call_controller($action, $controller);
			die();
		}
	}

	/**
	 * Check for ajax post
	 *
	 * @return type
	 */
	static function is_ajax() {
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get State
	 *
	 * @return State
	 */
	public function get_state() {
		if ($this->state) {
			return $this->state;
		} else {
			return false;
		}
	}

	/**
	 * Get controller
	 *
	 * @param type $type
	 *
	 * @return boolean
	 */
	public function get_controller($type) {
		return Core::get_instance()->get_state()->get_controller($type);
	}

	/**
	 * Get view
	 *
	 * @return type
	 */
	public function get_view() {
		return View::get_instance();
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
	 * Set state
	 *
	 * @param State $state
	 */
	public function set_state($state) {
		$this->state = $state;
	}

	/**
	 * Get data from config files
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get_config($name) {
		if (!empty($name)) {
			return require(MP_RM_CONFIGS_PATH . "{$name}.php");
		}
	}

	/**
	 * Include all files from folder
	 *
	 * @param string $folder
	 * @param boolean $inFolder
	 */
	static function include_all($folder, $inFolder = true) {
		if (file_exists($folder)) {
			$includeArr = scandir($folder);
			foreach ($includeArr as $include) {
				if (!is_dir($folder . "/" . $include)) {
					include_once($folder . "/" . $include);
				} else {
					if ($include != "." && $include != ".." && $inFolder) {
						Core::include_all($folder . "/" . $include);
					}
				}
			}
		}
	}
}
