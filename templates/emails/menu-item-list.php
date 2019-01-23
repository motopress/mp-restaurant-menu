<ul>
	<?php foreach ($cart_items as $item) {
		$show_names = apply_filters('mprm_email_show_names', true);
		$show_links = apply_filters('mprm_email_show_links', true);
		$show_child = apply_filters('mprm_child_show_items', false);

		$sku = mprm_use_skus() ? mprm_get_menu_item_sku($item['id']) : '';
		$quantity = mprm_item_quantities_enabled() ? $item['quantity'] : '';
		$price_id = mprm_get_cart_item_price_id($item);
		$cart_item_post = get_post($item['id']);


		if ($show_names) {
			if (!$show_child && !empty($item['parent'])) {
				continue;
			}
			$item_title = get_the_title($item['id']) . ' ' . apply_filters('mprm_email_price_name', mprm_get_price_name($item['id']), $item, $order_id);

			if (!empty($quantity) && $quantity > 1) {
				$price = $quantity . ' x ' . mprm_currency_filter(mprm_format_amount($item['item_price']));
			} else {
				$price = mprm_currency_filter(mprm_format_amount($item['item_price']));
			}
			?><li><?php
				do_action('mprm_email_menu_item_before', $item);
				?><strong><?php echo $item_title ?></strong><span>&nbsp;–&nbsp;<?php echo $price ?></span><?php
					if (!empty($sku)) {
						?><br><span><?php echo "&nbsp;&ndash;&nbsp;" . __('SKU', 'mp-restaurant-menu') . ': ' . $sku ?></span><?php
					} ?>
				<?php if (('' != mprm_get_menu_item_notes($item['id']))) {
					?><span><?php echo ' &mdash; <small>' . mprm_get_menu_item_notes($item['id']) . '</small>' ?></span><?php
					} ?>
				<?php do_action('mprm_email_menu_item_after', $item, $order_id); ?></li><?php
		} else {
			?><li><span><?php echo ('' != mprm_get_menu_item_notes($item['id'])) ? ' &mdash; <small>' . mprm_get_menu_item_notes($item['id']) . '</small>' : '' ?></span></li><?php
		}
	} ?>
</ul>