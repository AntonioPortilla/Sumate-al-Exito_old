<?php

add_action('init', array(AUTHORSURE_PROFILE,'admin_init'));

class authorsure_profile {
    
	static function admin_init() {
		add_action('load-profile.php', array(AUTHORSURE_PROFILE, 'load_page'));	
		add_action('load-user-edit.php', array(AUTHORSURE_PROFILE, 'load_page'));	
		add_action('edit_user_profile_update', array(AUTHORSURE_PROFILE, 'save_user'));
		add_action('personal_options_update', array(AUTHORSURE_PROFILE, 'save_profile'));		
	}

	static function get_user() {
		global $user_id;
		wp_reset_vars(array('user_id'));  //get ID of user being edited if not editing own profile 
		return (isset($user_id) && ($user_id > 0)) ? new WP_User((int) $user_id) : wp_get_current_user();	        
	}

	static function load_page() {
		$profile = self::get_user();
		if (authorsure::is_author($profile)) {
			add_filter('user_contactmethods', array(AUTHORSURE,'add_contactmethods_profile'),10,1);		
			add_filter( 'genesis_formatting_allowedtags', array(AUTHORSURE,'genesis_allow_img') );
			if (!self::is_profile() || current_user_can('manage_options'))
				add_action( self::is_profile() ? 'show_user_profile' : 'edit_user_profile', array(AUTHORSURE_PROFILE,'show_authors_panel'),8,2);
			if ('extended'==AUTHORSURE::get_option('author_bio'))
				add_action( self::is_profile() ? 'show_user_profile' : 'edit_user_profile', array(AUTHORSURE_PROFILE,'show_extended_bio'),8,2);
			authorsure::wordpress_allow_img();
			global $current_screen;
			if (method_exists($current_screen,'add_help_tab')) {
    		   $current_screen->add_help_tab( array(
        		'id'	=> 'authorsure_instructions_tab',
        		'title'	=> __('AuthorSure Instructions'),
        		'content'	=> '<h3>AuthorSure Instructions For Authors</h3>
<ol>
<li>Sign up for a Google account</li>
<li>Upload a photo to your Google profile</li>
<li>Add a contributor link that refers to your author page on this site. E.g. '.get_author_posts_url($profile->ID).'</li>
<li>Update your profile below with your Google Plus Profile URL</li>
<li>Enter the URL of a post you have written into the <a href="http://www.google.com/webmasters/tools/richsnippets">Google Rich Snippets Testing Tool</a> and 
check the page is valid and that your photo appears in the preview of the search results</li>
<li>Submit a <a href="http://www.authorsure.com/authorship-request">Authorship Request</a> to Google</li>
</ol>') );

	    		$current_screen->add_help_tab( array(
		        	'id'	=> 'authorsure_help_tab',
    		    	'title'	=> __('AuthorSure Profiles'),
        			'content'	=> __(
'<h3>AuthorSure Profiles</h3><p>In the <b>Contact Info</b> section below you can specify links to your other profies such as GooglePlus, Facebook, Twitter, etc. </p>
<p>The Authorsure plugin will show these links on your Author page with the rel="me" attribute set for authentication purposes.</p>
<p>It is important to fill in your <b>GooglePlus Profile URL</b> below if you want to verify your author profile with Google.</p>')) );
			}
		}
	}
	
	static function show_extended_bio($user) {
		$label = __('Extended Biographical Info');
		$help = __('Supply an extended bio to go on your author page. This can include links, images and videos.');
		$key = authorsure::get_extended_bio_key();
		$bio =  get_user_option($key, $user->ID);
		print <<< EXTENDED_BIO
<table class="form-table">
<tr>
	<th><label for="{$key}">{$label}</label></th>
	<td><textarea name="{$key}" id="{$key}" rows="10" cols="30">{$bio}</textarea><br />
	<span class="description">{$help}</span></td>
</tr>
</table>
EXTENDED_BIO;
    }
    
	static function show_authors_panel($user) {
		$label = __('Include on Author List');
		$help = __('Check the box to include the author in the list of authors. The author list is displayed by placing the shortcode [authorsure_authors] on a page.');
		$key = authorsure::get_show_author_key();
		$show_author = get_user_option($key, $user->ID);
		$show = $show_author ? 'checked="checked"' : '';
		print <<< AUTHOR_LIST
<h3>AuthorSure Bio Settings</h3>
<table class="form-table">
<tr>
	<th><label for="{$key}">Include On Author List?:</label></th>
	<td><input class="valinp" type="checkbox" name="{$key}" id="{$key}" {$show} value="1" /><br />
	<span class="description">{$help}</span></td>
</tr>
</table>
AUTHOR_LIST;
    }    
	
	static function is_profile() {
		return defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE; 
	}
	
	static function save_profile($user_id) {
		if (self::is_profile()) self::save($user_id);
	}

	static function save_user($user_id) {
		if ( ! self::is_profile()) self::save($user_id);
	}

	static function save($user_id) {
		$profiles = authorsure::get_pro_options();
		if (is_array($profiles)) 
			foreach ($profiles as $key => $labels) 
				if (array_key_exists($key,$_POST))
					if ($key == 'skype') {
						$_POST[$key] = self::sanitize_skype(strtolower(trim($_POST[$key])));
					} else 
						$_POST[$key] = esc_url(strtolower(trim($_POST[$key])));
		
		$extended_bio_key = authorsure::get_extended_bio_key();	
		$old_val =  get_user_option($extended_bio_key, $user->ID);		
		$new_val = array_key_exists($extended_bio_key,$_POST) ? $_POST[$extended_bio_key] : '';
		if  ($old_val != $new_val) update_usermeta( $user_id, $extended_bio_key, $_POST[$extended_bio_key]);		
		
		$show_author_key = authorsure::get_show_author_key();	
		$new_val = array_key_exists($show_author_key,$_POST) ? $_POST[$show_author_key] : false;
		$old_val =  get_user_option($show_author_key, $user_id);
		if  ($old_val != $new_val) update_usermeta( $user_id, $show_author_key, $new_val);			
	}	

	static function sanitize_skype($skype_name) {
		if ((strlen($skype_name) > 6) && ('skype:'==substr($skype_name,0,6))) $skype_name = substr($skype_name,6);
		if (strpos($skype_name,'?') == FALSE) 
			return $skype_name;
		else
			return substr($skype_name,0, strpos($skype_name,'?'));
	}

}
?>