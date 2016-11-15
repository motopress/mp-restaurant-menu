<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;
use mp_restaurant_menu\classes\View as View;

/**
 * Class Errors
 * @package mp_restaurant_menu\classes\models
 */
class Errors extends Model {
	protected static $instance;

	/**
	 * @return Errors
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Print errors
	 */
	public function print_errors() {
		$errors = $this->get_errors();
		if ($errors) {
			$classes = apply_filters('mprm_error_class', array(
				'mprm-errors', 'mprm-notice', 'mprm-notice-error'
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
	 * @return mixed array if errors are present, false if none found
	 */
	public function get_errors() {
		return $this->get('session')->get_session_by_key('mprm_errors');
	}

	/**
	 * Clears all stored errors.
	 *
	 * @return void
	 */
	public function clear_errors() {
		$this->get('session')->set('mprm_errors', null);
	}

	/**
	 * Get error html
	 * @return bool|mixed
	 */
	public function get_error_html() {
		$errors = $this->get_errors();
		if ($errors) {

			$classes = apply_filters('mprm_error_class', array(
				'mprm-errors', 'mprm-notice', 'mprm-notice-error'
			));

			$error_html = View::get_instance()->get_template_html('shop/errors', array('errors' => $errors, 'classes' => $classes));
			$this->clear_errors();
			return $error_html;
		}
		return false;
	}

	/**
	 * Set Error
	 *
	 * Stores an error in a session var.
	 *
	 * @param int $error_id ID of the error being set
	 * @param string $error_message Message to store with the error
	 *
	 * @return void
	 */
	public function set_error($error_id, $error_message) {
		$errors = $this->get_errors();
		if (!$errors) {
			$errors = array();
		}
		$errors[$error_id] = $error_message;
		$this->get('session')->set('mprm_errors', $errors);
	}

	/**
	 * Removes (unset) a stored error
	 *
	 * @param int $error_id ID of the error being set
	 *
	 * @return void/string
	 */
	public function unset_error($error_id) {
		$errors = $this->get_errors();
		if ($errors) {
			unset($errors[$error_id]);
			$this->get('session')->set('mprm_errors', $errors);
		}
	}
}