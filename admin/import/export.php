<h2><?php esc_html_e('Export', 'mp-restaurant-menu') ?></h2>
<p><?php esc_html_e('When you click the button below WordPress will create an XML file for you to save to your computer. Once you\'ve saved the download file, you can use the Import function in another WordPress installation to import the content from this site.', 'mp-restaurant-menu') ?></p>
<form novalidate="novalidate" method="post" id="mprm_export">
	<input type="hidden" name="controller" value="import">
	<input type="hidden" name="mprm_action" value="export">
	<p class="submit"><input type="submit" value="<?php esc_attr_e('Export', 'mp-restaurant-menu') ?>" class="button button-primary" id="submit" name="submit"></p>
</form>