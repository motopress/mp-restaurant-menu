<?php if (mprm_get_template_mode() == "theme") { ?>
	<div class="mprm-content-container mprm-title-big"><b><?php echo $mprm_menu_item->post_title ?></b></div>
<?php } else { ?>
	<h3 class="mprm-title"><?php echo $mprm_menu_item->post_title  ?></h3>
<?php } ?>

