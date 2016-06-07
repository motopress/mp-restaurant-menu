<div id="mprm-payment-processing">
	<p><?php printf(__('Your purchase is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'mp-restaurant-menu'), $success_page_uri); ?>
		<span class="mprm-cart-ajax"><i class="mprm-icon-spinner mprm-icon-spin"></i></span>
		<script type="text/javascript">setTimeout(function() {
				window.location = '<?php echo $success_page_uri; ?>';
			}, 8000);</script>
</div>