<?php
global $post;
use mp_restaurant_menu\classes\models\Cart as Cart;

$table_column_class = apply_filters('mprm_table_column_class', Cart::get_instance()->item_quantities_enabled() ? 'mprm-table-column-4' : 'mprm-table-column-3');

?>
<table id="mprm_checkout_cart" <?php echo !$is_ajax_disabled ? 'class="ajaxed ' . $table_column_class . '"' : '' ?>>
	<thead>
	<tr class="mprm_cart_header_row">
		<?php do_action('mprm_checkout_table_header_first'); ?>
		<th class="mprm_cart_item_name"><?php _e('Product', 'mp-restaurant-menu'); ?></th>
		<th class="mprm_cart_item_price"><?php _e('Price', 'mp-restaurant-menu'); ?></th>
		<?php if (Cart::get_instance()->item_quantities_enabled()) : ?>
			<th class="mprm_cart_quantities"><?php _e('Quantity', 'mp-restaurant-menu'); ?></th>
		<?php endif; ?>
		<th class="mprm_cart_actions"><?php _e('Actions', 'mp-restaurant-menu'); ?></th>
		<?php do_action('mprm_checkout_table_header_last'); ?>
	</tr>
	</thead>
	<tbody>
	<?php do_action('mprm_cart_items_before'); ?>

	<?php if ($cart_items && !empty($cart_items)) : ?>
		<?php foreach ($cart_items as $index => $item) : ?>

			<?php do_action('mprm_cart_item_before', $item, $index); ?>

			<tr class="mprm_cart_item" id="mprm_cart_item_<?php echo esc_attr($index) . '_' . esc_attr($item['id']); ?>" data-cart-key="<?php echo esc_attr($index) ?>" data-menu-item-id="<?php echo esc_attr($item['id']); ?>">
				<?php do_action('mprm_checkout_table_body_first', $item); ?>

				<td class="mprm_cart_item_name">
					<div class="mprm_cart_item_name_wrapper">
						<?php if (current_theme_supports('post-thumbnails') && has_post_thumbnail($item['id'])) { ?>
							<div class="mprm_cart_item_image">
								<?php echo get_the_post_thumbnail($item['id'], apply_filters('mprm_checkout_image_size', 'thumbnail')); ?>
							</div>
						<?php }
						$item_title = Cart::get_instance()->get_cart_item_name($item); ?>

						<a class="mprm-link" href="<?php echo get_permalink($item['id']) ?>">
							<span class="mprm_checkout_cart_item_title"><?php echo esc_html($item_title) ?></span>
						</a>
						<?php do_action('mprm_checkout_cart_item_title_after', $item); ?>
					</div>
				</td>
				<td class="mprm_cart_item_price">
					<?php
					echo Cart::get_instance()->cart_item_price($item['id'], $item['options']);
					do_action('mprm_checkout_cart_item_price_after', $item);
					?>
				</td>
				<?php if (Cart::get_instance()->item_quantities_enabled()) : ?>
					<td class="mprm_cart_quantities">
						<input type="number" min="1" step="1" name="mprm-cart-menu_item-<?php echo $index; ?>-quantity" data-key="<?php echo $index; ?>" class="mprm-input mprm-item-quantity" value="<?php echo Cart::get_instance()->get_cart_item_quantity($item['id'], $item['options'], $index); ?>"/>
						<input type="hidden" name="mprm-cart-menu-item[]" value="<?php echo $item['id']; ?>"/>
						<input type="hidden" name="mprm-cart-menu-item-<?php echo $index; ?>-options" value="<?php echo esc_attr(json_encode($item['options'])); ?>"/>
					</td>
				<?php endif; ?>

				<td class="mprm_cart_actions">
					<?php do_action('mprm_cart_actions', $item, $index); ?>
					<a class="mprm_cart_remove_item_btn" href="<?php echo esc_url(Cart::get_instance()->remove_item_url($index)); ?>"><?php _e('Remove', 'mp-restaurant-menu'); ?></a>
				</td>

				<?php do_action('mprm_checkout_table_body_last', $item); ?>
			</tr>

			<?php do_action('mprm_cart_item_after', $item, $index); ?>

		<?php endforeach; ?>
	<?php endif; ?>

	<?php do_action('mprm_cart_items_middle'); ?>

	<?php if (Cart::get_instance()->cart_has_fees()) : ?>
		<?php foreach (Cart::get_instance()->get_cart_fees() as $fee_id => $fee) : ?>
			<tr class="mprm_cart_fee" id="mprm_cart_fee_<?php echo $fee_id; ?>">
				<?php do_action('mprm_cart_fee_rows_before', $fee_id, $fee); ?>
				<td class="mprm_cart_fee_label"><?php echo esc_html($fee['label']); ?></td>
				<td class="mprm_cart_fee_amount"><?php echo esc_html(mprm_currency_filter(mprm_format_amount($fee['amount']))); ?></td>
				<td>
					<?php if (!empty($fee['type']) && 'item' == $fee['type']) : ?>
						<a href="<?php echo esc_url(Cart::get_instance()->remove_cart_fee_url($fee_id)); ?>"><?php _e('Remove', 'mp-restaurant-menu'); ?></a>
					<?php endif; ?>
				</td>
				<?php do_action('mprm_cart_fee_rows_after', $fee_id, $fee); ?>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php do_action('mprm_cart_items_after'); ?>
	</tbody>
</table>