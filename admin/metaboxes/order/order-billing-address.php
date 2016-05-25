<?php global $post;
$order = mprm_get_order_object($post);
$address = $order->address; ?>
<div id="mprm-billing-details" class="">
	<div class="mprm-row">
		<div class="mprm-columns mprm-four">
			<p>
				<strong class="order-data-address-line"><?php _e('Street Address Line 1:', 'mp-restaurant-menu'); ?></strong><br/>
				<input type="text" name="mprm-order-address[0][line1]" value="<?php echo esc_attr($address['line1']); ?>" class="medium-text"/>
			</p>
			<p>
				<strong class="order-data-address-line"><?php _e('Street Address Line 2:', 'mp-restaurant-menu'); ?></strong><br/>
				<input type="text" name="mprm-order-address[0][line2]" value="<?php echo esc_attr($address['line2']); ?>" class="medium-text"/>
			</p>

		</div>
		<div class="mprm-columns mprm-four">
			<p>
				<strong class="order-data-address-line"><?php echo _x('City:', 'Address City', 'mp-restaurant-menu'); ?></strong><br/>
				<input type="text" name="mprm-order-address[0][city]" value="<?php echo esc_attr($address['city']); ?>" class="medium-text"/>

			</p>
			<p>
				<strong class="order-data-address-line"><?php echo _x('Zip / Postal Code:', 'Zip / Postal code of address', 'mp-restaurant-menu'); ?></strong><br/>
				<input type="text" name="mprm-order-address[0][zip]" value="<?php echo esc_attr($address['zip']); ?>" class="medium-text"/>

			</p>
		</div>
		<div class="mprm-columns mprm-four">
			<p id="mprm-order-address-country-wrap">
				<strong class="order-data-address-line"><?php echo _x('Country:', 'Address country', 'mp-restaurant-menu'); ?></strong><br/>
				<?php
				echo mprn_select(
					array(
						'class' => 'mprm-country-list',
						'options' => mprm_get_country_list(),
						'name' => 'mprm-order-address[0][country]',
						'selected' => $address['country'],
						'show_option_all' => false,
						'show_option_none' => false,
						'chosen' => true,
						'placeholder' => __('Select a country', 'mp-restaurant-menu'),
						'data_attr' => array('text_single' => __('Select a country', 'mp-restaurant-menu'))
					));

				?>
			</p>
			<p id="mprm-order-address-state-wrap">
				<strong class="order-data-address-line"><?php echo _x('State / Province:', 'State / province of address', 'mp-restaurant-menu'); ?></strong><br/>
				<?php
				$states = mprm_get_shop_states($address['country']);

				if (!empty($states)) {
					echo mprn_select(array(
						'class' => 'mprm-country-state',
						'options' => $states,
						'name' => 'mprm-order-address[0][state]',
						'selected' => $address['state'],
						'show_option_all' => false,
						'show_option_none' => false,
						'chosen' => true,
						'placeholder' => __('Select a state', 'mp-restaurant-menu'),
						'data_attr' => array('text_single' => __('Select a state', 'mp-restaurant-menu'))
					));
				} else { ?>
					<input type="text" name="mprm-order-address[0][state]" value="<?php echo esc_attr($address['state']); ?>" class="medium-text"/>
					<?php
				} ?>
			</p>
		</div>
	</div>
</div>
