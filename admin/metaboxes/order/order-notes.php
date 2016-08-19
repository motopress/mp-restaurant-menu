<?php
global $post;
$order = mprm_get_order_object($post);
$order_id = $order->ID;
?>
<div id="mprm-order-notes" class="">
	<div id="mprm-order-notes-inner">
		<?php
		$notes = mprm_get_payment_notes($order_id);
		if (!empty($notes)) :
			$no_notes_display = ' style="display:none;"';
			foreach ($notes as $note) :
				echo mprm_get_payment_note_html($note, $order_id);
			endforeach;
		else :
			$no_notes_display = '';
		endif; ?>
		<p class="mprm-no-order-notes"<?php echo $no_notes_display ?>><?php _e('No order notes', 'mp-restaurant-menu') ?></p>
	</div>
	<textarea name="mprm-order-note" id="mprm-order-note" class="large-text"></textarea>
	<p>
		<button id="mprm-add-order-note" class="button button-secondary right" data-order-id="<?php echo absint($order_id); ?>"><?php _e('Add Note', 'mp-restaurant-menu'); ?></button>
	</p>
	<div class="mprm-clear"></div>
</div>
