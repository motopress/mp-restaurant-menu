<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Taxes
 * @package mp_restaurant_menu\classes\models
 */
class Taxes extends Model {
	protected static $instance;
	
	/**
	 * @return Taxes
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * @return mixed
	 */
	public function display_tax_rate() {
		$ret = $this->use_taxes() && $this->get('settings')->get_option('display_tax_rate', false);
		
		return apply_filters('mprm_display_tax_rate', $ret);
	}
	
	/**
	 * @return bool
	 */
	public function use_taxes() {
		$ret = $this->get('settings')->get_option('enable_taxes', false);
		
		return (bool)apply_filters('mprm_use_taxes', $ret);
	}
	
	/**
	 * @param int $amount
	 * @param bool $country
	 * @param bool $state
	 *
	 * @return mixed
	 */
	public function calculate_tax($amount = 0, $country = false, $state = false) {
		$rate = $this->get_tax_rate($country, $state);
		$tax = 0.00;
		if ($this->use_taxes()) {
			if ($this->prices_include_tax()) {
				$pre_tax = ($amount / (1 + $rate));
				$tax = $amount - $pre_tax;
			} else {
				$tax = $amount * $rate;
			}
		}
		
		return apply_filters('mprm_taxed_amount', $tax, $rate, $country, $state);
	}
	
	/**
	 * @param bool $country
	 * @param bool $state
	 *
	 * @return mixed
	 */
	public function get_tax_rate($country = false, $state = false) {
		$rate = (float)$this->get('settings')->get_option('tax_rate', 0);
		$user_address = $this->get('customer')->get_customer_address();
		if (empty($country)) {
			if (!empty($_POST[ 'billing_country' ])) {
				$country = $_POST[ 'billing_country' ];
			} elseif (is_user_logged_in() && !empty($user_address)) {
				$country = $user_address[ 'country' ];
			}
			$country = !empty($country) ? $country : $this->get('settings')->get_shop_country();
		}
		if (empty($state)) {
			if (!empty($_POST[ 'state' ])) {
				$state = $_POST[ 'state' ];
			} elseif (is_user_logged_in() && !empty($user_address)) {
				$state = $user_address[ 'state' ];
			}
			$state = !empty($state) ? $state : $this->get('settings')->get_shop_state();
		}
		if (!empty($country)) {
			$tax_rates = $this->get_tax_rates();
			if (!empty($tax_rates)) {
				// Locate the tax rate for this country / state, if it exists
				foreach ($tax_rates as $key => $tax_rate) {
					if ($country != $tax_rate[ 'country' ])
						continue;
					if (!empty($tax_rate[ 'global' ])) {
						if (!empty($tax_rate[ 'rate' ])) {
							$rate = number_format($tax_rate[ 'rate' ], 4);
						}
					} else {
						if (empty($tax_rate[ 'state' ]) || strtolower($state) != strtolower($tax_rate[ 'state' ]))
							continue;
						$state_rate = $tax_rate[ 'rate' ];
						if (0 !== $state_rate || !empty($state_rate)) {
							$rate = number_format($state_rate, 4);
						}
					}
				}
			}
		}
		if ($rate > 1) {
			// Convert to a number we can use
			$rate = $rate / 100;
		}
		
		return apply_filters('mprm_tax_rate', $rate, $country, $state);
	}
	
	/**
	 * @return mixed
	 */
	public function get_tax_rates() {
		$rates = get_option('mprm_tax_rates', array());
		
		return apply_filters('mprm_get_tax_rates', $rates);
	}
	
	/**
	 * @return mixed
	 */
	public function prices_include_tax() {
		$ret = ($this->get('settings')->get_option('prices_include_tax', false) == 'yes' && $this->use_taxes());
		
		return apply_filters('mprm_prices_include_tax', $ret);
	}
	
	/**
	 * @param int $menu_item_id
	 *
	 * @return mixed
	 */
	public function menu_item_is_tax_exclusive($menu_item_id = 0) {
		$ret = (bool)get_post_meta($menu_item_id, '_mprm_menu_item_tax_exclusive', true);
		
		return apply_filters('menu_item_is_tax_exclusive', $ret, $menu_item_id);
	}
	
	/**
	 * @return mixed
	 */
	public function prices_show_tax_on_checkout() {
		$ret = ($this->get('settings')->get_option('checkout_include_tax', false) == 'yes' && $this->use_taxes());
		
		return apply_filters('mprm_taxes_on_prices_on_checkout', $ret);
	}
	
	/**
	 * Formatted tax rate
	 *
	 * @param bool $country
	 * @param bool $state
	 *
	 * @return mixed
	 */
	public function get_formatted_tax_rate($country = false, $state = false) {
		$rate = $this->get_tax_rate($country, $state);
		$rate = round($rate * 100, 4);
		$formatted = $rate .= '%';
		
		return apply_filters('mprm_formatted_tax_rate', $formatted, $rate, $country, $state);
	}
	
	/**
	 * Cart need tax address
	 *
	 * @return bool
	 */
	public function cart_needs_tax_address_fields() {
		if (!$this->is_cart_taxed()) {
			return false;
		}
	}
	
	/**
	 * Cart taxed
	 * @return bool
	 */
	public function is_cart_taxed() {
		return $this->use_taxes();
	}
}