<?php
use mp_restaurant_menu\classes\models;
use mp_restaurant_menu\classes\View;

/**
 * @return mixed
 */
function mprm_is_checkout() {
	return models\Checkout::get_instance()->is_checkout();
}

/**
 * @return mixed
 */
function mprm_get_checkout_uri() {
	return models\Checkout::get_instance()->get_checkout_uri();
}

function mprm_get_checkout_cart_template() {
	$data = array();
	$data[ 'is_ajax_disabled' ] = models\Settings::get_instance()->is_ajax_disabled();
	$data[ 'cart_items' ] = models\Cart::get_instance()->get_cart_contents();
	mprm_get_template('shop/checkout-cart', $data);
}

/**
 * @return mixed
 */
function mprm_checkout_button_next() {
	$color = mprm_get_option('checkout_color', 'mprm-btn blue');
	$color = ($color == 'inherit') ? '' : $color;
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$style = mprm_get_option('button_style', 'button');
	$purchase_page = mprm_get_option('purchase_page', '0');
	ob_start();
	?>
	<input type="hidden" name="mprm_action" value="gateway_select"/>
	<input type="hidden" name="page_id" value="<?php echo absint($purchase_page); ?>"/>
	<input type="submit" name="gateway_submit" id="mprm_next_button" class="mprm-submit <?php echo $color; ?> <?php echo $padding; ?> <?php echo $style; ?>" value="<?php _e('Next', 'mp-restaurant-menu'); ?>"/>
	<?php
	return apply_filters('mprm_checkout_button_next', ob_get_clean());
}

/**
 * Checkout purchase button
 * @return mixed
 */
function mprm_checkout_button_purchase() {
	$color = mprm_get_option('checkout_color', 'mprm-btn blue');
	$color = ($color == 'inherit') ? '' : $color;
	$style = mprm_get_option('button_style', 'button');
	$label = mprm_get_option('checkout_label', '');
	$padding = mprm_get_option('checkout_padding', 'mprm-inherit');
	$minimum_order_amount = models\Settings::get_instance()->get_option('minimum_order_amount', 0);
	$cart_total = mprm_get_cart_total();
	
	if ( $cart_total ) {
		$complete_purchase = !empty($label) ? $label : __('Purchase', 'mp-restaurant-menu');
	} else {
		$complete_purchase = !empty($label) ? $label : __('Free', 'mp-restaurant-menu');
	}
	ob_start();
	
	//minimum_order_amount
	if ($minimum_order_amount && $cart_total < $minimum_order_amount) {
	?>
	<div class="mprm-notice mprm-notice-error">
		<div class="mprm-error">
			<span class="mprm-notice-text">
				<?php
				printf( __('Your current order total is %s — you must have an order with a minimum of %s to place your order.', 'mp-restaurant-menu'), 
					mprm_currency_filter( mprm_format_amount( $cart_total ) ),
					mprm_currency_filter( mprm_format_amount( $minimum_order_amount ) )
				);
				?>
			</span>
		</div>
	</div>
	<?php } else { ?>
	<input type="submit" class="mprm-submit <?php echo $color; ?> <?php echo $padding; ?> <?php echo $style; ?>" id="mprm-purchase-button" name="mprm-purchase" value="<?php echo $complete_purchase; ?>"/>
	<?php
	}
	return apply_filters('mprm_checkout_button_purchase', ob_get_clean());
}

/**
 * @return bool
 */
function mprm_is_no_guest_checkout() {
	return models\Misc::get_instance()->no_guest_checkout();
}

function mprm_checkout_tax_fields() {
	if (models\Taxes::get_instance()->cart_needs_tax_address_fields() && mprm_get_cart_total()) {
		mprm_default_cc_address_fields();
	}
}

function mprm_checkout_submit() { ?>
	<fieldset id="mprm_purchase_submit">
		<?php do_action('mprm_purchase_form_before_submit'); ?>
		<?php mprm_checkout_hidden_fields(); ?>
		<?php echo mprm_checkout_button_purchase(); ?>
		<?php do_action('mprm_purchase_form_after_submit'); ?>
	</fieldset>
	<?php
}

