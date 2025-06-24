<?php
use mp_restaurant_menu\classes\View as View;

View::get_instance()->render_html('../admin/import/header', $data);

if ( current_user_can('export') ) {
	View::get_instance()->render_html('../admin/import/export', $data);
}

if ( current_user_can('import') ) {
	View::get_instance()->render_html('../admin/import/import', $data);
}

View::get_instance()->render_html('../admin/import/footer', $data);
