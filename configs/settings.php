<?php
return array(
	'tabs' => array(
		'general' => array(
			'label' => esc_html__('General', 'mp-restaurant-menu'),
			'section' => array(
				'main' => array('label' => esc_html__('General Settings', 'mp-restaurant-menu')),
				'currency' => array('label' => esc_html__('Currency Settings', 'mp-restaurant-menu')),
				//'open_hours_section' => array('label' => esc_html__('Open Hours', 'mp-restaurant-menu'))
			),
			'category_view' => array(
				'grid' => array(
					'title' => esc_html__('Grid', 'mp-restaurant-menu'),
					'default' => true
				),
				'list' => array(
					'title' => esc_html__('List', 'mp-restaurant-menu'),
					'default' => false
				)
			),
			'currency_pos' => array(
				"left" => esc_html__('Left', 'mp-restaurant-menu'),
				"right" => esc_html__('Right', 'mp-restaurant-menu'),
				"left_space" => esc_html__('Left with space', 'mp-restaurant-menu'),
				"right_space" => esc_html__('Right with space', 'mp-restaurant-menu')
			)
		),
		'products' => array('label' => esc_html__('Products', 'mp-restaurant-menu')),
		'checkout' => array('label' => esc_html__('Checkout', 'mp-restaurant-menu')),
		'email' => array('label' => esc_html__('Email', 'mp-restaurant-menu'))
	),
);
