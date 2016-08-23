<?php
if (empty($price)) {
	$price = mprm_get_price();
}
if (!empty($price)) :
	$price = mprm_currency_filter(mprm_format_amount($price));
	if (mprm_get_template_mode() == "theme") { ?>
		<div class="mprm-content-container">
			<div class="mprm-price-container">
				<span class="mprm-price"><?php echo $price ?></span>
			</div>
		</div>
	<?php } else { ?>
		<span class="mprm-price"><?php echo $price ?></span>
	<?php }
endif;