<?php
$menu_item = $this->get_post_type('menu_item');
return array(
//	array(
//		'post_type' => $menu_item,
//		'name' => 'sub_header',
//		'title' => __('Sub title', 'mp-restaurant-menu'),
//		'context' => 'normal',
//		'priority' => 'low',
//		'callback' => array($this->get('menu_item'), 'render_meta_box')
//	),
	array(
		'post_type' => $menu_item,
		'name' => 'price',
		'title' => __('Price', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback_args' => array('description' => __('Price in monetary decimal (.) format without thousand separators and currency symbols', 'mp-restaurant-menu')),
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'nutritional',
		'title' => __('Nutrition Facts', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'attributes',
		'title' => __('Portion Size', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback_args' => array('description' => __('Portion Size', 'mp-restaurant-menu')),
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'sku',
		'title' => __('SKU', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback_args' => array('description' => __('SKU', 'mp-restaurant-menu')),
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'mp_menu_gallery',
		'title' => __('Image Gallery', 'mp-restaurant-menu'),
		'context' => 'side',
		'priority' => 'low',
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	)
);