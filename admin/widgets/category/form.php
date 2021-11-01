<?php extract($data); ?>
<p>
	<label for="<?php echo esc_attr( $widget_object->get_field_id('title') );?>"><?php _e('Title', 'mp-restaurant-menu'); ?></label>
	<input id="<?php echo esc_attr( $widget_object->get_field_id('title') );?>" type="text" class="widefat" name="<?php echo esc_attr( $widget_object->get_field_name('title') );?>" placeholder="" value="<?php echo !empty($title) ? esc_attr( $title ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
</p>
<p>
	<label for="<?php echo esc_attr( $widget_object->get_field_id('view') );?>"><?php _e('View mode', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo esc_attr( $widget_object->get_field_id('view') );?>" class="widefat" name="<?php echo esc_attr( $widget_object->get_field_name('view') );?>">
		<option value="grid" <?php echo !empty($view) && $view == 'grid' ? 'selected=""' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php _e('Grid', 'mp-restaurant-menu'); ?></option>
		<option value="list" <?php echo !empty($view) && $view == 'list' ? 'selected=""' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php _e('List', 'mp-restaurant-menu'); ?></option>
	</select>
</p>
<p>
	<label for="<?php echo esc_attr( $widget_object->get_field_id('categ[]') );?>"><?php _e('Categories', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo esc_attr( $widget_object->get_field_id('categ[]') );?>" class="widefat" name="<?php echo esc_attr( $widget_object->get_field_name('categ[]') )?>" multiple="multiple">
		<?php if ($categories): ?>
			<?php foreach ($categories as $category): ?>
				<option value="<?php echo esc_attr( $category->term_id );?>" <?php echo in_array($category->term_id, $categ) ? 'selected="selected"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( $category->name ); ?>
				</option>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
</p>
<p>
	<label for="<?php echo esc_attr( $widget_object->get_field_id('title') ); ?>"><?php _e('Columns', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo esc_attr( $widget_object->get_field_id('categ[]') ); ?>" class="widefat" name="<?php echo esc_attr( $widget_object->get_field_name('col') );?>">
		<option value="1" <?php echo $col === '1' ? 'selected="selected"' : '' ?> class="event-column-1">1 <?php _e('column', 'mp-restaurant-menu'); ?></option>
		<option value="2" <?php echo $col === '2' ? 'selected="selected"' : '' ?> class="event-column-2">2 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
		<option value="3" <?php echo $col === '3' ? 'selected="selected"' : '' ?> class="event-column-3">3 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
		<option value="4" <?php echo $col === '4' ? 'selected="selected"' : '' ?> class="event-column-4">4 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
		<option value="6" <?php echo $col === '6' ? 'selected="selected"' : '' ?> class="event-column-6">6 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
	</select>
</p>
<p>
	<input id="<?php echo esc_attr( $widget_object->get_field_id('categ_name') ); ?>" class="checkbox" type="checkbox" name="<?php echo esc_attr( $widget_object->get_field_name('categ_name') );?>" <?php echo isset($categ_name) ? 'checked=""' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
	<label for="<?php echo esc_attr( $widget_object->get_field_id('categ_name') ); ?>"><?php _e('Show category name', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<input id="<?php echo esc_attr( $widget_object->get_field_id('feat_img') ); ?>" class="checkbox" type="checkbox" name="<?php echo esc_attr( $widget_object->get_field_name('feat_img') );?>" <?php echo isset($feat_img) ? 'checked=""' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>/>
	<label for="<?php echo esc_attr( $widget_object->get_field_id('feat_img') );?>"><?php _e('Show category featured image', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<input id="<?php echo esc_attr( $widget_object->get_field_id('categ_icon') );?>" class="checkbox" type="checkbox" name="<?php echo esc_attr( $widget_object->get_field_name('categ_icon') );?>" <?php echo isset($categ_icon) ? 'checked=""' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
	<label for="<?php echo esc_attr( $widget_object->get_field_id('categ_icon') );?>"><?php _e('Show category icon', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<input id="<?php echo esc_attr( $widget_object->get_field_id('categ_descr') );?>" class="checkbox" type="checkbox" name="<?php echo esc_attr( $widget_object->get_field_name('categ_descr') );?>" <?php echo isset($categ_descr) ? 'checked=""' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
	<label for="<?php esc_attr( echo $widget_object->get_field_id('categ_descr') );?>"><?php _e('Show category description', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<label for="<?php echo esc_attr( $widget_object->get_field_id('desc_length') );?>"><?php _e('Description length', 'mp-restaurant-menu'); ?></label>
	<input id="<?php echo esc_attr( $widget_object->get_field_id('desc_length') );?>" type="text" class="widefat" name="<?php echo esc_attr( $widget_object->get_field_name('desc_length') );?>" placeholder="" value="<?php echo !empty($desc_length) ? $desc_length : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
</p>
