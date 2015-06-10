<?php
/*
Plugin Name: YouTube Video to Post
Plugin URI: http://www.constantinb.com/project/youtube-video-import-wordpress-plugin/
Description: Import YouTube videos directly into WordPress and display them as posts or embeded in existing posts and/or pages as single videos or playlists.
Author: Constantin Boiangiu
Version: 1.0.9.1
Author URI: http://www.constantinb.com
*/	

define( 'CBC_PATH'		, plugin_dir_path(__FILE__) );
define( 'CBC_URL'		, plugin_dir_url(__FILE__) );
define( 'CBC_VERSION'	, '1.0.9.1');

include_once CBC_PATH.'includes/functions.php';
include_once CBC_PATH.'includes/shortcodes.php';
include_once CBC_PATH.'includes/libs/custom-post-type.class.php';
include_once CBC_PATH.'includes/libs/video-import.class.php';
include_once CBC_PATH.'includes/libs/automatic-import.class.php';
include_once CBC_PATH.'includes/third-party-compatibility.php';
/**
 * Enqueue player script on single custom post page
 */
function cbc_single_video_scripts(){
	$settings 	= cbc_get_settings();
	$is_visible = $settings['archives'] ? true : is_single();
	
	if( is_admin() || !$is_visible || !ccb_is_video() ){
		return;
	}
	
	ccb_enqueue_player();	
}
add_action('wp_print_scripts', 'cbc_single_video_scripts');

/**
 * Process the post content to remove autoembeds if needed and make URL's clickable
 * @param string $content
 */
function ccb_first_content_filter( $content ){
	if( is_admin() || !ccb_is_video() ){
		return $content;
	}
	
	$settings 	= cbc_get_settings();
	if( isset( $settings['prevent_autoembed'] ) && $settings['prevent_autoembed'] ){
		// remove the autoembed filter
		remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
	}
		
	return $content;
}
add_filter('the_content', 'ccb_first_content_filter', 1);

/**
 * Filter custom post content to add custom video embed to it
 */
function ccb_single_custom_post_filter( $content ){
	
	$plugin_settings 	= cbc_get_settings();
	$is_visible = $plugin_settings['archives'] ? true : is_single();
	
	if( is_admin() || !$is_visible || !ccb_is_video() ){
		return $content;
	}
	
	global $post;
	$settings 	= ccb_get_video_settings( $post->ID, true );
	$video 		= get_post_meta($post->ID, '__cbc_video_data', true);
	
	if( !$video ){
		return $content;
	}
	
	$settings['video_id'] = $video['video_id'];
		
	$width = $settings['width'];
	$height = cbc_player_height( $settings['aspect_ratio'] , $width);
	
	// Filter - add extra CSS classes on single video container div element
	$class = apply_filters('ccb_video_post_css_class', array(), $post);
	$extra_css = implode(' ', $class);
	
	$video_container = '<div class="ccb_single_video_player '.$extra_css.'" '.cbc_data_attributes($settings).' style="width:'.$width.'px; height:'.$height.'px; max-width:100%;"><!-- player container --></div>';
	
	ccb_enqueue_player();
	
	// put the filter back for other posts; remove in funciton 'ccb_first_content_filter'
	add_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
	
	if( 'below-content' == $settings['video_position'] ){
		return $content.$video_container;
	}else{
		return $video_container.$content;
	}	
}
add_filter('the_content', 'ccb_single_custom_post_filter', 100);

/**
 * Load translations
 */
function ccb_translations(){
	load_plugin_textdomain('cbc_video', false, dirname( plugin_basename( __FILE__ ) ).'/languages');
}
add_action('init', 'ccb_translations', 1);

/**
 * Plugin activation; register permalinks for videos
 */
function cbc_activation_hook(){
	global $CBC_POST_TYPE;
	if( !$CBC_POST_TYPE ){
		return;
	}
	// register custom post
	$CBC_POST_TYPE->register_post();
	// create rewrite ( soft )
	flush_rewrite_rules( false );
}
register_activation_hook( __FILE__, 'cbc_activation_hook');

/**
 * Running for external cron JOB
 */
function cbc_run_external_cron(){
	// stop everything if this is a server cron call
	$options = cbc_get_settings();
	if( 'server_cron' == $options['automatic_import_uses'] && cbc_is_server_cron_call() ){
		die();
	}	
}
add_action('init', 'cbc_run_external_cron', 2);

include_once CBC_PATH.'includes/libs/upgrade.class.php';
new CCB_Plugin_Upgrade(array(
	'plugin'			=> __FILE__,
	'code' 				=> get_option('_cbc_yt_plugin_envato_licence', ''),
	'product'			=> 71,
	'remote_url' 		=> 'http://www.constantinb.com/check-updates/',
	'changelog_url'		=> 'http://www.constantinb.com/plugin-details/',
	'current_version' 	=> CBC_VERSION
));