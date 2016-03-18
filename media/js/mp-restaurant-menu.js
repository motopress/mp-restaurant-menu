/*global jQuery:false, MP_RM_Registry:false*/
(function($) {
	"use strict";
	$(document).ready(function() {
		// if edit and add menu_item
		if ('mp_menu_item' === $(window.post_type).val()) {
			MP_RM_Registry._get("Menu-Item").init();
		}
		// if settings
		if('restaurant-menu_page_admin?page=mprm-settings' === window.pagenow){
			MP_RM_Registry._get('Menu-Settings').init();
		}
		// if edit and add menu_category
		if('edit-mp_menu_category' === window.pagenow){
			MP_RM_Registry._get("Menu-Category").init();
		}
	});
}(jQuery));
