<tr class="form-field">
	<th for="category-order"><?php _e('Order', 'mp-restaurant-menu'); ?></th>
	<td id='menu-cat-order'>
		<input type="text" id="category-order" value="<?php echo esc_attr($order); ?>" name="term_meta[order]"/>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[iconname]"><?php _e('Icon', 'mp-restaurant-menu'); ?></label></th>
	<td id='menu-cat-icon'>
		<input type="text" id="IconPicker" name="term_meta[iconname]" value="<?php echo esc_attr($iconname) ?>"/>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label><?php _e('Thumbnail', 'foodpress'); ?></label></th>
	<td>
		<div id="menu_category_thumbnail" style="float:left;margin-right:10px;">
			<img src="<?php echo !empty($thumb_url) ? esc_attr($thumb_url) : esc_attr($placeholder) ?>" data-placeholder="<?php echo esc_attr($placeholder) ?>" width="150px" height="150px"/>
		</div>
		<input type="hidden" id="menu_category_thumbnail_id" name="term_meta[thumbnail_id]" value="<?php echo esc_attr($thumbnail_id); ?>"/>
		<button type="submit" class="upload_image_button button"><?php _e('Set Image', 'mp-restaurant-menu'); ?></button>
		<button type="submit" class="remove_image_button button"><?php _e('Remove', 'mp-restaurant-menu'); ?></button>
		<div class="clear"></div>
	</td>
</tr>