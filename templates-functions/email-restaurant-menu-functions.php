<?php
use mp_restaurant_menu\classes\models;

/**
 * @param $tag
 * @param $description
 * @param $func
 */
function mprm_add_email_tag($tag, $description, $func) {
	models\Emails::get_instance()->add($tag, $description, $func);
}

/**
 * Remove an email tag
 *
 * @since 1.9
 *
 * @param string $tag Email tag to remove hook from
 */
function mprm_remove_email_tag($tag) {
	models\Emails::get_instance()->remove($tag);
}

/**
 * @param $tag
 *
 * @return bool
 */
function mprm_email_tag_exists($tag) {
	return models\Emails::get_instance()->email_tag_exists($tag);
}

/**
 *
 * Get email tags
 * @return mixed
 */
function mprm_get_email_tags() {
	return models\Emails::get_instance()->get_tags();
}


/**
 * @return string
 */
function mprm_get_emails_tags_list() {
	$list = '';

	$email_tags = mprm_get_email_tags();

	if (count($email_tags) > 0) {

		foreach ($email_tags as $email_tag) {
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';
		}
	}

	return $list;
}

/**
 * @param $content
 * @param $order_id
 *
 * @return mixed|void
 */
function mprm_do_email_tags($content, $order_id) {

	$content = models\Emails::get_instance()->do_tags($content, $order_id);

	$content = apply_filters('mprm_email_template_tags', $content, mprm_get_payment_meta($order_id), $order_id);

	return $content;
}

/**
 *
 */
