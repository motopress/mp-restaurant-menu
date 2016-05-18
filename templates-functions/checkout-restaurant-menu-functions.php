<?php
use \mp_restaurant_menu\classes\models;

function mprm_is_checkout() {
	return models\Checkout::get_instance()->is_checkout();
}

function mprm_get_checkout_uri() {
	return models\Checkout::get_instance()->get_checkout_uri();
}