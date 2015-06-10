<?php
/**
 * Shortcode to display a single video in post/page
 * Usage:
 * 
 * [cbc_video id="video_id_from_wp"]
 * 
 * Complete params:
 * 
 * - id : video ID from WordPress import (post ID) - required
 * - volume : video volume (number between 1 and 100) - optional
 * - width : width of video (number) - optional; works in conjunction with aspect_ratio
 * - aspect_ratio : aspect ratio of video ( 16x9 or 4x3 ) - optional; needed to calculate height
 * - autoplay : play video on page load ( 0 or 1 ) - optional
 * - controls : display controls on video ( 0 or 1 ) - optional
 * 
 * @param array $atts
 * @param string $content
 */
function cbc_single_video( $atts = array(), $content = '' ){
	// check if atts is set
	if( !is_array( $atts ) ){
		return;
	}	
	// look for video ID
	if( !array_key_exists('id', $atts) ){
		return;
	}
	// post id from atts
	$post_id = absint( $atts['id'] );
	// post
	$post = get_post( $atts['id'] );
	if( !$post ){
		return;
	}	
	// check post type
	global $CBC_POST_TYPE;
	if( !ccb_is_video($post) ){
		return false;
	}
	
	if( is_feed() ){
		return false;
	}
	
	// get video options attached to post
	$video_opt = ccb_get_video_settings( $post_id );
	// get video data
	$video 	= get_post_meta($post_id, '__cbc_video_data', true);	
	// combine video vars with atts
	$vars = shortcode_atts(array(
		'controls' 		=> $video_opt['controls'],
		'autoplay'		=> $video_opt['autoplay'],
		'volume' 		=> $video_opt['volume'],
		'width' 		=> $video_opt['width'],
		'aspect_ratio' 	=> $video_opt['aspect_ratio']
	), $atts);
	
	if( !$atts['width'] ){
		return false;
	}
	
	$width	= absint( $vars['width'] );
	$height = cbc_player_height( $vars['aspect_ratio'] , $vars['width']);
	
	$settings = wp_parse_args( $vars, $video_opt );
	$settings['video_id'] = $video['video_id'];
	
	$video_container = '<div class="ccb_single_video_player" '.cbc_data_attributes( $settings ).' style="width:'.$width.'px; height:'.$height.'px; max-width:100%;"><!--video player--></div>';
	// add JS file
	ccb_enqueue_player();
	
	return $video_container;
}
add_shortcode('cbc_video', 'cbc_single_video');

/**
 * Shortcode to display a playlist of videos
 * @param array $atts
 * @param string $content
 */
function cbc_video_playlist( $atts = array(), $content = '' ){
	// check if atts is set
	if( !is_array( $atts ) ){
		return;
	}	
	// look for video ID's
	if( !array_key_exists('videos', $atts) ){
		return;
	}
	// look for video ids
	$video_ids = explode(',', $atts['videos']);
	if( !$video_ids ){
		return;
	}
	
	$content = cbc_output_playlist( $video_ids );	
	return $content;
}
add_shortcode('cbc_playlist', 'cbc_video_playlist');


function cbc_output_playlist( $videos = 'latest', $results = 5, $theme = 'default', $player_settings = array(), $taxonomy = false ){
	global $CBC_POST_TYPE;
	$args = array(
		'post_type' 		=> array($CBC_POST_TYPE->get_post_type(), 'post'),
		'posts_per_page' 	=> absint( $results ),
		'numberposts'		=> absint( $results ),
		'post_status'		=> 'publish',
		'supress_filters'	=> true
	);
	
	// taxonomy query
	if( !is_array( $videos ) && isset( $taxonomy ) && !empty( $taxonomy ) && ((int)$taxonomy) > 0 ){
		$term = get_term( $taxonomy, $CBC_POST_TYPE->get_post_tax(), ARRAY_A );
		if( !is_wp_error( $term ) ){			
			$args[ $CBC_POST_TYPE->get_post_tax() ] = $term['slug'];
		}	
	}
	
	// if $videos is array, the function was called with an array of video ids
	if( is_array( $videos ) ){
		
		$ids = array();
		foreach( $videos as $video_id ){
			$ids[] = absint( $video_id );
		}		
		$args['include'] 		= $ids;
		$args['posts_per_page'] = count($ids);
		$args['numberposts'] 	= count($ids);
		
	}elseif( is_string( $videos ) ){
		
		$found = false;
		switch( $videos ){
			case 'latest':
				$args['orderby']	= 'post_date';
				$args['order']		= 'DESC';
				$found 				= true;
			break;	
		}
		if( !$found ){
			return;
		}
				
	}else{ // if $videos is anything else other than array or string, bail out		
		return;		
	}
	
	// get video posts
	$posts = get_posts( $args );
	
	if( !$posts ){
		return;
	}
	
	$videos = array();
	foreach( $posts as $post_key => $post ){
		
		if( !ccb_is_video( $post ) ){
			continue;
		}
		
		if( isset( $ids ) ){
			$key = array_search($post->ID, $ids);
		}else{
			$key = $post_key;
		}	
		
		if( is_numeric( $key ) ){
			$videos[ $key ] = array(
				'ID'			=> $post->ID,
				'title' 		=> $post->post_title,
				'video_data' 	=> get_post_meta( $post->ID, '__cbc_video_data', true )
			);
		}
	}
	ksort( $videos );
	
	ob_start();
	
	// set custom player settings if any
	global $CBC_PLAYER_SETTINGS;
	if( $player_settings && is_array( $player_settings ) ){
		
		$CBC_PLAYER_SETTINGS = $player_settings;
	}
	
	global $cbc_video;
	
	include( CBC_PATH.'themes/default/player.php' );
	$content = ob_get_contents();
	ob_end_clean();
	
	ccb_enqueue_player();
	wp_enqueue_script(
		'cbc-yt-player-default', 
		CBC_URL.'themes/default/assets/script.js', 
		array('ccb-video-player'), 
		'1.0'
	);
	wp_enqueue_style(
		'ccb-yt-player-default', 
		CBC_URL.'themes/default/assets/stylesheet.css', 
		false, 
		'1.0'
	);
	
	// remove custom player settings
	$CBC_PLAYER_SETTINGS = false;
	
	return $content;
	
}
