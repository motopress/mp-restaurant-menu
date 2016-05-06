<div id="edd-billing-details" class="postbox">
	<h3 class="hndle">
		<span><?php _e( 'Billing Address', 'easy-digital-downloads' ); ?></span>
	</h3>
	<div class="inside edd-clearfix">

		<div id="edd-order-address">

			<div class="order-data-address">
				<div class="data column-container">
					<div class="column">
						<p>
							<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'easy-digital-downloads' ); ?></strong><br/>
							<input type="text" name="edd-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="medium-text" />
						</p>
						<p>
							<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'easy-digital-downloads' ); ?></strong><br/>
							<input type="text" name="edd-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="medium-text" />
						</p>

					</div>
					<div class="column">
						<p>
							<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'easy-digital-downloads' ); ?></strong><br/>
							<input type="text" name="edd-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="medium-text"/>

						</p>
						<p>
							<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'easy-digital-downloads' ); ?></strong><br/>
							<input type="text" name="edd-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="medium-text"/>

						</p>
					</div>
					<div class="column">
						<p id="edd-order-address-country-wrap">
							<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'easy-digital-downloads' ); ?></strong><br/>
							<?php
							echo EDD()->html->select( array(
								'options'          => edd_get_country_list(),
								'name'             => 'edd-payment-address[0][country]',
								'selected'         => $address['country'],
								'show_option_all'  => false,
								'show_option_none' => false,
								'chosen'           => true,
								'placeholder' => __( 'Select a country', 'easy-digital-downloads' )
							) );
							?>
						</p>
						<p id="edd-order-address-state-wrap">
							<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'easy-digital-downloads' ); ?></strong><br/>
							<?php
							$states = edd_get_shop_states( $address['country'] );
							if( ! empty( $states ) ) {
								echo EDD()->html->select( array(
									'options'          => $states,
									'name'             => 'edd-payment-address[0][state]',
									'selected'         => $address['state'],
									'show_option_all'  => false,
									'show_option_none' => false,
									'chosen'           => true,
									'placeholder' => __( 'Select a state', 'easy-digital-downloads' )
								) );
							} else { ?>
								<input type="text" name="edd-payment-address[0][state]" value="<?php echo esc_attr( $address['state'] ); ?>" class="medium-text"/>
								<?php
							} ?>
						</p>
					</div>
				</div>
			</div>
		</div><!-- /#edd-order-address -->

		<?php do_action( 'edd_payment_billing_details', $payment_id ); ?>

	</div><!-- /.inside -->
</div><!-- /#edd-billing-details -->
<?php do_action( 'edd_view_order_details_billing_after', $payment_id ); ?>