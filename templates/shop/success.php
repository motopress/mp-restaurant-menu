<?php
use \mp_restaurant_menu\classes\models\Payments as Payments;
use \mp_restaurant_menu\classes\models\Gateways as Gateways;
use \mp_restaurant_menu\classes\models\Taxes as Taxes;
use \mp_restaurant_menu\classes\models\Cart as Cart;
use \mp_restaurant_menu\classes\models\Misc as Misc;
use \mp_restaurant_menu\classes\models\Menu_item as Menu_item;
use \mp_restaurant_menu\classes\models\Formatting as Formatting;

// No key found
if (!isset($payment_key)) { ?>
	<p class="mprm-alert mprm-alert-error"><?php echo $mprm_receipt_args['error'] ?></p>

<?php }

if (empty($payment)) : ?>

	<div class="mprm_errors mprm-alert mprm-alert-error">
		<?php _e('The specified receipt ID appears to be invalid', 'mp-restaurant-menu'); ?>
	</div>

	<?php
	return;
endif;
?>
<table id="mprm_purchase_receipt">
	<thead>
	<?php do_action('mprm_payment_receipt_before', $payment, $receipt_args); ?>

	<?php if (filter_var($receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<th><strong><?php _e('Payment', 'mp-restaurant-menu'); ?>:</strong></th>
			<th><?php echo Payments::get_instance()->get_payment_number($payment->ID); ?></th>
		</tr>
	<?php endif; ?>
	</thead>

	<tbody>

	<tr>
		<td class="mprm_receipt_payment_status"><strong><?php _e('Payment Status', 'mp-restaurant-menu'); ?>:</strong></td>
		<td class="mprm_receipt_payment_status <?php echo strtolower($status); ?>"><?php echo $status; ?></td>
	</tr>

	<?php if (filter_var($receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<td><strong><?php _e('Payment Key', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo get_post_meta($payment->ID, '_mprm_order_purchase_key', true); ?></td>
		</tr>
	<?php endif; ?>

	<?php if (filter_var($receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<td><strong><?php _e('Payment Method', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo Gateways::get_instance()->get_gateway_checkout_label(Payments::get_instance()->get_payment_gateway($payment->ID)); ?></td>
		</tr>
	<?php endif; ?>
	<?php if (filter_var($receipt_args['date'], FILTER_VALIDATE_BOOLEAN)) : ?>
		<tr>
			<td><strong><?php _e('Date', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo date_i18n(get_option('date_format'), strtotime($meta['date'])); ?></td>
		</tr>
	<?php endif; ?>

	<?php if (($fees = Payments::get_instance()->get_payment_fees($payment->ID, 'fee'))) : ?>
		<tr>
			<td><strong><?php _e('Fees', 'mp-restaurant-menu'); ?>:</strong></td>
			<td>
				<ul class="mprm_receipt_fees">
					<?php foreach ($fees as $fee) : ?>
						<li>
							<span class="mprm_fee_label"><?php echo esc_html($fee['label']); ?></span>
							<span class="mprm_fee_sep">&nbsp;&ndash;&nbsp;</span>
							<span class="mprm_fee_amount"><?php echo Menu_item::get_instance()->currency_filter(Formatting::get_instance()->format_amount($fee['amount'])); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</td>
		</tr>
	<?php endif; ?>

	<?php if (filter_var($receipt_args['discount'], FILTER_VALIDATE_BOOLEAN) && isset($user['discount']) && $user['discount'] != 'none') : ?>
		<tr>
			<td><strong><?php _e('Discount(s)', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo $user['discount']; ?></td>
		</tr>
	<?php endif; ?>

	<?php if (Taxes::get_instance()->use_taxes()) : ?>
		<tr>
			<td><strong><?php _e('Tax', 'mp-restaurant-menu'); ?></strong></td>
			<td><?php echo Payments::get_instance()->payment_tax($payment->ID); ?></td>
		</tr>
	<?php endif; ?>

	<?php if (filter_var($receipt_args['price'], FILTER_VALIDATE_BOOLEAN)) : ?>

		<tr>
			<td><strong><?php _e('Subtotal', 'mp-restaurant-menu'); ?></strong></td>
			<td>
				<?php echo Payments::get_instance()->payment_subtotal($payment->ID); ?>
			</td>
		</tr>

		<tr>
			<td><strong><?php _e('Total Price', 'mp-restaurant-menu'); ?>:</strong></td>
			<td><?php echo Payments::get_instance()->payment_amount($payment->ID); ?></td>
		</tr>

	<?php endif; ?>

	<?php do_action('mprm_payment_receipt_after', $payment, $receipt_args); ?>
	</tbody>
</table>

<?php do_action('mprm_payment_receipt_after_table', $payment, $receipt_args); ?>

<?php if (filter_var($receipt_args['products'], FILTER_VALIDATE_BOOLEAN)) : ?>

	<h3><?php echo apply_filters('mprm_payment_receipt_products_title', __('Products', 'mp-restaurant-menu')); ?></h3>

	<table id="mprm_purchase_receipt_products">
		<thead>
		<th><?php _e('Name', 'mp-restaurant-menu'); ?></th>
		<?php if (Misc::get_instance()->use_skus()) { ?>
			<th><?php _e('SKU', 'mp-restaurant-menu'); ?></th>
		<?php } ?>

		<?php if (Cart::get_instance()->item_quantities_enabled()) : ?>
			<th><?php _e('Quantity', 'mp-restaurant-menu'); ?></th>
		<?php endif; ?>

		<th><?php _e('Price', 'mp-restaurant-menu'); ?></th>
		</thead>

		<tbody>
		<?php if ($cart) : ?>
			<?php foreach ($cart as $key => $item) : ?>

				<?php if (!apply_filters('mprm_user_can_view_receipt_item', true, $item)) : ?>
					<?php continue; // Skip this item if can't view it ?>
				<?php endif; ?>

				<?php if (empty($item['in_bundle'])) : ?>
					<tr>
						<td>

							<?php
							$price_id = Cart::get_instance()->get_cart_item_price_id($item);
							//							$menu_item_files = edd_get_menu_item_files($item['id'], $price_id);
							?>

							<div class="mprm_purchase_receipt_product_name">
								<?php echo esc_html($item['name']); ?>
								<?php if (Menu_item::get_instance()->has_variable_prices($item['id']) && !is_null($price_id)) : ?>
									<span class="mprm_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;<?php echo Menu_item::get_instance()->get_price_option_name($item['id'], $price_id, $payment->ID); ?></span>
								<?php endif; ?>
							</div>

							<?php if ($receipt_args['notes']) : ?>
								<div class="mprm_purchase_receipt_product_notes"><?php echo wpautop(mprm_get_menu_item_notes($item['id'])); ?></div>
							<?php endif; ?>

							<!--							--><?php
							//							if (edd_is_payment_complete($payment->ID) && edd_receipt_show_menu_item_files($item['id'], $receipt_args, $item)) : ?>
							<!--								<ul class="mprm_purchase_receipt_files">-->
							<!--									--><?php
							//									if (!empty($menu_item_files) && is_array($menu_item_files)) :
							//
							//										foreach ($menu_item_files as $filekey => $file) :
							//
							//											$menu_item_url = edd_get_menu_item_file_url($meta['key'], $email, $filekey, $item['id'], $price_id);
							//											?>
							<!--											<li class="mprm_menu_item_file">-->
							<!--												<a href="--><?php //echo esc_url($menu_item_url); ?><!--" class="mprm_menu_item_file_link">--><?php //echo edd_get_file_name($file); ?><!--</a>-->
							<!--											</li>-->
							<!--											--><?php
							//											do_action('edd_receipt_files', $filekey, $file, $item['id'], $payment->ID, $meta);
							//										endforeach;
							//
							//									elseif (edd_is_bundled_product($item['id'])) :
							//
							//										$bundled_products = edd_get_bundled_products($item['id']);
							//
							//										foreach ($bundled_products as $bundle_item) : ?>
							<!--											<li class="mprm_bundled_product">-->
							<!--												<span class="mprm_bundled_product_name">--><?php //echo get_the_title($bundle_item); ?><!--</span>-->
							<!--												<ul class="mprm_bundled_product_files">-->
							<!--													--><?php
							//													$menu_item_files = edd_get_menu_item_files($bundle_item);
							//
							//													if ($menu_item_files && is_array($menu_item_files)) :
							//
							//														foreach ($menu_item_files as $filekey => $file) :
							//
							//															$menu_item_url = edd_get_menu_item_file_url($meta['key'], $email, $filekey, $bundle_item, $price_id); ?>
							<!--															<li class="mprm_menu_item_file">-->
							<!--																<a href="--><?php //echo esc_url($menu_item_url); ?><!--" class="mprm_menu_item_file_link">--><?php //echo esc_html($file['name']); ?><!--</a>-->
							<!--															</li>-->
							<!--															--><?php
							//															do_action('edd_receipt_bundle_files', $filekey, $file, $item['id'], $bundle_item, $payment->ID, $meta);
							//
							//														endforeach;
							//													else :
							//														echo '<li>' . __('No menu_itemable files found for this bundled item.', 'mp-restaurant-menu') . '</li>';
							//													endif;
							//													?>
							<!--												</ul>-->
							<!--											</li>-->
							<!--											--><?php
							//										endforeach;
							//
							//									else :
							//										echo '<li>' . apply_filters('mprm_receipt_no_files_found_text', __('No menu_itemable files found.', 'mp-restaurant-menu'), $item['id']) . '</li>';
							//									endif; ?>
							<!--								</ul>-->
							<!--							--><?php //endif; ?>

						</td>
						<?php if (Misc::get_instance()->use_skus()) : ?>
							<td><?php echo mprm_get_menu_item_sku($item['id']); ?></td>
						<?php endif; ?>

						<?php if (Cart::get_instance()->item_quantities_enabled()) { ?>
							<td><?php echo $item['quantity']; ?></td>
						<?php } ?>
						<td>
							<?php if (empty($item['in_bundle'])) : // Only show price when product is not part of a bundle ?>
								<?php echo Menu_item::get_instance()->currency_filter(Formatting::get_instance()->format_amount($item['price'])); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if (($fees = Payments::get_instance()->get_payment_fees($payment->ID, 'item'))) : ?>
			<?php foreach ($fees as $fee) : ?>
				<tr>
					<td class="mprm_fee_label"><?php echo esc_html($fee['label']); ?></td>
					<?php if (Cart::get_instance()->item_quantities_enabled()) : ?>
						<td></td>
					<?php endif; ?>
					<td class="mprm_fee_amount"><?php echo Menu_item::get_instance()->currency_filter(Formatting::get_instance()->format_amount($fee['amount'])) ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>

	</table>
<?php endif; ?>
