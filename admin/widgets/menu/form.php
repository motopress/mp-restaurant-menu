<p>
	<label for="<?php echo $widget_object->get_field_id('title') ?>"><?php _e('Title', 'mp-restaurant-menu'); ?></label>
	<input type="text" id="<?php echo $widget_object->get_field_id('title') ?>" class="widefat" name="<?php echo $widget_object->get_field_name('title') ?>" placeholder="" value="<?php echo !empty($title) ? $title : '' ?>">
</p>
<p>
	<label for="<?php echo $widget_object->get_field_id('view') ?>"><?php _e('View mode', 'mp-restaurant-menu'); ?></label>

	<select class="widefat mprm-widget-view" id="<?php echo $widget_object->get_field_id('view') ?>" name="<?php echo $widget_object->get_field_name('view') ?>">
		<option value="grid" <?php selected($view, 'grid'); ?>><?php _e('Grid', 'mp-restaurant-menu'); ?></option>
		<option value="list" <?php selected($view, 'list'); ?>><?php _e('List', 'mp-restaurant-menu'); ?></option>
		<option value="simple-list" <?php selected($view, 'simple-list'); ?>><?php _e('Simple list', 'mp-restaurant-menu'); ?></option>
	</select>
</p>
<p>
	<label for="<?php echo $widget_object->get_field_id('categ[]') ?>"><?php _e('Categories', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo $widget_object->get_field_id('categ[]') ?>" class="widefat" name="<?php echo $widget_object->get_field_name('categ[]') ?>" multiple>
		<?php if ($categories): ?>
			<?php foreach ($categories as $category): ?>
				<option value="<?php echo $category->term_id ?>" <?php echo in_array($category->term_id, $categ) ? 'selected=""' : ''; ?>>
					<?php echo $category->name; ?>
				</option>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
</p>
<p>
	<label for="<?php echo $widget_object->get_field_id('tags_list[]') ?>"><?php _e('Tags', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo $widget_object->get_field_id('tags_list[]') ?>" class="widefat" name="<?php echo $widget_object->get_field_name('tags_list[]') ?>" multiple>
		<?php if ($menu_tags): ?>
			<?php foreach ($menu_tags as $tag): ?>
				<option value="<?php echo $tag->term_id ?>" <?php echo in_array($tag->term_id, $tags_list) ? 'selected=""' : ''; ?>><?php echo $tag->name; ?></option>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
</p>
<p>
	<label for="<?php echo $widget_object->get_field_id('item_ids') ?>"><?php _e('Menu item IDs', 'mp-restaurant-menu'); ?></label>
	<input id="<?php echo $widget_object->get_field_id('item_ids') ?>" type="text" class="widefat" name="<?php echo $widget_object->get_field_name('item_ids') ?>" value="<?php echo !empty($item_ids) ? $item_ids : '' ?>"/>
</p>
<p>
	<label for="<?php echo $widget_object->get_field_id('col') ?>"><?php _e('Columns', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo $widget_object->get_field_id('col') ?>" class="widefat" name="<?php echo $widget_object->get_field_name('col') ?>">
		<option value="1" <?php echo $col === '1' ? 'selected="selected"' : '' ?> class="event-column-1">1 <?php _e('column', 'mp-restaurant-menu'); ?></option>
		<option value="2" <?php echo $col === '2' ? 'selected="selected"' : '' ?> class="event-column-2">2 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
		<option value="3" <?php echo $col === '3' ? 'selected="selected"' : '' ?> class="event-column-3">3 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
		<option value="4" <?php echo $col === '4' ? 'selected="selected"' : '' ?> class="event-column-4">4 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
		<option value="6" <?php echo $col === '6' ? 'selected="selected"' : '' ?> class="event-column-6">6 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
	</select>
</p>
<p class="mprm-widget-price_pos <?php echo ($view === 'simple-list') ? '' : 'hidden' ?>">
	<label for="<?php echo $widget_object->get_field_id('price_pos') ?>"><?php _e('Price position', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo $widget_object->get_field_id('price_pos') ?>" class="widefat mprm-widget-price_pos" name="<?php echo $widget_object->get_field_name('price_pos') ?>">
		<option value="points" <?php selected($price_pos, 'points') ?>><?php _e('Dotted line and price on the right', 'mp-restaurant-menu'); ?></option>
		<option value="right" <?php selected($price_pos, 'right') ?>><?php _e('Price on the right', 'mp-restaurant-menu'); ?></option>
		<option value="after_title" <?php selected($price_pos, 'after_title') ?>>  <?php _e('Price next to the title', 'mp-restaurant-menu'); ?></option>
	</select>
</p>
<p>
	<label for="<?php echo $widget_object->get_field_id('categ_name') ?>"><?php _e('Show category name', 'mp-restaurant-menu'); ?></label>
	<select id="<?php echo $widget_object->get_field_id('categ_name') ?>" class="widefat mprm-widget-categ_name" name="<?php echo $widget_object->get_field_name('categ_name') ?>">
		<option value="only_text" <?php echo !empty($categ_name) && $categ_name == 'only_text' ? 'selected=""' : '' ?>><?php _e('Only text', 'mp-restaurant-menu'); ?></option>
		<option class="<?php echo ($view !== 'simple-list') ? '' : 'hidden' ?>" value="with_img" <?php echo !empty($categ_name) && $categ_name == 'with_img' ? 'selected=""' : '' ?>><?php _e('Title with image', 'mp-restaurant-menu'); ?></option>
		<option value="none" <?php echo !empty($categ_name) && $categ_name == 'none' ? 'selected=""' : '' ?>><?php _e('Don`t show', 'mp-restaurant-menu'); ?></option>
	</select>
</p>
<p>
	<input id="<?php echo $widget_object->get_field_id('show_attributes') ?>" class="checkbox" type="checkbox" name="<?php echo $widget_object->get_field_name('show_attributes') ?>" <?php echo isset($show_attributes) ? 'checked=""' : '' ?> />
	<label for="<?php echo $widget_object->get_field_id('show_attributes') ?>"><?php _e('Show attributes', 'mp-restaurant-menu'); ?></label>
</p>
<p class="mprm-widget-feat_img <?php echo ($view !== 'simple-list') ? '' : 'hidden' ?>">
	<input id="<?php echo $widget_object->get_field_id('feat_img') ?>" class="checkbox mprm-widget-feat_img" type="checkbox" name="<?php echo $widget_object->get_field_name('feat_img') ?>" <?php echo isset($feat_img) ? 'checked=""' : '' ?>/>
	<label for="<?php echo $widget_object->get_field_id('feat_img') ?>"><?php _e('Show featured image', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<input id="<?php echo $widget_object->get_field_id('excerpt') ?>" class="checkbox" type="checkbox" name="<?php echo $widget_object->get_field_name('excerpt') ?>" <?php echo isset($excerpt) ? 'checked=""' : '' ?> />
	<label for="<?php echo $widget_object->get_field_id('excerpt') ?>"><?php _e('Show excerpt', 'mp-restaurant-menu'); ?></label>
</p>

<p>
	<input id="<?php echo $widget_object->get_field_id('price') ?>" class="checkbox" type="checkbox" name="<?php echo $widget_object->get_field_name('price') ?>" <?php echo isset($price) ? 'checked=""' : '' ?> />
	<label for="<?php echo $widget_object->get_field_id('price') ?>"><?php _e('Show price', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<input id="<?php echo $widget_object->get_field_id('tags') ?>" class="checkbox" type="checkbox" name="<?php echo $widget_object->get_field_name('tags') ?>" <?php echo isset($tags) ? 'checked=""' : '' ?> />
	<label for="<?php echo $widget_object->get_field_id('tags') ?>"><?php _e('Show tags', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<input id="<?php echo $widget_object->get_field_id('ingredients') ?>" class="checkbox" type="checkbox" name="<?php echo $widget_object->get_field_name('ingredients') ?>" <?php echo isset($ingredients) ? 'checked=""' : '' ?> />
	<label for="<?php echo $widget_object->get_field_id('ingredients') ?>"><?php _e('Show ingredients', 'mp-restaurant-menu'); ?></label>
</p>

<p class="mprm-widget-buy <?php echo ($view !== 'simple-list') ? '' : 'hidden' ?>">
	<input id="<?php echo $widget_object->get_field_id('buy') ?>" class="checkbox" type="checkbox" name="<?php echo $widget_object->get_field_name('buy') ?>" <?php echo isset($buy) ? 'checked="checked"' : '' ?> />
	<label for="<?php echo $widget_object->get_field_id('buy') ?>"><?php _e('Show buy button', 'mp-restaurant-menu'); ?></label>
</p>

<p>
	<input id="<?php echo $widget_object->get_field_id('link_item') ?>" class="checkbox" type="checkbox" name="<?php echo $widget_object->get_field_name('link_item') ?>" <?php echo isset($link_item) ? 'checked=""' : '' ?> />
	<label for="<?php echo $widget_object->get_field_id('link_item') ?>"><?php _e('Link item', 'mp-restaurant-menu'); ?></label>
</p>
<p>
	<label for="<?php echo $widget_object->get_field_id('desc_length') ?>"><?php _e('Excerpt length', 'mp-restaurant-menu'); ?></label>
	<input id="<?php echo $widget_object->get_field_id('desc_length') ?>" type="text" class="widefat" name="<?php echo $widget_object->get_field_name('desc_length') ?>" value="<?php echo !empty($desc_length) ? $desc_length : '' ?>">
</p>