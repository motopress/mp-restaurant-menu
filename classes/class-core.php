<?php
namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\models\Session;
use mp_restaurant_menu\classes\models\Settings;
use mp_restaurant_menu\classes\Shortcodes;
use mp_restaurant_menu\classes\modules\MPRM_Widget;

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
			'menu_item' => 'mp_menu_item',
			'order' => 'mprm_order'
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
	 * Get taxonomy name
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function get_tax_name($value) {
		if (isset($this->taxonomy_names[$value])) {
			return $this->taxonomy_names[$value];
		}
	}

	/**
	 * Get post type
	 *
	 * @param string $value
	 *
	 * @return string
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
		global $mprm_options;
		ob_start('mp_restaurant_menu\classes\Preprocessor::fatal_error_handler');
		// run session
		if (!session_id()) {
			session_start();
		}
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
		$mprm_options = Settings::get_instance()->get_settings();
		Session::get_instance()->maybe_start_session();
		Session::get_instance()->init();
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
	 * Install current state
	 */
	public function install_state($name) {
		// Include plugin state
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
	 * @return bool
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
	 * @return object/bool State
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
	 * @param object $state
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
