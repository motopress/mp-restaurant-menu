<?php
namespace mp_restaurant_menu\classes\upgrade;

use mp_restaurant_menu\classes\Capabilities;
use mp_restaurant_menu\classes\Core;

/**
 * Class Install
 * @package mp_restaurant_menu\classes\upgrade
 */
class Install extends Core {

	protected static $instance;

	private $tables = array('mprm_customer');

	private $structure_version = '2.0.0';

	/**
	 * @return Install
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get structure version
	 *
	 * @return string
	 */
	public function get_structure_version() {
		return $this->structure_version;
	}

	/**
	 * Create structure
	 */
	public function create_structure() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$db_version = get_option('mprm_db_version', '0.0.0');
		if (version_compare($this->structure_version, $db_version, ">")) {
			foreach ($this->tables as $table) {
				if (!$this->check_table_exists($table)) {
					$this->create_table($table);
				}
			}
			update_option('mprm_db_version', $this->structure_version);
		}
	}

	/**
	 * Check table exists
	 *
	 * @param $table_name
	 *
	 * @return bool
	 */
	public function check_table_exists($table_name) {
		global $wpdb;
		$table_name_with_prefix = $wpdb->prefix . $table_name;
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_with_prefix'") != $table_name_with_prefix) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Create Table
	 *
	 * @param $table_name
	 */
	public function create_table($table_name) {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name_with_prefix = $wpdb->prefix . $table_name;

		$sql = $this->get_table_structure($table_name, $table_name_with_prefix, $charset_collate);
		if (!empty($sql)) {
			dbDelta($sql);
		}

	}

	/**
	 * Get table structure
	 *
	 * @param $table_name
	 * @param $table_name_with_prefix
	 * @param $charset_collate
	 *
	 * @return mixed
	 */
	public function get_table_structure($table_name, $table_name_with_prefix, $charset_collate) {
		$sql = '';
		switch ($table_name) {
			case'mprm_customer':
				$sql = $this->get_table_structureV2($table_name_with_prefix, $charset_collate);
				break;
			default:
				break;
		}
		return $sql;
	}

	/**
	 * Table structure for version 2.0
	 *
	 * @param $table_name_with_prefix
	 *
	 * @param $charset_collate
	 *
	 * @return string
	 */
	public function get_table_structureV2($table_name_with_prefix, $charset_collate) {
		$sql = "CREATE TABLE {$table_name_with_prefix} (
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
		PRIMARY KEY (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		) {$charset_collate};";
		return $sql;
	}

	/**
	 * Delete table
	 *
	 * @param $table_name
	 */
	public function delete_table($table_name) {
		global $wpdb;
		$table_name_with_prefix = $wpdb->prefix . $table_name;

		$wpdb->query("DROP TABLE IF EXISTS {$table_name_with_prefix}");

		delete_option($table_name_with_prefix . '_db_version');
	}

	/**
	 * Roles Capability
	 */
	public function setup_roles_capabilities() {
		// User capability
		Capabilities::get_instance()->add_roles();
		Capabilities::get_instance()->add_caps();
	}

	/**
	 * Upgrade if need
	 */
	public function upgrade_roles_capabilities() {
		//Capabilities::get_instance()->remove_caps();
		Capabilities::get_instance()->add_roles();
		Capabilities::get_instance()->add_caps();
	}
}