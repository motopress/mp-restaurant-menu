<div class="wrap mprm-settings">
	<form method="<?php echo esc_attr(apply_filters('mprm_settings_form_method_tab_' . urlencode($active_tab), 'post')); ?>" id="mainform" action="options.php" enctype="multipart/form-data">
		<h2 class="nav-tab-wrapper mprm-nav-tab-wrapper">
			<?php foreach ($settings_tabs as $tab_id => $tab_name) {
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
		<?php if (!empty($sections)): ?>
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
		<?php endif;
		// Let's verify we have a 'main' section to show
		ob_start();
		do_settings_sections('mprm_settings_' . $active_tab . '_main');
		$has_main_settings = strlen(ob_get_contents()) > 0;
		ob_end_clean();
		if (false === $has_main_settings) {
			unset($sections[ 'main' ]);
			if ('main' === $section) {
				foreach ($sections as $section_key => $section_title) {
					if (!empty($all_settings[ $active_tab ][ $section_key ])) {
						$section = $section_key;
						break;
					}
				}
			}
		}
		settings_fields('mprm_settings');
		do_settings_sections('mprm_settings_' . $active_tab . '_' . $section);
		submit_button(); ?>
	</form>
</div>