function mprm_checkout_additional_information() {
	View::get_instance()->get_template('/shop/checkout-additional-information');
}

function mprm_checkout_final_total() {
	?>
	<p id="mprm_final_total_wrap">
		<strong><?php _e('Total:', 'mp-restaurant-menu'); ?></strong>
		<span class="mprm_cart_amount" data-subtotal="<?php echo mprm_get_cart_subtotal(); ?>" data-total="<?php echo mprm_get_cart_subtotal(); ?>"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_total())); ?></span>
	</p>
	<?php
}

function mprm_checkout_hidden_fields() {
	?>
	<?php if (is_user_logged_in()) { ?>
		<input type="hidden" name="mprm-user-id" value="<?php echo get_current_user_id(); ?>"/>
	<?php } ?>
	<input type="hidden" name="mprm_action" value="purchase"/>
	<input type="hidden" name="controller" value="cart"/>
	<input type="hidden" name="mprm-gateway" value="<?php echo models\Gateways::get_instance()->get_chosen_gateway(); ?>"/>
	<?php
}

/**
 *  Delivery address default
 */
function mprm_checkout_delivery_address() {
	if (mprm_get_option('shipping_address')): ?>
		<p id="mprm-address-wrap">
			<label for="shipping_address" class="mprm-label">
				<?php _e('Shipping address:', 'mp-restaurant-menu'); ?>
			</label>
			<input type="text" name="shipping_address" value="" class="medium-text" placeholder="<?php _e('Enter your address.', 'mp-restaurant-menu'); ?>"/>
		</p>
	<?php endif;
}

/**
 * Checkout order notes
 */
function mprm_checkout_order_note() {
	?>
	<p id="mprm-phone-number-wrap">
		<label for="customer_note" class="mprm-label">
			<?php _e('Order notes:', 'mp-restaurant-menu'); ?>
		</label>
		<textarea type="text" name="customer_note" id="customer_note" class="phone-number mprm-input"></textarea>
	</p>
<?php }

/**
 * Summary table
 */

function mprm_checkout_summary_table() { ?>

	<span class="mprm-payment-details-label"><legend><?php _e('Order totals', 'mp-restaurant-menu'); ?></legend></span>
	<table class="mprm-table">
		<?php do_action('mprm_checkout_table_subtotal_before'); ?>
		<tr>
			<td class=""><span><?php _e('Subtotal', 'mp-restaurant-menu'); ?> </span></td>
			<td><span class="mprm_cart_subtotal_amount"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_subtotal())) ?></span></td>
		</tr>
		<?php do_action('mprm_checkout_table_subtotal_after'); ?>
		
		<?php do_action('mprm_checkout_table_tax_before'); ?>
		<?php if (mprm_use_taxes()) : ?>
			<tr <?php if (!mprm_is_cart_taxed()) echo ' style="display:none;"'; ?>>
				<td><span><?php _e('Tax', 'mp-restaurant-menu'); ?></span></td>
				<td><span class="mprm_cart_tax_amount" data-tax="<?php echo mprm_get_cart_tax(); ?>"><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_tax())) ?></span></td>
			</tr>
		<?php endif; ?>
		<?php do_action('mprm_checkout_table_tax_after'); ?>
		
		<?php do_action('mprm_checkout_table_total_before'); ?>
		<tr class="mprm-checkout-total">
			<td>
				<span><b><?php _e('Total', 'mp-restaurant-menu'); ?></b></span>
			</td>
			<td><span class="mprm_cart_amount"><b><?php echo mprm_currency_filter(mprm_format_amount(mprm_get_cart_total())) ?></b></span></td>
		</tr>
		<?php do_action('mprm_checkout_table_total_after'); ?>
	</table>

<?php }

/**
 * Send to success page
 *
 * @param string $query_string
 */
function mprm_send_to_success_page($query_string) {
	return models\Checkout::get_instance()->send_to_success_page($query_string);
}

/**
 * Send back to checkout
 *
 * @param $args
 */
function mprm_send_back_to_checkout($args) {
	return models\Checkout::get_instance()->send_back_to_checkout($args);
}
