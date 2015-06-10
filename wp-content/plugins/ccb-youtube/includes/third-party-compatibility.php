<?php
/**
 * Third party themes and plugins compatibility.
 * Contains compatibility with various plugins and themes.
 */

class CBC_Third_Party_Compat{
	
	private $active_theme;
	private $compatible_theme = array();
	
	public function __construct(){
		
		// get template details
		$this->active_theme = wp_get_theme();
		if( is_object( $this->active_theme ) ){
			// check if it's child theme			
			if( is_object( $this->active_theme->parent() ) ){
				// set theme to parent
				$this->active_theme = $this->active_theme->parent();
			}
		}else{
			$this->active_theme = false;
		}
		
		$this->check_compatibility();		
		
		// hooks and filters to set up things correctly
		if( $this->compatible_theme ){
			// if video should be auto embeded into theme post, add it to post content
			if( array_key_exists('autoembed', $this->compatible_theme) ){			
				add_filter( 'cbc_video_post_content', array( $this, 'do_autoembeds' ), 30, 3 );
			}
			// if extra meta should be set on post, add the action
			if( array_key_exists('extra_meta', $this->compatible_theme) ){
				add_action( 'cbc_post_insert', array( $this, 'add_extra_meta' ), 30, 3 );
			}			
		}		
	}
	
	/**
	 * Action 'cbc_post_insert' callback function to set up extra meta fields on posts as required by theme
	 *  
	 * @param int $post_id
	 * @param array $video
	 * @param array $theme_import
	 */
	public function add_extra_meta( $post_id, $video, $theme_import ){
		
		if( !$theme_import || !array_key_exists('extra_meta', $theme_import) ){
			return;
		}
		
		$extra_meta = $theme_import['extra_meta'];
		$player_settings = cbc_get_player_settings();
		
		foreach( $extra_meta as $meta_key => $data ){
			switch( $data['type'] ){
				// static variables, most likely used by themes to detect videos
				case 'static': 
					update_post_meta($post_id, $meta_key, $data['value']);
				break;
				// video url
				case 'video_url': 
					$url = 'http://www.youtube.com/watch?v='.$video['video_id'];
					update_post_meta($post_id, $meta_key, $url);
				break;
				// get settings from plugin settings page for player
				case 'player_settings': 
					if( array_key_exists($data['value'], $player_settings) ){					
						update_post_meta($post_id, $meta_key, $player_settings[ $data['value'] ]);
					}
				break;	
				// data set on video array
				case 'video_data':
					$value = false;
					if( 'human_duration' == $data['value'] ){
						$value = cbc_human_time( $video['duration'] );
					}else{
						if( array_key_exists($data['value'], $video) ){
							$value = $video[ $data['value'] ];
						}	
					}
					if( $value ){
						update_post_meta($post_id, $meta_key, $value);
					}					
				break;
									
			}
		}				
	}
	
	/**
	 * Filter 'cbc_video_post_content' callback to put video link in post content for autoembeds 
	 *
	 * @param string $post_content
	 * @param array $video
	 * @param array $theme_import
	 */
	public function do_autoembeds( $post_content, $video, $theme_import ){
		// check if importing in theme
		if( !$theme_import ){
			return $post_content;
		}
		
		// we want to add the video link to post content at the beginning
		$video_url = 'http://www.youtube.com/watch?v='.$video['video_id'];		
		return $video_url . "\n" . $post_content;
	}	
	
	/**
	 * Check if active theme is compatible with the plugin
	 */
	private function check_compatibility(){
		
		if( !$this->active_theme ){
			return false;
		}
		
		$theme_name = strtolower( $this->active_theme->Name );
		// filter for third party code
		$themes = apply_filters( 'cbc_youtube_theme_support', $this->wp_themes() );
		// check if theme is supported
		if( is_array($themes) && array_key_exists($theme_name, $themes) ){
			// store compatible theme compatibility details in variable
			$this->compatible_theme = $themes[ $theme_name ];
			return true;
		}
		
		return false;		
	}
	
	/**
	 * Returns the compatibility layer for active theme (is compatible)
	 */
	public function get_theme_compatibility(){
		return $this->compatible_theme;
	}
	
