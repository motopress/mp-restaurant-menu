<?php
/**
 * Plugin Name: Restaurant Menu
 * Plugin URI: https://motopress.com
 * Description: This plugin gives you the power to effectively create, maintain and display online menus for almost any kind of restaurant, cafes and other typical food establishments.
 * Version: 2.3.3
 * Author: MotoPress
 * Author URI: https://motopress.com
 * License: GPLv2 or later
 * Text Domain: mp-restaurant-menu
 * Domain Path: /languages
 */
use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\upgrade\Install;

define('MP_RM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MP_RM_MEDIA_URL', plugins_url(plugin_basename(__DIR__) . '/media/'));
define('MP_RM_JS_URL', MP_RM_MEDIA_URL . 'js/');
define('MP_RM_CSS_URL', MP_RM_MEDIA_URL . 'css/');
define('MP_RM_PLUGIN_NAME', str_replace('-', '_', dirname(plugin_basename(__FILE__))));
define('MP_RM_LANG_PATH', MP_RM_PLUGIN_PATH . 'languages/');
define('MP_RM_TEMPLATES_PATH', MP_RM_PLUGIN_PATH . 'templates/');
define('MP_RM_CLASSES_PATH', MP_RM_PLUGIN_PATH . 'classes/');
define('MP_RM_PREPROCESSORS_PATH', MP_RM_CLASSES_PATH . 'preprocessors/');
define('MP_RM_CONTROLLERS_PATH', MP_RM_CLASSES_PATH . 'controllers/');
define('MP_RM_WIDGETS_PATH', MP_RM_CLASSES_PATH . 'widgets/');
define('MP_RM_MODELS_PATH', MP_RM_CLASSES_PATH . 'models/');
define('MP_RM_MODULES_PATH', MP_RM_CLASSES_PATH . 'modules/');
define('MP_RM_LIBS_PATH', MP_RM_CLASSES_PATH . 'libs/');
define('MP_RM_CONFIGS_PATH', MP_RM_PLUGIN_PATH . 'configs/');
define('MP_RM_TEMPLATES_ACTIONS', MP_RM_PLUGIN_PATH . 'templates-actions/');
define('MP_RM_TEMPLATES_FUNCTIONS', MP_RM_PLUGIN_PATH . 'templates-functions/');
define('MP_RM_DEBUG', FALSE);

register_activation_hook(__FILE__, array(MP_Restaurant_Menu_Setup_Plugin::init(), 'on_activation'));
register_deactivation_hook(__FILE__, array('MP_Restaurant_Menu_Setup_Plugin', 'on_deactivation'));
register_uninstall_hook(__FILE__, array('MP_Restaurant_Menu_Setup_Plugin', 'on_uninstall'));
add_action('plugins_loaded', array('MP_Restaurant_Menu_Setup_Plugin', 'init'));

/**
 * Class MP_Restaurant_Menu_Setup_Plugin
 */
class MP_Restaurant_Menu_Setup_Plugin {
	protected static $instance;
	
	/**
	 * MP_Restaurant_Menu_Setup_Plugin constructor.
	 */
	public function __construct() {
		MP_Restaurant_Menu_Setup_Plugin::include_all();
		Core::get_instance()->init_plugin(MP_RM_PLUGIN_NAME);
		if (!defined('MP_RM_TEMPLATE_PATH')) {
			define('MP_RM_TEMPLATE_PATH', $this->template_path());
		}
	}
	
	/**
	 * Include files
	 */
	static function include_all() {
		/**
		 * Install Fire bug
		 */
		require_once MP_RM_LIBS_PATH . 'FirePHPCore/fb.php';
		/**
		 * Include Gump Validator
		 */
		require_once MP_RM_LIBS_PATH . 'gump.class.php';
		/**
		 * Include WP Parser
		 */
		require_once MP_RM_LIBS_PATH . 'parsers.php';
		/**
		 * Include WP Parser
		 */
		require_once MP_RM_LIBS_PATH . 'gateways/ipnlistener.php';
		/**
		 * Include state
		 */
		require_once MP_RM_CLASSES_PATH . 'class-state-factory.php';
		/**
		 * Include Core class
		 */
		require_once MP_RM_CLASSES_PATH . 'class-core.php';
		/**
		 * Include Core class
		 */
		require_once MP_RM_CLASSES_PATH . 'class-capability.php';
		/**
		 * Include Model
		 */
		require_once MP_RM_CLASSES_PATH . 'class-model.php';
		/**
		 * Include Controller
		 */
		require_once MP_RM_CLASSES_PATH . 'class-controller.php';
		/**
		 * Include Preprocessor
		 */
		require_once MP_RM_CLASSES_PATH . 'class-preprocessor.php';
		/**
		 * Include Module
		 */
		require_once MP_RM_CLASSES_PATH . 'class-module.php';
		/**
		 * Include view
		 */
		require_once MP_RM_CLASSES_PATH . 'class-view.php';
		/**
		 * Include media
		 */
		require_once MP_RM_CLASSES_PATH . 'class-media.php';
		/**
		 * Include hooks
		 */
		require_once MP_RM_CLASSES_PATH . 'class-hooks.php';
		/**
		 * Include hooks
		 */
		require_once MP_RM_CLASSES_PATH . 'class-shortcodes.php';
		
		require_once MP_RM_CLASSES_PATH . 'upgrade/class-upgrade.php';
		require_once MP_RM_CLASSES_PATH . 'upgrade/class-install.php';
	}
	
	/**
	 * Get the template path.
	 * @return string
	 */
	public function template_path() {
		return apply_filters('mprm_template_path', 'mp-restaurant-menu/');
	}
	
	/**
	 * @return MP_Restaurant_Menu_Setup_Plugin
	 */
	public static function init() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * On activation plugin
	 */
	public static function on_activation() {
		//Register all custom post type, taxonomy and rewrite rule
		Media::get_instance()->register_all_post_type();
		Media::get_instance()->register_all_taxonomies();
		
		// User capability
		Install::get_instance()->setup_roles_capabilities();
		
		// Create table/tables
		Install::get_instance()->create_structure();
		
		flush_rewrite_rules();
	}
	
	/**
	 * On deactivation plugin
	 */
	public static function on_deactivation() {
		update_option('mprm_capabilities_version', '0.0.0');
		flush_rewrite_rules();
	}
	
	/**
	 * On uninstall
	 */
	public static function on_uninstall() {
		
	}
}
