<h1><? _e('Export', 'mp-restaurant-menu') ?></h1>
<form novalidate="novalidate" method="post" id="mprm_export">
	<input type="hidden" name="controller" value="import">
	<input type="hidden" name="mprm_action" value="export">
	<p class="submit"><input type="submit" value="<?php _e('Export', 'mp-restaurant-menu') ?>" class="button button-primary" id="submit" name="submit"></p>
</form>