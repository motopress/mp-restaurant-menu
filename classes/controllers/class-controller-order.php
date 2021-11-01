<?php
namespace mp_restaurant_menu\classes\controllers;

use mp_restaurant_menu\classes\Controller;

/**
 * Class Controller_order
 * @package mp_restaurant_menu\classes\controllers
 */
class Controller_order extends Controller {
	protected static $instance;
	private $date;

	/**
	 * @return Controller_order
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function action_add_comment() {

		if ( current_user_can('manage_restaurant_menu') ) {

			$note_id = $this->get('payments')->insert_payment_note(
				sanitize_text_field( $_REQUEST['order_id'] ),
				sanitize_textarea_field( $_REQUEST['noteText'] )
			);

			$this->date['success'] = (is_numeric($note_id)) ? true : false;
			if ($this->date['success']) {
				$this->date['data']['html'] = $this->get('payments')->get_payment_note_html($note_id, sanitize_text_field( $_REQUEST['order_id'] ));
			}
			$this->send_json($this->date);
		}
	}

	public function action_remove_comment() {

		if ( current_user_can('manage_restaurant_menu') ) {

			$this->date['success'] = $this->get('payments')->delete_payment_note(
				sanitize_text_field( $_REQUEST['note_id'] ),
				sanitize_text_field( $_REQUEST['order_id'] )
			);
			$this->send_json($this->date);
		}
	}

}