function mprm_load_email_tags() {
	do_action('mprm_add_email_tags');
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_menu_item_list($order_id) {
	$payment = new models\Order($order_id);

	$payment_data = $payment->get_meta();
	$menu_item_list = '<ul>';
	$cart_items = $payment->cart_details;
	$email = $payment->email;

	if ($cart_items) {
		$show_names = apply_filters('mprm_email_show_names', true);
		$show_links = apply_filters('mprm_email_show_links', true);

		foreach ($cart_items as $item) {

			if (mprm_use_skus()) {
				$sku = mprm_get_menu_item_sku($item['id']);
			}

			if (mprm_item_quantities_enabled()) {
				$quantity = $item['quantity'];
			}

			$price_id = mprm_get_cart_item_price_id($item);
			if ($show_names) {

				$title = '<strong class="mprm-' . get_post($item['id'])->post_type . '">' . get_the_title($item['id']) . '</strong>';

				if (!empty($quantity) && $quantity > 1) {
					$title .= "&nbsp;&ndash;&nbsp;" . $quantity . ' x ' . mprm_currency_filter(mprm_format_amount($item['item_price']));
//					$title .= "&nbsp;&ndash;&nbsp;" . __('Quantity', 'mp-restaurant-menu') . ': ' . $quantity;
				}

				if (!empty($sku)) {
					$title .= "&nbsp;&ndash;&nbsp;" . __('SKU', 'mp-restaurant-menu') . ': ' . $sku;
				}
//
//				if ($price_id !== null) {
//					$title .= "&nbsp;&ndash;&nbsp;" . mprm_item_quantities_enabled();
//				}

				$menu_item_list .= '<li>' . apply_filters('mprm_email_receipt_menu_item_title', $title, $item, $price_id, $order_id) . '<br/>';
			}


			if ('' != mprm_get_menu_item_notes($item['id'])) {
				$menu_item_list .= ' &mdash; <small>' . mprm_get_menu_item_notes($item['id']) . '</small>';
			}


			if ($show_names) {
				$menu_item_list .= '</li>';
			}
		}
	}
	$menu_item_list .= '</ul>';

	return $menu_item_list;
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_menu_item_list_plain($order_id) {
	$payment = new models\Order($order_id);

	$payment_data = $payment->get_meta();
	$cart_items = $payment->cart_details;
	$email = $payment->email;
	$menu_item_list = '';

	if ($cart_items) {
		$show_names = apply_filters('mprm_email_show_names', true);
		$show_links = apply_filters('mprm_email_show_links', true);

		foreach ($cart_items as $item) {

			if (mprm_use_skus()) {
				$sku = mprm_get_menu_item_sku($item['id']);
			}

			if (mprm_item_quantities_enabled()) {
				$quantity = $item['quantity'];
			}

			$price_id = mprm_get_cart_item_price_id($item);
			if ($show_names) {

				$title = get_the_title($item['id']);

				if (!empty($quantity) && $quantity > 1) {
//					$title .= __('Quantity', 'mp-restaurant-menu') . ': ' . $quantity;
					$title .= ' ' . $quantity . ' x ' . mprm_currency_filter(mprm_format_amount($item['item_price']));
				}

				if (!empty($sku)) {
					$title .= __('SKU', 'mp-restaurant-menu') . ': ' . $sku;
				}

				if ($price_id !== null) {
					$title .= mprm_item_quantities_enabled();
				}

				$menu_item_list .= "\n";

				$menu_item_list .= apply_filters('mprm_email_receipt_menu_item_title', $title, $item, $price_id, $order_id) . "\n";
			}

			if ('' != mprm_get_menu_item_notes($item['id'])) {
				$menu_item_list .= "\n";
				$menu_item_list .= mprm_get_menu_item_notes($item['id']) . "\n";
			}
		}
	}

	return $menu_item_list;
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_first_name($order_id) {
	$payment = new models\Order($order_id);
	$user_info = $payment->user_info;

	if (empty($user_info)) {
		return '';
	}

	$email_name = mprm_get_email_names($user_info);

	return $email_name['name'];
}

/**
 * @param $user_info
 *
 * @return array
 */
function mprm_get_email_names($user_info) {
	$email_names = array();
	$user_info = maybe_unserialize($user_info);

	$email_names['fullname'] = '';
	if (isset($user_info['id']) && $user_info['id'] > 0 && isset($user_info['first_name'])) {
		$user_data = get_userdata($user_info['id']);
		$email_names['name'] = $user_info['first_name'];
		$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names['username'] = $user_data->user_login;
	} elseif (isset($user_info['first_name'])) {
		$email_names['name'] = $user_info['first_name'];
		$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names['username'] = $user_info['first_name'];
	} else {
		$email_names['name'] = $user_info['email'];
		$email_names['username'] = $user_info['email'];
	}

	return $email_names;
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_fullname($order_id) {
	$payment = new models\Order($order_id);
	$user_info = $payment->user_info;

	if (empty($user_info)) {
		return '';
	}

	$email_name = mprm_get_email_names($user_info);
	return $email_name['fullname'];
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_username($order_id) {
	$payment = new models\Order($order_id);
	$user_info = $payment->user_info;

	if (empty($user_info)) {
		return '';
	}

	$email_name = mprm_get_email_names($user_info);
	return $email_name['username'];
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_user_email($order_id) {
	$payment = new models\Order($order_id);

	return $payment->email;
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_billing_address($order_id) {

	$user_info = mprm_get_payment_meta_user_info($order_id);
	$user_address = !empty($user_info['address']) ? $user_info['address'] : array('line1' => '', 'line2' => '', 'city' => '', 'country' => '', 'state' => '', 'zip' => '');

	$return = $user_address['line1'] . "\n";
	if (!empty($user_address['line2'])) {
		$return .= $user_address['line2'] . "\n";
	}
	$return .= $user_address['city'] . ' ' . $user_address['zip'] . ' ' . $user_address['state'] . "\n";
	$return .= $user_address['country'];

	return $return;
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_date($order_id) {
	$payment = new \mp_restaurant_menu\classes\models\Order($order_id);
	return date_i18n(get_option('date_format'), strtotime($payment->date));
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_subtotal($order_id) {
	$payment = new models\Order($order_id);
	$subtotal = mprm_currency_filter(mprm_format_amount($payment->subtotal), $payment->currency);
	return html_entity_decode($subtotal, ENT_COMPAT, 'UTF-8');
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_tax($order_id) {
	$payment = new models\Order($order_id);
	$tax = mprm_currency_filter(mprm_format_amount($payment->tax), $payment->currency);
	return html_entity_decode($tax, ENT_COMPAT, 'UTF-8');
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_price($order_id) {
	$payment = new models\Order($order_id);
	$price = mprm_currency_filter(mprm_format_amount($payment->total), $payment->currency);
	return html_entity_decode($price, ENT_COMPAT, 'UTF-8');
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_payment_id($order_id) {
	$payment = new models\Order($order_id);
	return $payment->number;
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_receipt_id($order_id) {
	$payment = new models\Order($order_id);
	return $payment->key;
}


/**
 * @param $order_id
 *
 * @return mixed|void
 */
function mprm_email_tag_payment_method($order_id) {
	$payment = new models\Order($order_id);
	return models\Gateways::get_instance()->get_gateway_checkout_label($payment->gateway);
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_sitename($order_id) {
	return wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
}


/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_receipt_link($order_id) {
	$receipt_url = esc_url(add_query_arg(array(
		'payment_key' => mprm_get_payment_key($order_id)
	), mprm_get_success_page_uri()));
	$formatted = sprintf(__('%1$sView it in your browser %2$s', 'mprm'), '<a href="' . $receipt_url . '">', '&raquo;</a>');

	if (mprm_get_option('email_template') !== 'none') {
		return $formatted;
	} else {
		return $receipt_url;
	}
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_discount_codes($order_id) {
	$user_info = mprm_get_payment_meta_user_info($order_id);

	$discount_codes = '';

	if (isset($user_info['discount']) && $user_info['discount'] !== 'none') {
		$discount_codes = $user_info['discount'];
	}

	return $discount_codes;
}

/**
 * @param $order_id
 *
 * @return string
 */
function mprm_email_tag_ip_address($order_id) {
	$payment = new models\Order($order_id);
	return $payment->ip;
}

/**
 * @return mixed|string|void
 */
function mprm_get_default_sale_notification_email() {

	$default_email_body = __('A new purchase has been made!', 'mp-restaurant-menu') . "\n\n";
	$default_email_body .= __('Purchased products:', 'mp-restaurant-menu') . "\n";
	$default_email_body .= '{menu_item_list}' . "\n\n";
	$default_email_body .= __('Purchased by: ', 'mp-restaurant-menu') . " " . "{fullname}" . "\n";
	$default_email_body .= __('Amount: ', 'mp-restaurant-menu') . " " . "{price}" . "\n";
	$default_email_body .= __('Payment Method: ', 'mp-restaurant-menu') . " " . "{payment_method}" . "\n\n";
	$default_email_body .= __('Thank you', 'mp-restaurant-menu');

	$message = mprm_get_option('sale_notification', false);
	$message = !empty($message) ? $message : $default_email_body;

	return $message;
}
