<?php ?>
<table class="mprm-table-order-items mprm-hidden" cellspacing="0">
	<tbody>
	<?php foreach ( $items as $item ) {
		$menu_item = get_post( $item[ 'id' ] );
		?>
		<tr class="mprm-order-table-row">
			<td class="mprm-quantity"><?php echo $item[ 'quantity' ] ?></td>
			<td class="mprm-item-title">
				<a href="<?php echo get_edit_post_link( $menu_item->ID ) ?>" title="<?php echo $menu_item->post_title ?>"><?php echo $menu_item->post_title ?></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>