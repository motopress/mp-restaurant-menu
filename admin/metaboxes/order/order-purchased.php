<?php global $post;

$order = mprm_get_order_object($post);
$cart_items = $order->cart_details;
$order_id = $order->ID;
$currency_code = $order->currency;

do_action('mprm_view_order_details_main_before', $order_id);

$column = mprm_item_quantities_enabled() ? 'mprm-three' : 'mprm-four';
?>
	<div id="mprm-purchased-wrapper">

		<?php if (is_array($cart_items)) :

			$i = 0;
			foreach ($cart_items as $key => $cart_item) : ?>

				<div class="mprm-row item">
					<?php
					$item_id = isset($cart_item['id']) ? $cart_item['id'] : $cart_item;
					$price = isset($cart_item['price']) ? $cart_item['price'] : false;
					$item_price = isset($cart_item['item_price']) ? $cart_item['item_price'] : $price;
					$price_id = isset($cart_item['item_number']['options']['price_id']) ? $cart_item['item_number']['options']['price_id'] : null;
					$quantity = isset($cart_item['quantity']) && $cart_item['quantity'] > 0 ? $cart_item['quantity'] : 1;
					$tax = isset($cart_item['tax']) ? $cart_item['tax'] : 0;

					if (false === $price) {
						$price = mprm_get_menu_item_final_price($item_id, $user_info, null);
					}
					?>
					<div class="item mprm-columns <?php echo $column ?>">
							<span class="mprm-<?php echo get_post_type($item_id) ?>">
								<a href="<?php echo admin_url('post.php?post=' . $item_id . '&action=edit'); ?>">
									<?php echo get_the_title($item_id);
									if (isset($cart_items[$key]['item_number']) && isset($cart_items[$key]['item_number']['options'])) {
										$price_options = $cart_items[$key]['item_number']['options'];
										if (mprm_has_variable_prices($item_id) && isset($price_id)) {
											echo ' - ' . mprm_get_price_option_name($item_id, $price_id, $order_id);
										}
									}
									?>
								</a>
							</span>
						<input type="hidden" name="mprm-order-details[<?php echo $key; ?>][id]" class="mprm-order-detail-id" value="<?php echo esc_attr($item_id); ?>"/>
						<input type="hidden" name="mprm-order-details[<?php echo $key; ?>][price_id]" class="mprm-order-detail-price-id" value="<?php echo esc_attr($price_id); ?>"/>
						<input type="hidden" name="mprm-order-details[<?php echo $key; ?>][item_price]" class="mprm-order-detail-item-price" value="<?php echo esc_attr($item_price); ?>"/>
						<input type="hidden" name="mprm-order-details[<?php echo $key; ?>][amount]" class="mprm-order-detail-amount" value="<?php echo esc_attr($price); ?>"/>
						<input type="hidden" name="mprm-order-details[<?php echo $key; ?>][quantity]" class="mprm-order-detail-quantity" value="<?php echo esc_attr($quantity); ?>"/>
						<input type="hidden" name="mprm-order-details[<?php echo $key; ?>][tax]" class="mprm-order-detail-tax" value="<?php echo esc_attr($tax); ?>"/>
					</div>

					<?php if (mprm_item_quantities_enabled()) : ?>
						<div class="quantity mprm-columns <?php echo $column ?>">
							<span class="item-price"><?php echo mprm_currency_filter(mprm_format_amount($item_price)); ?></span>
							&nbsp;&times;&nbsp;<span class="item-quantity"><?php echo $quantity; ?></span>
						</div>
					<?php endif; ?>

					<div class="price mprm-columns <?php echo $column ?>">
						<?php if (mprm_item_quantities_enabled()) : ?>
							<?php echo __('Total:', 'mp-restaurant-menu') . '&nbsp;'; ?>
						<?php endif; ?>
						<span class="price-text"><?php echo mprm_currency_filter(mprm_format_amount($price), $currency_code); ?></span>
					</div>

					<div class="actions mprm-columns <?php echo $column ?>">
						<input type="hidden" class="mprm-order-detail-has-log" name="mprm-order-details[<?php echo $key; ?>][has_log]" value="1"/>
						<a href="" class="mprm-order-remove-menu-item mprm-delete" data-key="<?php echo esc_attr($key); ?>"><?php _e('Remove', 'mp-restaurant-menu'); ?></a>
					</div>

				</div>
				<?php
				$i++;
			endforeach; ?>

			<div class="mprm-row">
				<div class="item mprm-columns <?php echo $column ?>">
					<?php
					echo mprm_menu_item_dropdown(array(
						'name' => 'mprm-order-menu-item-select',
						'id' => 'mprm-order-menu-item-select',
						'chosen' => true,
						'show_option_all' => false,
						'show_option_none' => false,
						'placeholder' => __('Select a Menu item', 'mp-restaurant-menu'),
						'data_attr' => array('text_single' => __('Select a Menu item', 'mp-restaurant-menu'))
					));
					?>
				</div>

				<?php if (mprm_item_quantities_enabled()) : ?>
					<div class="quantity mprm-columns <?php echo $column ?>">
						<span><?php _e('Quantity', 'mp-restaurant-menu'); ?>:&nbsp;</span>
						<input type="number" id="mprm-order-menu-item-quantity" class="small-text" min="1" step="1" value="1"/>
					</div>
				<?php endif; ?>

				<div class="price mprm-columns <?php echo $column ?>">
					<?php

					echo mprm_text(array(
							'name' => 'mprm-order-menu-item-amount',
							'id' => 'mprm-order-menu-item-amount',
							'label' => __('Item Price: ', 'mp-restaurant-menu'),
							'class' => 'mprm-order-menu-item-price'
						)
					);
					?>
				</div>

				<div class="actions mprm-columns <?php echo $column ?>">
					<a href="" id="mprm-order-add-menu-item" class="button button-secondary"><?php printf(__('Add %s to Payment', 'mp-restaurant-menu'), mprm_get_label_singular()); ?></a>
				</div>
			</div>
			<input type="hidden" name="mprm-order-menu-items-changed" id="mprm-payment-menu-items-changed" value=""/>
			<input type="hidden" name="mprm-order-removed" id="mprm-order-removed" value="{}"/>

		<?php else : $key = 0; ?>
			<div class="mprm-row">
				<p><?php printf(__('No %s included with this purchase', 'mp-restaurant-menu'), mprm_get_label_plural()); ?></p>
			</div>
		<?php endif; ?>
	</div>

<?php do_action('mprm_view_order_details_files_after', $order_id); ?>