	/**
	 * WP themes compatibility
	 */
	private function wp_themes(){
		
		$themes = array(
			// http://www.themeforest.net/item/true-mag-wordpress-theme-for-video-and-magazine/6755267 
			'truemag' => array(
				'post_type' 	=> 'post',
				'taxonomy' 		=> false,
				'post_meta' 	=> array(
					'url' => 'tm_video_url'
				),
				'post_format' 	=> 'video',
				'theme_name' 	=> 'TrueMag',
				'url'			=> 'http://www.themeforest.net/item/true-mag-wordpress-theme-for-video-and-magazine/6755267/?ref=cboiangiu',
				'extra_meta' 	=> array(
					'time_video' => array(				
						'type' 	=> 'video_data',
						'value' => 'human_duration'
					)
				),
			),
			
			// http://themeforest.net/item/avada-responsive-multipurpose-theme/2833226
			'avada' => array(
				'post_type' => 'post',
				'taxonomy'	=> false,
				'post_meta'	=> array(
					'embed' => 'pyre_video'
				),
				'post_format' 	=> false,
				'theme_name' 	=> 'Avada',
				'url'			=> 'http://themeforest.net/item/avada-responsive-multipurpose-theme/2833226/?ref=cboiangiu'
			),
			
			// http://themeforest.net/item/goodwork-modern-responsive-multipurpose-wordpress-theme/4574698
			'goodwork' => array(
				'post_type' => 'post',
				'taxonomy' 	=> false,
				'post_meta' => array(
					'embed' => 'rb_meta_box_post_assets_ev'
				),
				'post_format'	=> 'video',
				'theme_name' 	=> 'Goodwork',
				'url'			=> 'http://themeforest.net/item/goodwork-modern-responsive-multipurpose-wordpress-theme/4574698/?ref=cboiangiu'	
			),
			
			// http://themeforest.net/item/simplemag-magazine-theme-for-creative-stuff/4923427
			'simplemag' => array(
				'post_type' => 'post',
				'taxonomy' 	=> false,
				'post_meta'	=> array(
					'url' => 'add_video_url'
				),
				'post_format' => 'video',
				'theme_name' => 'SimpleMag',
				'url' => 'http://themeforest.net/item/simplemag-magazine-theme-for-creative-stuff/4923427/?ref=cboiangiu'
			),			
			
			// http://themeforest.net/item/sahifa-responsive-wordpress-newsmagazineblog/2819356
			'sahifa' => array(
				'post_type' => 'post',
				'taxonomy'	=> false,
				'post_meta' => array(
					'embed' => 'tie_embed_code'
				),
				'post_format' 	=> 'video',
				'theme_name' 	=> 'Sahifa',
				'url'			=> 'http://themeforest.net/item/sahifa-responsive-wordpress-newsmagazineblog/2819356/?ref=cboiangiu',
				'extra_meta' => array( // store extra meta fields needed to be set on theme post type
					'tie_post_head' => array(
						'type' 	=> 'static', // a static value, will always be stored having the same value
						'value' => 'video'
					),
					'tie_video_url' => array(
						'type' 	=> 'video_url', // store video URL as needed by the theme
						'value' => false
					)
				)
			),
			
			// http://themeforest.net/item/wave-video-theme-for-wordpress/45855
			'wave' => array(
				'post_type' => 'post',
				'taxonomy' 	=> false,
				'post_meta' => array(
					'embed' => 'video_embed_value'
				),
				'post_format'	=> 'video',
				'theme_name' 	=> 'Wave',
				'url'			=> 'http://themeforest.net/item/wave-video-theme-for-wordpress/45855/?ref=cboiangiu',
				'extra_meta'	=> array(
					'video_width_value' => array(
						'type' 	=> 'player_settings', // variable needs value from plugin player settings
						'value' => 'width' // value needed is stored under key width in plugin settings
					),
					'is_video_value' => array(
						'type' 	=> 'static',
						'value' => true
					)
				)	
			),
			
			// http://themeforest.net/item/detube-professional-video-wordpress-theme/2664497
			'detube' => array(
				'post_type'	=> 'post', 					// the type of post videos are saved on
				'taxonomy'	=> false,					// taxonomy; for regular posts leave false
				'post_meta' => array(
					'url' 		=> 'dp_video_url', 		// custom field on post to save video url for detube compatibility
					'thumbnail' => 'dp_video_poster'	// custom fiels on post to save video thumbnail for detube compatibility
				),
				'post_format' 	=> false,				// post format
				'theme_name' 	=> 'DeTube',			// theme name to display
				'url'			=> 'http://themeforest.net/item/detube-professional-video-wordpress-theme/2664497/?ref=cboiangiu'
			),
			
			// default in WordPress
			'twenty thirteen' => array(
				'post_type' 	=> 'post',
				'taxonomy' 		=> false,
				'post_meta'		=> array(),
				'post_format' 	=> 'video',
				'theme_name'	=> 'Twenty Thirteen',
				'autoembed'		=> 'before', // add video URL to auto embed videos before the post content,
				'url'			=> 'http://wordpress.org/themes/twentythirteen'
			),
			
			// default in WordPress
			'twenty fourteen' => array(
				'post_type' 	=> 'post',
				'taxonomy' 		=> false,
				'post_meta'		=> array(),
				'post_format' 	=> 'video',
				'theme_name'	=> 'Twenty Fourteen',
				'autoembed'		=> 'before', // add video URL to auto embed videos before the post content
				'url'			=> 'http://wordpress.org/themes/twentyfourteen'
			),
			
			// http://templatic.com/freethemes/video
			'video' => array(
				'post_type' => 'videos',
				'taxonomy' 	=> 'videoscategory',
				'post_meta' => array(
					'embed' => 'video'
				),
				'post_format' => 'video',
				'theme_name' => 'Video',
				'url'		=> 'http://templatic.com/freethemes/video',
				'extra_meta' => array(
					'time' => array(
						'type' 	=> 'video_data', // data is pulled from video details
						'value' => 'human_duration' // for seconds duration, use only duration
					)	
				)
			),			
		);
		
		return $themes;
	}
	
