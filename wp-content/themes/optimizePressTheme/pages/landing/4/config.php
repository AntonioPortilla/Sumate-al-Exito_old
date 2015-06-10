<?php  if ( ! defined('OP_DIR')) exit('No direct script access allowed');
$config['name'] = __('Full Background Style', OP_SN);
$config['screenshot'] = 'LP_4A.jpg';
$config['screenshot_thumbnail'] = 'LP_4A-thumb.jpg';
$config['description'] = __('Landing page with full screen background', OP_SN);

$config['header_layout'] = array(
	'menu-positions' => array(
		'alongside' => array(
			'title' => __('Logo With Alongside Navigation', OP_SN),
			'preview' => array(
				'image' => OP_IMG.'previews/navpos_alongside.png',
				'width' => 477,
				'height' => 67
			),
			'link_color' => true,
			'link_selector' => '.banner .nav > li > a',
			'dropdown_selector' => '.banner .nav a',
		),
		'below' => array(
			'title' => __('Banner/Header with navigation below', OP_SN),
			'preview' => array(
				'image' => OP_IMG.'previews/navpos_below.png',
				'width' => 477,
				'height' => 89
			),
		)
	)
);

$config['disable'] = array(
	'layout' => array(
		'header_layout' => true,
		'size_color' => true,
		'footer_area' => array(
			'large_footer' => true,
		)
	),
	'color_schemes' => true,
	'content_layout' => true,
	'functionality' => array(
		'comments' => true,
	)
);

$config['feature_areas'] = array(
	'A' => array(
		'image' => $theme_url.'styles/LP_4A.jpg',
		'width' => 212,
		'height' => 156,
                'tooltip_title' => __('Theme 1', OP_SN),
                'tooltip_description' => __('White box on full background', OP_SN),
	),
	'B' => array(
		'image' => $theme_url.'styles/LP_4B.jpg',
		'width' => 212,
		'height' => 156,
                'tooltip_title' => __('Theme 2', OP_SN),
                'tooltip_description' => __('Black box on full background', OP_SN),
	)
);