<?php
namespace mp_restaurant_menu\classes;
//model
use mp_restaurant_menu\classes\Model;
/**
 * Singleton factory
 */
class State_Factory {
	protected static $instance;
	protected $namespace;
	public function __construct($namespace = 'mp_restaurant_menu') {
		$this->namespace = $namespace;
	}
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Get register instance object
	 *
	 * @param type $value
	 *
	 * @return type
	 */
	public function get_model($value = null) {
		$model = false;
		if ('model' == $value) {
			$model = Model::get_instance();
		} else {
			$class = "{$this->namespace}\classes\models\\" . ucfirst($value);
			if (class_exists($class)) {
				$model = $class::get_instance();
			}
		}
		return $model;
	}
	/**
	 * Get controller instance object
	 *
	 * @param type $value
	 *
	 * @return type
	 */
	public function get_controller($value = null) {
		$controller = false;
		$class = "{$this->namespace}\classes\controllers\Controller_" . ucfirst($value);
		if (class_exists($class)) {
			$controller = $class::get_instance();
		}
		return $controller;
	}
	/**
	 *  Get Preprocessor instance object
	 *
	 * @param type $value
	 *
	 * @return type
	 */
	public function get_preprocessor($value = null) {
		$preprocessor = false;
		$class = "{$this->namespace}\classes\preprocessors\Preprocessor_" . ucfirst($value);
		if (class_exists($class)) {
			$preprocessor = $class::get_instance();
		}
		return $preprocessor;
	}
}
