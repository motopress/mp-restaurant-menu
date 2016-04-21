<div class="mprm-nutritional-values">
	<p>
		<label><?php _e('Calories', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["calories"]['val'])) ? esc_attr($data["value"]["calories"]['val']) : ""; ?>" name="nutritional[calories][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Calories' ?>" name="nutritional[calories][title]">
	</p>
	<p>
		<label><?php _e('Cholesterol', 'mp-restaurant-menu'); ?>:</label><br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["cholesterol"]['val'])) ? esc_attr($data["value"]["cholesterol"]['val']) : ""; ?>" name="nutritional[cholesterol][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Cholesterol' ?>" name="nutritional[cholesterol][title]">
	</p>
	<p>
		<label><?php _e('Fiber', 'mp-restaurant-menu'); ?>:</label> <br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["fiber"]['val'])) ? esc_attr($data["value"]["fiber"]['val']) : ""; ?>" name="nutritional[fiber][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Fiber' ?>" name="nutritional[fiber][title]">
	</p>
	<p>
		<label><?php _e('Sodium', 'mp-restaurant-menu'); ?>:</label><br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["sodium"]['val'])) ? esc_attr($data["value"]["sodium"]['val']) : ""; ?>" name="nutritional[sodium][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Sodium' ?>" name="nutritional[sodium][title]">
	</p>
	<p>
		<label><?php _e('Carbohydrates', 'mp-restaurant-menu'); ?>:</label><br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["carbohydrates"]['val'])) ? esc_attr($data["value"]["carbohydrates"]['val']) : ""; ?>" name="nutritional[carbohydrates][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Carbohydrates' ?>" name="nutritional[carbohydrates][title]">
	</p>
	<p>
		<label><?php _e('Fat', 'mp-restaurant-menu'); ?>:</label><br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["fat"]['val'])) ? esc_attr($data["value"]["fat"]['val']) : ""; ?>" name="nutritional[fat][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Fat' ?>" name="nutritional[fat][title]">
	</p>
	<p>
		<label><?php _e('Protein', 'mp-restaurant-menu'); ?>:</label><br/>
		<input type="text" placeholder="0" value="<?php echo (!empty($data["value"]["protein"]['val'])) ? esc_attr($data["value"]["protein"]['val']) : ""; ?>" name="nutritional[protein][val]">
		<input type="hidden" placeholder="0" value="<?php echo 'Protein' ?>" name="nutritional[protein][title]">
	</p>
</div>