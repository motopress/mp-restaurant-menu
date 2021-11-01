<div class="<?php echo esc_attr( implode(' ', $classes) );?>">
	<?php foreach ($errors as $error_id => $error) { ?>
		<p class="mprm_error" id="mprm-error_<?php echo esc_attr( $error_id );?>">
			<strong><?php _e('Error', 'mp-restaurant-menu') ?></strong>: <?php echo esc_html( $error );?>
		</p>
	<?php } ?>
</div>