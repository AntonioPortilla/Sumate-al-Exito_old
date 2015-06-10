<?php
require_once( dirname( __FILE__ ) . '/lib/framework.php' );

if (!class_exists('Op_Arrow_Walker_Nav_Menu')) {
	class Op_Arrow_Walker_Nav_Menu extends Walker_Nav_Menu
	{
		public function display_element($el, &$children, $max_depth, $depth = 0, $args, &$output)
		{
			$id = $this->db_fields['id'];    

			if(isset($children[$el->$id])) {
				$el->classes[] = 'has_children';    	
			}			  

			parent::display_element($el, $children, $max_depth, $depth, $args, $output);
		}
	}	
}


add_filter('the_generator','killVersion');
function killVersion() { 
	return ''; 
}
remove_action('wp_head', 'wp_generator');
remove_action( 'wp_head', 'wlwmanifest_link' ) ; 
remove_action( 'wp_head', 'rsd_link' ) ;

if (!current_user_can('edit_users')) {
    add_action('init', create_function('$a', "remove_action('init', 'wp_version_check');"), 2);
    add_filter('pre_option_update_core', create_function('$a', "return null;"));
}
