<?php

/**
 * Creates from a number of given seconds a readable duration ( HH:MM:SS )
 * @param int $seconds
 */
function cbc_human_time( $seconds ){
	
	$seconds = absint( $seconds );
	
	if( $seconds < 0 ){
		return;
	}
	
	$h = floor( $seconds / 3600 );
	$m = floor( $seconds % 3600 / 60 );
	$s = floor( $seconds %3600 % 60 );
	
	return ( ($h > 0 ? $h . ":" : "").($m > 0 ? ($h > 0 && $m < 10 ? "0" : "") . $m . ":" : "0:") . ($s < 10 ? "0" : "") . $s);
	
}

/**
 * Query YouTube for single video details
 * @param string $video_id
 * @param string $source
 */
function cbc_query_video( $video_id, $source = 'youtube' ){
	if( empty( $video_id ) ){
		return false;
	}	
	$sources = array(
		'youtube' => 'http://gdata.youtube.com/feeds/api/videos/%s?v=2&alt=jsonc'
	);
	if( !array_key_exists($source, $sources) ){
		return false;
	}
	
	$url 		= $sources[ $source ];
	$request 	= wp_remote_get( sprintf( $url, $video_id ) );
	
	return $request;
}

/**
 * @deprecated 
 * @since 1.8.1
 * 
 * Use ccb_is_video() instead
 */
function ccb_is_video_post(){
	return ccb_is_video();	
}

/**
 * Utility function. Checks if a given or current post is video created by the plugin 
 * @param object $post
 */
function ccb_is_video( $post = false ){
	if( !$post ){
		global $post;
	}
	if( !$post ){
		return false;
	}
	
	global $CBC_POST_TYPE;
	if( $CBC_POST_TYPE->get_post_type() == $post->post_type ){
		return true;
	}
	
	if( 'post' == $post->post_type ){
		$is_video = get_post_meta($post->ID, '__cbc_is_video', true);
		if( $is_video ){
			return true;
		}
	}
	
	return false;	
}

/**
 * Adds video player script to page
 */
function ccb_enqueue_player(){	
	wp_enqueue_script(
		'ccb-video-player',
		CBC_URL.'assets/front-end/js/video-player.js',
		array('jquery', 'swfobject'),
		'1.0'
	);
	
	wp_enqueue_style(
		'ccb-video-player',
		CBC_URL.'assets/front-end/css/video-player.css'
	);
}

/**
 * Formats the response from the feed for a single entry
 * @param array $entry
 */
function cbc_format_video_entry( $raw_entry ){
	// playlists have individual items stored under key video
	if( array_key_exists('video', $raw_entry) ){
		$raw_entry = $raw_entry['video'];
	}
		
	/*
	if( isset( $raw_entry['status'] ) ){
		if( 'restricted' == $raw_entry['status']['value'] && 'private' == $raw_entry['status']['reason'] ){
			return false;
		}
	}
	*/
	// permissions
	$entry = array();
	
	/*
	$permissions = array();
	foreach( $raw_entry['accessControl'] as $k => $p ){
		$permissions[ $k ] = 'allowed' == $p;
	}
	*/
	
	$thumbnails = array();
	if( isset( $raw_entry['thumbnail'] ) ){
		foreach( $raw_entry['thumbnail'] as $thumbnail ){
			$thumbnails[] = $thumbnail;
		}
	}	
		
	$entry = array(
		'video_id'		=> $raw_entry['id'],
		'uploader'		=> $raw_entry['uploader'],
		'published' 	=> $raw_entry['uploaded'],
		'updated'		=> $raw_entry['updated'],
		'title'			=> $raw_entry['title'],
		'description' 	=> $raw_entry['description'],
		'category'		=> $raw_entry['category'],
		'duration'		=> $raw_entry['duration'],
		'thumbnails'	=> $thumbnails,				
		'stats'			=> array(
			'comments'		=> isset( $raw_entry['commentCount'] ) 	? $raw_entry['commentCount'] 	: 0,
			'rating'		=> isset( $raw_entry['rating'] ) 		? $raw_entry['rating'] 			: 0,
			'rating_count'	=> isset( $raw_entry['ratingCount'] )	? $raw_entry['ratingCount']		: 0,
			'views'			=> isset( $raw_entry['viewCount'] ) 	? $raw_entry['viewCount'] 		: 0,
		)				
	);
	
	return $entry;
}

/**
 * Utility function, returns plugin default settings
 */
function cbc_plugin_settings_defaults(){
	$defaults = array(
		'public'				=> true, // post type is public or not
		'archives'				=> false, // display video embed on archive pages
		'homepage'				=> false, // include custom post type on homepage
		'main_rss'				=> false, // include custom post type into the main RSS feed
		'use_microdata'			=> false, // put microdata on video pages ( more details on: http://schema.org )
		'post_type_post'		=> false, // when true all videos will be imported as post type post and will disregard the theme compatibility layer
		// rewrite	
		'post_slug'				=> 'video',
		'taxonomy_slug'			=> 'videos',	
		// bulk import
		'import_categories'		=> true, // import categories from YouTube
		'import_title' 			=> true, // import titles on custom posts
		'import_description' 	=> 'post_content', // import descriptions on custom posts
		'remove_after_text'		=> '', // descriptions that have this content will be truncated up to this text
		'prevent_autoembed'		=> false, // prevent autoembeds on video posts
		'make_clickable'		=> false, // make urls pasted in content clickable
		'import_date'			=> false, // import video date as post date
		'featured_image'		=> false, // set thumbnail as featured image
		'import_results' 		=> 100, // default number of feed results to display
		'import_status'			=> 'draft', // default import status of videos
		'automatic_import_uses'	=> 'wp_cron', // set automatic import to run on wp cron or external cron job		
		// automatic import
		'import_frequency'		=> 5, // in minutes
		'import_quantity'		=> 20,
		'manual_import_per_page' => 20		
	);
	return $defaults;
}

