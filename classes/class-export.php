<?php

namespace mp_restaurant_menu\classes;

/**
 * Export class
 */
class Export extends Core {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function export_wp($args = array()) {
		$defaults = array('content' => 'all', 'author' => false, 'category' => false,
			'start_date' => false, 'end_date' => false, 'status' => false,
		);
		$args = wp_parse_args($args, $defaults);
	}
}
