<?php
function op_theme1_modules($sections){
	$sections = array_merge(array(
		'home_feature' => array(
			'title' => __('Homepage Feature Area', OP_SN),
			'module' => 'feature_area',
			'options' => array(
				'before' => '
<div class="featured-panel">
	<div class="content-width cf">',
				'after' => '
	</div>
</div>'
			),
		),
		'advertising' => array(
			'title' => __('Advertising', OP_SN),
			'module' => 'advertising',
			'options' => op_theme_config('mod_options','advertising')
		),
		'sidebar_optin' => array(
			'title' => __('Side Bar Optin', OP_SN),
			'module' => 'signup_form',
			'options' => op_theme_config('mod_options','sidebar_optin')
		)
	),$sections);
	return $sections;
}
add_filter('op_edit_sections_modules', 'op_theme1_modules');
