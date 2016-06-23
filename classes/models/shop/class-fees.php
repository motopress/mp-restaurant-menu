<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Fees
 * @package mp_restaurant_menu\classes\models
 */
class Fees extends Model {
	protected static $instance;

	/**
	 * @return Fees
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function has_fees($type = 'fee') {
		if ('all' == $type || 'fee' == $type) {
			if (!$this->get('cart')->get_cart_contents()) {
				$type = 'item';
			}
		}
		$fees = $this->get_fees($type);
		return !empty($fees) && is_array($fees);
	}

	/**
	 * @param string $type
	 * @param int $menu_item_id
	 *
	 * @return array
	 */
	public function get_fees($type = 'fee', $menu_item_id = 0) {
		$fees = $this->get('session')->get('mprm_cart_fees');
		if (!$this->get('cart')->get_cart_contents()) {
			// We can only get item type fees when the cart is empty
			$type = 'item';
		}
		if (!empty($fees) && !empty($type) && 'all' !== $type) {
			foreach ($fees as $key => $fee) {
				if (!empty($fee['type']) && $type != $fee['type']) {
					unset($fees[$key]);
				}
			}
		}
		if (!empty($fees) && !empty($menu_item_id)) {
			// Remove fees that don't belong to the specified Menu item
			foreach ($fees as $key => $fee) {
				if ((int)$menu_item_id !== (int)$fee['menu_item_id']) {
					unset($fees[$key]);
				}
			}
		}
		if (!empty($fees)) {
			// Remove fees that belong to a specific menu_item but are not in the cart
			foreach ($fees as $key => $fee) {
				if (empty($fee['menu_item_id'])) {
					continue;
				}
				if (!$this->get('cart')->item_in_cart($fee['menu_item_id'])) {
					unset($fees[$key]);
				}
			}
		}
		return !empty($fees) ? $fees : array();
	}

	/**
	 * @param int $menu_item_id
	 *
	 * @return mixed
	 */
	public function total($menu_item_id = 0) {
		$fees = $this->get_fees('all', $menu_item_id);
		$total = (float)0.00;
		if ($this->has_fees('all')) {
			foreach ($fees as $fee) {
				$total += $this->get('formatting')->sanitize_amount($fee['amount']);
			}
		}
		return $this->get('formatting')->sanitize_amount($total);
	}
}
