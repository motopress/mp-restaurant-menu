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
		$default_email_body = __("Dear", "mp-restaurant-menu") . " {name},\n\n";
		$default_email_body .= __("Thank you for your purchase. Please click on the link(s) below to download your files.", "mp-restaurant-menu") . "\n\n";
		$default_email_body .= "{download_list}\n\n";
		$default_email_body .= "{sitename}";

		$email = $this->get('settings')->get_option('purchase_receipt', false);
		$email = $email ? stripslashes($email) : $default_email_body;

		$email_body = apply_filters('mprm_email_template_wpautop', true) ? wpautop($email) : $email;

		$email_body = apply_filters('mprm_purchase_receipt_' . $this->get('settings_emails')->get_template(), $email_body, $payment_id, $payment_data);

		return apply_filters('mprm_purchase_receipt', $email_body, $payment_id, $payment_data);
	}

	public function email_preview_template_tags($message) {
		$download_list = '<ul>';
		$download_list .= '<li>' . __('Sample Product Title', 'mp-restaurant-menu') . '<br />';
		$download_list .= '<div>';
		$download_list .= '<a href="#">' . __('Sample Download File Name', 'mp-restaurant-menu') . '</a> - <small>' . __('Optional notes about this download.', 'mp-restaurant-menu') . '</small>';
		$download_list .= '</div>';
		$download_list .= '</li>';
		$download_list .= '</ul>';

		$file_urls = esc_html(trailingslashit(get_site_url()) . 'test.zip?test=key&key=123');

		$price = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price(10.50, true));

		$gateway = 'PayPal';

		$receipt_id = strtolower(md5(uniqid()));

		$notes = __('These are some sample notes added to a product.', 'mp-restaurant-menu');

		$tax = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price(1.00, true));

		$sub_total = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price(9.50, true));

		$payment_id = rand(1, 100);

		$user = wp_get_current_user();

		$message = str_replace('{download_list}', $download_list, $message);
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
		//$subject = mprm_do_email_tags($subject, 0);

		$heading = $this->get('settings')->get_option('purchase_heading', __('Purchase Receipt', 'mp-restaurant-menu'));
		$heading = apply_filters('mprm_purchase_heading', $heading, 0, array());

		$attachments = apply_filters('mprm_receipt_attachments', array(), 0, array());
		//	$message = mprm_do_email_tags($this->get_email_body_content(0, array()), 0);

		$emails = $this->get('settings_emails');
		$emails->__set('from_name', $from_name);
		$emails->__set('from_email', $from_email);
		$emails->__set('heading', $heading);

		$headers = apply_filters('mprm_receipt_headers', $emails->get_headers(), 0, array());
		$emails->__set('headers', $headers);

		//$emails->send(mprm_get_admin_notice_emails(), $subject, $message, $attachments);
	}
}