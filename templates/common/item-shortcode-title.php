<?php if (!empty($mprm_view_args['link_item'])) { ?>
	<h3 class="mprm-title">
		<a class="mprm-link" href="<?php echo get_permalink($mprm_menu_item->ID) ?>">    <?php echo $mprm_menu_item->post_title ?></a>
	</h3>
	<?php
} else {
	?>
	<h3 class="mprm-title"><?php echo $mprm_menu_item->post_title ?></h3>
	<?php
}