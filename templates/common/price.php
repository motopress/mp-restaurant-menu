<?php
if (empty($price)) {
	$price = mprm_get_price();
}
if (!empty($price)) :
	$price = mprm_currency_filter(mprm_format_amount($price)); ?>
	<div class="mprm-content-container"><span class="mprm-price"><?php echo $price ?></span></div>
<?php endif;