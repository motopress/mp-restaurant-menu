<?php
namespace mp_restaurant_menu\classes\models;

use mp_restaurant_menu\classes\Model;

class Emails extends Model {
	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_email_body_content($payment_id = 0, $payment_data = array()) {
		$default_email_body = __("Dear", "mp-restaurant-menu") . " {name},\n";
		$default_email_body .= __("Thank you for your purchase. Please click on the link(s) below to menu_item your files.", "mp-restaurant-menu") . "\n";
		$default_email_body .= "{menu_item_list}\n";
		$default_email_body .= "{sitename}";
		$email = $this->get('settings')->get_option('purchase_receipt', false);
		$email = $email ? stripslashes($email) : $default_email_body;
		$email_body = apply_filters('mprm_email_template_wpautop', true) ? wpautop($email) : $email;
		$email_body = apply_filters('mprm_purchase_receipt_' . $this->get('settings_emails')->get_template(), $email_body, $payment_id, $payment_data);
		return apply_filters('mprm_purchase_receipt', $email_body, $payment_id, $payment_data);
	}

	public function email_preview_template_tags($message) {
		$menu_item_list = '<ul>';
		$menu_item_list .= '<li>' . __('Sample Product Title', 'mp-restaurant-menu') . '<br />';
		$menu_item_list .= '<div>';
		$menu_item_list .= '<a href="#">' . __('Sample Download File Name', 'mp-restaurant-menu') . '</a> - <small>' . __('Optional notes about this menu_item.', 'mp-restaurant-menu') . '</small>';
		$menu_item_list .= '</div>';
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

	public function email_test_purchase_receipt() {
		$from_name = $this->get('settings')->get_option('from_name', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
		$from_name = apply_filters('mprm_purchase_from_name', $from_name, 0, array());

		$from_email = $this->get('settings')->get_option('from_name', get_bloginfo('admin_email'));
		$from_email = apply_filters('mprm_test_purchase_from_address', $from_email, 0, array());

		$subject = $this->get('settings')->get_option('purchase_subject', __('Purchase Receipt', 'mp-restaurant-menu'));
		$subject = apply_filters('mprm_purchase_subject', wp_strip_all_tags($subject), 0);
		$subject = '';// edd_do_email_tags($subject, 0);

		$heading = $this->get('settings')->get_option('purchase_heading', __('Purchase Receipt', 'mp-restaurant-menu'));
		$heading = apply_filters('mprm_purchase_heading', $heading, 0, array());

		$attachments = apply_filters('mprm_receipt_attachments', array(), 0, array());
		$message = '';//edd_do_email_tags($this->get_email_body_content(0, array()), 0);

		$emails = $this->get('settings_emails');
		$emails->__set('from_name', $from_name);
		$emails->__set('from_email', $from_email);
		$emails->__set('heading', $heading);
		$headers = apply_filters('mprm_receipt_headers', $emails->get_headers(), 0, array());
		$emails->__set('headers', $headers);
		//$emails->send(mprm_get_admin_notice_emails(), $subject, $message, $attachments);
	}

	public function is_email_banned($email = '') {
		if (empty($email)) {
			return false;
		}
		$banned_emails = $this->get_banned_emails();
		if (!is_array($banned_emails) || empty($banned_emails)) {
			return false;
		}
		foreach ($banned_emails as $banned_email) {
			if (is_email($banned_email)) {
				$ret = ($banned_email == trim($email) ? true : false);
			} else {
				$ret = (stristr(trim($email), $banned_email) ? true : false);
			}
			if (true === $ret) {
				break;
			}
		}
		return apply_filters('mprm_is_email_banned', $ret, $email);
	}

	public function get_banned_emails() {
		$emails = array_map('trim', $this->get('settings')->get_option('banned_emails', array()));
		return apply_filters('mprm_get_banned_emails', $emails);
	}

	public function trigger_purchase_receipt($payment_id) {
		// Make sure we don't send a purchase receipt while editing a payment
		if (isset($_POST['mprm-action']) && 'edit_payment' == $_POST['mprm-action']) {
			return;
		}
		// Send email with secure menu_item link
		$this->email_purchase_receipt($payment_id);
	}

	public function email_purchase_receipt($payment_id, $admin_notice = true) {
		$payment_data = $this->get('payments')->get_payment_meta($payment_id);
		$from_name = $this->get('settings')->get_option('from_name', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
		$from_name = apply_filters('mprm_purchase_from_name', $from_name, $payment_id, $payment_data);
		$from_email = $this->get('settings')->get_option('from_email', get_bloginfo('admin_email'));
		$from_email = apply_filters('mprm_purchase_from_address', $from_email, $payment_id, $payment_data);
		$to_email = $this->get('payments')->get_payment_user_email($payment_id);
		$subject = $this->get('settings')->get_option('purchase_subject', __('Purchase Receipt', 'mp-restaurant-menu'));
		$subject = apply_filters('mprm_purchase_subject', wp_strip_all_tags($subject), $payment_id);
		$subject = edd_do_email_tags($subject, $payment_id);
		$heading = $this->get('settings')->get_option('purchase_heading', __('Purchase Receipt', 'mp-restaurant-menu'));
		$heading = apply_filters('mprm_purchase_heading', $heading, $payment_id, $payment_data);
		$attachments = apply_filters('mprm_receipt_attachments', array(), $payment_id, $payment_data);
		//$message = edd_do_email_tags($this->get_email_body_content($payment_id, $payment_data), $payment_id);
		$message = '';
		$emails = $this->get('settings_emails');
		$emails->__set('from_name', $from_name);
		$emails->__set('from_email', $from_email);
		$emails->__set('heading', $heading);

		$headers = apply_filters('mprm_receipt_headers', $emails->get_headers(), $payment_id, $payment_data);
		$emails->__set('headers', $headers);
		$emails->send($to_email, $subject, $message, $attachments);
		if ($admin_notice && !$this->admin_notices_disabled($payment_id)) {
			do_action('mprm_admin_sale_notice', $payment_id, $payment_data);
		}
	}

	public function admin_notices_disabled($payment_id = 0) {
		$ret = $this->get('settings')->get_option('disable_admin_notices', false);
		return (bool)apply_filters('mprm_admin_notices_disabled', $ret, $payment_id);
	}

	public function resend_purchase_receipt($data) {
		$purchase_id = absint($data['purchase_id']);
		if (empty($purchase_id)) {
			return;
		}
		if (!current_user_can('edit_shop_payments')) {
			wp_die(__('You do not have permission to edit this payment record', 'mp-restaurant-menu'), __('Error', 'mp-restaurant-menu'), array('response' => 403));
		}
		$this->email_purchase_receipt($purchase_id, false);
		// Grab all menu_items of the purchase and update their file menu_item limits, if needed
		// This allows admins to resend purchase receipts to grant additional file menu_items
		//$menu_items = $this->get('payments')->get_payment_meta_cart_details($purchase_id, true);
//		if (is_array($menu_items)) {
//			foreach ($menu_items as $menu_item) {
//				$limit = edd_get_file_menu_item_limit($menu_item['id']);
//				if (!empty($limit)) {
//					edd_set_file_menu_item_limit_override($menu_item['id'], $purchase_id);
//				}
//			}
//		}
		wp_redirect(add_query_arg(array('mprm-message' => 'email_sent', 'mprm-action' => false, 'purchase_id' => false)));
		exit;
	}

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

	public function admin_email_notice($payment_id = 0, $payment_data = array()) {
		$payment_id = absint($payment_id);
		if (empty($payment_id)) {
			return;
		}
		if (!$this->get('payments')->get_payment_by('id', $payment_id)) {
			return;
		}
		$from_name = $this->get('settings')->get_option('from_name', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
		$from_name = apply_filters('mprm_purchase_from_name', $from_name, $payment_id, $payment_data);
		$from_email = $this->get('settings')->get_option('from_email', get_bloginfo('admin_email'));
		$from_email = apply_filters('mprm_admin_sale_from_address', $from_email, $payment_id, $payment_data);
		$subject = $this->get('settings')->get_option('sale_notification_subject', sprintf(__('New menu_item purchase - Order #%1$s', 'mp-restaurant-menu'), $payment_id));
		$subject = apply_filters('mprm_admin_sale_notification_subject', wp_strip_all_tags($subject), $payment_id);
		//$subject = edd_do_email_tags($subject, $payment_id);
		$headers = "From: " . stripslashes_deep(html_entity_decode($from_name, ENT_COMPAT, 'UTF-8')) . " <$from_email>\r\n";
		$headers .= "Reply-To: " . $from_email . "\r\n";
		//$headers  .= "MIME-Version: 1.0\r\n";
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

	function get_sale_notification_body_content($payment_id = 0, $payment_data = array()) {
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
		$menu_item_list = '';
		$menu_items = maybe_unserialize($payment_data['menu_items']);
		if (is_array($menu_items)) {
			foreach ($menu_items as $menu_item) {
				$id = isset($payment_data['cart_details']) ? $menu_item['id'] : $menu_item;
				$title = get_the_title($id);
				if (isset($menu_item['options'])) {
					if (isset($menu_item['options']['price_id'])) {
						$title .= ' - ' . $this->get('menu_item')->get_price_option_name($id, $menu_item['options']['price_id'], $payment_id);
					}
				}
				$menu_item_list .= html_entity_decode($title, ENT_COMPAT, 'UTF-8') . "\n";
			}
		}
		$gateway = $this->get('gateways')->get_gateway_admin_label(get_post_meta($payment_id, '_mprm_order_gateway', true));
		$default_email_body = __('Hello', 'mp-restaurant-menu') . "\n\n" . sprintf(__('A %s purchase has been made', 'mp-restaurant-menu'), mprm_get_label_plural()) . ".\n\n";
		$default_email_body .= sprintf(__('%s sold:', 'mp-restaurant-menu'), mprm_get_label_plural()) . "\n\n";
		$default_email_body .= $menu_item_list . "\n\n";
		$default_email_body .= __('Purchased by: ', 'mp-restaurant-menu') . " " . html_entity_decode($name, ENT_COMPAT, 'UTF-8') . "\n";
		$default_email_body .= __('Amount: ', 'mp-restaurant-menu') . " " . html_entity_decode(mprm_currency_filter(mprm_format_amount(mprm_get_payment_amount($payment_id))), ENT_COMPAT, 'UTF-8') . "\n";
		$default_email_body .= __('Payment Method: ', 'mp-restaurant-menu') . " " . $gateway . "\n\n";
		$default_email_body .= __('Thank you', 'mp-restaurant-menu');
		$email = $this->get('settings')->get_option('sale_notification', false);
		$email = $email ? stripslashes($email) : $default_email_body;
		$email_body = ''; //edd_email_template_tags( $email, $payment_data, $payment_id, true );
		$email_body = ''; //edd_do_email_tags($email, $payment_id);
		$email_body = '';
		$email_body = apply_filters('mprm_email_template_wpautop', true) ? wpautop($email_body) : $email_body;
		return apply_filters('mprm_sale_notification', $email_body, $payment_id, $payment_data);
	}

	public function get_admin_notice_emails() {
		$emails = $this->get('settings')->get_option('admin_notice_emails', false);
		$emails = strlen(trim($emails)) > 0 ? $emails : get_bloginfo('admin_email');
		$emails = array_map('trim', explode("\n", $emails));
		return apply_filters('mprm_admin_notice_emails', $emails);
	}

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

	public function init_action() {
		add_action('mprm_admin_sale_notice', array($this, 'admin_email_notice'), 10, 2);
		add_action('mprm_complete_purchase', array($this, 'trigger_purchase_receipt'), 999, 1);
		add_action('mprm_send_test_email', array($this, 'send_test_email'));
		add_action('mprm_email_links', array($this, 'resend_purchase_receipt'));
		add_action('mprm_insert_user', array($this, 'new_user_notification'), 10, 2);
	}
}