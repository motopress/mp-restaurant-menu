<div class="wrap mprm-settings">
	<form method="<?php echo esc_attr(apply_filters('mprm_settings_form_method_tab_' . urlencode($active_tab), 'post')); ?>" id="mainform" action="" enctype="multipart/form-data">
		<h2 class="nav-tab-wrapper mprm-nav-tab-wrapper">
			<?php
			foreach ($settings_tabs as $tab_id => $tab_name) {

				$tab_url = add_query_arg(array(
					'settings-updated' => false,
					'tab' => $tab_id,
				));

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg('section', $tab_url);

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url($tab_url) . '" title="' . esc_attr($tab_name) . '" class="nav-tab' . $active . '">';
				echo esc_html($tab_name);
				echo '</a>';
			}
			?>
		</h2>
		<?php if (!empty($tabs[$active_tab]['section'])): ?>
			<div>
				<?php
				$number_of_sections = count($sections);
				$number = 0;
				if ($number_of_sections > 1) {
					echo '<div><ul class="subsubsub">';
					foreach ($sections as $section_id => $section_name) {
						echo '<li>';
						$number++;
						$tab_url = add_query_arg(array(
							'settings-updated' => false,
							'tab' => $active_tab,
							'section' => $section_id
						));
						$class = '';
						if ($section == $section_id) {
							$class = 'current';
						}
						echo '<a class="' . $class . '" href="' . esc_url($tab_url) . '">' . $section_name . '</a>';

						if ($number != $number_of_sections) {
							echo ' | ';
						}
						echo '</li>';
					}
					echo '</ul></div>';
				} ?>
			</div>
			<br class="mprm-clear">
		<?php endif; ?>
		<?php
		// Let's verify we have a 'main' section to show
		ob_start();
		do_settings_sections('mprm_settings_' . $active_tab . '_main');
		$has_main_settings = strlen(ob_get_contents()) > 0;
		ob_end_clean();

		if (false === $has_main_settings) {
			unset($sections['main']);

			if ('main' === $section) {
				foreach ($sections as $section_key => $section_title) {
					if (!empty($all_settings[$active_tab][$section_key])) {
						$section = $section_key;
						break;
					}
				}
			}
		}
		do_settings_sections('mprm_settings_' . $active_tab . '_' . $section);
		?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'mp-restaurant-menu') ?>"/>
		</p>
	</form>
</div>

<!--
 <div class="wrap"> 
 	<h1 id="settings-title"> <?php _e('General Settings', 'mp-restaurant-menu') ?> </h1>
 	<form novalidate="novalidate" method="post" id="rm_settings"> 
 		<input type="hidden" name="controller" value="settings"> 
 		<input type="hidden" name="mprm_action" value="save"> 
 		<h3> <?php _e('View Options', 'mp-restaurant-menu') ?> </h3>
 		<table class="form-table"> 
 			<tbody> 
 			 <?php if (!empty($settings['category_view'])): ?>
 				<tr> 
 					<th scope="row"> 
 						<label for="category_view"> <?php _e('Menu category view', 'mp-restaurant-menu') ?> </label>
 					</th> 
 					<td> 
 						<select id="category_view" name="category_view"> 
 							 <?php foreach ($settings['category_view'] as $key => $value): ?>
 								<option value=" <?php echo $key ?> "  <?php
	if (!empty($current_settings['category_view']) && $key == $current_settings['category_view']) {
		echo 'selected="selected"';
	} elseif ($value['default']) {
		echo 'selected="selected"';
	}
	?> > <?php echo $value['title'] ?> </option>
 							 <?php endforeach; ?>
 						</select> 
 						<p class="description" id="category-view-description"> <?php _e('Choose the way to display your menu items within category.', 'mp-restaurant-menu') ?> </p>
 					</td> 
 				</tr> 
 			 <?php endif; ?>
 			</tbody> 
 		</table> 
 		<h3> <?php _e('Currency Options', 'mp-restaurant-menu') ?> </h3>
 		<table class="form-table"> 
 			<tbody> 
 			<tr> 
 				<th scope="row"> 
 					<label for="currency_code"> <?php _e('Currency', 'mp-restaurant-menu') ?> </label>
 				</th> 
 				<td> 
 					<select name="currency_code" required data-placeholder=" <?php esc_attr_e('Choose a currency', 'mp-restaurant-menu'); ?> ">
 						<option value=""> <?php _e('Choose a currency', 'mp-restaurant-menu'); ?> </option>
 						 <?php
foreach ($currencies as $code => $name) {
	echo '<option value="' . esc_attr($code) . '" ';
	echo !empty($current_settings['currency_code']) && $code == $current_settings['currency_code'] ? ' selected="selected" ' : '';
	echo '>' . esc_html($name . ' (' . $instance->get_currency_symbol($code) . ')') . '</option>';
}
?>
 					</select> 
 				</td> 
 			</tr> 
 			 <?php if (!empty($settings['category_view'])): ?>
 				<tr> 
 					<th scope="row"> 
 						<label for="currency_pos"> <?php _e('Currency Position', 'mp-restaurant-menu') ?> </label>
 					</th> 
 					<td> 
 						<select id="currency_pos" name="currency_pos"> 
 							 <?php foreach ($settings['currency_pos'] as $key => $value): ?>
 								<option value=" <?php echo $key ?> "  <?php
	if (!empty($current_settings['currency_pos']) && $key == $current_settings['currency_pos']) {
		echo 'selected="selected"';
	}
	?> > <?php echo $value ?> </option>
 							 <?php endforeach; ?>
 						</select> 
 					</td> 
 				</tr> 
 			 <?php endif; ?>
 			</tbody> 
 		</table> 
 		<p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p> 
 	</form> 
 	<div class="updated settings-error notice is-dismissible hidden" id="setting-error-settings_updated"> 
 		<p> 
 			<strong> <?php _e('Settings saved.', 'mp-restaurant-menu') ?> </strong>
 		</p> 
 		<button class="notice-dismiss" type="button"> 
 			<span class="screen-reader-text"> <?php _e('Dismiss this notice.', 'mp-restaurant-menu') ?> </span>
 		</button>
 	</div>
</div> -->