<?php

namespace mp_restaurant_menu\classes;

/**
 * View class
 */
class View {

	private $data;
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Render template
	 *
	 * @param type $template
	 * @param type $data
	 */
	function render_template($template = null, $data = null) {
		$this->template = $template;
		if (is_array($data)) {
			extract($data);
		}
		$this->data = $data;
		include_once MP_RM_TEMPLATES_PATH . 'index.php';
	}

	/**
	 * Render html
	 *
	 * @param string $template
	 * @param array $data
	 * @param bool $output : true - echo , false - return
	 *
	 * @return type
	 */
	public function render_html($template, $data = null, $output = true) {
		$includeFile = MP_RM_TEMPLATES_PATH . $template . '.php';
		ob_start();
		if (is_array($data)) {
			extract($data);
		}
		$this->data = $data;
		include($includeFile);
		$out = ob_get_clean();
		if ($output) {
			echo $out;
		} else {
			return $out;
		}
	}
}
