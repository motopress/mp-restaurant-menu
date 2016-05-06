<div id="edd-payment-notes" class="postbox">
	<h3 class="hndle"><span><?php _e( 'Payment Notes', 'easy-digital-downloads' ); ?></span></h3>
	<div class="inside">
		<div id="edd-payment-notes-inner">
			<?php
			$notes = edd_get_payment_notes( $payment_id );
			if ( ! empty( $notes ) ) :
				$no_notes_display = ' style="display:none;"';
				foreach ( $notes as $note ) :

					echo edd_get_payment_note_html( $note, $payment_id );

				endforeach;
			else :
				$no_notes_display = '';
			endif;
			echo '<p class="edd-no-payment-notes"' . $no_notes_display . '>'. __( 'No payment notes', 'easy-digital-downloads' ) . '</p>';
			?>
		</div>
		<textarea name="edd-payment-note" id="edd-payment-note" class="large-text"></textarea>

		<p>
			<button id="edd-add-payment-note" class="button button-secondary right" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'easy-digital-downloads' ); ?></button>
		</p>

		<div class="clear"></div>
	</div><!-- /.inside -->
</div><!-- /#edd-payment-notes -->