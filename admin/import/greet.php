<div class="narrow">
	<p><?php _e('Howdy! Upload your WordPress eXtended RSS (WXR) file and we&#8217;ll import the posts, pages, comments, custom fields, categories, and tags into this site.', 'mp-restaurant-menu') ?></p>
	<p><?php _e('Choose a WXR (.xml) file to upload, then click Upload file and import.', 'mp-restaurant-menu') ?></p>
	<?php wp_import_upload_form('admin.php?import=mprm-importer&amp;step=1') ?>
</div>