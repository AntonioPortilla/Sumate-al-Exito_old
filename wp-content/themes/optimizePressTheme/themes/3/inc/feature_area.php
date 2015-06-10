<?php
class OptimizePress_Theme_Feature_Area {
	
	function __construct(){
		add_filter('op_mod_feature_area_styles',array($this,'styles_array'));
	}
	
	function styles_array(){
		$content_fields = op_optin_default_fields();
		$styles = array(
			'video_optin' => array(
				'title' => __('Video &amp; Signup Form', OP_SN),
				'options' => array(
					'video' => array(
						'module' => 'video',
						'title' => __('Video', OP_SN),
					),
					'signup_form' => array(
						'module' => 'signup_form',
						'title' => __('Optin Form', OP_SN),
						'mod_options' => array(
							'disable' => 'color_scheme|on_off_switch|content|submit_button',
							'add_wrapper' => false,
						),
						'use_content' => 'content',
						'use_button' => 'submit_button',
					),
					'content' => array(
						'module' => 'content_fields',
						'title' => __('Content', OP_SN),
						'mod_options' => array(
							'fields' => $content_fields
						)
					),
					'submit_button' => array(
						'module' => 'submit_button',
						'title' => __('Submit Button', OP_SN),
					),
				)
			),
			'video_content' => array(
				'title' => __('Video &amp; Content', OP_SN),
				'options' => array(
					'video' => array(
						'module' => 'video',
						'title' => __('Video', OP_SN),
					),
					'content' => array(
						'module' => 'content_fields',
						'title' => __('Content', OP_SN),
						'mod_options' => array(
							'fields' => array(
								'title' => array(
									'name' => __('Title', OP_SN),
									'help' => __('Enter the title to be displayed at the top of the content area', OP_SN),
								),
								'form_header' => array(
									'name' => __('Sub Title', OP_SN),
									'help' => __('Enter the sub title/message text above your submit button', OP_SN),
								),
								'footer_note' => array(
									'name' => __('Footer Text', OP_SN),
									'help' => __('Enter the message below the submit button (HTML allowed)', OP_SN),
								),
								'submit_button' => array(
									'name' => __('Submit Button', OP_SN),
									'help' => __('Enter the text for the button on your feature area', OP_SN),
								),
								'link_url'=>__('Button Link URL', OP_SN),
							),
						)
					)
				)
			),
			'image_hover_optin' => array(
				'title' => __('Rollover Image &amp; Signup Form', OP_SN),
				'options' => array(
					'image_fields' => array(
						'module' => 'content_fields',
						'title' => __('Images', OP_SN),
						'mod_options' => array(
							'fields' => array(
								'image' => array(
									'name' => __('Image', OP_SN),
									'help' => __('Enter the image for your homepage feature area.  This will be shown when the page is loaded', OP_SN),
								),
								'hover_image' => array(
									'name' => __('Hover Image', OP_SN),
									'help' => __('Enter the hover/rollover image for the homepage feature area.  This will be shown when the user hovers over the original image with their mouse.  We recommend keeping both images the same size/dimensions', OP_SN),
								)
							)
						),
						'template' => array($this,'hover_image'),
					),
					'signup_form' => array(
						'module' => 'signup_form',
						'title' => __('Optin Form', OP_SN),
						'mod_options' => array(
							'disable' => 'color_scheme|on_off_switch|content|submit_button',
							'add_wrapper' => false,
						),
						'use_content' => 'content',
						'use_button' => 'submit_button',
					),
					'content' => array(
						'module' => 'content_fields',
						'title' => __('Content', OP_SN),
						'mod_options' => array(
							'fields' => $content_fields
						)
					),
					'submit_button' => array(
						'module' => 'submit_button',
						'title' => __('Submit Button', OP_SN),
					),
				)
			),
			'html_content' => array(
				'title' => __('HTML Content', OP_SN),
				'options' => array(
					'content' => array(
						'module' => 'content_fields',
						'title' => __('Content', OP_SN),
						'mod_options' => array(
							'fields' => array(
								'content' => array(
									'name' => __('HTML Content', OP_SN),
									'type' => 'wysiwyg',
									'help' => __('Enter HTML content to show in your homepage feature area.', OP_SN),
								)
							)
						),
						'template' => array($this,'html_content'),
					)
				),
				'config' => array(
					'before' => '
<div class="featured-panel html-version">
	<div class="content-width cf">',
				)
			),
		);
		return $styles;
	}
	
	function html_content($content){
		$content = op_get_var($content,'content',array());
		$html = '
<div class="content">
'.apply_filters('the_content',op_get_var($content,'content')).'
</div>';
		return $html;
	}
	
	function hover_image($content){
		$content = op_get_var($content,'content',array());
		$image = op_get_var($content,'image');
		$hover_image = op_get_var($content,'hover_image');
		$html = '';
		if($image != ''){
			$html = '
<div class="op-hover-image">
	<img src="'.$image.'" alt=""'.($hover_image!=''?' class="normal"':'').' />'.($hover_image!=''?'
	<img src="'.$hover_image.'" class="hover" alt="" />':'').'
</div>';
		}
		return $html;
	}
}
$op_feature_area = new OptimizePress_Theme_Feature_Area();