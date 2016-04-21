<?php
if (empty($price)) {
	$price = mprm_get_price();
}
if (!empty($price)) :?>
	<span class="mprm-price"><?php echo $price ?></span>
<?php endif;