<div class="mprm-payment-note" id="mprm-payment-note-<?php echo esc_attr( $note->comment_ID );?>">
	<p>
		<strong><?php echo $user; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>&nbsp;&ndash;&nbsp; <?php echo date_i18n($date_format, strtotime($note->comment_date)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br/>
		<?php echo wp_kses_post( $note->comment_content ); ?>&nbsp;&ndash;&nbsp;
		<a href="<?php echo esc_url($delete_note_url) ?>" class="mprm-delete-order-note" data-note-id="<?php echo absint($note->comment_ID) ?>" data-order-id="<?php echo absint($payment_id) ?>" title="<?php _e('Delete this payment note', 'mp-restaurant-menu') ?>"><?php _e('Delete', 'mp-restaurant-menu') ?></a>
	</p>
</div>