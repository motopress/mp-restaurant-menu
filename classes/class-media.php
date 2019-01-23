<?php
namespace mp_restaurant_menu\classes;

use mp_restaurant_menu\classes\models\Settings;
use mp_restaurant_menu\classes\models\Settings_emails;
use mp_restaurant_menu\classes\modules\Menu;
use mp_restaurant_menu\classes\modules\Taxonomy;

/**
 * Class Media
 * @package mp_restaurant_menu\classes
 */
class Media extends Core {
	
	protected static $instance;
	
	/**
	 * Get instance
	 *
	 * @return Media
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Cut string
	 *
	 * @param string $args
	 *
	 * @return int|mixed|string
	 */
	public static function cut_str($args = '') {
		$default = array(
			'maxchar' => 350,
			'text' => '',
			'save_format' => false,
			'more_text' => __('Read more', 'mp-restaurant-menu') . '...',
			'echo' => false,
		);
		if (is_array($args)) {
			$rgs = $args;
		} else {
			parse_str($args, $rgs);
		}
		$args = array_merge($default, $rgs);
		$args[ 'maxchar' ] += 0;
		// cutting
		if (mb_strlen($args[ 'text' ]) > $args[ 'maxchar' ] && $args[ 'maxchar' ] != 0) {
			$args[ 'text' ] = mb_substr($args[ 'text' ], 0, $args[ 'maxchar' ]);
			$args[ 'text' ] = $args[ 'text' ] . '...';
		}
		// save br ad paragraph
		if ($args[ 'save_format' ]) {
			$args[ 'text' ] = str_replace("\r", '', $args[ 'text' ]);
			$args[ 'text' ] = preg_replace("~\n+~", "</p><p>", $args[ 'text' ]);
			$args[ 'text' ] = "<p>" . str_replace("\n", "<br />", trim($args[ 'text' ])) . "</p>";
		}
		if ($args[ 'echo' ]) {
			return print $args[ 'text' ];
		}
		
		return $args[ 'text' ];
	}
	
	/**
	 * Loads the contents into the page template
	 *
	 * @return string Page content
	 */
	public function load_content_into_page_template($contents = '') {
		// only run once!!!
		remove_filter('the_content', array($this, 'load_content_into_page_template'));
		$this->get('query')->restoreQuery();
		
		ob_start();
		
		View::get_instance()->get_template('common/page-parts/single-taxonomy');
		
		$contents = ob_get_clean();
		
		// make sure the loop ends after our template is included
		if (!is_404()) {
			$this->get('query')->endQuery();
		}
		
		return $contents;
	}
	
	/**
	 * Decide if we need to spoof the query.
	 */
	public function maybeSpoofQuery() {
		// hijack this method right up front if it's a password protected post and the password isn't entered
		if (is_single() && post_password_required() || is_feed()) {
			return;
		}
		
		global $wp_query;
		
		if ($wp_query->is_main_query() && $this->get('query')->is_restaurant_menu_query() && mprm_get_option('theme_templates', '') != '') {
			
			// we need to ensure that we always enter the loop, whether or not there are any events in the actual query
			
			$spoofed_post = $this->spoofed_post();
			
			$GLOBALS[ 'post' ] = $spoofed_post;
			$wp_query->posts[] = $spoofed_post;
			$wp_query->post_count = count($wp_query->posts);
			
			$wp_query->spoofed = true;
			$wp_query->rewind_posts();
			
		}
	}
	
	/**
	 * Spoof the query so that we can operate independently of what has been queried.
	 *
	 * @return object
	 */
	public function spoofed_post() {
		$spoofed_post = array(
			'ID' => 0,
			'post_status' => 'draft',
			'post_author' => 0,
			'post_parent' => 0,
			'post_type' => 'page',
			'post_date' => 0,
			'post_date_gmt' => 0,
			'post_modified' => 0,
			'post_modified_gmt' => 0,
			'post_content' => '',
			'post_title' => '',
			'post_excerpt' => '',
			'post_content_filtered' => '',
			'post_mime_type' => '',
			'post_password' => '',
			'post_name' => '',
			'guid' => '',
			'menu_order' => 0,
			'pinged' => '',
			'to_ping' => '',
			'ping_status' => '',
			'comment_status' => 'closed',
			'comment_count' => 0,
			'is_404' => false,
			'is_page' => false,
			'is_single' => false,
			'is_archive' => false,
			'is_tax' => false,
		);
		
		return ( object )$spoofed_post;
	}
	
