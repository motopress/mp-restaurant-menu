<?php namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Taxes extends Model {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function use_taxes() {
		$ret = $this->get('settings')->get_option('enable_taxes', false);
		return (bool)apply_filters('mprm_use_taxes', $ret);
	}

	public function display_tax_rate() {
		$ret = $this->use_taxes() && $this->get('settings')->get_option('display_tax_rate', false);

		return apply_filters('mprm_display_tax_rate', $ret);
	}

	public function prices_include_tax() {
		$ret = ($this->get('settings')->get_option('prices_include_tax', false) == 'yes' && $this->use_taxes());

		return apply_filters('mprm_prices_include_tax', $ret);
	}

	public function get_tax_rates() {
		$rates = get_option('mprm_tax_rates', array());
		return apply_filters('mprm_get_tax_rates', $rates);
	}

	public function get_tax_rate($country = false, $state = false) {
		$rate = (float)$this->get('settings')->get_option('tax_rate', 0);

		$user_address = $this->get('customer')->get_customer_address();

		if (empty($country)) {
			if (!empty($_POST['billing_country'])) {
				$country = $_POST['billing_country'];
			} elseif (is_user_logged_in() && !empty($user_address)) {
				$country = $user_address['country'];
			}
			$country = !empty($country) ? $country : $this->get('settings')->get_shop_country();
		}

		if (empty($state)) {
			if (!empty($_POST['state'])) {
				$state = $_POST['state'];
			} elseif (is_user_logged_in() && !empty($user_address)) {
				$state = $user_address['state'];
			}
			$state = !empty($state) ? $state : $this->get('settings')->get_shop_state();
		}

		if (!empty($country)) {
			$tax_rates = $this->get_tax_rates();

			if (!empty($tax_rates)) {

				// Locate the tax rate for this country / state, if it exists
				foreach ($tax_rates as $key => $tax_rate) {

					if ($country != $tax_rate['country'])
						continue;

					if (!empty($tax_rate['global'])) {
						if (!empty($tax_rate['rate'])) {
							$rate = number_format($tax_rate['rate'], 4);
						}
					} else {

						if (empty($tax_rate['state']) || strtolower($state) != strtolower($tax_rate['state']))
							continue;

						$state_rate = $tax_rate['rate'];
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
		return apply_filters('edd_tax_rate', $rate, $country, $state);
	}

}