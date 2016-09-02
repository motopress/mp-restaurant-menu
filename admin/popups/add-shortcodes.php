<form id="mprm-shortcode-form" data-selector="shortcode-form">
	<div class="mprm-line" data-selector="data-line">
		<div class="mprm-left-side"><?php _e('Shortcode type', 'mp-restaurant-menu'); ?></div>
		<div class="mprm-right-side">
			<select name="shortcode_name" data-selector="shortcode_name">
				<option value="mprm_categories"><?php _e('Show categories', 'mp-restaurant-menu'); ?></option>
				<option value="mprm_items"><?php _e('Show menu items', 'mp-restaurant-menu'); ?></option>
			</select>
		</div>
	</div>

	<div id="mprm-shortcode-html-container">
		<?php \mp_restaurant_menu\classes\View::get_instance()->render_html('../admin/popups/shortcode-mprm_categories', $data) ?>
	</div>

	<div class="mprm-line mprm-hidden" data-selector="data-line">
		<div class="mprm-left-side"><?php _e('Overlay color', 'mp-restaurant-menu'); ?></div>
		<div class="mprm-right-side">
			<input type="text" name="overlay_color" class="spectrum" data-selector="form_data" value="rgba(0, 0, 0, 0)"/>
		</div>
	</div>
	<div class="mprm-line mprm-hidden" data-selector="data-line">
		<div class="mprm-left-side"><?php _e('Hover color', 'mp-restaurant-menu'); ?></div>
		<div class="mprm-right-side">
			<input type="text" name="hover_color" class="spectrum" data-selector="form_data" value="rgba(0, 0, 0, 0)"/>
		</div>
	</div>
	<div class="mprm-line mprm-hidden" data-selector="data-line">
		<div class="mprm-left-side"><?php _e('Text color', 'mp-restaurant-menu'); ?></div>
		<div class="mprm-right-side">
			<input type="text" name="text_color" class="spectrum" data-selector="form_data" value="rgb(0, 0, 0)"/>
		</div>
	</div>
	<div class="mprm-line mprm-hidden" data-selector="data-line">
		<div class="mprm-left-side"><?php _e('Show attributes', 'mp-restaurant-menu'); ?></div>
		<div class="mprm-right-side">
			<input type="checkbox" name="attributes" checked value="1" data-selector="form_data"/>
		</div>
	</div>


	<div class="mprm-line stick" data-selector="data-line">
		<input class="button button-primary button-large" type="button" data-selector="insert_shortcode" value="<?php _e('Insert shortcode', 'mp-restaurant-menu'); ?>"/>
	</div>
</form>