<?php
namespace mp_restaurant_menu\classes;

/**
 * View class
 */
class View {
	protected static $instance;
	public $template;
	protected $template_path;
	protected $templates_path;
	protected $prefix = 'mprm';
	private $data;
	
	/**
	 * View constructor.
	 */
	public function __construct() {
		$this->template_path = MP_RM_TEMPLATE_PATH;
		$this->templates_path = MP_RM_TEMPLATES_PATH;
	}
	
	/**
	 * @return View
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Render template
	 *
	 * @param string $template
	 * @param array $data
	 */
	function render_template($template = null, $data = null) {
		$this->template = $template;
		if (is_array($data)) {
			extract($data);
		}
		$this->data = $data;
		include_once($this->templates_path . 'index.php');
	}
	
	/**
	 * Render html
	 *
	 * @param string $template
	 * @param array $data
	 * @param bool $output : true - echo , false - return
	 *
	 * @return string
	 */
	
	public function render_html($template, $data = null, $output = true) {
		$includeFile = $this->templates_path . $template . '.php';
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
	
	/**
	 * Get template part theme/plugin
	 *
	 * @param string $name
	 * @param string $slug
	 *
	 * @return void
	 */
	public function get_template_part($slug, $name = '') {
		$template = '';
		
		// Look in your-theme/slug-name.php and your-theme/mp-restaurant-menu/slug-name.php
		if ($name) {
			$template = locate_template(array("{$slug}-{$name}.php", $this->template_path . "{$slug}-{$name}.php"));
		}
		
		// Get default slug-name.php
		if (!$template && $name && file_exists($this->templates_path . "{$slug}-{$name}.php")) {
			$template = $this->templates_path . "{$slug}-{$name}.php";
		}
		
		// If template file doesn't exist, look in your-theme/slug.php and your-theme/mp-restaurant-menu/slug.php
		if (!$template) {
			$template = locate_template(array("{$slug}.php", $this->template_path . "{$slug}.php"));
		}
		
		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters($this->prefix . '_get_template_part', $template, $slug, $name);
		
		if ($template) {
			load_template($template, false);
		}
	}
	
	/**
	 * @param $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return mixed/void
	 */
	public function get_template_html($template_name, $args = array(), $template_path = '', $default_path = '') {
		ob_start();
		$this->get_template($template_name, $args, $template_path, $default_path);
		
		return ob_get_clean();
	}
	
	/**
	 * Get template
	 *
	 * @param $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 */
	public function get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
		$template_name = $template_name . '.php';
		
		if (!empty($args) && is_array($args)) {
			extract($args);
		}
		
		$located = $this->locate_template($template_name, $template_path, $default_path);
		
		if (!file_exists($located)) {
			_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '2.1');
			
			return;
		}
		
		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters($this->prefix . '_get_template', $located, $template_name, $args, $template_path, $default_path);
		
		do_action($this->prefix . '_before_template_part', $template_name, $template_path, $located, $args);
		
		include($located);
		
		do_action($this->prefix . '_after_template_part', $template_name, $template_path, $located, $args);
	}
	
	/**
	 * Locate template
	 *
	 * @param $template_name
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return mixed
	 */
	function locate_template($template_name, $template_path = '', $default_path = '') {
		if (!$template_path) {
			$template_path = $this->template_path;
		}
		
		if (!$default_path) {
			$default_path = $this->templates_path;
		}
		
		// Look within passed path within the theme - this is priority.
		$template_args = array(trailingslashit($template_path) . $template_name, $template_name);
		
		$template = locate_template($template_args);
		
		// Get default template/
		if (!$template) {
			$template = $default_path . $template_name;
		}
		
		// Return what we found.
		return apply_filters($this->prefix . '_locate_template', $template, $template_name, $template_path);
	}
	
	/**
	 * @param $template
	 *
	 * @return string
	 */
	public function template_loader($template) {
		global $post, $taxonomy;
		
		
		$file = '';
		$find = array();
		
		if (is_embed()) {
			return $template;
		}
		
		if (is_single() && in_array($post->post_type, Core::get_instance()->get_post_types())) {
			
			$file = "single-{$post->post_type}.php";
			$find[] = $file;
			$find[] = $this->template_path . $file;
			
		} elseif (in_array($taxonomy, Core::get_instance()->get_taxonomy_types())) {
			$theme_template = mprm_get_theme_template();
			$display_taxonomy = mprm_get_option('display_taxonomy', 'default');
			
			$term = get_queried_object();
			
			if (!empty($theme_template) && ($display_taxonomy !== 'default')) {
				
				$file = $theme_template;
				
				if (!is_single() || !post_password_required()) {
					add_action('loop_start', array(Media::get_instance(), 'setup_pseudo_taxonomy_template'));
				}
				
			} else {
				
				if (is_tax(Core::get_instance()->get_tax_name('menu_category')) || is_tax(Core::get_instance()->get_tax_name('menu_tag'))) {
					$file = "taxonomy-{$term->taxonomy}.php";
				} else {
					$file = 'archive.php';
				}
			}
			
			$find[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] = $this->template_path . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
			$find[] = 'taxonomy-' . $term->taxonomy . '.php';
			$find[] = $this->template_path . 'taxonomy-' . $term->taxonomy . '.php';
			$find[] = $file;
			$find[] = $this->template_path . $file;
			
		} elseif (is_post_type_archive(Core::get_instance()->get_post_types())) {
			$file = "archive-{$post->post_type}.php";
			$find[] = $file;
			$find[] = $this->template_path . $file;
			$find[] = 'archive.php';
		}
		
		if ($file) {
			$find_template = locate_template(array_unique($find));
			if (!empty($find_template)) {
				$template = $find_template;
			}
		}
		
		return $template;
	}
}