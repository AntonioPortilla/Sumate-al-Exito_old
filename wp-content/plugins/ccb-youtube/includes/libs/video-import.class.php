<?php

class CBC_Video_Import{
	
	private $results;
	private $total_items;
	private $errors = false;
	
	public function __construct( $args ){
		
		$defaults = array(
			'source' 		=> 'youtube', // video source
			'feed'			=> 'query', // type of feed to retrieve
			'query'			=> false, // feed query - can contain username, playlist ID or serach query
			'results' 		=> 20, // number of results to retrieve
			'start-index'	=> 0,
			'response' 		=> 'jsonc', // YouTube response type
			'order'			=> 'published', // order
			'language'		=> 'en',
			'safe'			=> 'moderate',
			'hd'			=> false,
			'format'		=> '1,5',
			'duration'		=> false
		);
		
		$data = wp_parse_args($args, $defaults);
		// if no query is specified, bail out
		if( !$data['query'] ){
			return false;
		}
		
		// ordering or returned results. This needs to be processed
		$data['order'] = $this->order( $data['source'], $data['feed'], $data['order'] );
		if( !$data['order'] ){
			unset( $data['order'] );
		}
		
		$sources = $this->sources();
		// if sources doesn't exist, bail out
		if( !array_key_exists($data['source'], $sources) ){
			return false;
		}
		
		$source_data = $sources[ $data['source'] ];
		$vars = array();
		
		$feed_type = $source_data['feeds'][ $data['feed'] ];
		if( array_key_exists('vars', $feed_type) ){
			foreach( $feed_type['vars'] as $var ){
				if( isset( $data[ $var ] ) && $data[ $var ] ){
					$vars[ $source_data['variables'][ $var ]['var'] ] = $data[ $var ];
				}
			}
		}else{
			foreach( $source_data['variables'] as $arg => $var ){
				if( isset( $data[ $arg ] ) && $data[ $arg ] ){
					$vars[ $var['var'] ] = $data[ $arg ]; 
				}
			}
		}

		$source_url		= $source_data['url'];
		$source_query 	= sprintf( $source_data['feeds'][$data['feed']]['uri'], $data['query'] );
		$full_url 		= add_query_arg(array( $vars ), $source_url.$source_query);//.'&key=...';
		
		$yt_api_key = cbc_get_yt_api_key();
		if( $yt_api_key && cbc_get_yt_api_key('validity') ){
			$full_url .= '&key='.$yt_api_key;
		}
		
		$content = wp_remote_get( $full_url );
		
		// first check for WP errors
		if( is_wp_error($content) ){
			$this->errors = $content;
			return false;
		}
		
		// second, check for YouTube API error (key may be wrong)
		if( $yt_api_key && 403 == wp_remote_retrieve_response_code($content) ){
			$this->errors	= new WP_Error();
			$this->errors->add( 'cbc_invalid_api_key', __('Your YouTube API key is not valid. All other requests will be made without using the API key. Please review your settings and API key or consider removing the API key from Settings.', 'cbc_video') );
			cbc_invalidate_api_key();
			return false;
		}
		
		if( 200 != $content['response']['code'] ){
			$body 			= wp_remote_retrieve_body( $content );
			$response_data 	= json_decode( $body);
			$errors			= $response_data->error->errors;
			$this->errors	= new WP_Error();
			
			foreach ( $errors as $error ){
				$this->errors->add( $error->code, $error->internalReason );
			}			
			return false;			
		}
		
		$result 		= json_decode( $content['body'], true );
		
		if( isset( $result['data']['items'] ) ){
			$raw_entries = $result['data']['items'];
		}else{
			$raw_entries = array();
		}	
		
		$entries =	array();
		foreach ( $raw_entries as $entry ){	
			$entries[] = cbc_format_video_entry( $entry );					
		}		
		
		$this->results 		= $entries;
		$this->total_items 	= $result['data']['totalItems'];
	}
	
	public function get_feed(){
		if( !$this->results ){
			return array();
		}
		return $this->results;
	}
	
	public function get_errors(){
		return $this->errors;
	}
	
	public function get_total_items(){
		return $this->total_items;
	}
	
	/**
	 * Video sources with complete variables and URI
	 */
	private function sources(){
		$sources = array(
			'youtube' => array(
				'url'		=> 'http://gdata.youtube.com/feeds/api/',
				'variables' => array(
					'response' => array(
						'var' 	=> 'alt',
						'value' => 'jsonc' // atom, json, jsonc
					),
					/**
					 * Video feeds: relevance, published, viewCount, rating
					 * Playlist: position, commentCount, duration, published, reversedPosition, title, viewCount
					 */
					'order' => array(
						'var' 	=> 'orderby',
						'value' => 'published' 
					),
					'results' => array(
						'var' 	=> 'max-results',
						'value' => false // no more than 50
					),
					'start-index' => array(
						'var' => 'start-index',
						'value' => 0
					),
					'language' => array(
						'var' 	=> 'hl',
						'value' => 'en' 
					),
					'safe'	=> array(
						'var' 	=> 'safeSearch',
						'value'	=> 'moderate'
					),
					/**
					 * String true or false to return only HD videos
					 */
					'hd' => array(
						'var' => 'hd',
						'value'	=> false
					),
					'format' => array(
						'var' 	=> 'format',
						'value' => '1,5'
					),
					'duration' => array(
						'var' 	=> 'duration',
						'value' => 'medium' // short (< 4 min); medium ( > 4min, < 20min ); long ( > 20min )
					)
				),
				'feeds' => array(
					'user' => array(
						'uri' => 'users/%1$s/uploads/?v=2',
						'vars' => array( 'response', 'results', 'start-index', 'order' )
					),
					'playlist' => array(
						'uri' => 'playlists/%1$s/?v=2',
						'vars' => array( 'response', 'results', 'start-index' )
					),
					'query' => array(
						'uri' => 'videos?v=2&q=%1$s'
					)				
				)
			),
		);
		
		return $sources;
	}
	
	private function order( $source, $feed_type = false, $orderby = false ){
		/**
		 * If orderby parameter has value of -1, remove the order parameter.
		 * This is needed for automatic updates that in some cases don't retrieve due to a 
		 * YouTube indexed search issue when orderby is set.
		 * This will use the default orderby (which is by publishing date)
		 */
		if( -1 === $orderby ){
			return false;
		}
		
		$order = array(
			'youtube' => array(
				'query' => array('published', 'viewCount', 'relevance', 'rating'),
				'user' => array('published', 'viewCount', 'position', 'commentCount', 'duration', 'reversedPosition', 'title'),
				'playlist' => array('published', 'viewCount', 'position', 'commentCount', 'duration', 'reversedPosition', 'title'),
				'default' => 'published'
			) 
		);
		
		if( !array_key_exists($source, $order) || !array_key_exists($feed_type, $order[$source]) ){
			return false;
		}
		
		$ord = $order[$source][$feed_type];
		if( !in_array($orderby, $ord) ){
			return $order[$source]['default'];
		}

		return $orderby;		
	}
	
}