<?php
if (empty($price)) {
	$price = mprm_get_price();
}
if (!empty($price)) {
	?>
	<div class="mprm-price-box">
		<h3 class="mprm-price"><?php printf(__('Price: %s', 'mp-restaurant-menu'), $price); ?></h3>
	</div>
	<?php
}