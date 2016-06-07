<?php
return array(
	'tabs' => array(
		'general' => array(
			'label' => __('General', 'mp-restaurant-menu'),
			'section' => array(
				'main' => array('label' => __('General Settings', 'mp-restaurant-menu')),
				'currency' => array('label' => __('Currency Settings', 'mp-restaurant-menu'))
			),
			'category_view' => array(
				'grid' => array(
					'title' => __('Grid', 'mp-restaurant-menu'),
					'default' => true
				),
				'list' => array(
					'title' => __('List', 'mp-restaurant-menu'),
					'default' => false
				)
			),
			'currency_pos' => array(
				"left" => __('Left', 'mp-restaurant-menu'),
				"right" => __('Right', 'mp-restaurant-menu'),
				"left_space" => __('Left with space', 'mp-restaurant-menu'),
				"right_space" => __('Right with space', 'mp-restaurant-menu')
			)
		),
		'products' => array('label' => __('Products', 'mp-restaurant-menu')),
		'checkout' => array('label' => __('Checkout', 'mp-restaurant-menu')),
		'email' => array('label' => __('Email', 'mp-restaurant-menu'))
	),
);
