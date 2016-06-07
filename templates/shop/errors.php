<div class="<?php echo implode(' ', $classes) ?>">
	<?php foreach ($errors as $error_id => $error) { ?>
		<p class="mprm_error" id="mprm-error_<?php echo $error_id ?>">
			<strong><?php _e('Error', 'mp-restaurant-menu') ?></strong>: <?php echo $error ?>
		</p>
	<?php } ?>
</div>