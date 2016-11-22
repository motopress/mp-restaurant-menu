<?php apply_filters('mprm-cart-item-before', $item, $id) ?>

<li class="mprm-cart-item">
	<?php apply_filters('mprm-cart-item-data-before', $item) ?>
	<span class="mprm-cart-item-title"><a href="{cart_item_url}">{item_title}</a></span>
	<span class="mprm-cart-item-separator">-</span><span class="mprm-cart-item-quantity">&nbsp;{item_quantity}&nbsp;&times;</span><span class="mprm-cart-item-price">&nbsp;{item_amount}&nbsp;</span>
	<a href="{remove_url}" data-cart-item="{cart_item_id}" data-menu-item-id="{item_id}" data-action="mprm_remove_from_cart" class="mprm-remove-from-cart mprm-theme-mode"></a>
	<?php apply_filters('mprm-cart-item-data-after', $item) ?>
</li>

<?php apply_filters('mprm-cart-item-after', $item, $id) ?>
