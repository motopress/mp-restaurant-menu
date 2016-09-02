<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('View mode', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<select name="view" data-selector="form_data">
			<option value="grid"><?php _e('Grid', 'mp-restaurant-menu'); ?></option>
			<option value="list"><?php _e('List', 'mp-restaurant-menu'); ?></option>
			<option value="simple-list"><?php _e('Simple list', 'mp-restaurant-menu'); ?></option>
		</select>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Categories', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<select name="categ" multiple="6" data-selector="form_data">
			<?php if ($categories): ?>
				<?php foreach ($categories as $category): ?>
					<option value="<?php echo $category->term_id ?>"><?php echo $category->name; ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Tags', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<select name="tags_list" multiple="6" data-selector="form_data">
			<?php if ($tags): ?>
				<?php foreach ($tags as $tag): ?>
					<option value="<?php echo $tag->term_id ?>"><?php echo $tag->name; ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Menu item IDs', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="text" name="item_ids" value="" data-selector="form_data"/>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Columns', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<select name="col" data-selector="form_data">
			<option value="1" class="event-column-1">1 <?php _e('column', 'mp-restaurant-menu'); ?></option>
			<option value="2" class="event-column-2">2 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
			<option value="3" class="event-column-3">3 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
			<option value="4" class="event-column-4">4 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
			<option value="6" class="event-column-6">6 <?php _e('columns', 'mp-restaurant-menu'); ?></option>
		</select>
	</div>
</div>

<div class="mprm-line mprm-hidden" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Price position:', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<select name="price_pos" data-selector="form_data">
			<option value="points"><?php _e('Dotted line and price on the right', 'mp-restaurant-menu'); ?></option>
			<option value="right"><?php _e('Price on the right', 'mp-restaurant-menu'); ?></option>
			<option value="after_title"><?php _e('Price next to the title', 'mp-restaurant-menu'); ?></option>
		</select>
	</div>
</div>

<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Show category name', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<select name="categ_name" data-selector="form_data">
			<option value="only_text"><?php _e('Only text', 'mp-restaurant-menu'); ?></option>
			<option value="with_img"><?php _e('Title with image', 'mp-restaurant-menu'); ?></option>
			<option value="none"><?php _e('Don`t show', 'mp-restaurant-menu'); ?></option>
		</select>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Show attributes', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="checkbox" name="show_attributes" checked value="1" data-selector="form_data"/>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Show featured image', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="checkbox" name="feat_img" checked value="1" data-selector="form_data"/>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Show excerpt', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="checkbox" name="excerpt" checked value="1" data-selector="form_data"/>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Show price', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="checkbox" name="price" checked value="1" data-selector="form_data"/>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Show tags', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="checkbox" name="tags" checked value="1" data-selector="form_data"/>
	</div>
</div>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Show ingredients', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="checkbox" name="ingredients" checked value="1" data-selector="form_data"/>
	</div>
</div>
<?php if (mprm_get_option('enable_ecommerce')): ?>
	<div class="mprm-line" data-selector="data-line">
		<div class="mprm-left-side"><?php _e('Show buy button', 'mp-restaurant-menu'); ?></div>
		<div class="mprm-right-side">
			<input type="checkbox" name="buy" checked value="1" data-selector="form_data"/>
		</div>
	</div>
<?php endif; ?>
<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Link item', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="checkbox" name="link_item" checked value="1" data-selector="form_data"/>
	</div>
</div>

<div class="mprm-line" data-selector="data-line">
	<div class="mprm-left-side"><?php _e('Description length', 'mp-restaurant-menu'); ?></div>
	<div class="mprm-right-side">
		<input type="text" name="desc_length" data-selector="form_data" placeholder="">
	</div>
</div>