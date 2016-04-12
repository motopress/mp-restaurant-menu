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

	function get_email_body_content($payment_id = 0, $payment_data = array()) {
		$default_email_body = __("Dear", "easy-digital-downloads") . " {name},\n\n";
		$default_email_body .= __("Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads") . "\n\n";
		$default_email_body .= "{download_list}\n\n";
		$default_email_body .= "{sitename}";

		$email = $this->get('settings')->get_option('purchase_receipt', false);
		$email = $email ? stripslashes($email) : $default_email_body;

		$email_body = apply_filters('mprm_email_template_wpautop', true) ? wpautop($email) : $email;

		$email_body = apply_filters('mprm_purchase_receipt_' . $this->get('settings_emails')->get_template(), $email_body, $payment_id, $payment_data);

		return apply_filters('mprm_purchase_receipt', $email_body, $payment_id, $payment_data);
	}

	function email_preview_template_tags($message) {
		$download_list = '<ul>';
		$download_list .= '<li>' . __('Sample Product Title', 'easy-digital-downloads') . '<br />';
		$download_list .= '<div>';
		$download_list .= '<a href="#">' . __('Sample Download File Name', 'easy-digital-downloads') . '</a> - <small>' . __('Optional notes about this download.', 'easy-digital-downloads') . '</small>';
		$download_list .= '</div>';
		$download_list .= '</li>';
		$download_list .= '</ul>';

		$file_urls = esc_html(trailingslashit(get_site_url()) . 'test.zip?test=key&key=123');

		$price = $this->get('menu_item')->currency_filter($this->get('menu_item')->get_formatting_price(10.50, true));

		$gateway = 'PayPal';

		$receipt_id = strtolower(md5(uniqid()));

		$notes = __('These are some sample notes added to a product.', 'easy-digital-downloads');

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
		$message = str_replace('{receipt_link}', sprintf(__('%1$sView it in your browser.%2$s', 'easy-digital-downloads'), '<a href="' . esc_url(add_query_arg(array('payment_key' => $receipt_id, 'edd_action' => 'view_receipt'), home_url())) . '">', '</a>'), $message);

		$message = apply_filters('mprm_email_preview_template_tags', $message);

		return apply_filters('mprm_email_template_wpautop', true) ? wpautop($message) : $message;
	}
}