	/**
	 * Get all compatible themes
	 */
	public function get_compatible_themes(){
		return $this->wp_themes();
	}
	
}

/**
 * ==========================================
 * Plugins compatibility
 * ==========================================
 */

/**
 * Yoast Video SEO compatibility 
 * 
 * Callback function on action 'cbc_post_insert'
 * 
 * @param int $post_id
 * @param array $video
 * @param array $theme_import
 */
function yoast_video_seo_compatibility(  $post_id, $video, $theme_import, $post_type ){
	
	// check if Yoast video SEO class exists ()
	if( !class_exists('wpseo_Video_Sitemap') ){
		return;
	}
	// get plugin options
	$yoast_options = get_option( 'wpseo_video' );
	// check if current post type is allowed
	if( isset( $yoast_options['videositemap_posttypes'] ) ){
		if( !is_array( $yoast_options['videositemap_posttypes'] ) || !in_array( $post_type, $yoast_options['videositemap_posttypes'] ) ){
			return;
		}
	}
	
	$image = '';
	if( has_post_thumbnail( $post_id ) ){
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ) );
		if( !$img ){
			$image = $video['thumbnails'][0];
		}else{
			$image = $img[0];
		}
	}else{
		$image = $video['thumbnails'][0];
	}
	
	$meta_desc = htmlspecialchars( substr( preg_replace( '/\s+/', ' ', strip_tags( $video['description'] ) ), 0, 115 ) );
	$meta_title = esc_attr( cbc_strip_tags(  $video['title'] ) );
	
	$data = array(
		'post_id' 			=> $post_id,
		'title'				=> $meta_title,
		'publication_date' 	=> $video['published'],
		'description'		=> $meta_desc,
		'url'				=> 'http://www.youtube.com/watch?v='.$video['video_id'],
		'id'				=> $video['video_id'],
		'player_loc'		=> 'http://www.youtube-nocookie.com/v/'.$video['video_id'],
		'type'				=> 'youtube',
		'thumbnail_loc'		=> $image,
		'view_count'		=> $video['stats']['views'],
		'duration'			=> $video['duration'],
		'category'			=> $video['category'],
		'tag'				=> array()
	);
	
	update_post_meta( $post_id, '_yoast_wpseo_video_meta', $data );	
	// seo metadesc
	update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_desc );	
	// seo metatitle
	update_post_meta( $post_id, '_yoast_wpseo_title', $meta_title );		
}
add_action( 'cbc_post_insert', 'yoast_video_seo_compatibility', 10, 4 );
