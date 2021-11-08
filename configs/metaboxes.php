<?php
$menu_item = $this->get_post_type('menu_item');
return array(
//	array(
//		'post_type' => $menu_item,
//		'name' => 'sub_header',
//		'title' => esc_html__('Sub title', 'mp-restaurant-menu'),
//		'context' => 'normal',
//		'priority' => 'low',
//		'callback' => array($this->get('menu_item'), 'render_meta_box')
//	),
	array(
		'post_type' => $menu_item,
		'name' => 'price',
		'title' => esc_html__('Price', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback_args' => array('description' => esc_html__('Price in monetary decimal (.) format without thousand separators and currency symbols', 'mp-restaurant-menu')),
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'nutritional',
		'title' => esc_html__('Nutrition Facts', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'attributes',
		'title' => esc_html__('Portion Size', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback_args' => array('description' => esc_html__('Portion Size', 'mp-restaurant-menu')),
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'sku',
		'title' => esc_html__('SKU', 'mp-restaurant-menu'),
		'context' => 'normal',
		'priority' => 'low',
		'callback_args' => array('description' => esc_html__('SKU', 'mp-restaurant-menu')),
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	),
	array(
		'post_type' => $menu_item,
		'name' => 'mp_menu_gallery',
		'title' => esc_html__('Image Gallery', 'mp-restaurant-menu'),
		'context' => 'side',
		'priority' => 'low',
		'callback' => array($this->get('menu_item'), 'render_meta_box')
	)
);