	/**
	 * Registered page in admin wp
	 */
	public function admin_menu() {
		global $submenu;
		// get taxonomy names
		$category_name = $this->get_tax_name('menu_category');
		$tag_name = $this->get_tax_name('menu_tag');
		$ingredient_name = $this->get_tax_name('ingredient');
		// get post types
		$menu_item = $this->get_post_type('menu_item');
		$order = $this->get_post_type('order');
		$menu_slug = "edit.php?post_type={$menu_item}";
		
		// Restaurant menu
		Menu::add_menu_page(array(
			'title' => _x('Restaurant Menu', 'Menu label', 'mp-restaurant-menu'),
			'menu_slug' => $menu_slug,
			'icon_url' => MP_RM_MEDIA_URL . '/img/icon.png',
			'capability' => 'manage_restaurant_menu',
			'position' => '59.52'
		));
		// Menu items
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Menu Items', 'mp-restaurant-menu'),
			'menu_slug' => "edit.php?post_type={$menu_item}",
			'capability' => 'manage_restaurant_menu',
		));
		// Add new
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Add New', 'mp-restaurant-menu'),
			'menu_slug' => "post-new.php?post_type={$menu_item}",
			'capability' => 'manage_restaurant_menu',
		));
		// Categories
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Categories', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$category_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_restaurant_menu',
		));
		// Tags
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Tags', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$tag_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_restaurant_menu',
		));
		// Ingredients
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Ingredients', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$ingredient_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_restaurant_menu',
		));
		// Orders
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Orders', 'mp-restaurant-menu'),
			'menu_slug' => "edit.php?post_type=$order",
			'capability' => 'manage_restaurant_menu',
		));
		// Customers
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Customers', 'mp-restaurant-menu'),
			'menu_slug' => "mprm-customers",
			'function' => array($this->get_controller('customer'), 'action_content'),
			'capability' => 'manage_restaurant_menu',
		));
		// Settings
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Settings', 'mp-restaurant-menu'),
			'menu_slug' => "mprm-settings",
			'function' => array($this->get_controller('settings'), 'action_content'),
			'capability' => 'manage_restaurant_settings',
		));
		// Import/Export
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Import / Export', 'mp-restaurant-menu'),
			'menu_slug' => "mprm-import",
			'function' => array($this->get_controller('import'), 'action_content'),
			'capability' => 'import',
		));
		
		
		$this->register_settings();
		
		$pend_count = count(get_posts(array('posts_per_page' => -1, 'post_status' => 'mprm-pending', 'post_type' => 'mprm_order', 'fields' => 'ids')));
		
		foreach ($submenu as $key => $value) {
			if (isset($submenu[ $key ][ 5 ])) {
				if ($submenu[ $key ][ 5 ][ 2 ] == 'edit.php?post_type=mprm_order') {
					$submenu[ $key ][ 5 ][ 0 ] .= " <span class='update-plugins count-$pend_count'><span class='plugin-count'>" . $pend_count . '</span></span>';
					
					return;
				}
			}
		}
	}
	
	/**
	 * Register settings
	 */
	public function register_settings() {
		if (false == get_option('mprm_settings')) {
			add_option('mprm_settings');
		}
		foreach ($this->get_registered_settings() as $tab => $sections) {
			foreach ($sections as $section => $settings) {
				// Check for backwards compatibility
				$section_tabs = $this->get_settings_tab_sections($tab);
				if (!is_array($section_tabs) || !array_key_exists($section, $section_tabs)) {
					$section = 'main';
					$settings = $sections;
				}
				add_settings_section('mprm_settings_' . $tab . '_' . $section, __return_null(), '__return_false', 'mprm_settings_' . $tab . '_' . $section);
				
				foreach ($settings as $option) {
					// For backwards compatibility
					if (empty($option[ 'id' ])) {
						continue;
					}
					$name = isset($option[ 'name' ]) ? $option[ 'name' ] : '';
					add_settings_field(
						'mprm_settings[' . $option[ 'id' ] . ']',
						$name,
						method_exists(Settings::get_instance(), $option[ 'type' ] . '_callback') ? array(Settings::get_instance(), $option[ 'type' ] . '_callback') : array(Settings::get_instance(), 'missing_callback'),
						'mprm_settings_' . $tab . '_' . $section,
						'mprm_settings_' . $tab . '_' . $section,
						array(
							'section' => $section,
							'id' => isset($option[ 'id' ]) ? $option[ 'id' ] : null,
							'desc' => !empty($option[ 'desc' ]) ? $option[ 'desc' ] : '',
							'name' => isset($option[ 'name' ]) ? $option[ 'name' ] : null,
							'size' => isset($option[ 'size' ]) ? $option[ 'size' ] : null,
							'options' => isset($option[ 'options' ]) ? $option[ 'options' ] : '',
							'std' => isset($option[ 'std' ]) ? $option[ 'std' ] : '',
							'min' => isset($option[ 'min' ]) ? $option[ 'min' ] : null,
							'max' => isset($option[ 'max' ]) ? $option[ 'max' ] : null,
							'step' => isset($option[ 'step' ]) ? $option[ 'step' ] : null,
							'chosen' => isset($option[ 'chosen' ]) ? $option[ 'chosen' ] : null,
							'multiple' => isset($option[ 'multiple' ]) ? $option[ 'multiple' ] : null,
							'placeholder' => isset($option[ 'placeholder' ]) ? $option[ 'placeholder' ] : null,
							'allow_blank' => isset($option[ 'allow_blank' ]) ? $option[ 'allow_blank' ] : true,
							'readonly' => isset($option[ 'readonly' ]) ? $option[ 'readonly' ] : false,
							'faux' => isset($option[ 'faux' ]) ? $option[ 'faux' ] : false,
						)
					);
				}
				
			}
		}
		// Creates our settings in the options table
		register_setting('mprm_settings', 'mprm_settings', array(Settings::get_instance(), 'mprm_settings_sanitize'));
	}
	
	/**
	 * Registered settings
	 *
	 * @return mixed
	 */
	public function get_registered_settings() {
		
		$mprm_settings = array(
			/** General Settings */
			'general' => apply_filters('mprm_settings_general',
				array(
					'main' => array(
						'enable_ecommerce' => array(
							'id' => 'enable_ecommerce',
							'name' => __('Enable eCommerce', 'mp-restaurant-menu'),
							'type' => 'checkbox',
							'desc' => __('Sell food and beverages online', 'mp-restaurant-menu'),
						),
						'purchase_page' => array(
							'id' => 'purchase_page',
							'name' => __('Checkout Page', 'mp-restaurant-menu'),
							'desc' => __('The page where buyers will complete their purchases. Use <i>[mprm_checkout]</i> shortcode on this page.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'mp-restaurant-menu'),
						),
						'success_page' => array(
							'id' => 'success_page',
							'name' => __('Success Transaction Page', 'mp-restaurant-menu'),
							'desc' => __('The page buyers are sent to after completing their purchases. Use <i>[mprm_success]</i> shortcode on this page.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'mp-restaurant-menu'),
						),
						'failure_page' => array(
							'id' => 'failure_page',
							'name' => __('Failed Transaction Page', 'mp-restaurant-menu'),
							'desc' => __('The page buyers are sent to if their transaction is cancelled or fails.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'mp-restaurant-menu'),
						),
						'purchase_history_page' => array(
							'id' => 'purchase_history_page',
							'name' => __('Purchase History Page', 'mp-restaurant-menu'),
							'desc' => __('This page shows a complete purchase history for the current user. Use <i>[mprm_purchase_history]</i> shortcode on this page.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => $this->get_pages(),
							'chosen' => true,
							'placeholder' => __('Select a page', 'mp-restaurant-menu'),
						),
						'ecommerce_settings' => array(
							'id' => 'ecommerce_settings',
							'name' => '<h3>' . __('Display', 'mp-restaurant-menu') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),
						'template_mode' => array(
							'id' => 'template_mode',
							'name' => __('Template Mode', 'mp-restaurant-menu'),
							'options' => apply_filters('mprm_available_theme_mode',
								array('theme' => __('Theme Mode', 'mp-restaurant-menu'),
									'plugin' => __('Developer Mode', 'mp-restaurant-menu')
								)
							),
							'desc' => '<br>' . __('Choose Theme Mode to display the content with the styles of your theme.', 'mp-restaurant-menu') . "<br>" . __('Choose Developer Mode to control appearance of the content with custom page templates, actions and filters. This option can\'t be changed if your theme is initially integrated with the plugin.', 'mp-restaurant-menu'),
							'readonly' => current_theme_supports('mp-restaurant-menu') ? true : false,
							'type' => 'select',
						
						),
						'category_view' => array(
							'id' => 'category_view',
							'name' => __('Category Layout', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => array(
								'grid' => __('Grid', 'mp-restaurant-menu'),
								'list' => __('List', 'mp-restaurant-menu')
							),
							'chosen' => false,
							'desc' => __('Choose the way to display your menu items within category.', 'mp-restaurant-menu'),
						),
					),
					'section_currency' => array(
						'currency' => array(
							'id' => 'currency',
							'name' => __('Currency', 'mp-restaurant-menu'),
							'desc' => __('Choose your currency. <i>Note that some payment gateways have currency restrictions.</i>', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => Settings::get_instance()->get_currencies_with_symbols(),
							'chosen' => true,
							'std' => 'USD'
						),
						'currency_position' => array(
							'id' => 'currency_position',
							'name' => __('Currency Position', 'mp-restaurant-menu'),
							'desc' => __('Choose the location of the currency sign.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => array(
								'before' => __('Before - $10', 'mp-restaurant-menu'),
								'after' => __('After - 10$', 'mp-restaurant-menu'),
							),
						),
						'thousands_separator' => array(
							'id' => 'thousands_separator',
							'name' => __('Thousand Separator', 'mp-restaurant-menu'),
							'desc' => __('Thousand separator of displayed prices', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'small',
							'std' => ',',
						),
						'decimal_separator' => array(
							'id' => 'decimal_separator',
							'name' => __('Decimal Separator', 'mp-restaurant-menu'),
							'desc' => __('Decimal separator of displayed prices', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'small',
							'std' => '.',
						),
						'number_decimals' => array(
							'id' => 'number_decimals',
							'name' => __('Number of Decimals', 'mp-restaurant-menu'),
							'desc' => __('Number of decimal points shown in displayed prices', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'small',
							'std' => '2',
						)
					)
				)
			),
			'display' => apply_filters('mprm_settings_display',
				array(
					'main' => array(
						'display_taxonomy' => array(
							'id' => 'display_taxonomy',
							'name' => __('Display categories and tags', 'mp-restaurant-menu'),
							'options' => array(
								'default' => __('Default', 'mp-restaurant-menu'),
								'grid' => __('Grid', 'mp-restaurant-menu'),
								'list' => __('List', 'mp-restaurant-menu'),
								'simple-list' => __('Simple list', 'mp-restaurant-menu')
							),
							'desc' => '<br>' . __('Choose a view template to control the appearance of your restaurant menu content.', 'mp-restaurant-menu'),
							'readonly' => false,
							'type' => 'select',
							'std' => 'default'
						),
						'theme_templates' => array(
							'id' => 'theme_templates',
							'name' => __('Page template', 'mp-restaurant-menu'),
							'options' => $this->get_theme_template(),
							'desc' => '<br>' . __('Choose a page template to control the appearance of your restaurant menu content.', 'mp-restaurant-menu'),
							'readonly' => false,
							'type' => 'select',
						)
					),
					'taxonomy_grid' => array(
						'col' => array(
							'id' => 'col',
							'name' => __('Columns', 'mp-restaurant-menu'),
							'options' => array(
								'1' => __('1 column', 'mp-restaurant-menu'),
								'2' => __('2 columns', 'mp-restaurant-menu'),
								'3' => __('3 columns', 'mp-restaurant-menu'),
								'4' => __('4 columns', 'mp-restaurant-menu'),
								'6' => __('6 columns', 'mp-restaurant-menu')
							),
							'std' => 3,
							'desc' => '',
							'readonly' => false,
							'type' => 'section_select',
						),
						'categ_name' => array(
							'id' => 'categ_name',
							'name' => __('Show category name', 'mp-restaurant-menu'),
							'options' => array(
								'only_text' => __('Only text', 'mp-restaurant-menu'),
								'with_img' => __('Title with image', 'mp-restaurant-menu'),
								'none' => __('Don`t show', 'mp-restaurant-menu')
							),
							'std' => 'only_text',
							'desc' => '',
							'readonly' => false,
							'type' => 'section_select',
						),
						'show_attributes' => array(
							'id' => 'show_attributes',
							'name' => __('Show attributes', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'feat_img' => array(
							'id' => 'feat_img',
							'name' => __('Show featured image', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'excerpt' => array(
							'id' => 'excerpt',
							'name' => __('Show excerpt', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'price' => array(
							'id' => 'price',
							'name' => __('Show price', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'tags' => array(
							'id' => 'tags',
							'name' => __('Show tags', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'ingredients' => array(
							'id' => 'ingredients',
							'name' => __('Show ingredients', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'buy' => array(
							'id' => 'buy',
							'name' => __('Show buy button', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'link_item' => array(
							'id' => 'link_item',
							'name' => __('Link item', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'grid_desc_length' => array(
							'id' => 'grid_desc_length',
							'name' => __('Description length', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_text'
						)
					),
					'taxonomy_list' => array(
						'col' => array(
							'id' => 'col',
							'name' => __('Columns', 'mp-restaurant-menu'),
							'options' => array(
								'1' => __('1 column', 'mp-restaurant-menu'),
								'2' => __('2 columns', 'mp-restaurant-menu'),
								'3' => __('3 columns', 'mp-restaurant-menu'),
								'4' => __('4 columns', 'mp-restaurant-menu'),
								'6' => __('6 columns', 'mp-restaurant-menu')
							),
							'std' => 2,
							'desc' => '',
							'readonly' => false,
							'type' => 'section_select',
						),
						'categ_name' => array(
							'id' => 'categ_name',
							'name' => __('Show category name', 'mp-restaurant-menu'),
							'options' => array(
								'only_text' => __('Only text', 'mp-restaurant-menu'),
								'with_img' => __('Title with image', 'mp-restaurant-menu'),
								'none' => __('Don`t show', 'mp-restaurant-menu')
							),
							'std' => 'only_text',
							'desc' => '',
							'readonly' => false,
							'type' => 'section_select',
						),
						'show_attributes' => array(
							'id' => 'show_attributes',
							'name' => __('Show attributes', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'feat_img' => array(
							'id' => 'feat_img',
							'name' => __('Show featured image', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'excerpt' => array(
							'id' => 'excerpt',
							'name' => __('Show excerpt', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'price' => array(
							'id' => 'price',
							'name' => __('Show price', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'tags' => array(
							'id' => 'tags',
							'name' => __('Show tags', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'ingredients' => array(
							'id' => 'ingredients',
							'name' => __('Show ingredients', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'buy' => array(
							'id' => 'buy',
							'name' => __('Show buy button', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'link_item' => array(
							'id' => 'link_item',
							'name' => __('Link item', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'desc_length' => array(
							'id' => 'desc_length',
							'name' => __('Description length', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_text'
						)
					),
					'taxonomy_simple_list' => array(
						'col' => array(
							'id' => 'col',
							'name' => __('Columns', 'mp-restaurant-menu'),
							'options' => array(
								'1' => __('1 column', 'mp-restaurant-menu'),
								'2' => __('2 columns', 'mp-restaurant-menu'),
								'3' => __('3 columns', 'mp-restaurant-menu'),
								'4' => __('4 columns', 'mp-restaurant-menu'),
								'6' => __('6 columns', 'mp-restaurant-menu')
							),
							'desc' => '',
							'readonly' => false,
							'type' => 'section_select',
						),
						'price_pos' => array(
							'id' => 'price_pos',
							'name' => __('Price position', 'mp-restaurant-menu'),
							'options' => array(
								'points' => __('Dotted line and price on the right', 'mp-restaurant-menu'),
								'right' => __('Price on the right', 'mp-restaurant-menu'),
								'after_title' => __('Price next to the title', 'mp-restaurant-menu')
							),
							'std' => 'points',
							'desc' => '',
							'readonly' => false,
							'type' => 'section_select',
						),
						'categ_name' => array(
							'id' => 'categ_name',
							'name' => __('Show category name', 'mp-restaurant-menu'),
							'options' => array(
								'only_text' => __('Only text', 'mp-restaurant-menu'),
								'with_img' => __('Title with image', 'mp-restaurant-menu'),
								'none' => __('Don`t show', 'mp-restaurant-menu')
							),
							'desc' => '',
							'readonly' => false,
							'type' => 'section_select',
						),
						'show_attributes' => array(
							'id' => 'show_attributes',
							'name' => __('Show attributes', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'excerpt' => array(
							'id' => 'excerpt',
							'name' => __('Show excerpt', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'price' => array(
							'id' => 'price',
							'name' => __('Show price', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'tags' => array(
							'id' => 'tags',
							'name' => __('Show tags', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'ingredients' => array(
							'id' => 'ingredients',
							'name' => __('Show ingredients', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_checkbox'
						),
						'link_item' => array(
							'id' => 'link_item',
							'name' => __('Link item', 'mp-restaurant-menu'),
							'desc' => '',
							'std' => true,
							'type' => 'section_checkbox'
						),
						'desc_length' => array(
							'id' => 'desc_length',
							'name' => __('Description length', 'mp-restaurant-menu'),
							'desc' => '',
							'type' => 'section_text'
						)
					)
				)
			),
			/** Payment Gateways Settings */
			'gateways' => apply_filters('mprm_settings_gateways',
				array(
					'main' => array(
						'gateways' => array(
							'id' => 'gateways',
							'name' => __('Active Payment Gateways', 'mp-restaurant-menu'),
							'desc' => __('Choose the payment gateways you want to enable.', 'mp-restaurant-menu'),
							'type' => 'gateways',
							'options' => $this->get('gateways')->get_payment_gateways(),
						),
						'default_gateway' => array(
							'id' => 'default_gateway',
							'name' => __('Default Gateway', 'mp-restaurant-menu'),
							'desc' => __('This gateway will be loaded automatically on the checkout page.', 'mp-restaurant-menu'),
							'type' => 'gateway_select',
							'options' => $this->get('gateways')->get_payment_gateways(),
						),
						'test_mode' => array(
							'id' => 'test_mode',
							'name' => __('Test Mode', 'mp-restaurant-menu'),
							'desc' => __('While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'accepted_cards' => array(
							'id' => 'accepted_cards',
							'name' => __('Payment Method Icons', 'mp-restaurant-menu'),
							'desc' => __('Display these icons on the checkout page', 'mp-restaurant-menu'),
							'type' => 'payment_icons',
							'options' => apply_filters('mprm_accepted_payment_icons', array(
									'mastercard' => 'Mastercard',
									'visa' => 'Visa',
									'americanexpress' => 'American Express',
									'discover' => 'Discover',
									'paypal' => 'PayPal',
								)
							),
						),
					),
					'cod' => array(
						'cod_title' => array(
							'id' => 'cod_title',
							'name' => __('Title', 'mp-restaurant-menu'),
							'desc' => __('Payment method title that the customer will see on your website.', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'regular',
							'std' => __('Cash on delivery', 'mp-restaurant-menu'),
						),
						'cod_description' => array(
							'id' => 'cod_description',
							'name' => __('Description', 'mp-restaurant-menu'),
							'desc' => __('Payment method description that the customer will see on your website.', 'mp-restaurant-menu'),
							'type' => 'textarea',
						),
						'cod_process_payments_manually' => array(
							'id' => 'cod_process_payments_manually',
							'name' => __('Process Payments Manually', 'mp-restaurant-menu'),
							'desc' => __('Review an order and set Complete status manually.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
					),
					'paypal' => array(
						'paypal_title' => array(
							'id' => 'paypal_title',
							'name' => __('Title', 'mp-restaurant-menu'),
							'desc' => __('Payment method title that the customer will see on your website.', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'regular',
							'std' => __('Pay via PayPal', 'mp-restaurant-menu'),
						),
						'paypal_description' => array(
							'id' => 'paypal_description',
							'name' => __('Description', 'mp-restaurant-menu'),
							'desc' => __('Payment method description that the customer will see on your website.', 'mp-restaurant-menu'),
							'type' => 'textarea',
						),
						'paypal_email' => array(
							'id' => 'paypal_email',
							'name' => __('PayPal Email', 'mp-restaurant-menu'),
							'desc' => __('Enter your PayPal account\'s email', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'regular',
						),
						'paypal_page_style' => array(
							'id' => 'paypal_page_style',
							'name' => __('PayPal Page Style', 'mp-restaurant-menu'),
							'desc' => __('Enter the name of the page style to use, or leave blank for default', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'regular',
						),
						'disable_paypal_verification' => array(
							'id' => 'disable_paypal_verification',
							'name' => __('Disable PayPal IPN Verification', 'mp-restaurant-menu'),
							'desc' => __('If payments via PayPal are not getting marked as complete, then check this box. <a href="https://developer.paypal.com/webapps/developer/docs/classic/products/instant-payment-notification/" target="_blank">More about IPN</a>', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
					)
				)
			),
			/** Emails Settings */
			'checkout' => apply_filters('mprm_settings_misc',
				array(
					'main' => array(
						'customer_phone' => array(
							'id' => 'customer_phone',
							'name' => __('Phone is Required', 'mp-restaurant-menu'),
							'desc' => __('Check this box to display telephone field on the checkout page.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'shipping_address' => array(
							'id' => 'shipping_address',
							'name' => __('Enable Shipping', 'mp-restaurant-menu'),
							'desc' => __('Check this box to display shipping address field on the checkout page.', 'mp-restaurant-menu'),
							'type' => 'checkbox'
						),
						'enforce_ssl' => array(
							'id' => 'enforce_ssl',
							'name' => __('Enforce SSL on Checkout', 'mp-restaurant-menu'),
							'desc' => __('Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'mp-restaurant-menu'),
							'type' => 'checkbox'
						),
						'logged_in_only' => array(
							'id' => 'logged_in_only',
							'name' => __('Disable Guest Checkout', 'mp-restaurant-menu'),
							'desc' => __('Users must be logged-in to purchase menu items.', 'mp-restaurant-menu'),
							'type' => 'checkbox'
						),
						'show_register_form' => array(
							'id' => 'show_register_form',
							'name' => __('Show Register / Login Form?', 'mp-restaurant-menu'),
							'desc' => __('Display the registration and login forms on the checkout page for non-logged-in users.', 'mp-restaurant-menu'),
							'type' => 'select',
							'std' => 'none',
							'options' => array(
								'both' => __('Registration and Login Forms', 'mp-restaurant-menu'),
								'registration' => __('Registration Form Only', 'mp-restaurant-menu'),
								'login' => __('Login Form Only', 'mp-restaurant-menu'),
								'none' => __('None', 'mp-restaurant-menu'),
							)
						),
						'enable_ajax_cart' => array(
							'id' => 'enable_ajax_cart',
							'name' => __('Enable Ajax', 'mp-restaurant-menu'),
							'desc' => __('Check this box to enable AJAX for the shopping cart.', 'mp-restaurant-menu'),
							'type' => 'checkbox'
						),
						'redirect_on_add' => array(
							'id' => 'redirect_on_add',
							'name' => __('Redirect to Checkout', 'mp-restaurant-menu'),
							'desc' => __('Immediately redirect to checkout after adding an item to the cart.', 'mp-restaurant-menu'),
							'type' => 'checkbox'
						),
						'item_quantities' => array(
							'id' => 'item_quantities',
							'name' => __('Items Amount', 'mp-restaurant-menu'),
							'desc' => __('Allow items amount to be changed on the checkout page.', 'mp-restaurant-menu'),
							'type' => 'checkbox'
						),
						'minimum_order_amount' => array(
							'id' => 'minimum_order_amount',
							'name' => __('Minimum Order Amount', 'mp-restaurant-menu'),
							'desc' => __('Price in monetary decimal (.) format without thousand separators and currency symbols', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'regular',
						),
					)
				)
			),
			/** Email templates */
			'emails' => apply_filters('mprm_settings_emails',
				array(
					'main' => array(
						
						'email_template' => array(
							'id' => 'email_template',
							'name' => __('Email Template', 'mp-restaurant-menu'),
							'desc' => __('Choose a template. Click "Save Changes", then "Preview Purchase Receipt" to see the new template.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => Settings_emails::get_instance()->get_email_templates()
						),
						'email_logo' => array(
							'id' => 'email_logo',
							'name' => __('Logo', 'mp-restaurant-menu'),
							'desc' => __('Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed in HTML emails only.', 'mp-restaurant-menu'),
							'type' => 'upload',
						),
						'email_settings' => array(
							'id' => 'email_settings',
							'name' => '',
							'desc' => '',
							'type' => 'hook',
						),
					),
					'purchase_receipts' => array(
						'purchase_receipt_settings' => array(
							'id' => 'purchase_receipt_settings',
							'name' => '<h3>' . __('Purchase Receipt', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),
						'from_name' => array(
							'id' => 'from_name',
							'name' => __('From Name', 'mp-restaurant-menu'),
							'desc' => __('The name purchase receipts are said to come from. Use your site or shop name.', 'mp-restaurant-menu'),
							'type' => 'text',
							'std' => get_bloginfo('name'),
						),
						'from_email' => array(
							'id' => 'from_email',
							'name' => __('From Email', 'mp-restaurant-menu'),
							'desc' => __('Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'mp-restaurant-menu'),
							'type' => 'text',
							'std' => get_bloginfo('admin_email'),
						),
						'purchase_subject' => array(
							'id' => 'purchase_subject',
							'name' => __('Purchase Email Subject', 'mp-restaurant-menu'),
							'desc' => __('Enter the subject line for the purchase receipt email.', 'mp-restaurant-menu'),
							'type' => 'text',
							'std' => __('Purchase Receipt', 'mp-restaurant-menu'),
						),
						'purchase_heading' => array(
							'id' => 'purchase_heading',
							'name' => __('Purchase Email Heading', 'mp-restaurant-menu'),
							'desc' => __('Enter the heading for the purchase receipt email.', 'mp-restaurant-menu'),
							'type' => 'text',
							'std' => __('Purchase Receipt', 'mp-restaurant-menu'),
						),
						'purchase_receipt' => array(
							'id' => 'purchase_receipt',
							'name' => __('Purchase Receipt', 'mp-restaurant-menu'),
							'desc' => __('Enter the text that is sent as purchase receipt email to users after completion of a successful purchase. HTML is accepted. Available template tags:', 'mp-restaurant-menu') . '<br/>' . mprm_get_emails_tags_list(),
							'type' => 'rich_editor',
							'std' => __("Dear {name},\n\nThank you for your purchase. Your order details are shown below for your reference:\n{menu_item_list}\nTotal: {price}\n\n{receipt_link}", 'mp-restaurant-menu'),
						),
					),
					'sale_notifications' => array(
						'sale_notification_settings' => array(
							'id' => 'sale_notification_settings',
							'name' => '<h3>' . __('Sale Notifications for shop owner', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),
						'sale_notification_subject' => array(
							'id' => 'sale_notification_subject',
							'name' => __('Sale Notification Subject', 'mp-restaurant-menu'),
							'desc' => __('Enter the subject line for the sale notification email.', 'mp-restaurant-menu'),
							'type' => 'text',
							'std' => 'New purchase - Order #{payment_id}',
						),
						'sale_notification' => array(
							'id' => 'sale_notification',
							'name' => __('Sale Notification', 'mp-restaurant-menu'),
							'desc' => __('Enter the text that is sent as sale notification email after completion of a purchase. HTML is accepted. Available template tags:', 'mp-restaurant-menu') . '<br/>' . mprm_get_emails_tags_list(),
							'type' => 'rich_editor',
							'std' => mprm_get_default_sale_notification_email(),
						),
						'admin_notice_emails' => array(
							'id' => 'admin_notice_emails',
							'name' => __('Sale Notification Emails', 'mp-restaurant-menu'),
							'desc' => __('Enter the email address(es) that should receive a notification anytime a sale is made, one per line', 'mp-restaurant-menu'),
							'type' => 'textarea',
							'std' => get_bloginfo('admin_email'),
						),
						'disable_admin_notices' => array(
							'id' => 'disable_admin_notices',
							'name' => __('Disable Sale Notifications', 'mp-restaurant-menu'),
							'desc' => __('Check this box if you do not want to receive sales notification emails.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
					),
				)
			),
			/** Styles Settings */
			'styles' => apply_filters('mprm_settings_styles',
				array(
					'main' => array(
						'style_settings' => array(
							'id' => 'style_settings',
							'name' => '<h3>' . __('Style Settings', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),
						'disable_styles' => array(
							'id' => 'disable_styles',
							'name' => __('Disable Styles', 'mp-restaurant-menu'),
							'desc' => __('Check this box to disable all included styling of buttons, checkout fields, and all other elements.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'button_header' => array(
							'id' => 'button_header',
							'name' => '<strong>' . __('Add to cart button style:', 'mp-restaurant-menu') . '</strong>',
							'desc' => __('Options for add to cart and purchase buttons', 'mp-restaurant-menu'),
							'type' => 'header',
						),
						'button_style' => array(
							'id' => 'button_style',
							'name' => __('Button Style', 'mp-restaurant-menu'),
							'desc' => __('Choose the style you want to use for the buttons.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => $this->get_button_styles(),
						),
						'checkout_color' => array(
							'id' => 'checkout_color',
							'name' => __('Button Color', 'mp-restaurant-menu'),
							'desc' => __('Choose the color you want to use for the buttons.', 'mp-restaurant-menu'),
							'type' => 'color_select',
							'options' => $this->get_button_colors(),
						),
						'checkout_padding' => array(
							'id' => 'checkout_padding',
							'name' => __('Button Size', 'mp-restaurant-menu'),
							'desc' => __('Choose the size you want to use for the buttons.', 'mp-restaurant-menu'),
							'type' => 'select',
							'options' => $this->get_padding_styles(),
						),
					),
				)
			),
			/** Taxes Settings */
			'taxes' => apply_filters('mprm_settings_taxes',
				array(
					'main' => array(
						'tax_settings' => array(
							'id' => 'tax_settings',
							'name' => '<h3>' . __('Tax Settings', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),
						'enable_taxes' => array(
							'id' => 'enable_taxes',
							'name' => __('Enable Taxes', 'mp-restaurant-menu'),
							'desc' => __('Check this box to enable taxes on purchases.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'tax_rate' => array(
							'id' => 'tax_rate',
							'name' => __('Tax Rate', 'mp-restaurant-menu'),
							'desc' => __('Specify a tax rate percentage (e.g. 10%). All customers will be charged this rate.', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'small',
						),
					),
				)
			),
			/** Extension Settings */
			'extensions' => apply_filters('mprm_settings_extensions',
				array()
			),
			/** licenses list */
			'licenses' => apply_filters('mprm_settings_licenses',
				array()
			),
			/** Misc Settings */
			'misc' => apply_filters('mprm_settings_misc',
				array(
					'main' => array(
						'button_settings' => array(
							'id' => 'button_settings',
							'name' => '<h3>' . __('Button Text', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),
						'checkout_label' => array(
							'id' => 'checkout_label',
							'name' => __('Complete Purchase Text', 'mp-restaurant-menu'),
							'desc' => __('The button label for completing a purchase.', 'mp-restaurant-menu'),
							'type' => 'text',
							'std' => __('Purchase', 'mp-restaurant-menu'),
						),
						'add_to_cart_text' => array(
							'id' => 'add_to_cart_text',
							'name' => __('Add to Cart Text', 'mp-restaurant-menu'),
							'desc' => __('Text shown on the Add to Cart Buttons.', 'mp-restaurant-menu'),
							'type' => 'text',
							'std' => __('Add to Cart', 'mp-restaurant-menu'),
						)
					
					),
					'site_terms' => array(
						'terms_settings' => array(
							'id' => 'terms_settings',
							'name' => '<h3>' . __('Agreement Settings', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),
						'show_agree_to_terms' => array(
							'id' => 'show_agree_to_terms',
							'name' => __('Agree to Terms', 'mp-restaurant-menu'),
							'desc' => __('Check this box to show an Agree To Terms checkbox on the checkout page that users must agree to before purchasing.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'agree_label' => array(
							'id' => 'agree_label',
							'name' => __('Agree to Terms Label', 'mp-restaurant-menu'),
							'desc' => __('Label shown next to the agree to terms check box.', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'regular',
						),
						'agree_text' => array(
							'id' => 'agree_text',
							'name' => __('Agreement Text', 'mp-restaurant-menu'),
							'desc' => __('If Agree to Terms is checked, enter the agreement terms here.', 'mp-restaurant-menu'),
							'type' => 'rich_editor',
						),
					),
				)
			)
		);
		
		return apply_filters('mprm_registered_settings', $mprm_settings);
	}
	
	/**
	 * @param bool $force
	 *
	 * @return array
	 */
	public function get_pages($force = false) {
		$pages_options = array('' => ''); // Blank option
		if ((!isset($_GET[ 'page' ]) || 'mprm-settings' != $_GET[ 'page' ]) && !$force) {
			return $pages_options;
		}
		$pages = get_pages();
		if ($pages) {
			foreach ($pages as $page) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}
		
		return $pages_options;
	}
	
	/**
	 * Get theme templates
	 *
	 * @return array
	 */
	protected function get_theme_template() {
		
		$template_options = array(
			'' => esc_html__('Default Template', 'mp-restaurant-menu'),
		);
		
		$templates = get_page_templates();
		
		ksort($templates);
		
		foreach (array_keys($templates) as $template) {
			$template_options[ $templates[ $template ] ] = $template;
		}
		
		return $template_options;
	}
	
	/**
	 * Button styles
	 *
	 * @return mixed
	 */
	public function get_button_styles() {
		$styles = array(
			'button' => __('Button', 'mp-restaurant-menu'),
			'plain' => __('Plain Text', 'mp-restaurant-menu')
		);
		
		return apply_filters('mprm_button_styles', $styles);
	}
	
	/**
	 * Button colors
	 *
	 * @return mixed
	 */
	public function get_button_colors() {
		$colors = array(
			'inherit' => array(
				'label' => __('Default', 'mp-restaurant-menu'),
				'hex' => ''
			),
			'white' => array(
				'label' => __('White', 'mp-restaurant-menu'),
				'hex' => '#ffffff'
			),
			'gray' => array(
				'label' => __('Gray', 'mp-restaurant-menu'),
				'hex' => '#f0f0f0'
			),
			'blue' => array(
				'label' => __('Blue', 'mp-restaurant-menu'),
				'hex' => '#428bca'
			),
			'red' => array(
				'label' => __('Red', 'mp-restaurant-menu'),
				'hex' => '#d9534f'
			),
			'green' => array(
				'label' => __('Green', 'mp-restaurant-menu'),
				'hex' => '#5cb85c'
			),
			'yellow' => array(
				'label' => __('Yellow', 'mp-restaurant-menu'),
				'hex' => '#f0ad4e'
			),
			'orange' => array(
				'label' => __('Orange', 'mp-restaurant-menu'),
				'hex' => '#ed9c28'
			),
			'dark-gray' => array(
				'label' => __('Dark Gray', 'mp-restaurant-menu'),
				'hex' => '#363636'
			)
		);
		
		return apply_filters('mprm_button_colors', $colors);
	}
	
	/**
	 * Padding styles
	 *
	 * @return mixed
	 */
	public function get_padding_styles() {
		$styles = array(
			'mprm-inherit' => __('Default', 'mp-restaurant-menu'),
			'mprm-small' => __('Small', 'mp-restaurant-menu'),
			'mprm-middle' => __('Middle', 'mp-restaurant-menu'),
			'mprm-big' => __('Large', 'mp-restaurant-menu')
		);
		
		return apply_filters('mprm_padding_styles', $styles);
	}
	
	/**
	 * Label singular
	 *
	 * @param bool $lowercase
	 *
	 * @return string
	 */
	public function get_label_singular($lowercase = false) {
		$defaults = $this->get_default_labels();
		
		return ($lowercase) ? strtolower($defaults[ 'singular' ]) : $defaults[ 'singular' ];
	}
	
	/**
	 * Default labels
	 *
	 * @return mixed
	 */
	public function get_default_labels() {
		$defaults = array(
			'singular' => __('Menu item', 'mp-restaurant-menu'),
			'plural' => __('Menu items', 'mp-restaurant-menu')
		);
		
		return apply_filters('mprm_default_menu_items_name', $defaults);
	}
	
	/**
	 * Settings tab
	 *
	 * @param $tab
	 *
	 * @return array/bool
	 */
	public function get_settings_tab_sections($tab) {
		$tabs = false;
		$sections = $this->get_registered_settings_sections();
		if ($tab && !empty($sections[ $tab ])) {
			$tabs = $sections[ $tab ];
		} else if ($tab) {
			$tabs = false;
		}
		
		return $tabs;
	}
	
	/**
	 * Registered settings sections
	 *
	 * @return array|bool|mixed
	 */
	public function get_registered_settings_sections() {
		static $sections = false;
		if (false !== $sections) {
			return $sections;
		}
		$sections = array(
			'general' => apply_filters('mprm_settings_sections_general', array(
					'main' => __('General', 'mp-restaurant-menu'),
					'section_currency' => __('Currency Settings', 'mp-restaurant-menu'),
				)
			),
			'display' => apply_filters('mprm_settings_sections_display', array(
					'main' => __('General', 'mp-restaurant-menu'),
					'taxonomy_grid' => __('Grid', 'mp-restaurant-menu'),
					'taxonomy_list' => __('List', 'mp-restaurant-menu'),
					'taxonomy_simple_list' => __('Simple list', 'mp-restaurant-menu')
				)
			),
			'gateways' => apply_filters('mprm_settings_sections_gateways', array(
				'main' => __('Gateways', 'mp-restaurant-menu'),
				'paypal' => __('PayPal Standard', 'mp-restaurant-menu'),
				'cod' => __('Cash On Delivery', 'mp-restaurant-menu'),
			)),
			'emails' => apply_filters('mprm_settings_sections_emails', array(
				'main' => __('Email Template', 'mp-restaurant-menu'),
				'purchase_receipts' => __('Purchase Receipt', 'mp-restaurant-menu'),
				'sale_notifications' => __('New Sale Notifications', 'mp-restaurant-menu'),
			)),
			'styles' => apply_filters('mprm_settings_sections_styles', array(
				'main' => __('Style Settings', 'mp-restaurant-menu'),
			)),
			'checkout' => apply_filters('mprm_settings_sections_styles', array(
				'main' => __('Checkout Settings', 'mp-restaurant-menu'),
			)),
			'taxes' => apply_filters('mprm_settings_sections_taxes', array(
				'main' => __('Tax Settings', 'mp-restaurant-menu'),
			)),
			'extensions' => apply_filters('mprm_settings_sections_extensions', array(
				'main' => __('Main', 'mp-restaurant-menu')
			)),
			'licenses' => apply_filters('mprm_settings_sections_licenses', array()),
			'misc' => apply_filters('mprm_settings_sections_misc', array(
				'main' => __('Button Text', 'mp-restaurant-menu'),
				'site_terms' => __('Terms of Agreement', 'mp-restaurant-menu')
			)),
		);
		$sections = apply_filters('mprm_settings_sections', $sections);
		
		return $sections;
	}
	
	/**
	 * Admin script
	 */
	public function admin_enqueue_scripts() {
		global $current_screen;
		$this->current_screen($current_screen);
	}
	
	/**
	 * Current screen
	 *
	 * @param \WP_Screen $current_screen
	 */
	public function current_screen(\WP_Screen $current_screen) {
		$this->enqueue_style('admin-styles', 'admin-styles.css');
		$prefix = $this->get_prefix();
		if (!empty($current_screen)) {
			switch ($current_screen->base) {
				case"post":
				case"page":
					wp_enqueue_script('underscore');
					$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
					$this->enqueue_script('jBox', "libs/jBox{$prefix}.js");
					wp_localize_script("mp-restaurant-menu", 'mprm_admin_vars', $this->get_config('language-admin-js'));
					$this->enqueue_style('jBox', 'lib/jbox/jBox.css');
					break;
				default:
					break;
			}
			
			switch ($current_screen->id) {
				case "restaurant-menu_page_admin?page=mprm-settings":
					wp_enqueue_script('underscore');
					$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
					wp_localize_script("mp-restaurant-menu", 'mprm_admin_vars', $this->get_config('language-admin-js'));
					wp_enqueue_script('wp-util');
					wp_enqueue_media();
					wp_enqueue_script('thickbox');
					wp_enqueue_style('thickbox');
					break;
				case"restaurant-menu_page_mprm-customers":
				case "customize":
				case "widgets":
				case "edit-mp_menu_item":
					$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
					wp_localize_script("mp-restaurant-menu", 'mprm_admin_vars', $this->get_config('language-admin-js'));
					break;
				case"restaurant-menu_page_mprm-settings":
					wp_enqueue_script('wp-util');
					wp_enqueue_media();
					$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
					wp_localize_script("mp-restaurant-menu", 'mprm_admin_vars', $this->get_config('language-admin-js'));
					$this->enqueue_style('mprm-chosen', 'lib/chosen.min.css');
					$this->enqueue_script('mprm-chosen', "libs/chosen.jquery{$prefix}.js", array("jquery"), '1.1.0');
					break;
				case "edit-mp_menu_category":
					$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
					$this->enqueue_script('iconset-mprm-icon', "libs/iconset-mprm-icon{$prefix}.js");
					$this->enqueue_script('fonticonpicker', "libs/jquery.fonticonpicker{$prefix}.js", array("jquery"), '2.0.0');
					wp_localize_script("mp-restaurant-menu", 'mprm_admin_vars', $this->get_config('language-admin-js'));
					$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.min.css');
					$this->enqueue_style('fonticonpicker', 'lib/jquery.fonticonpicker.min.css');
					$this->enqueue_style('fonticonpicker.grey', 'lib/jquery.fonticonpicker.grey.min.css');
					wp_enqueue_media();
					break;
				case "mprm_order":
					$this->enqueue_style('mprm-chosen', 'lib/chosen.min.css');
					$this->enqueue_script('mprm-chosen', "libs/chosen.jquery{$prefix}.js", array("jquery"), '1.1.0');
					wp_enqueue_media();
					break;
				default:
					break;
			}
		}
	}
	
	/**
	 * Enqueue style
	 *
	 * @param string $name
	 * @param string $path
	 * @param array $deps
	 * @param bool /string $version
	 * * @return void
	 */
	public function enqueue_style($name, $path, $deps = array(), $version = false) {
		if (empty($version)) {
			$version = $this->get_version();
		}
		wp_enqueue_style($name, MP_RM_CSS_URL . $path, $deps, $version);
	}
	
	/**
	 * @return string
	 */
	public function get_prefix() {
		$prefix = !MP_RM_DEBUG ? '.min' : '';
		
		return $prefix;
	}
	
	/**
	 * Enqueue script
	 *
	 * @param string $name
	 * @param string $path
	 * @param array $deps
	 * @param bool /string $version
	 *
	 * @return void
	 */
	public function enqueue_script($name, $path, $deps = array("jquery"), $version = false) {
		if (empty($version)) {
			$version = $this->get_version();
		}
		wp_enqueue_script(apply_filters('mprm-script-' . $name, $name), MP_RM_JS_URL . $path, $deps, $version);
	}
	
	/**
	 * Wp head
	 */
	public function enqueue_scripts() {
		$this->add_theme_css();
	}
	
	/**
	 * Add theme css
	 */
	private function add_theme_css() {
		global $post_type;
		$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.min.css');
		$this->enqueue_style('mprm-style', 'style.css');
		wp_enqueue_script('wp-util');
		
		switch ($post_type) {
			case"mp_menu_item":
				$this->enqueue_style('magnific-popup', 'lib/magnific-popup.min.css');
				break;
			default:
				break;
		}
	}
	
	/**
	 * Wp footer
	 */
	public function wp_footer() {
		$this->add_theme_js();
	}
	
	/**
	 * Add theme js
	 */
	private function add_theme_js() {
		global $post_type, $taxonomy;
		$prefix = $this->get_prefix();
		switch ($post_type) {
			case "mp_menu_item":
				wp_enqueue_script('underscore');
				$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
				$this->enqueue_script('magnific-popup', "libs/jquery.magnific-popup{$prefix}.js", array("jquery"), '1.0.1');
				break;
			
			default:
				break;
		}
		
		switch ($taxonomy) {
			case "mp_menu_category":
			case "mp_menu_tag":
				wp_enqueue_script('underscore');
				$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
				break;
			default:
				break;
			
		}
	}
	
	/**
	 * Add js
	 *
	 * @param bool $type
	 */
	public function add_plugin_js($type = false) {
		$prefix = $this->get_prefix();
		switch ($type) {
			case"shortcode":
			case"widget":
				wp_enqueue_script('underscore');
				$this->enqueue_script('mp-restaurant-menu', "mp-restaurant-menu{$prefix}.js");
				break;
		}
	}
	
	/**
	 * Register all post type
	 */
	public function register_all_post_type() {
		$menu_item_post_type = $this->get_post_type('menu_item');
		
		if (post_type_exists($menu_item_post_type)) {
			return;
		}
		
		register_post_type($menu_item_post_type, array(
			//'label' => 'mp_menu_item',
			'labels' =>
				array(
					'name' => __('Menu Items', 'mp-restaurant-menu'),
					'singular_name' => __('Menu Item', 'mp-restaurant-menu'),
					'add_new' => __('Add New', 'mp-restaurant-menu'),
					'add_new_item' => __('Add New Menu Item', 'mp-restaurant-menu'),
					'edit_item' => __('Edit Menu Item', 'mp-restaurant-menu'),
					'new_item' => __('New Menu Item', 'mp-restaurant-menu'),
					'all_items' => __('All Menu Items', 'mp-restaurant-menu'),
					'view_item' => __('View Menu Item', 'mp-restaurant-menu'),
					'search_items' => __('Search Menu Item', 'mp-restaurant-menu'),
					'not_found' => __('No menu items found', 'mp-restaurant-menu'),
					'not_found_in_trash' => __('No menu items found in Trash', 'mp-restaurant-menu'),
					'parent_item_colon' => __('media', 'mp-restaurant-menu'),
					'menu_name' => __('Menu Items', 'mp-restaurant-menu'),
				),
			'public' => true,
			'has_archive' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => $menu_item_post_type,
			'menu_position' => 21,
			'hierarchical' => false,
			'map_meta_cap' => true,
			'show_in_nav_menus' => false,
			'rewrite' =>
				array(
					'slug' => 'menu',
					'with_front' => true,
					'hierarchical' => true,
				),
			'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'page-attributes'),
			'show_in_admin_bar' => true,
		));
		
		register_post_type($this->get_post_type('order'), array(
			'labels' => array(
				'name' => __('Orders', 'mp-restaurant-menu'),
				'singular_name' => _x('Order', 'shop_order post type singular name', 'mp-restaurant-menu'),
				'add_new' => __('Add Order', 'mp-restaurant-menu'),
				'add_new_item' => __('Add New Order', 'mp-restaurant-menu'),
				'edit' => __('Edit', 'mp-restaurant-menu'),
				'edit_item' => __('Edit Order', 'mp-restaurant-menu'),
				'new_item' => __('New Order', 'mp-restaurant-menu'),
				'view' => __('View Order', 'mp-restaurant-menu'),
				'view_item' => __('View Order', 'mp-restaurant-menu'),
				'search_items' => __('Search Orders', 'mp-restaurant-menu'),
				'not_found' => __('No Orders found', 'mp-restaurant-menu'),
				'not_found_in_trash' => __('No Orders found in trash', 'mp-restaurant-menu'),
				'parent' => __('Parent Orders', 'mp-restaurant-menu'),
				'menu_name' => _x('Orders', 'Admin menu name', 'mp-restaurant-menu')
			),
			'description' => __('This is where store orders are stored.', 'mp-restaurant-menu'),
			'public' => false,
			'show_ui' => true,
			'capability_type' => $this->get_post_type('order'),
			'capabilities' => array(
				'create_posts' => false,
			),
			'map_meta_cap' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'show_in_menu' => false,
			'hierarchical' => false,
			'show_in_nav_menus' => false,
			'rewrite' => false,
			'query_var' => false,
			'supports' => array('title', 'comments'),
			'has_archive' => false,
		));
	}
	
	/**
	 * Register all taxonomies
	 */
	public function register_all_taxonomies() {
		if (taxonomy_exists($this->get_tax_name('menu_category'))) {
			return;
		}
		
		$menu_item = $this->get_post_type('menu_item');
		
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('menu_category'),
			'object_type' => array($menu_item),
			'titles' => array('many' => __('Categories', 'mp-restaurant-menu'), 'single' => __('Category', 'mp-restaurant-menu')),
			'slug' => 'menu-category',
			'show_in_nav_menus' => true
		));
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('menu_tag'),
			'object_type' => array($menu_item),
			'titles' => array('many' => __('Tags', 'mp-restaurant-menu'), 'single' => __('Tag', 'mp-restaurant-menu')),
			'slug' => 'menu-tag',
			'show_in_nav_menus' => true
		));
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('ingredient'),
			'object_type' => array($menu_item),
			'titles' => array('many' => __('Ingredients', 'mp-restaurant-menu'), 'single' => __('Ingredient', 'mp-restaurant-menu')),
		));
	}
	
	/**
	 * Include pseudo template
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function modify_single_template($template) {
		global $post;
		
		if (!empty($post) && in_array($post->post_type, $this->post_types)) {
			add_action('loop_start', array($this, 'setup_pseudo_template'));
		}
		
		return $template;
	}
	
	/**
	 * Pseudo template
	 *
	 * @param object $query
	 */
	public function setup_pseudo_template($query) {
		global $post;
		
		if ($query->is_main_query()) {
			
			if (!empty($post) && in_array($post->post_type, $this->post_types) && $this->get_template_mode() == 'theme') {
				add_filter('the_content', array($this, 'append_post_content'));
			}
			remove_action('loop_start', array($this, 'setup_pseudo_template'));
		}
	}
	
	/**
	 * Get template mode
	 * @return mixed|string
	 */
	public function get_template_mode() {
		$template_mode = Settings::get_instance()->get_option('template_mode', 'theme');
		if (current_theme_supports('mp-restaurant-menu')) {
			return 'plugin';
		}
		
		return $template_mode;
	}
	
	/**
	 * Pseudo taxonomy template
	 *
	 * @param  \WP_Query $query
	 */
	public function setup_pseudo_taxonomy_template($query) {
		
		do_action('mprm_filter_the_page_title');
		
		if ($query->is_main_query() && self::$wpHeadFinished) {
			// on loop start, unset the global post so that template tags don't work before the_content()
			add_action('the_post', array($this, 'spoof_the_post'));
			
			// on the_content, load our taxonomy template template
			add_filter('the_content', array($this, 'load_content_into_page_template'));
			
			// only do this once
			remove_action('loop_start', array($this, 'setup_pseudo_taxonomy_template'));
		}
	}
	
	/**
	 * Spoof the global post just once
	 **/
	public function spoof_the_post() {
		$GLOBALS[ 'post' ] = $this->spoofed_post();
		remove_action('the_post', array($this, 'spoof_the_post'));
	}
	
	/**
	 * Append additional post content
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function append_post_content($content) {
		global $post;
		// run only once
		remove_filter('the_content', array($this, 'append_post_content'));
		
		$append_content = '';
		
		switch ($post->post_type) {
			case $this->post_types[ 'menu_item' ]:
				$append_content .= $this->get_view()->get_template_html('theme-support/single-' . $this->post_types[ 'menu_item' ]);
				break;
			case $this->post_types[ 'order' ]:
			default:
				break;
		}
		
		return $content . $append_content;
	}
	
	/**
	 * Template include
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function template_include($template) {
		global $post, $taxonomy;
		
		if (is_embed()) {
			return $template;
		}
		
		if ($this->get_template_mode() == 'plugin') {
			$find = array();
			if (!empty($post) && is_single() && in_array(get_post_type(), $this->post_types)) {
				foreach ($this->post_types as $post_type) {
					if ($post->post_type == $post_type) {
						$find[] = "single-$post_type.php";
						
						$find_template = locate_template(array_unique($find));
						
						if (file_exists($find_template)) {
							$template = $find_template;
						} else {
							$template = MP_RM_TEMPLATES_PATH . "single-$post_type.php";
						}
					}
				}
			}
			
			if (!empty($taxonomy) && is_tax() && in_array($taxonomy, $this->taxonomy_names)) {
				foreach ($this->taxonomy_names as $taxonomy_name) {
					if (basename($template) != "taxonomy-$taxonomy_name.php") {
						$path = MP_RM_TEMPLATES_PATH . "taxonomy-$taxonomy_name.php";
						if (is_tax($taxonomy_name) && $taxonomy == $taxonomy_name && file_exists($path)) {
							$template = $path;
						}
					}
				}
			}
		}
		
		return $template;
	}
	
	/**
	 * Connect js for MCE editor
	 *
	 * @param $plugin_array
	 *
	 * @return mixed
	 */
	public function mce_external_plugins($plugin_array) {
		global $pagenow;
		
		$default_array = array('post-new.php', 'post.php');
		
		if (in_array($pagenow, $default_array)) {
			$path = MP_RM_MEDIA_URL . "js/mce-mp-restaurant-menu-plugin{$this->get_prefix()}.js";
			$plugin_array[ 'mp_restaurant_menu' ] = $path;
		}
		
		return $plugin_array;
	}
	
	/**
	 * Add button in MCE editor
	 *
	 * @param $buttons
	 *
	 * @return mixed
	 */
	public function mce_buttons($buttons) {
		array_push($buttons, 'mp_add_menu');
		
		return $buttons;
	}
	
	/**
	 * Disable autosave
	 */
	public function disable_autosave() {
		global $post;
		if (!empty($post) && $post->post_type == 'mprm_order') {
			wp_dequeue_script('autosave');
		}
	}
	
	/**
	 * Get settings tabs
	 * @return mixed
	 */
	public function get_settings_tabs() {
		$settings = $this->get_registered_settings();
		$tabs = array();
		$tabs[ 'general' ] = _x('General', 'General settings tab', 'mp-restaurant-menu');
		$tabs[ 'display' ] = _x('Display', 'Disapley settings tab', 'mp-restaurant-menu');
		$tabs[ 'gateways' ] = _x('Payment', 'Payment settings tab', 'mp-restaurant-menu');
		$tabs[ 'checkout' ] = _x('Checkout', 'Checkout settings tab', 'mp-restaurant-menu');
		$tabs[ 'emails' ] = _x('Emails', 'Email settings tab', 'mp-restaurant-menu');
		$tabs[ 'styles' ] = _x('Styles', 'Styles settings tab', 'mp-restaurant-menu');
		$tabs[ 'taxes' ] = _x('Taxes', 'Taxes settings tab', 'mp-restaurant-menu');
		
		if (!empty($settings[ 'extensions' ])) {
			$tabs[ 'extensions' ] = _x('Extensions', 'Extensions settings tab', 'mp-restaurant-menu');
		}
		
		if (!empty($settings[ 'licenses' ])) {
			$tabs[ 'licenses' ] = _x('Licenses', 'Licenses settings tab', 'mp-restaurant-menu');
		}
		$tabs[ 'misc' ] = _x('Misc', 'Misc settings tab', 'mp-restaurant-menu');
		
		return apply_filters('mprm_settings_tabs', $tabs);
	}
}