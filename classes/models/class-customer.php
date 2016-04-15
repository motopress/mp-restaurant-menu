<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Customer extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_customer_address() {
		if (empty($user_id)) {
			$user_id = get_current_user_id();
		}

		$address = get_user_meta($user_id, '_edd_user_address', true);

		if (!isset($address['line1']))
			$address['line1'] = '';

		if (!isset($address['line2']))
			$address['line2'] = '';

		if (!isset($address['city']))
			$address['city'] = '';

		if (!isset($address['zip']))
			$address['zip'] = '';

		if (!isset($address['country']))
			$address['country'] = '';

		if (!isset($address['state']))
			$address['state'] = '';

		return $address;
	}
}