/**
 * Utility function, returns plugin settings
 */
function cbc_get_settings(){
	$defaults = cbc_plugin_settings_defaults();
	$option = get_option('_cbc_plugin_settings', $defaults);
	
	foreach( $defaults as $k => $v ){
		if( !isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	return $option;
}

/**
 * Verification function to see if setting to force imports as posts is set.
 */
function import_as_post(){
	$settings = cbc_get_settings();
	if( isset( $settings['post_type_post'] ) && $settings['post_type_post'] ){
		return (bool) $settings['post_type_post'];
	}
	return false;
}

/**
 * Utility function, updates plugin settings
 */
function cbc_update_settings(){	
	$defaults = cbc_plugin_settings_defaults();
	foreach( $defaults as $key => $val ){
		if( is_numeric( $val ) ){
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = (int)$_POST[ $key ];
			}
			continue;
		}
		if( is_bool( $val ) ){
			$defaults[ $key ] = isset( $_POST[ $key ] );
			continue;
		}
		
		if( isset( $_POST[ $key ] ) ){
			$defaults[ $key ] = $_POST[ $key ];
		}
	}
	
	// rewrite
	$plugin_settings = cbc_get_settings();
	$flush_rules = false;
	if( isset( $_POST['post_slug'] ) ){
		$post_slug = sanitize_title( $_POST['post_slug'] );
		if( !empty( $_POST['post_slug'] ) && $plugin_settings['post_slug'] !== $post_slug ){
			$defaults['post_slug'] = $post_slug;
			$flush_rules = true;
		}else{
			$defaults['post_slug'] = $plugin_settings['post_slug'];
		}
	}
	if( isset( $_POST['taxonomy_slug'] ) ){
		$tax_slug = sanitize_title( $_POST['taxonomy_slug'] );
		if( !empty( $_POST['taxonomy_slug'] ) && $plugin_settings['taxonomy_slug'] !== $tax_slug ){
			$defaults['taxonomy_slug'] = $tax_slug;
			$flush_rules = true;
		}else{
			$defaults['taxonomy_slug'] = $plugin_settings['taxonomy_slug'];
		}
	}
	
	
	update_option('_cbc_plugin_settings', $defaults);
	// update automatic imports
	global $CBC_AUTOMATIC_IMPORT;
	$CBC_AUTOMATIC_IMPORT->update_transient();
	
	if( $flush_rules ){	
		// create rewrite ( soft )
		global $CBC_POST_TYPE;
		// register custom post
		$CBC_POST_TYPE->register_post();
		// create rewrite ( soft )
		flush_rewrite_rules( false );
	}	
}

/**
 * Global player settings defaults.
 */
function cbc_player_settings_defaults(){
	$defaults = array(
		'controls' 	=> 1, // show player controls. Values: 0 or 1
		'autohide' 	=> 0, // 0 - always show controls; 1 - hide controls when playing; 2 - hide progress bar when playing
		'fs'		=> 1, // 0 - fullscreen button hidden; 1 - fullscreen button displayed
		'theme'		=> 'dark', // dark or light
		'color'		=> 'red', // red or white	
	
		'iv_load_policy' => 1, // 1 - show annotations; 3 - hide annotations
		'modestbranding' => 1, // 1 - small branding
		'rel'			 =>	1, // 0 - don't show related videos when video ends; 1 - show related videos when video ends
		'showinfo'		 => 0, // 0 - don't show video info by default; 1 - show video info in player
	
		'autoplay'	=> 0, // 0 - on load, player won't play video; 1 - on load player plays video automatically
		//'loop'		=> 0, // 0 - video won't start again once finished; 1 - video will play again once finished

		'disablekb'	=> 0, // 0 - allow keyboard controls; 1 - disable keyboard controls

		// extra settings
		'aspect_ratio'		=> '16x9',
		'width'				=> 640,
		'video_position' 	=> 'below-content', // in front-end custom post, where to display the video: above or below post content
		'volume'			=> 30, // video default volume	
	);
	return $defaults;
}

/**
 * Get general player settings
 */
function cbc_get_player_settings(){
	$defaults 	= cbc_player_settings_defaults();
	$option 	= get_option('_cbc_player_settings', $defaults);
	
	foreach( $defaults as $k => $v ){
		if( !isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	// various player outputs may set their own player settings. Return those.
	global $CBC_PLAYER_SETTINGS;
	if( $CBC_PLAYER_SETTINGS ){
		foreach( $option as $k => $v ){
			if( isset( $CBC_PLAYER_SETTINGS[$k] ) ){
				$option[$k] = $CBC_PLAYER_SETTINGS[$k];
			}
		}
	}
	
	return $option;
}

/**
 * Update general player settings
 */
function cbc_update_player_settings(){
	$defaults = cbc_player_settings_defaults();
	foreach( $defaults as $key => $val ){
		if( is_numeric( $val ) ){
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = (int)$_POST[ $key ];
			}else{
				$defaults[ $key ] = 0;
			}
			continue;
		}
		if( is_bool( $val ) ){
			$defaults[ $key ] = isset( $_POST[ $key ] );
			continue;
		}
		
		if( isset( $_POST[ $key ] ) ){
			$defaults[ $key ] = $_POST[ $key ];
		}
	}
	
	update_option('_cbc_player_settings', $defaults);	
}

/**
 * Displays checked argument in checkbox
 * @param bool $val
 * @param bool $echo
 */
function cbc_check( $val, $echo = true ){
	$checked = '';
	if( is_bool($val) && $val ){
		$checked = ' checked="checked"';
	}
	if( $echo ){
		echo $checked;
	}else{
		return $checked;
	}	
}

/**
 * Displays a style="display:hidden;" if passed $val is bool and false
 * @param bool $val
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cbc_hide( $val, $compare = false, $before=' style="', $after = '"', $echo = true ){
	$output = '';
	if(  $val == $compare ){
		$output .= $before.'display:none;'.$after;
	}
	if( $echo ){
		echo $output;
	}else{
		return $output;
	}
}

/**
 * Display select box
 * @param array $args - see $defaults in function
 * @param bool $echo
 */
function cbc_select( $args = array(), $echo = true ){
	
	$defaults = array(
		'options' 	=> array(),
		'name'		=> false,
		'id'		=> false,
		'class'		=> '',
		'selected'	=> false,
		'use_keys'	=> true
	);
	
	$o = wp_parse_args($args, $defaults);
	
	if( !$o['id'] ){
		$output = sprintf( '<select name="%1$s" id="%1$s" class="%2$s">', $o['name'], $o['class']);
	}else{
		$output = sprintf( '<select name="%1$s" id="%2$s" class="%3$s">', $o['name'], $o['id'], $o['class']);
	}	
	
	foreach( $o['options'] as $val => $text ){
		$opt = '<option value="%1$s"%2$s>%3$s</option>';
		
		$value = $o['use_keys'] ? $val : $text;
		$c = $o['use_keys'] ? $val == $o['selected'] : $text == $o['selected'];
		$checked = $c ? ' selected="selected"' : '';		
		$output .= sprintf($opt, $value, $checked, $text);		
	}
	
	$output .= '</select>';
	
	if( $echo ){
		echo $output;
	}
	
	return $output;
}

/**
 * Calculate player height from given aspect ratio and width
 * @param string $aspect_ratio
 * @param int $width
 */
function cbc_player_height( $aspect_ratio, $width ){
	$width = absint($width);
	$height = 0;
	switch( $aspect_ratio ){
		case '4x3':
			$height = ($width * 3) / 4;
		break;
		case '16x9':
		default:	
			$height = ($width * 9) / 16;
		break;	
	}
	return $height;
}

/**
 * Single post default settings
 */
function ccb_post_settings_defaults(){
	// general player settings
	$plugin_defaults = cbc_get_player_settings();	
	return $plugin_defaults;
}

/**
 * Returns playback settings set on a video post
 */
function ccb_get_video_settings( $post_id = false, $output = false ){
	global $CBC_POST_TYPE;
	if( !$post_id ){
		global $post;
		if( !$post || !ccb_is_video($post) ){
			return false;
		}
		$post_id = $post->ID;		
	}else{
		$post = get_post( $post_id );
		if( !$post || !ccb_is_video($post) ){
			return false;
		}
	}
	
	$defaults = ccb_post_settings_defaults();
	$option = get_post_meta( $post_id, '__cbc_playback_settings', true );
	
	foreach( $defaults as $k => $v ){
		if( !isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	if( $output ){
		foreach( $option as $k => $v ){
			if( is_bool( $v ) ){
				$option[$k] = absint( $v );
			}
		}
	}
	
	return $option;
}

/**
 * Utility function, updates video settings
 */
function ccb_update_video_settings( $post_id ){
	
	if( !$post_id ){
		return false;
	}
	
	$post = get_post( $post_id );
	if( !$post || !ccb_is_video( $post ) ){
		return false;
	}
		
	$defaults = ccb_post_settings_defaults();
	foreach( $defaults as $key => $val ){
		if( is_numeric( $val ) ){
			if( isset( $_POST[ $key ] ) ){
				$defaults[ $key ] = (int)$_POST[ $key ];
			}else{
				$defaults[ $key ] = 0;
			}
			continue;
		}
		if( is_bool( $val ) ){
			$defaults[ $key ] = isset( $_POST[ $key ] );
			continue;
		}
		
		if( isset( $_POST[ $key ] ) ){
			$defaults[ $key ] = $_POST[ $key ];
		}
	}
	
	update_post_meta($post_id, '__cbc_playback_settings', $defaults);	
}

/**
 * Set thumbnail as featured image for a given post ID
 * @param unknown_type $post_id
 */
function cbc_set_featured_image( $post_id, $post_type ){
	
	if( !$post_id ){
		return false;
	}
	
	$post = get_post( $post_id );		
	if( !$post ){
		return false;
	}

	if( $post->post_type !== $post_type ){
		$video_id = get_post_meta( $post_id, '__cbc_video_id', true );
		if( !$video_id ){
			return false;
		}
		
		$request = cbc_query_video($video_id);
		if( $request && 200 == $request['response']['code'] ){				
			$data 		= json_decode( $request['body'], true );
			$video_meta = cbc_format_video_entry( $data['data'] );
		}else{
			return false;
		}		
	}else{	
		$video_meta = get_post_meta( $post_id, '__cbc_video_data', true );
	}	
		
	if( !$video_meta ){
		return false;
	}
		
	// check if thumbnail was already imported
	$attachment = get_posts( array(
		'post_type' 	=> 'attachment',
		'meta_key'  	=> 'video_thumbnail',
		'meta_value'	=> $video_meta['video_id']
	));
	// if thumbnail exists, return it
	if( $attachment ){
		// set image as featured for current post
		set_post_thumbnail( $post_id, $attachment[0]->ID );
		return array(
			'post_id' 		=> $post_id,
			'attachment_id' => $attachment[0]->ID
		);
	}

	// get the thumbnail	
	$response = wp_remote_get( $video_meta['thumbnails'][1], array( 'sslverify' => false ) );
	if( is_wp_error( $response ) ) {
		return false;
	} 
		
	$image_contents = $response['body'];
	$image_type 	= wp_remote_retrieve_header( $response, 'content-type' );
	// Translate MIME type into an extension
	if ( $image_type == 'image/jpeg' ){
		$image_extension = '.jpg';
	}elseif ( $image_extension == 'image/png' ){
		$image_extension = '.png';
	}
			
	// Construct a file name using post slug and extension
	$new_filename = urldecode( basename( get_permalink( $post_id ) ) ) . $image_extension;

	// Save the image bits using the new filename
	$upload = wp_upload_bits( $new_filename, null, $image_contents );
	if ( $upload['error'] ) {
		return false;
	}
		
	$image_url 	= $upload['url'];
	$filename 	= $upload['file'];

	$wp_filetype = wp_check_filetype( basename( $filename ), null );
	$attachment = array(
		'post_mime_type'	=> $wp_filetype['type'],
		'post_title'		=> get_the_title( $post_id ),
		'post_content'		=> '',
		'post_status'		=> 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	// Add field to mark image as a video thumbnail
	update_post_meta( $attach_id, 'video_thumbnail', $video_meta['video_id'] );
		
	// set image as featured for current post
	update_post_meta( $post_id, '_thumbnail_id', $attach_id );
	
	return array(
		'post_id' 		=> $post_id,
		'attachment_id' => $attach_id
	);	
}

/**
 * Register widgets.
 */
function cbc_load_widgets() {
	// check if posts are public
	$options = cbc_get_settings();
	if( !isset( $options['public'] ) || !$options['public'] ){
		return;
	}
		
	include CBC_PATH.'includes/libs/latest-videos-widget.class.php';
	register_widget( 'CBC_Latest_Videos_Widget' );
	
	include CBC_PATH.'includes/libs/videos-taxonomy-widget.class.php';
	register_widget( 'CBC_Videos_Taxonomy_Widget' );
	
}
add_action( 'widgets_init', 'cbc_load_widgets' );

/**
 * TinyMce
 */
function ccb_tinymce_buttons(){
	// Don't bother doing this stuff if the current user lacks permissions
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
		return;
 	
	// Don't load unless is post editing (includes post, page and any custom posts set)
	$screen = get_current_screen();
	global $CBC_POST_TYPE;
	if( 'post' != $screen->base || ccb_is_video() ){
		return;
	}  
	
	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true') {
   		
		wp_enqueue_script(array(
			'jquery-ui-dialog'
		));
			
		wp_enqueue_style(array(
			'wp-jquery-ui-dialog'
		));
   	
	    add_filter('mce_external_plugins', 'ccb_tinymce_plugin');
	    add_filter('mce_buttons', 'ccb_register_buttons');
   }	
}

function ccb_register_buttons($buttons) {	
	array_push($buttons, 'separator', 'ccb_shortcode');
	return $buttons;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function ccb_tinymce_plugin($plugin_array) {
	$plugin_array['ccb_shortcode'] = CBC_URL.'assets/back-end/js/tinymce/shortcode.js';
	return $plugin_array;
}

add_action('admin_head', 'ccb_tinymce_buttons');

function ccb_load_post_edit_styling(){
	global $post;
	if( !$post || ccb_is_video($post) ){
		return;
	}
	
	wp_enqueue_style(
		'ccb-shortcode-modal',
		CBC_URL.'assets/back-end/css/shortcode-modal.css',
		false,
		'1.0'
	);
	
	wp_enqueue_script(
		'ccb-shortcode-modal',
		CBC_URL.'assets/back-end/js/shortcode-modal.js',
		false,
		'1.0'
	);
	
	$messages = array(
		'playlist_title' => __('Videos in playlist', 'cbc_video'),
		'no_videos'		 => __('No videos selected.<br />To create a playlist check some videos from the list on the right.', 'cbc_video'),
		'deleteItem'	 => __('Delete from playlist', 'cbc_video'),
		'insert_playlist'=> __('Add shortcode into post', 'cbc_video')
	);
	
	wp_localize_script('ccb-shortcode-modal', 'CBC_SHORTCODE_MODAL', $messages);
}
add_action('admin_print_styles-post.php', 'ccb_load_post_edit_styling');
add_action('admin_print_styles-post-new.php', 'ccb_load_post_edit_styling');

/**
 * Enqueue some funcitonality scripts on widgets page
 */
function ccb_widgets_scripts(){
	$plugin_settings = cbc_get_settings();
	if( isset( $plugin_settings['public'] ) && !$plugin_settings['public'] ){
		return;
	}
	
	wp_enqueue_script(
		'cbc-video-edit',
		CBC_URL.'assets/back-end/js/video-edit.js',
		array('jquery'),
		'1.0'
	);
}
add_action('admin_print_scripts-widgets.php', 'ccb_widgets_scripts');

/**  Bulk actions hack - remove when issue on creating new bulk actions is solved in WP **/

function cbc_bulk_actions_js(){
	global $CBC_POST_TYPE;
	if( !isset( $_GET['post_type'] ) || $CBC_POST_TYPE->get_post_type() != $_GET['post_type'] ){
		return;
	}
	
	wp_enqueue_script(
		'cbc-bulk-actions',
		CBC_URL.'assets/back-end/js/bulk-actions.js',
		array('jquery'),
		'1.0'
	);
	
	wp_enqueue_style(
		'cbc-bulk-actions-response',
		CBC_URL.'assets/back-end/css/video-list.css',
		false,
		'1.0'
	);
	
	wp_localize_script(
		'cbc-bulk-actions', 
		'cbc_bulk_actions', 
		array(
			'actions' 		=> cbc_actions(),
			'wait'			=> __('Processing, please wait...', 'cbc_video'),
			'wait_longer'	=> __('Not done yet, please be patient...', 'cbc_video'),
			'maybe_error' 	=> __('There was an error while importing your thumbnails. Please try again.', 'cbc_video')
		)	
	);
	
}
add_action('admin_print_scripts-edit.php', 'cbc_bulk_actions_js');

/**
 * A list of allowed actions
 */
function cbc_actions(){	
	$actions = array(
		'cbc_thumbnail' => __('Import thumbnails', 'cbc_video')
	);
	
	return $actions;
}

/**
 * AJAX response to thumbnail importing
 */
function cbc_ajax_import_thumbnails(){
	
	if( !isset( $_REQUEST['action'] ) && !isset( $_REQUEST['action2'] ) ){
		wp_send_json_error( __('Sorry, there was an error, please try again.', 'cbc_video') );
	}
	
	if( !isset( $_REQUEST['post'] ) || empty( $_REQUEST['post'] ) ){
		wp_send_json_error( __('<strong>Error!</strong> Select some posts to import thumbnails for.', 'cbc_video') );
	}
	
	global $CBC_POST_TYPE;
	if( !isset( $_REQUEST['post_type'] ) || $CBC_POST_TYPE->get_post_type() != $_REQUEST['post_type'] ){
		wp_send_json_error( __('Thumbnail imports work only for custom post type.', 'cbc_video') );
	}
	
	$action = false;
	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
		$action = $_REQUEST['action'];

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
		$action = $_REQUEST['action2'];
	
	if( !$action || !array_key_exists($action, cbc_actions()) ){
		wp_send_json_error( __('Please select a valid action.', 'cbc_video') );
	}	
		
	// security check
	check_admin_referer('bulk-posts');

	$post_ids = array_map('intval', $_REQUEST['post']);
	foreach( $post_ids as $post_id ){
		
		switch( $action ){
			case 'cbc_thumbnail':
				cbc_set_featured_image( $post_id, $CBC_POST_TYPE->get_post_type() );		
			break;	
		}		
	}
	
	wp_send_json_success( __('All thumbnails successfully imported.', 'cbc_video') );
	
	die();
}
add_action('wp_ajax_cbc_thumbnail', 'cbc_ajax_import_thumbnails');

/**  /Bulk actions hack  **/

/**
 * TEMPLATING
 */

/**
 * Outputs default player data
 */
function cbc_output_player_data( $echo = true ){
	$player = cbc_get_player_settings();
	$attributes = cbc_data_attributes( $player, $echo );	
	return $attributes;
}

/**
 * Output video parameters as data-* attributes
 * @param array $array - key=>value pairs
 * @param bool $echo	
 */
function cbc_data_attributes( $attributes, $echo = false ){
	$result = array();
	foreach( $attributes as $key=>$value ){
		$result[] = sprintf( 'data-%s="%s"', $key, $value );
	}
	if( $echo ){
		echo implode(' ', $result);
	}else{
		return implode(' ', $result);
	}	
}

/**
 * Outputs the default player size
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cbc_output_player_size( $before = ' style="', $after='"', $echo = true ){
	$player = cbc_get_player_settings();
	$height = cbc_player_height($player['aspect_ratio'], $player['width']);
	$output = 'width:'.$player['width'].'px; height:'.$height.'px;';
	if( $echo ){
		echo $before.$output.$after;
	}
	
	return $before.$output.$after;
}

/**
 * Output width according to player
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cbc_output_width( $before = ' style="', $after='"', $echo = true ){
	$player = cbc_get_player_settings();
	if( $echo ){
		echo $before.'width: '.$player['width'].'px; '.$after;
	}
	return $before.'width: '.$player['width'].'px; '.$after;
}

/**
 * Output video thumbnail
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cbc_output_thumbnail( $before = '', $after = '', $echo = true ){
	global $cbc_video;
	$output = '';
	if( isset( $cbc_video['video_data']['thumbnails'][0] ) ){
		$output = sprintf('<img src="%s" alt="" />', $cbc_video['video_data']['thumbnails'][0]);
	}
	if( $echo ){
		echo $before.$output.$after;
	}
	return $before.$output.$after;
}

/**
 * Output video title
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cbc_output_title( $include_duration = true,  $before = '', $after = '', $echo = true  ){
	global $cbc_video;
	$output = '';
	if( isset( $cbc_video['title'] ) ){
		$output = $cbc_video['title'];
	}
	
	if( $include_duration ){
		$output .= ' <span class="duration">['.cbc_human_time( $cbc_video['video_data']['duration'] ).']</span>';
	}
	
	if( $echo ){
		echo $before.$output.$after;
	}
	return $before.$output.$after;
}

/**
 * Outputs video data
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cbc_output_video_data( $before = " ", $after="", $echo = true ){
	global $cbc_video;
	
	$video_settings = ccb_get_video_settings( $cbc_video['ID'] );	
	$video_id 		= $cbc_video['video_data']['video_id'];
	$data = array(
		'video_id' 	=> $video_id,
		'autoplay' 	=> $video_settings['autoplay'],
		'volume'  	=> $video_settings['volume']
	);
	
	$output = cbc_data_attributes($data);
	if( $echo ){
		echo $before.$output.$after;
	}
	
	return $before.$output.$after;
}

function cbc_video_post_permalink( $echo  = true ){
	global $cbc_video;
	
	$pl = get_permalink( $cbc_video['ID'] );
	
	if( $echo ){
		echo $pl;
	}
	
	return $pl;
	
}

/**
 * Themes compatibility layer
 */

/**
 * Check if theme is supported by the plugin.
 * Returns false or an array containing a mapping for custom post fields to store information on
 */
function cbc_check_theme_support(){	
	
	global $CBC_THIRD_PARTY_THEME;
	if( !$CBC_THIRD_PARTY_THEME ){
		$CBC_THIRD_PARTY_THEME = new CBC_Third_Party_Compat();
	}
	$theme = $CBC_THIRD_PARTY_THEME->get_theme_compatibility();
	return $theme;
}

/**
 * Returns all compatible themes details
 */
function cbc_get_compatible_themes(){
	// access the theme support function to create the class instance
	cbc_check_theme_support();
	global $CBC_THIRD_PARTY_THEME;
	
	return $CBC_THIRD_PARTY_THEME->get_compatible_themes();
}

/**
 * Playlists
 */

/**
 * Global playlist settings defaults.
 */
function cbc_playlist_settings_defaults(){
	$defaults = array(
		'post_title' 		=> '',
		'playlist_type'		=> 'user',
		'playlist_id'		=> '',
		'playlist_live'		=> true,
		'theme_import'		=> false,
		'native_tax'		=> -1,
		'theme_tax'			=> -1,
		'import_user'		=> -1,
		'start_date'		=> false,
		'no_reiterate'		=> false
	);
	return $defaults;
}

/**
 * Get general playlist settings
 */
function cbc_get_playlist_settings( $post_id ){
	$defaults 	= cbc_playlist_settings_defaults();
	$option 	= get_post_meta($post_id, '_cbc_playlist_settings', true);
	
	foreach( $defaults as $k => $v ){
		if( !isset( $option[ $k ] ) ){
			$option[ $k ] = $v;
		}
	}
	
	return $option;
}

/**
 * Templating function for administration purposes. Displays the next update session of playlists automatic update.
 * @param string $before
 * @param string $after
 * @param bool $echo
 */
function cbc_automatic_update_message( $before = '', $after = '', $echo = true ){
	global $CBC_AUTOMATIC_IMPORT;
	$import_data 	= $CBC_AUTOMATIC_IMPORT->get_update();
	
	if( !$import_data ){
		return;
	}
	
	$post = get_post( $import_data['post_id'] );
	
	if( !$post ){
		return;
	}
	
	$options = cbc_get_settings();	
	$timeout = cbc_human_update_delay();
	// start messages
	if( $timeout['countdown'] ){
		$message = sprintf( __('Next automatic update scheduled in <strong>%s</strong>.', 'cbc_video'), $timeout['time'] );
	}else{
		$message = sprintf( __('Automatic update is late by <strong>%s</strong>.', 'cbc_video'), $timeout['time'] );
		if( $timeout['seconds'] > $CBC_AUTOMATIC_IMPORT->get_delay() ){
			$message .= ' '.__( 'Please check your server CRON JOB urgently.', 'cbc_video' );
		}else{		
			$message .= sprintf( ' ' . __('If delay exceeds <strong>%s</strong> please check your server CRON JOB.', 'cbc_video'), cbc_human_time($CBC_AUTOMATIC_IMPORT->get_delay()) );
		}
	}	
	
	$message.= '<br />' . sprintf( __('Last updated playlist: <em>%s</em>.', 'cbc_video'), $post->post_title );
	$message.= '<br /><span style="color:red;">' . __('Already existing videos will not be imported twice.', 'cbc_video') . '</span>';
	
	if('server_cron' == $options['automatic_import_uses']){
		$message.= '<br /><br /><span style="color:red">'.sprintf(__('Automatic update runs by SERVER CRON. <br />Please make sure you have set up a cron job to open address <strong style="background:black; color:white; padding:0px 2px;">%s</strong>', 'cbc_video'), cbc_get_server_cron_address()).'</span>';
	}
	
	if( $echo ){
		echo $before.$message.$after;
	}
	return $before.$message.$after;
}

/**
 * For server cron jobs, calls should be made having variable cbc_external_cron set to string true
 * 
 * ie: http://some_wp_website.com?cbc_external_cron=true
 * 
 * The function below tests the existance of the GET variable
 */
function cbc_is_server_cron_call(){
	if( isset( $_GET['cbc_external_cron'] ) && 'true' == $_GET['cbc_external_cron'] ){
		return true;
	}
	return false;
}

function cbc_get_server_cron_address(){
	return get_bloginfo('url').'/?cbc_external_cron=true';
}

function cbc_human_update_delay(){
	
	global $CBC_AUTOMATIC_IMPORT;
	$import_data 	= $CBC_AUTOMATIC_IMPORT->get_update();
	$delay 			= $CBC_AUTOMATIC_IMPORT->get_delay();
	
	if( !$import_data || isset( $import_data['empty'] ) ){
		return;
	}
	
	// get the time
	$current_time = time();
	$countdown = true;
	// calculate delay for outdated cron jobs - for server cron
	if( $current_time - $import_data['time'] > $delay ){
		$diff = $current_time - ($import_data['time'] + $delay);
		$countdown = false;
	}else{// normal delay countdown	
		$diff = $delay -( $current_time - $import_data['time'] );
	}	
	return array( 'time' => cbc_human_time($diff), 'seconds' => $diff, 'countdown' => $countdown );
}

function cbc_automatic_update_timing(){
	
	$values = array(
		'1'		=> __('minute', 'cbc_video'),
		'5'		=> __('5 minutes', 'cbc_video'),
		'15' 	=> __('15 minutes', 'cbc_video'),
		'30' 	=> __('30 minutes', 'cbc_video'),
		'60'	=> __('hour', 'cbc_video'),
		'120'	=> __('2 hours', 'cbc_video'),
		'180'	=> __('3 hours', 'cbc_video'),
		'360'	=> __('6 hours', 'cbc_video'),
		'720'	=> __('12 hours', 'cbc_video'),
		'1440'	=> __('day', 'cbc_video')
	);
	return $values;	
}

function cbc_automatic_update_batches(){
	
	$values = array(
		'1'	 => __('1 video', 'cbc_video'),
		'5'	 => __('5 videos', 'cbc_video'),
		'10' => __('10 videos', 'cbc_video'),
		'15' => __('15 videos', 'cbc_video'),
		'20' => __('20 videos', 'cbc_video'),
		'25' => __('25 videos', 'cbc_video'),
		'30' => __('30 videos', 'cbc_video'),
		'40' => __('40 videos', 'cbc_video'),
		'50' => __('50 videos', 'cbc_video')
	);
	
	return $values;
	
}

/**
 * Add microdata on video pages
 * @param string/HTML $content
 */
function cbc_video_schema( $content ){
	
	// check if microdata insertion is permitted
	$settings = cbc_get_settings();
	if( !isset( $settings['use_microdata'] ) || !$settings['use_microdata'] ){
		return $content;
	}
	// check the post
	global $post;
	if( !$post || !is_object( $post ) ){
		return $content;
	}
	// check if feed
	if ( is_feed() ){
		return $content;
	}
	// get video data from post
	$video_data = get_post_meta( $post->ID, '__cbc_video_data', true );
	if( !$video_data ){
		// check if post has video ID
		$video_id = get_post_meta( $post->ID, '__cbc_video_id', true );
		if( !$video_id ){
			return $content;
		}
		
		$request = cbc_query_video( $video_id );
		if( is_wp_error( $request ) ){
			return $content;
		}		
		if(  200 == $request['response']['code'] ){				
			$data 		= json_decode( $request['body'], true );
			$video_data = cbc_format_video_entry( $data['data'] );
			update_post_meta($post->ID, '__cbc_video_data', $video_data);
		}else{
			return $content;
		}		
	}
	// if no video data, bail out
	if( !$video_data ){
		return $content;
	}
	
	$image = '';
	if( has_post_thumbnail( $post->ID ) ){
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );
		if( !$img ){
			$image = $video_data['thumbnails'][0];
		}else{
			$image = $img[0];
		}
	}else{
		$image = $video_data['thumbnails'][0];
	}
	// template for meta tag
	$meta = '<meta itemprop="%s" content="%s">';
	
	// create microdata output
	$html = "\n".'<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">'."\n\t";
	$html.= sprintf( $meta, 'name', esc_attr( cbc_strip_tags( get_the_title() ) ) )."\n\t";
	$html.= sprintf( $meta, 'description', trim( substr( esc_attr( cbc_strip_tags( $post->post_content ) ), 0, 300 ) ) )."\n\t";
	$html.= sprintf( $meta, 'thumbnailURL', $image )."\n\t";
	$html.= sprintf( $meta, 'embedURL', 'http://www.youtube-nocookie.com/v/'.$video_data['video_id'] )."\n\t";
	$html.= sprintf( $meta, 'uploadDate', date( 'c', strtotime( $post->post_date ) ) )."\n\t";
	$html.= sprintf( $meta, 'duration', cbc_iso_duration( $video_data['duration'] ) )."\n";
	$html.= "</span>\n";
	
	return $content.$html;
}
add_filter('the_content', 'cbc_video_schema', 999);

/**
 * More efficient strip tags
 *
 * @link  http://www.php.net/manual/en/function.strip-tags.php#110280
 * @param string $string string to strip tags from
 * @return string
 */
function cbc_strip_tags($string) {
   
    // ----- remove HTML TAGs -----
    $string = preg_replace ('/<[^>]*>/', ' ', $string);
   
    // ----- remove control characters -----
    $string = str_replace("\r", '', $string);    // --- replace with empty space
    $string = str_replace("\n", ' ', $string);   // --- replace with space
    $string = str_replace("\t", ' ', $string);   // --- replace with space
   
    // ----- remove multiple spaces -----
    $string = trim(preg_replace('/ {2,}/', ' ', $string));
   
    return $string;
}

/**
 * Returns ISO duration from a given number of seconds
 * @param int $seconds
 */
function cbc_iso_duration( $seconds ) {
	$return = 'PT';
	if ( $seconds > 3600 ) {
		$hours = floor( $seconds / 3600 );
		$return .= $hours . 'H';
		$seconds = $seconds - ( $hours * 3600 );
	}
	if ( $seconds > 60 ) {
		$minutes = floor( $seconds / 60 );
		$return .= $minutes . 'M';
		$seconds = $seconds - ( $minutes * 60 );
	}
	if ( $seconds > 0 ) {
		$return .= $seconds . 'S';
	}
	return $return;
}

/**
 * Returns contextual help content from file
 * @param string $file - partial file name
 */
function cbc_get_contextual_help( $file ){
	if( !$file ){
		return false;
	}	
	$file_path = CBC_PATH. 'views/help/' . $file.'.html.php';
	if( is_file($file_path) ){
		ob_start();
		include( $file_path );
		$help_contents = ob_get_contents();
		ob_end_clean();		
		return $help_contents;
	}else{
		return false;
	}
}

/**
 * Returns the YouTube API key entered by user
 */
function cbc_get_yt_api_key( $return = 'key' ){
	$api_key = get_option('_cbc_yt_api_key', array('key' => false, 'valid' => true));
	if( !is_array($api_key) ){
		$api_key = array('key' => $api_key, 'valid' => true);
		update_option('_cbc_yt_api_key', $api_key);
	}	
	
	switch( $return ){
		case 'full':
			return $api_key;	
		break;	
		case 'key':
		default:
			return $api_key['key'];
		break;
		case 'validity':
			return $api_key['valid'];
		break;	
	}
}

/**
 * Update YouTube API key
 * @param string $key
 */
function cbc_update_api_key( $key ){
	if( empty( $key ) ){
		$key = false;
	}
	$api_key = array('key' => trim($key), 'valid' => true);
	update_option('_cbc_yt_api_key', $api_key);
}

/**
 * Invalidates API key
 */
function cbc_invalidate_api_key(){
	$api_key = cbc_get_yt_api_key('full');
	$api_key['valid'] = false;
	update_option('_cbc_yt_api_key', $api_key);
}

/**
 * Ajax callback that displays information about a given playlist
 */
function cbc_get_youtube_details(){
	
	if( !class_exists('CBC_Video_Import') ){
		require_once CBC_PATH.'includes/libs/video-import.class.php';
	}
	
	if( empty( $_POST['type'] ) || empty( $_POST['id'] ) ){
		_e('Please enter a playlist ID.', 'cbc_video');
		die();
	}
	
	$args = array(
		'feed' 			=> $_POST['type'],
		'query' 		=> $_POST['id'],
		'start-index' 	=> 1,
		'results' 		=> 1
	);			
	$feed 	= new CBC_Video_Import($args);
	
	if( is_wp_error( $feed->get_errors() ) ){
		echo '<span style="color:red;">'.$feed->get_errors()->get_error_message().'</span>';	
	}else{	
		$items	= (int)$feed->get_total_items();
		printf( __('Playlist contains %d videos.', 'cbc_video'), $items );
	}
	die();
}
// check playlist ajax action
add_action('wp_ajax_cbc_check_playlist', 'cbc_get_youtube_details');