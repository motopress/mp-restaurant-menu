<?php
/**
 * Plugin Name: Restaurant Menu
 * Plugin URI: http://www.getmotopress.com
 * Description: This plugin gives you the power to effectively create, maintain and display online menus for almost any kind of restaurant, cafes and other typical food establishments.
 * Version: 1.1.5
 * Author: MotoPress
 * Author URI: http://www.getmotopress.com
 * License: GPLv2 or later
 * Text Domain: mp-restaurant-menu
 * Domain Path: /languages
 */
use mp_restaurant_menu\classes\Core;
use mp_restaurant_menu\classes\Media;
use mp_restaurant_menu\classes\Capabilities;

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
define('MP_RM_DEBUG', false);


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
		global $wpdb;

		//Register all custom post type, taxonomy and rewrite rule
		Media::get_instance()->register_all_post_type();
		Media::get_instance()->register_all_taxonomies();
		flush_rewrite_rules();

		// User capability
		Capabilities::get_instance()->add_roles();
		Capabilities::get_instance()->add_caps();

		// Create table 
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . "mprm_customer";


		$sql = "CREATE TABLE " . $table_name . " (
		id bigint(11) NOT NULL AUTO_INCREMENT,
		user_id bigint(11) NOT NULL,
		email varchar(50) NOT NULL,
		name mediumtext NOT NULL,
		telephone varchar(15) NOT NULL,
		purchase_value mediumtext NOT NULL,
		purchase_count bigint(11) NOT NULL,
		payment_ids longtext NOT NULL,
		notes longtext NOT NULL,
		date_created datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		)  $charset_collate";

		dbDelta($sql);
		update_option($table_name . '_db_version', Core::get_instance()->get_version());
	}

	/**
	 * On deactivation plugin
	 */
	public static function on_deactivation() {

		Capabilities::get_instance()->remove_caps();
		flush_rewrite_rules();

	}

	/**
	 * On uninstall
	 */
	public static function on_uninstall() {

	}

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
	}

	/**
	 * MP_Restaurant_Menu_Setup_Plugin constructor.
	 */
	public function __construct() {
		$this->include_all();
		Core::get_instance()->init_plugin(MP_RM_PLUGIN_NAME);
	}
}
