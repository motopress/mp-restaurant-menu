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
		$args['maxchar'] += 0;
		// cutting
		if (mb_strlen($args['text']) > $args['maxchar'] && $args['maxchar'] != 0) {
			$args['text'] = mb_substr($args['text'], 0, $args['maxchar']);
			$args['text'] = $args['text'] . '...';
		}
		// save br ad paragraph
		if ($args['save_format']) {
			$args['text'] = str_replace("\r", '', $args['text']);
			$args['text'] = preg_replace("~\n+~", "</p><p>", $args['text']);
			$args['text'] = "<p>" . str_replace("\n", "<br />", trim($args['text'])) . "</p>";
		}
		if ($args['echo']) {
			return print $args['text'];
		}
		return $args['text'];
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
			'title' => __('Restaurant Menu', 'mp-restaurant-menu'),
			'menu_slug' => $menu_slug,
			'icon_url' => MP_RM_MEDIA_URL . '/img/icon.png',
			'capability' => 'edit_posts',
			'position' => '59.52'
		));
		// Menu items
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Menu Items', 'mp-restaurant-menu'),
			'menu_slug' => "edit.php?post_type={$menu_item}",
			'capability' => 'edit_posts',
		));

		// Add new
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Add New', 'mp-restaurant-menu'),
			'menu_slug' => "post-new.php?post_type={$menu_item}",
			'capability' => 'edit_posts',
		));
		// Categories
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Categories', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$category_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_categories',
		));
		// Tags
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Tags', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$tag_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_categories',
		));
		// Ingredients
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Ingredients', 'mp-restaurant-menu'),
			'menu_slug' => "edit-tags.php?taxonomy={$ingredient_name}&amp;post_type={$menu_item}",
			'capability' => 'manage_categories',
		));
		// Orders
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Orders', 'mp-restaurant-menu'),
			'menu_slug' => "edit.php?post_type=$order",
			'capability' => 'edit_posts',
		));
		//Customers
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Customers', 'mp-restaurant-menu'),
			'menu_slug' => "mprm-customers",
			'function' => array($this->get_controller('customer'), 'action_content'),
			'capability' => 'manage_options',
		));
		// Settings
		Menu::add_submenu_page(array(
			'parent_slug' => $menu_slug,
			'title' => __('Settings', 'mp-restaurant-menu'),
			'menu_slug' => "mprm-settings",
			'function' => array($this->get_controller('settings'), 'action_content'),
			'capability' => 'manage_options',
		));
		//Import/Export
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
			if (isset($submenu[$key][5])) {
				if ($submenu[$key][5][2] == 'edit.php?post_type=mprm_order') {
					$submenu[$key][5][0] .= " <span class='update-plugins count-$pend_count'><span class='plugin-count'>" . $pend_count . '</span></span>';
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
					if (empty($option['id'])) {
						continue;
					}
					$name = isset($option['name']) ? $option['name'] : '';
					add_settings_field(
						'mprm_settings[' . $option['id'] . ']',
						$name,
						method_exists(Settings::get_instance(), $option['type'] . '_callback') ? array(Settings::get_instance(), $option['type'] . '_callback') : array(Settings::get_instance(), 'missing_callback'),
						'mprm_settings_' . $tab . '_' . $section,
						'mprm_settings_' . $tab . '_' . $section,
						array(
							'section' => $section,
							'id' => isset($option['id']) ? $option['id'] : null,
							'desc' => !empty($option['desc']) ? $option['desc'] : '',
							'name' => isset($option['name']) ? $option['name'] : null,
							'size' => isset($option['size']) ? $option['size'] : null,
							'options' => isset($option['options']) ? $option['options'] : '',
							'std' => isset($option['std']) ? $option['std'] : '',
							'min' => isset($option['min']) ? $option['min'] : null,
							'max' => isset($option['max']) ? $option['max'] : null,
							'step' => isset($option['step']) ? $option['step'] : null,
							'chosen' => isset($option['chosen']) ? $option['chosen'] : null,
							'placeholder' => isset($option['placeholder']) ? $option['placeholder'] : null,
							'allow_blank' => isset($option['allow_blank']) ? $option['allow_blank'] : true,
							'readonly' => isset($option['readonly']) ? $option['readonly'] : false,
							'faux' => isset($option['faux']) ? $option['faux'] : false,
						)
					);
				}
			}
		}
		// Creates our settings in the options table
		register_setting('mprm_settings', 'mprm_settings', array(Settings::get_instance(), 'mprm_settings_sanitize'));
	}

	/**
	 * @return mixed|void
	 */
	public function get_registered_settings() {
		$mprm_settings = array(
			/** General Settings */
			'general' => apply_filters('mprm_settings_general',
				array(
					'main' => array(
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
						'ecommerce_settings' => array(
							'id' => 'ecommerce_settings',
							'name' => '<h3>' . __('eCommerce', 'mp-restaurant-menu') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),
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
//						'locale_settings' => array(
//							'id' => 'locale_settings',
//							'name' => '<h3>' . __('Store Location', 'mp-restaurant-menu') . '</h3>',
//							'desc' => '',
//							'type' => 'header',
//						),
//						'base_country' => array(
//							'id' => 'base_country',
//							'name' => __('Base Country', 'mp-restaurant-menu'),
//							'desc' => __('Where does your store operate from?', 'mp-restaurant-menu'),
//							'type' => 'select',
//							'options' => Settings::get_instance()->get_country_list(),
//							'chosen' => true,
//							'placeholder' => __('Select a country', 'mp-restaurant-menu'),
//						),
//						'base_state' => array(
//							'id' => 'base_state',
//							'name' => __('Base State / Province', 'mp-restaurant-menu'),
//							'desc' => __('What state / province does your store operate from?', 'mp-restaurant-menu'),
//							'type' => 'shop_states',
//							'chosen' => true,
//							'placeholder' => __('Select a state', 'mp-restaurant-menu'),
//						),
//						'tracking_settings' => array(
//							'id' => 'tracking_settings',
//							'name' => '<h3>' . __('Tracking Settings', 'mp-restaurant-menu') . '</h3>',
//							'desc' => '',
//							'type' => 'header',
//						)
					),
					'section_currency' => array(
						/*'currency_settings' => array(
							'id' => 'currency_settings',
							'name' => '<h3>' . __('Currency Settings', 'mp-restaurant-menu') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),*/
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
					),
				)
			),
			/** Payment Gateways Settings */
			'gateways' => apply_filters('mprm_settings_gateways',
				array(
					'main' => array(
						/*'gateway_settings' => array(
							'id' => 'api_header',
							'name' => '<h3>' . __('Gateway Settings', 'mp-restaurant-menu') . '</h3>',
							'desc' => '',
							'type' => 'header',
						),*/
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
					'paypal' => array(
						/*'paypal_settings' => array(
							'id' => 'paypal_settings',
							'name' => '<h3>' . __('PayPal Standard Gateway', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),*/
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
					),
				)
			),
			/** Emails Settings */
			'checkout' => apply_filters('mprm_settings_misc',
				array(
					'main' => array(
						'customer_phone' => array(
							'id' => 'customer_phone',
							'name' => __('Telephone Number Required', 'mp-restaurant-menu'),
							'desc' => __('Check this box to display telephone field on the checkout page.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'shipping_address' => array(
							'id' => 'shipping_address',
							'name' => __('Enable Shipping', 'mp-restaurant-menu'),
							'desc' => __('Check this box to display shipping address field on the checkout page.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'enforce_ssl' => array(
							'id' => 'enforce_ssl',
							'name' => __('Enforce SSL on Checkout', 'mp-restaurant-menu'),
							'desc' => __('Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'logged_in_only' => array(
							'id' => 'logged_in_only',
							'name' => __('Disable Guest Checkout', 'mp-restaurant-menu'),
							'desc' => __('Users must be logged-in to purchase menu items.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
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
							),
						),
						'enable_ajax_cart' => array(
							'id' => 'enable_ajax_cart',
							'name' => __('Enable Ajax', 'mp-restaurant-menu'),
							'desc' => __('Check this box to enable AJAX for the shopping cart.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
							'std' => '1',
						),
						'redirect_on_add' => array(
							'id' => 'redirect_on_add',
							'name' => __('Redirect to Checkout', 'mp-restaurant-menu'),
							'desc' => __('Immediately redirect to checkout after adding an item to the cart.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
						'item_quantities' => array(
							'id' => 'item_quantities',
							'name' => __('Items Amount', 'mp-restaurant-menu'),
							'desc' => __('Allow items amount to be changed on the checkout page.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
					)
				)
			),
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
//						'tax_rates' => array(
//							'id' => 'tax_rates',
//							'name' => '<strong>' . __('Tax Rates', 'mp-restaurant-menu') . '</strong>',
//							'desc' => __('Enter tax rates for specific regions.', 'mp-restaurant-menu'),
//							'type' => 'tax_rates',
//						),
						'tax_rate' => array(
							'id' => 'tax_rate',
							'name' => __('Tax Rate', 'mp-restaurant-menu'),
							'desc' => __('Specify a tax rate percentage (e.g. 10%). All customers will be charged this rate.', 'mp-restaurant-menu'),
							'type' => 'text',
							'size' => 'small',
						),
//						'prices_include_tax' => array(
//							'id' => 'prices_include_tax',
//							'name' => __('Prices entered with tax', 'mp-restaurant-menu'),
//							'desc' => __('This option affects how you enter prices.', 'mp-restaurant-menu'),
//							'type' => 'radio',
//							'std' => 'no',
//							'options' => array(
//								'yes' => __('Yes, I will enter prices inclusive of tax', 'mp-restaurant-menu'),
//								'no' => __('No, I will enter prices exclusive of tax', 'mp-restaurant-menu'),
//							),
//						),
//						'display_tax_rate' => array(
//							'id' => 'display_tax_rate',
//							'name' => __('Display Tax Rate on Prices', 'mp-restaurant-menu'),
//							'desc' => __('Some countries require a notice when product prices include tax.', 'mp-restaurant-menu'),
//							'type' => 'checkbox',
//						),
//						'checkout_include_tax' => array(
//							'id' => 'checkout_include_tax',
//							'name' => __('Display during checkout', 'mp-restaurant-menu'),
//							'desc' => __('Should prices on the checkout page be shown with or without tax?', 'mp-restaurant-menu'),
//							'type' => 'select',
//							'std' => 'no',
//							'options' => array(
//								'yes' => __('Including tax', 'mp-restaurant-menu'),
//								'no' => __('Excluding tax', 'mp-restaurant-menu'),
//							),
//						),
					),
				)
			),
			/** Extension Settings */
			'extensions' => apply_filters('mprm_settings_extensions',
				array()
			),
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
//					,
//						'buy_now_text' => array(
//							'id' => 'buy_now_text',
//							'name' => __('Buy Now Text', 'mp-restaurant-menu'),
//							'desc' => __('Text shown on the Buy Now Buttons.', 'mp-restaurant-menu'),
//							'type' => 'text',
//							'std' => __('Buy Now', 'mp-restaurant-menu'),
//						)
					),
					'file_menu_items' => array(
						'file_settings' => array(
							'id' => 'file_settings',
							'name' => '<h3>' . __('File Menu item Settings', 'mp-restaurant-menu') . '</h3>',
							'type' => 'header',
						),
						'menu_item_method' => array(
							'id' => 'menu_item_method',
							'name' => __('Menu item Method', 'mp-restaurant-menu'),
							'desc' => sprintf(__('Select the file menu_item method. Note, not all methods work on all servers.', 'mp-restaurant-menu'), $this->get_label_singular()),
							'type' => 'select',
							'options' => array(
								'direct' => __('Forced', 'mp-restaurant-menu'),
								'redirect' => __('Redirect', 'mp-restaurant-menu'),
							),
						),
//						'symlink_file_menu_items' => array(
//							'id' => 'symlink_file_menu_items',
//							'name' => __('Symlink File Menu item?', 'mp-restaurant-menu'),
//							'desc' => __('Check this if you are delivering really large files or having problems with file menu_items completing.', 'mp-restaurant-menu'),
//							'type' => 'checkbox',
//						),
						'file_menu_item_limit' => array(
							'id' => 'file_menu_item_limit',
							'name' => __('File Menu item Limit', 'mp-restaurant-menu'),
							'desc' => sprintf(__('The maximum number of times files can be menu_itemed for purchases. Can be overwritten for each %s.', 'mp-restaurant-menu'), $this->get_label_singular()),
							'type' => 'number',
							'size' => 'small',
						),
						'menu_item_link_expiration' => array(
							'id' => 'menu_item_link_expiration',
							'name' => __('Menu item Link Expiration', 'mp-restaurant-menu'),
							'desc' => __('How long should menu_item links be valid for? Default is 24 hours from the time they are generated. Enter a time in hours.', 'mp-restaurant-menu'),
							'type' => 'number',
							'size' => 'small',
							'std' => '24',
							'min' => '0',
						),
						'disable_remenu_item' => array(
							'id' => 'disable_remenu_item',
							'name' => __('Disable Remenu_item?', 'mp-restaurant-menu'),
							'desc' => __('Check this if you do not want to allow users to remenu_item items from their purchase history.', 'mp-restaurant-menu'),
							'type' => 'checkbox',
						),
					),
//					'accounting' => array(
//						'accounting_settings' => array(
//							'id' => 'accounting_settings',
//							'name' => '<h3>' . __('Accounting Settings', 'mp-restaurant-menu') . '</h3>',
//							'type' => 'header',
//						),
//						'enable_skus' => array(
//							'id' => 'enable_skus',
//							'name' => __('Enable SKU Entry', 'mp-restaurant-menu'),
//							'desc' => __('Check this box to allow entry of product SKUs. SKUs will be shown on purchase receipt and exported purchase histories.', 'mp-restaurant-menu'),
//							'type' => 'checkbox',
//						),
//						'enable_sequential' => array(
//							'id' => 'enable_sequential',
//							'name' => __('Sequential Order Numbers', 'mp-restaurant-menu'),
//							'desc' => __('Check this box to enable sequential order numbers.', 'mp-restaurant-menu'),
//							'type' => 'checkbox',
//						),
//						'sequential_start' => array(
//							'id' => 'sequential_start',
//							'name' => __('Sequential Starting Number', 'mp-restaurant-menu'),
//							'desc' => __('The number at which the sequence should begin.', 'mp-restaurant-menu'),
//							'type' => 'number',
//							'size' => 'small',
//							'std' => '1',
//						),
//						'sequential_prefix' => array(
//							'id' => 'sequential_prefix',
//							'name' => __('Sequential Number Prefix', 'mp-restaurant-menu'),
//							'desc' => __('A prefix to prepend to all sequential order numbers.', 'mp-restaurant-menu'),
//							'type' => 'text',
//						),
//						'sequential_postfix' => array(
//							'id' => 'sequential_postfix',
//							'name' => __('Sequential Number Postfix', 'mp-restaurant-menu'),
//							'desc' => __('A postfix to append to all sequential order numbers.', 'mp-restaurant-menu'),
//							'type' => 'text',
//						),
//					),
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
		if ((!isset($_GET['page']) || 'mprm-settings' != $_GET['page']) && !$force) {
			return $pages_options;
		}
		$pages = get_pages();
		if ($pages) {
			foreach ($pages as $page) {
				$pages_options[$page->ID] = $page->post_title;
			}
		}
		return $pages_options;
	}

	/**
	 * @return mixed|void
	 */
	public function get_button_styles() {
		$styles = array(
			'button' => __('Button', 'mp-restaurant-menu'),
			'plain' => __('Plain Text', 'mp-restaurant-menu')
		);
		return apply_filters('mprm_button_styles', $styles);
	}

	/**
	 * @return mixed|void
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
	 * @return mixed|void
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
	 * @param bool $lowercase
	 *
	 * @return string
	 */
	public function get_label_singular($lowercase = false) {
		$defaults = $this->get_default_labels();
		return ($lowercase) ? strtolower($defaults['singular']) : $defaults['singular'];
	}

	/**
	 * @return mixed|void
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
		if ($tab && !empty($sections[$tab])) {
			$tabs = $sections[$tab];
		} else if ($tab) {
			$tabs = false;
		}
		return $tabs;
	}

	/**
	 * @return array|bool|mixed|void
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
			)),
			'gateways' => apply_filters('mprm_settings_sections_gateways', array(
				'main' => __('Gateways', 'mp-restaurant-menu'),
				'paypal' => __('PayPal Standard', 'mp-restaurant-menu'),
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
					$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.css');
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
		wp_enqueue_script($name, MP_RM_JS_URL . $path, $deps, $version);
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
		$this->enqueue_style('mp-restaurant-menu-font', 'lib/mp-restaurant-menu-font.css');
		$this->enqueue_style('mprm-style', 'style.css');
		wp_enqueue_script('wp-util');
		switch ($post_type) {
			case"mp_menu_item":
				$this->enqueue_style('magnific-popup', 'lib/magnific-popup.css');
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
		if (post_type_exists($this->get_post_type('menu_item'))) {
			return;
		}
		register_post_type($this->get_post_type('menu_item'), array(
			'label' => 'mp_menu_item',
			'labels' =>
				array(
					'name' => __('Menu items', 'mp-restaurant-menu'),
					'singular_name' => __('Menu item', 'mp-restaurant-menu'),
					'add_new' => __('Add New', 'mp-restaurant-menu'),
					'add_new_item' => __('Add New Menu item', 'mp-restaurant-menu'),
					'edit_item' => __('Edit Menu item', 'mp-restaurant-menu'),
					'new_item' => __('New Menu item', 'mp-restaurant-menu'),
					'all_items' => __('All Menu items', 'mp-restaurant-menu'),
					'view_item' => __('View Menu item', 'mp-restaurant-menu'),
					'search_items' => __('Search Menu item', 'mp-restaurant-menu'),
					'not_found' => __('No menu items found', 'mp-restaurant-menu'),
					'not_found_in_trash' => __('No menu items found in Trash', 'mp-restaurant-menu'),
					'parent_item_colon' => __('media', 'mp-restaurant-menu'),
					'menu_name' => __('Menu items', 'mp-restaurant-menu'),
				),
			'public' => true,
			'has_archive' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'product',
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
			'capability_type' => 'shop_payment',
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
			'titles' => array('many' => __('menu categories', 'mp-restaurant-menu'), 'single' => __('menu category', 'mp-restaurant-menu')),
			'slug' => 'menu-category',
			'show_in_nav_menus' => true
		));
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('menu_tag'),
			'object_type' => array($menu_item),
			'titles' => array('many' => __('menu tags', 'mp-restaurant-menu'), 'single' => __('menu tag', 'mp-restaurant-menu')),
			'slug' => 'menu-tag',
			'show_in_nav_menus' => true
		));
		Taxonomy::get_instance()->register(array(
			'taxonomy' => $this->get_tax_name('ingredient'),
			'object_type' => array($menu_item),
			'titles' => array('many' => __('ingredients', 'mp-restaurant-menu'), 'single' => __('ingredient', 'mp-restaurant-menu')),
		));
	}

	/**
	 * Single template
	 *
	 * @param $template *
	 *
	 * @return mixed
	 */
	public function single_template($template) {
		global $post;


		if (!empty($post) && in_array($post->post_type, array_values($this->post_types)) && $this->template_mode() == 'theme') {
			add_filter('the_content', array($this, 'single_content'), 20, 2);
		}
		return $template;
	}

	/**
	 * @return mixed|string|void
	 */
	public function template_mode() {
		$template_mode = Settings::get_instance()->get_option('template_mode', 'theme');
		if (current_theme_supports('mp-restaurant-menu')) {
			return 'plugin';
		}
		return $template_mode;
	}

	/**
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function single_content($content) {
		global $post;
		$append_content = '';
		if (is_tax() && is_archive()) {
			return $content;
		}

		switch ($post->post_type) {
			case $this->post_types['menu_item']:

				$append_content .= $this->get_view()->render_html('theme-support/single-' . $this->post_types['menu_item'], array(), false);

				break;
			case $this->post_types['order']:
				$append_content .= $this->get_view()->render_html('', array(), false);
				break;
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

		if ($this->template_mode() == 'plugin') {
			if (!empty($post) && is_single() && in_array(get_post_type(), $this->post_types)) {
				foreach ($this->post_types as $post_type) {
					if (basename($template) != "single-$post_type.php") {
						$path = MP_RM_TEMPLATES_PATH . "single-$post_type.php";
						if ($post->post_type == $post_type && file_exists($path)) {
							$template = $path;
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
			$plugin_array['mp_restaurant_menu'] = $path;
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

	public function disable_autosave() {
		global $post;
		if (!empty($post) && $post->post_type == 'mprm_order') {
			wp_dequeue_script('autosave');
		}
	}

	/**
	 * @return mixed|void
	 */
	public function get_settings_tabs() {
		$settings = $this->get_registered_settings();
		$tabs = array();
		$tabs['general'] = __('General', 'mp-restaurant-menu');
		$tabs['gateways'] = __('Payment Gateways', 'mp-restaurant-menu');
		$tabs['checkout'] = __('Checkout Settings', 'mp-restaurant-menu');
		$tabs['emails'] = __('Emails', 'mp-restaurant-menu');
		$tabs['styles'] = __('Styles', 'mp-restaurant-menu');
		$tabs['taxes'] = __('Taxes', 'mp-restaurant-menu');
		if (!empty($settings['extensions'])) {
			$tabs['extensions'] = __('Extensions', 'mp-restaurant-menu');
		}
		if (!empty($settings['licenses'])) {
			$tabs['licenses'] = __('Licenses', 'mp-restaurant-menu');
		}
		$tabs['misc'] = __('Misc', 'mp-restaurant-menu');
		return apply_filters('mprm_settings_tabs', $tabs);
	}
}

