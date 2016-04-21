<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Errors extends Model {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function print_errors() {
		$errors = $this->get_errors();
		if ($errors) {
			$classes = apply_filters('mprm_error_class', array(
				'mprm-errors', 'mprm-alert', 'mprm-alert-error'
			));
			echo '<div class="' . implode(' ', $classes) . '">';
			// Loop error codes and display errors
			foreach ($errors as $error_id => $error) {
				echo '<p class="mprm_error" id="mprm-error_' . $error_id . '"><strong>' . __('Error', 'mp-restaurant-menu') . '</strong>: ' . $error . '</p>';
			}
			echo '</div>';
			$this->clear_errors();
		}
	}

	/**
	 * Get Errors
	 *
	 * Retrieves all error messages stored during the checkout process.
	 * If errors exist, they are returned.
	 *
	 * @since 1.0
	 * @uses EDD_Session::get()
	 * @return mixed array if errors are present, false if none found
	 */
	function get_errors() {
		return $this->get('session')->get_session_by_key('mprm_errors');
	}

	/**
	 * Set Error
	 *
	 * Stores an error in a session var.
	 *
	 * @since 1.0
	 * @uses EDD_Session::get()
	 *
	 * @param int $error_id ID of the error being set
	 * @param string $error_message Message to store with the error
	 *
	 * @return void
	 */
	function set_error($error_id, $error_message) {
		$errors = $this->get_errors();
		if (!$errors) {
			$errors = array();
		}
		$errors[$error_id] = $error_message;
		$this->get('session')->set('mprm_errors', $errors);
	}

	/**
	 * Clears all stored errors.
	 *
	 * @since 1.0
	 * @uses EDD_Session::set()
	 * @return void
	 */
	function clear_errors() {
		$this->get('session')->set('mprm_errors', null);
	}

	/**
	 * Removes (unsets) a stored error
	 *
	 * @since 1.3.4
	 * @uses EDD_Session::set()
	 *
	 * @param int $error_id ID of the error being set
	 *
	 * @return string
	 */
	function unset_error($error_id) {
		$errors = $this->get_errors();
		if ($errors) {
			unset($errors[$error_id]);
			$this->get('session')->set('mprm_errors', $errors);
		}
	}
}