<div class="mprm-attributes-values">
	<p>
		<label><?php esc_html_e('Weight', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["weight"]['val'])) ? esc_attr($data["value"]["weight"]['val']) : ""; ?>" name="attributes[weight][val]">
		<input type="hidden" placeholder="0" value="Weight" name="attributes[weight][title]">
	</p>
	<p>
		<label><?php esc_html_e('Volume', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["bulk"]['val'])) ? esc_attr($data["value"]["bulk"]['val']) : ""; ?>" name="attributes[bulk][val]">
		<input type="hidden" placeholder="0" value="Volume" name="attributes[bulk][title]">
	</p>
	<p>
		<label><?php esc_html_e('Size', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["size"]['val'])) ? esc_attr($data["value"]["size"]['val']) : ""; ?>" name="attributes[size][val]">
		<input type="hidden" placeholder="0" value="Size" name="attributes[size][title]">
	</p>
</div>