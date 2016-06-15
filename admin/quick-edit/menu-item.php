<?php
global $id;
$post = get_post($id);
wp_nonce_field('mp-restaurant-menu' . '_nonce', 'mp-restaurant-menu' . '_nonce_box');
$nutritional = get_post_meta($post->ID, 'nutritional', true);
$attributes = get_post_meta($post->ID, 'attributes', true);

if ('mprm-price' == $column_name): ?>
	<fieldset class="inline-edit-col-right inline-price">
		<div class="inline-edit-col column-<?php echo $column_name; ?>">
			<label class="inline-edit-group">
				<span class="title"><?php _e('Price', 'mp-restaurant-menu') ?></span>
				<span class="input-text-wrap"><input name="price" value="<?php echo get_post_meta($post->ID, 'price', true) ?>" type="text"/></span>
			</label>
		</div>
	</fieldset>
	<fieldset class="inline-edit-col-right inline-sku">
		<div class="inline-edit-col column-sku">
			<label class="inline-edit-group">
				<span class="title"><?php _e('SKU', 'mp-restaurant-menu') ?></span>
				<span class="input-text-wrap"><input name="price" value="<?php echo get_post_meta($post->ID, 'sku', true) ?>" type="text"/></span>
			</label>
		</div>
	</fieldset>
	<fieldset class="inline-edit-col-right inline-nutrition-facts">
		<legend class="inline-edit-legend"><?php _e('Nutrition Facts', 'mp-restaurant-menu') ?></legend>
		<div class="inline-edit-col column-nutrition-facts">
			<label class="inline-edit-group">
				<span class="title"><?php _e('Calories', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $nutritional["calories"]['val'] ?>" name="nutritional[calories][val]"></span>
				<span class="title"><?php _e('Cholesterol', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $nutritional["cholesterol"]['val'] ?>" name="nutritional[cholesterol][val]"></span>
				<span class="title"><?php _e('Fiber', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $nutritional["fiber"]['val'] ?>" name="nutritional[fiber][val]"></span>
				<span class="title"><?php _e('Sodium', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $nutritional["sodium"]['val'] ?>" name="nutritional[sodium][val]"></span>
				<span class="title"><?php _e('Carbohydrates', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $nutritional["carbohydrates"]['val'] ?>" name="nutritional[carbohydrates][val]"></span>
				<span class="title"><?php _e('Fat', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $nutritional["fat"]['val'] ?>" name="nutritional[fat][val]"></span>
				<span class="title"><?php _e('Protein', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $nutritional["protein"]['val'] ?>" name="nutritional[protein][val]"></span>
			</label>
		</div>
	</fieldset>
	<fieldset class="inline-edit-col-right inline-portion-size">
		<legend class="inline-edit-legend"><?php _e('Portion Size', 'mp-restaurant-menu') ?></legend>
		<div class="inline-edit-col column-portion-size">
			<label class="inline-edit-group">
				<span class="title"><?php _e('Weight', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $attributes["weight"]['val'] ?>" name="attributes[weight][val]"></span>
				<span class="title"><?php _e('Volume', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $attributes["bulk"]['val'] ?>" name="attributes[bulk][val]"></span>
				<span class="title"><?php _e('Size', 'mp-restaurant-menu'); ?>:</span>
				<span class="input-text-wrap"><input type="text" placeholder="0" value="<?php echo $attributes["size"]['val'] ?>" name="attributes[size][val]"></span>
			</label>
		</div>
	</fieldset>
<?php endif; ?>