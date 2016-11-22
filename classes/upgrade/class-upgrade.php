<?php
namespace mp_restaurant_menu\classes\upgrade;

use mp_restaurant_menu\classes\Capabilities;
use mp_restaurant_menu\classes\Core;

/**
 * Class Upgrade
 *
 * @package mp_restaurant_menu\classes\upgrade
 */
class Upgrade extends Core {

	protected static $instance;

	/**
	 * @return Upgrade
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get upgrade
	 */
	public function get_upgrade() {
		$this->check_upgrade_structure();
		$this->check_upgrade_cap_roles();
	}

	/**
	 * Upgrade structure
	 */
	public function check_upgrade_structure() {
		$capability_version = Install::get_instance()->get_structure_version();
		$saved_version = get_option('mprm_db_version', '0.0.0');

		if (version_compare($capability_version, $saved_version, ">")) {
			Install::get_instance()->create_structure();
		}
	}

	/**
	 * Upgrade cap and roles
	 */
	public function check_upgrade_cap_roles() {
		// User capability
		$capability_version = Capabilities::get_instance()->get_capability_version();
		$saved_version = get_option('mprm_capabilities_version', '0.0.0');

		if (version_compare($capability_version, $saved_version, ">")) {
			Install::get_instance()->setup_roles_capabilities();
		}
	}
}