<div class="mprm-attributes-values">
	<p>
		<label><?php _e('Weight', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["weight"]['val'])) ? esc_attr($data["value"]["weight"]['val']) : ""; ?>" name="attributes[weight][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Weight' ?>" name="attributes[weight][title]">
	</p>
	<p>
		<label><?php _e('Volume', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["bulk"]['val'])) ? esc_attr($data["value"]["bulk"]['val']) : ""; ?>" name="attributes[bulk][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Volume' ?>" name="attributes[bulk][title]">
	</p>
	<p>
		<label><?php _e('Size', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["size"]['val'])) ? esc_attr($data["value"]["size"]['val']) : ""; ?>" name="attributes[size][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Size' ?>" name="attributes[size][title]">
	</p>
</div>