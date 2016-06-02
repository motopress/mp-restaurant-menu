<?php use mp_restaurant_menu\classes\models;

$customers_table = new models\Customer_Reports();
$customers_table->prepare_items();

?>
<div class="wrap">
	<h1><?php _e('Customers', 'mp-restaurant-menu'); ?></h1>
	<?php do_action('mprm_customers_table_top'); ?>
	<form id="mprm-customers-filter" method="post">
		<?php
		$customers_table->search_box(__('Search Customers', 'mp-restaurant-menu'), 'mprm-customers');
		$customers_table->display();
		?>
		<input type="hidden" name="post_type" value="mp_menu_item"/>
		<input type="hidden" name="page" value="mprm-customers"/>
		<input type="hidden" name="view" value="customers"/>
	</form>
	<?php do_action('mprm_customers_table_bottom'); ?>
</div>
