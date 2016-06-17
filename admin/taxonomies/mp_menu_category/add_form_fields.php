<div class="form-field" id='fp_icon_field'>
	<label for="category-order"><?php _e('Order', 'mp-restaurant-menu'); ?></label>
	<input type="text" id="category-order" name="term_meta[order]"/>
</div>
<div class="form-field" id='fp_icon_field'>
	<label for="IconPicker"><?php _e('Icon', 'mp-restaurant-menu'); ?>
	</label>
	<input type="text" id="IconPicker" name="term_meta[iconname]"/>
</div>
<div class="form-field">
	<label><?php _e('Thumbnail', 'mp-restaurant-menu'); ?></label>
	<div id="menu_category_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo esc_attr($placeholder) ?>" data-placeholder="<?php echo esc_attr($placeholder) ?>" width="150px" height="150px"/></div>
	<input type="hidden" id="menu_category_thumbnail_id" name="term_meta[thumbnail_id]"/>
	<button type="button" class="upload_image_button button"><?php _e('Set Image', 'mp-restaurant-menu'); ?></button>
	<button type="button" class="remove_image_button button"><?php _e('Remove', 'mp-restaurant-menu'); ?></button>
	<div class="clear"></div>
</div>
