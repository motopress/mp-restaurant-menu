<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

/**
 * Class Emails
 *
 * @package mp_restaurant_menu\classes\models
 */
class Emails extends Model {

	protected static $instance;

	private $tags;

	private $payment_id;

	/**
	 * @return Emails
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Preview emails template
	 *
	 * @param $message
	 *
	 * @return mixed|string|void
	 */
	public function email_preview_template_tags($message) {
		$menu_item_list = '<ul>';
		$menu_item_list .= '<li>' . __('Sample Product Title', 'mp-restaurant-menu');
		$menu_item_list .= '</li>';
		$menu_item_list .= '</ul>';
		$file_urls = esc_html(trailingslashit(get_site_url()) . 'test.zip?test=key&key=123');
		$price = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price(10.50, true));
		$gateway = 'PayPal';
		$receipt_id = strtolower(md5(uniqid()));
		$notes = __('These are some sample notes added to a product.', 'mp-restaurant-menu');
		$tax = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price(1.00, true));
		$sub_total = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price(9.50, true));
		$payment_id = rand(1, 100);
		$user = wp_get_current_user();

		$message = str_replace('{menu_item_list}', $menu_item_list, $message);
		$message = str_replace('{file_urls}', $file_urls, $message);
		$message = str_replace('{name}', $user->display_name, $message);
		$message = str_replace('{fullname}', $user->display_name, $message);
		$message = str_replace('{username}', $user->user_login, $message);
		$message = str_replace('{date}', date(get_option('date_format'), current_time('timestamp')), $message);
		$message = str_replace('{subtotal}', $sub_total, $message);
		$message = str_replace('{tax}', $tax, $message);
		$message = str_replace('{price}', $price, $message);
		$message = str_replace('{receipt_id}', $receipt_id, $message);
		$message = str_replace('{payment_method}', $gateway, $message);
		$message = str_replace('{sitename}', get_bloginfo('name'), $message);
		$message = str_replace('{product_notes}', $notes, $message);
		$message = str_replace('{payment_id}', $payment_id, $message);
		$message = str_replace('{receipt_link}', sprintf(__('%1$sView it in your browser.%2$s', 'mp-restaurant-menu'), '<a href="' . esc_url(add_query_arg(array('payment_key' => $receipt_id, 'mprm_action' => 'view_receipt'), home_url())) . '">', '</a>'), $message);
		$message = apply_filters('mprm_email_preview_template_tags', $message);

		return apply_filters('mprm_email_template_wpautop', true) ? wpautop($message) : $message;
	}

	/**
	 * Check banned emails
	 *
	 * @param string $email
	 *
	 * @return bool|mixed
	 */
	public function is_email_banned($email = '') {
		if (empty($email)) {
			return false;
		}

		$return = false;
		$banned_emails = $this->get_banned_emails();

		if (!is_array($banned_emails) || empty($banned_emails)) {
			return false;
		}

		foreach ($banned_emails as $banned_email) {
			if (is_email($banned_email)) {
				$return = ($banned_email == trim($email) ? true : false);
			} else {
				$return = (stristr(trim($email), $banned_email) ? true : false);
			}
			if (true === $return) {
				break;
			}
		}
		return apply_filters('mprm_is_email_banned', $return, $email);
	}

	/**
	 * Banned emails
	 * @return mixed
	 */
	public function get_banned_emails() {
		$emails = array_map('trim', $this->get('settings')->get_option('banned_emails', array()));
		return apply_filters('mprm_get_banned_emails', $emails);
	}

	/**
	 * Trigger purchase receipt
	 *
	 * @param $payment_id
	 */
	public function trigger_purchase_receipt($payment_id) {
		// Make sure we don't send a purchase receipt while editing a payment
		if (isset($_POST['mprm-action']) && 'edit_payment' == $_POST['mprm-action']) {
			return;
		}
		// Send email with secure menu_item link
		$this->email_purchase_receipt($payment_id);
	}

	/**
	 * Email purchase
	 *
	 * @param $payment_id
	 * @param bool $admin_notice
	 */
	public function email_purchase_receipt($payment_id, $admin_notice = true) {

		if (!$this->get('payments')->get_payment_by('id', $payment_id)) {
			return;
		}

		$payment_data = $this->get('payments')->get_payment_meta($payment_id);

		if (!empty($payment_data)) {

			$from_name = $this->get('settings')->get_option('from_name', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
			$from_name = apply_filters('mprm_purchase_from_name', $from_name, $payment_id, $payment_data);
			$from_email = $this->get('settings')->get_option('from_email', get_bloginfo('admin_email'));
			$from_email = apply_filters('mprm_purchase_from_address', $from_email, $payment_id, $payment_data);
			$to_email = $this->get('payments')->get_payment_user_email($payment_id);
			$subject = $this->get('settings')->get_option('purchase_subject', __('Purchase Receipt', 'mp-restaurant-menu'));
			$subject = apply_filters('mprm_purchase_subject', wp_strip_all_tags($subject), $payment_id);
			$subject = mprm_do_email_tags($subject, $payment_id);
			$heading = $this->get('settings')->get_option('purchase_heading', __('Purchase Receipt', 'mp-restaurant-menu'));
			$heading = apply_filters('mprm_purchase_heading', $heading, $payment_id, $payment_data);
			$attachments = apply_filters('mprm_receipt_attachments', array(), $payment_id, $payment_data);
			$message = mprm_do_email_tags($this->get_email_body_content($payment_id, $payment_data), $payment_id);
			$emails = $this->get('settings_emails');

			$emails->__set('from_name', $from_name);
			$emails->__set('from_email', $from_email);
			$emails->__set('heading', $heading);

			$headers = apply_filters('mprm_receipt_headers', $emails->get_headers(), $payment_id, $payment_data);
			$emails->__set('headers', $headers);
			$emails->send($to_email, $subject, $message, $attachments);

			if (apply_filters('mprm_send_admin_notice', $admin_notice, $payment_id) && !$this->admin_notices_disabled($payment_id)) {
				do_action('mprm_admin_sale_notice', $payment_id, $payment_data);
			}
		}
	}

	/**
	 * Get email body
	 *
	 * @param int $payment_id
	 * @param array $payment_data
	 *
	 * @return mixed
	 */
	public function get_email_body_content($payment_id = 0, $payment_data = array()) {
		$default_email_body = __("Dear", "mp-restaurant-menu") . " {name},\n\n";
		$default_email_body .= __("Thank you for your purchase. Your order details are shown below for your reference:", "mp-restaurant-menu") . "\n";
		$default_email_body .= "{menu_item_list}\n";
		$default_email_body .= "Total: {price}\n\n";
		$default_email_body .= "{receipt_link}";
		$email = $this->get('settings')->get_option('purchase_receipt', false);
		$email = $email ? stripslashes($email) : $default_email_body;
		$email_body = apply_filters('mprm_email_template_wpautop', true) ? wpautop($email) : $email;
		$email_body = apply_filters('mprm_purchase_receipt_' . $this->get('settings_emails')->get_template(), $email_body, $payment_id, $payment_data);

		return apply_filters('mprm_purchase_receipt', $email_body, $payment_id, $payment_data);
	}

	/**
	 * Admin notices disabled
	 *
	 * @param int $payment_id
	 *
	 * @return bool
	 */
	public function admin_notices_disabled($payment_id = 0) {
		$ret = $this->get('settings')->get_option('disable_admin_notices', false);
		return (bool)apply_filters('mprm_admin_notices_disabled', $ret, $payment_id);
	}

	/**
	 * Resend purchase receipt
	 *
	 * @param $data
	 */
	public function resend_purchase_receipt($data) {
		$purchase_id = absint($data['purchase_id']);
		if (empty($purchase_id)) {
			return;
		}
		if (!current_user_can('edit_shop_payments')) {
			wp_die(__('You do not have permission to edit this payment record', 'mp-restaurant-menu'), __('Error', 'mp-restaurant-menu'), array('response' => 403));
		}
		$this->email_purchase_receipt($purchase_id, false);

		wp_redirect(add_query_arg(array('mprm-message' => 'email_sent', 'mprm-action' => false, 'purchase_id' => false)));
		exit;
	}

	/**
	 * Send test email
	 *
	 * @param $data
	 */
	public function send_test_email($data) {
		if (!wp_verify_nonce($data['_wpnonce'], 'mprm-test-email')) {
			return;
		}
		// Send a test email
		$this->email_test_purchase_receipt();
		// Remove the test email query arg
		wp_redirect(remove_query_arg('mprm_action'));
		exit;
	}

	/**
	 * Test email
	 */
	public function email_test_purchase_receipt() {
		$from_name = $this->get('settings')->get_option('from_name', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
		$from_name = apply_filters('mprm_purchase_from_name', $from_name, 0, array());

		$from_email = $this->get('settings')->get_option('from_name', get_bloginfo('admin_email'));
		$from_email = apply_filters('mprm_test_purchase_from_address', $from_email, 0, array());

		$subject = $this->get('settings')->get_option('purchase_subject', __('Purchase Receipt1', 'mp-restaurant-menu'));
		$subject = apply_filters('mprm_purchase_subject', wp_strip_all_tags($subject), 0);
		$subject = mprm_do_email_tags($subject, 0);

		$heading = $this->get('settings')->get_option('purchase_heading', __('Purchase Receipt1', 'mp-restaurant-menu'));
		$heading = apply_filters('mprm_purchase_heading', $heading, 0, array());

		$attachments = apply_filters('mprm_receipt_attachments', array(), 0, array());
		$message = mprm_do_email_tags($this->get_email_body_content(0, array()), 0);

		$emails = $this->get('settings_emails');
		$emails->__set('from_name', $from_name);
		$emails->__set('from_email', $from_email);
		$emails->__set('heading', $heading);
		$headers = apply_filters('mprm_receipt_headers', $emails->get_headers(), 0, array());
		$emails->__set('headers', $headers);
		$emails->send($this->get_admin_notice_emails(), $subject, $message, $attachments);
	}

	/**
	 * Admin emails notice
	 * @return mixed
	 */
	public function get_admin_notice_emails() {
		$emails = $this->get('settings')->get_option('admin_notice_emails', false);
		$emails = strlen(trim($emails)) > 0 ? $emails : get_bloginfo('admin_email');
		$emails = array_map('trim', explode("\n", $emails));
		return apply_filters('mprm_admin_notice_emails', $emails);
	}

	/**
	 * Admin email notice
	 *
	 * @param int $payment_id
	 * @param array $payment_data
	 */
	public function admin_email_notice($payment_id = 0, $payment_data = array()) {
		$payment_id = absint($payment_id);
		if (empty($payment_id)) {
			return;
		}
		$payment_object = $this->get('payments')->get_payment_by('id', $payment_id);

		if (!$payment_object) {
			return;
		}

		$from_name = $this->get('settings')->get_option('from_name', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
		$from_name = apply_filters('mprm_purchase_from_name', $from_name, $payment_id, $payment_data);
		$from_email = $this->get('settings')->get_option('from_email', get_bloginfo('admin_email'));
		$from_email = apply_filters('mprm_admin_sale_from_address', $from_email, $payment_id, $payment_data);
		$subject = $this->get('settings')->get_option('sale_notification_subject', sprintf(__('New purchase - Order #%1$s', 'mp-restaurant-menu'), $payment_id));
		$subject = apply_filters('mprm_admin_sale_notification_subject', wp_strip_all_tags($subject), $payment_id);
		$subject = mprm_do_email_tags($subject, $payment_id);
		$headers = "From: " . stripslashes_deep(html_entity_decode($from_name, ENT_COMPAT, 'UTF-8')) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$headers = apply_filters('mprm_admin_sale_notification_headers', $headers, $payment_id, $payment_data);
		$attachments = apply_filters('mprm_admin_sale_notification_attachments', array(), $payment_id, $payment_data);
		$message = $this->get_sale_notification_body_content($payment_id, $payment_data);
		$emails = $this->get('settings_emails');
		$emails->__set('from_name', $from_name);
		$emails->__set('from_email', $from_email);
		$emails->__set('headers', $headers);
		$emails->__set('heading', __('New Sale!', 'mp-restaurant-menu'));
		$emails->send($this->get_admin_notice_emails(), $subject, $message, $attachments);

	}

	/**
	 * Sale notification body content
	 *
	 * @param int $payment_id
	 * @param array $payment_data
	 *
	 * @return mixed
	 */
	public function get_sale_notification_body_content($payment_id = 0, $payment_data = array()) {

		$user_info = maybe_unserialize($payment_data['user_info']);
		$email = $this->get('payments')->get_payment_user_email($payment_id);
		if (isset($user_info['id']) && $user_info['id'] > 0) {
			$user_data = get_userdata($user_info['id']);
			$name = $user_data->display_name;
		} elseif (isset($user_info['first_name']) && isset($user_info['last_name'])) {
			$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
		} else {
			$name = $email;
		}
		$cart_details = $payment_data['cart_details'];
		$menu_item_list = '';
		$menu_items = maybe_unserialize($payment_data['menu_items']);

		if (is_array($menu_items)) {
			$menu_item_list .= '<ul>';
			foreach ($menu_items as $key => $menu_item) {
				foreach ($cart_details as $cart_item) {
					if ($menu_item['id'] == $cart_item['id']) {
						$price = $cart_item['price'];
					}
				}

				$id = isset($payment_data['cart_details']) ? $menu_item['id'] : $menu_item;
				$title = get_the_title($id);
				if (isset($menu_item['options'])) {
					if (isset($menu_item['options']['price_id'])) {
						$title .= ' ' . $menu_item['quantity'] . ' x ' . mprm_currency_filter(mprm_format_amount($price));
					}
				}
				$menu_item_list .= '<li style="margin-top: 0.5em;margin-bottom: 0.5em;">' . html_entity_decode($title, ENT_COMPAT, 'UTF-8') . '</li>';
			}
			$menu_item_list .= '</ul>';
		}
		$gateway = $this->get('gateways')->get_gateway_admin_label(get_post_meta($payment_id, '_mprm_order_gateway', true));

		$default_email_body = __('A new purchase has been made!', 'mp-restaurant-menu') . "\n\n";
		$default_email_body .= __('Purchased products:', 'mp-restaurant-menu') . "\n";
		$default_email_body .= $menu_item_list . "\n\n";
		$default_email_body .= __('Purchased by: ', 'mp-restaurant-menu') . " " . html_entity_decode($name, ENT_COMPAT, 'UTF-8') . "\n";
		$default_email_body .= __('Amount: ', 'mp-restaurant-menu') . " " . html_entity_decode(mprm_currency_filter(mprm_format_amount(mprm_get_payment_amount($payment_id))), ENT_COMPAT, 'UTF-8') . "\n";
		$default_email_body .= __('Payment Method: ', 'mp-restaurant-menu') . " " . $gateway . "\n\n";
		$default_email_body .= __('Thank you', 'mp-restaurant-menu');
		$email = $this->get('settings')->get_option('sale_notification', false);
		$email = $email ? stripslashes($email) : $default_email_body;
		$email_body = mprm_do_email_tags($email, $payment_id);
		$email_body = apply_filters('mprm_email_template_wpautop', true) ? wpautop($email_body) : $email_body;

		return apply_filters('mprm_sale_notification', $email_body, $payment_id, $payment_data);

	}

	/**
	 * New user notification
	 *
	 * @param int $user_id
	 * @param array $user_data
	 */
	public function new_user_notification($user_id = 0, $user_data = array()) {
		if (empty($user_id) || empty($user_data)) {
			return;
		}
		$from_name = $this->get('settings')->get_option('from_name', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
		$from_email = $this->get('settings')->get_option('from_email', get_bloginfo('admin_email'));
		$emails = $this->get('settings_emails');

		$emails->__set('from_name', $from_name);
		$emails->__set('from_email', $from_email);
		$admin_subject = sprintf(__('[%s] New User Registration', 'mp-restaurant-menu'), $from_name);
		$admin_heading = __('New user registration', 'mp-restaurant-menu');
		$admin_message = sprintf(__('Username: %s', 'mp-restaurant-menu'), $user_data['user_login']) . "\r\n\r\n";
		$admin_message .= sprintf(__('E-mail: %s', 'mp-restaurant-menu'), $user_data['user_email']) . "\r\n";
		$emails->__set('heading', $admin_heading);

		$emails->send(get_option('admin_email'), $admin_subject, $admin_message);

		$user_subject = sprintf(__('[%s] Your username and password', 'mp-restaurant-menu'), $from_name);
		$user_heading = __('Your account info', 'mp-restaurant-menu');
		$user_message = sprintf(__('Username: %s', 'mp-restaurant-menu'), $user_data['user_login']) . "\r\n";
		$user_message .= sprintf(__('Password: %s'), __('[Password entered at checkout]', 'mp-restaurant-menu')) . "\r\n";
		$user_message .= '<a href="' . wp_login_url() . '"> ' . esc_attr__('Click Here to Log In', 'mp-restaurant-menu') . ' &raquo;</a>' . "\r\n";
		$emails->__set('heading', $user_heading);
		$emails->send($user_data['user_email'], $user_subject, $user_message);
	}

	/**
	 * Add tags
	 *
	 * @param $tag
	 * @param $description
	 * @param $func
	 */
	public function add($tag, $description, $func) {
		if (is_callable($func)) {
			$this->tags[$tag] = array(
				'tag' => $tag,
				'description' => $description,
				'func' => $func
			);
		}
	}

	/**
	 * Remove tags
	 *
	 * @param $tag
	 */
	public function remove($tag) {
		unset($this->tags[$tag]);
	}

	/**
	 * @return mixed
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Do tags
	 *
	 * @param $content
	 * @param $payment_id
	 *
	 * @return mixed
	 */
	public function do_tags($content, $payment_id) {

		// Check if there is atleast one tag added
		if (empty($this->tags) || !is_array($this->tags)) {
			return $content;
		}

		$this->payment_id = $payment_id;

		$new_content = preg_replace_callback("/{([A-z0-9\-\_]+)}/s", array($this, 'do_tag'), $content);

		$this->payment_id = null;

		return $new_content;
	}

	/**
	 * Do tag
	 *
	 * @param $m
	 *
	 * @return mixed
	 */
	public function do_tag($m) {
		// Get tag
		$tag = $m[1];
		// Return tag if tag not set
		if (!$this->email_tag_exists($tag)) {
			return $m[0];
		}
		return call_user_func($this->tags[$tag]['func'], $this->payment_id, $tag);
	}

	/**
	 * Exists tags
	 *
	 * @param $tag
	 *
	 * @return bool
	 */
	public function email_tag_exists($tag) {
		return array_key_exists($tag, $this->tags);
	}

	/**
	 * Setup  emails tags
	 */
	public function mprm_setup_email_tags() {

		$email_tags = array(
			array(
				'tag' => 'menu_item_list',
				'description' => __('A list of purchased products', 'mp-restaurant-menu'),
				'function' => 'text/html' == $this->get('settings_emails')->get_content_type() ? 'mprm_email_tag_menu_item_list' : 'mprm_email_tag_menu_item_list_plain'
			),
			array(
				'tag' => 'name',
				'description' => __("The buyer's first name", 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_first_name'
			),
			array(
				'tag' => 'fullname',
				'description' => __("The buyer's full name, first and last", 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_full_name'
			),
			array(
				'tag' => 'username',
				'description' => __("The buyer's user name on the site, if they registered an account", 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_username'
			),
			array(
				'tag' => 'user_email',
				'description' => __("The buyer's email address", 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_user_email'
			),
			array(
				'tag' => 'billing_address',
				'description' => __('The buyer\'s billing address', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_billing_address'
			),
			array(
				'tag' => 'date',
				'description' => __('The date of the purchase', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_date'
			),
			array(
				'tag' => 'subtotal',
				'description' => __('The price of the purchase before taxes', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_subtotal'
			),
			array(
				'tag' => 'tax',
				'description' => __('The taxed amount of the purchase', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_tax'
			),
			array(
				'tag' => 'price',
				'description' => __('The total price of the purchase', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_price'
			),
			array(
				'tag' => 'payment_id',
				'description' => __('The unique ID number for this purchase', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_payment_id'
			),
			array(
				'tag' => 'receipt_id',
				'description' => __('The unique ID number for this purchase receipt', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_receipt_id'
			),
			array(
				'tag' => 'payment_method',
				'description' => __('The method of payment used for this purchase', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_payment_method'
			),
			array(
				'tag' => 'sitename',
				'description' => __('Your site name', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_site_name'
			),
			array(
				'tag' => 'receipt_link',
				'description' => __('Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_receipt_link'
			),
			array(
				'tag' => 'order_notes',
				'description' => __('Order notes.', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_order_notes'
			),
			array(
				'tag' => 'phone',
				'description' => __('The buyer\'s phone.', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_phone'
			),
			array(
				'tag' => 'shipping_address',
				'description' => __('Shipping address.', 'mp-restaurant-menu'),
				'function' => 'mprm_email_tag_shipping_address'
			),

		);

		$email_tags = apply_filters('mprm_email_tags', $email_tags);

		foreach ($email_tags as $email_tag) {
			mprm_add_email_tag($email_tag['tag'], $email_tag['description'], $email_tag['function']);
		}

	}

	/**
	 * Action action
	 */
	public function init_action() {
		add_action('init', 'mprm_load_email_tags', -999);
		add_action('mprm_add_email_tags', array($this, 'mprm_setup_email_tags'));

		add_action('mprm_admin_sale_notice', array($this, 'admin_email_notice'), 10, 2);

		add_action('mprm_complete_purchase', array($this, 'trigger_purchase_receipt'), 999, 1);
		add_action('mprm_send_test_email', array($this, 'send_test_email'));
		add_action('mprm_email_links', array($this, 'resend_purchase_receipt'));
		add_action('mprm_insert_user', array($this, 'new_user_notification'), 10, 2);
	}
}