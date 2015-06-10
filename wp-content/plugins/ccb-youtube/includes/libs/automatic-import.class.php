<?php
class CBC_Automatic_Import{
	// transient settings
	private $transient 	= '__cbc_playlists_update'; // name of transient
	private $last_updated = '__cbc_last_playlist_updated'; // option that stores stats on updating process
	
	public function __construct(){
		
		// first we need to verify if server cron is on and if this is an actual call from the cron
		$options = cbc_get_settings();
		if( 'server_cron' == $options['automatic_import_uses'] && !cbc_is_server_cron_call() ){
			return;
		}
		
		// if update should not run, bail out
		if( !$this->run_update() ){
			return;
		}
		
		// check cron type
		switch( $options['automatic_import_uses'] ){
			case 'wp_cron':
				add_action('init', array( $this, 'update_playlists' ), 1);
			break;
			case 'server_cron':
				if( !cbc_is_server_cron_call() ){
					return;
				}
				add_action('init', array( $this, 'update_playlists' ), 1);
			break;	
		}		
	}
	
	private function get_playlist(){
		global $CBC_POST_TYPE;
		$option = get_option($this->last_updated, array());
		
		if( $option && isset( $option['post_id'] ) ){
			$last_id = $option['post_id'];
		}
		
		// get all playlists
		$args = array(
			'post_type' 	=> $CBC_POST_TYPE->get_playlist_post_type(),
			'post_status' 	=> 'publish',
			'orderby' 		=> 'ID',
			'order'			=> 'ASC',
			'numberposts'	=> -1
		);		
		$playlists = get_posts($args);
		
		if( !$playlists ){
			$data = array(
				'post_id' 	=> false,
				'time'		=> time(),
				'empty' 	=> true
			);
			update_option($this->last_updated, $data);
			return;
		}
		
		// setting not yet set
		if( !isset( $last_id ) ){
			return $playlists[0]->ID;
		}
		
		$next_id = false;
		foreach ($playlists as $k => $playlist) {
			if( $last_id == $playlist->ID ){
				if( isset( $playlists[$k+1] ) ){
					$next_id = $playlists[$k+1]->ID;
				}else{
					$next_id = $playlists[0]->ID;
				}
			}
		}
		if( $next_id ){
			return $next_id;
		}else{
			return $playlists[0]->ID;
		}		
	}
	
	public function update_playlists(){
		// playlist ID
		$post_id = $this->get_playlist();
		
		// update last playlist option
		if( $post_id ){			
			$data = array(
				'post_id' 	=> $post_id,
				'time'		=> time()
			);
			update_option($this->last_updated, $data);
		}else{
			return;
		}
		
		global $CBC_POST_TYPE;
		// get playlist data
		$meta = get_post_meta( 
			$post_id, 
			$CBC_POST_TYPE->get_playlist_meta_name(), 
			true 
		);
		
		if( !$meta ){
			// @todo - here, maybe insert error in meta becuase something probably went wrong
			return;
		}
		
		if( !class_exists('CBC_Video_Import') ){
			require_once CBC_PATH.'includes/libs/video-import.class.php';
		}
		
		// plugin options
		$options = cbc_get_settings();
		// playlist should be parsed all over again?
		$reiterate = !( isset( $meta['no_reiterate'] ) && $meta['no_reiterate'] );
		
		// if processed feed video is equal to total feed videos, reset back to begining of playlist
		if( $meta['processed'] == $meta['total'] && $reiterate ){
			$meta['processed'] = 0;
		}
		
		// video query arguments
		$args = array(
			'feed' 			=> $meta['type'],
			'query' 		=> $meta['id'],
			'start-index' 	=> $meta['processed'] + 1,
			'results' 		=> $options['import_quantity'],
			'order'			=> -1 // no ordering to get fresh results
		);	

		$feed = new CBC_Video_Import($args);
		
		// if error, save it on playlist
		if( is_wp_error( $feed->get_errors() ) ){
			$meta['import_error'] = $feed->get_errors()->get_error_message();	
		}else{
			// if no error but a previous error was stored, remove it
			if( isset( $meta['import_error'] ) ){
				unset( $meta['import_error'] );
			}	
		}
		
		// videos
		$result = $feed->get_feed();
		/*
		if( !$result ){
			// @todo - here, update playlist meta to tell it reached the end
			return;
		}
		*/
		
		// flag end of playlist to make plugin start over
		if( !isset( $reset ) ){
			$reset = false;
		}
		
		// different behavior depending on playlist type
		switch( $meta['type'] ){
			case 'user':
				// if we have a date limit, process the results
				if( isset( $meta['start_date'] ) && !empty( $meta['start_date'] ) ){
					$start_timestamp = strtotime( $meta['start_date'] );
					// we assume no video was skipped
					foreach ( $result as $key => $entry ){
						$entry_timestamp = strtotime( $entry['published'] );
						if( $entry_timestamp < $start_timestamp ){
							$reset 	= true;
							$result = array_slice( $result, 0, $key );
							$meta['finished'] = true;							
							break;
						}						
					}					
				}
				
				if( !$reiterate && isset( $meta['finished'] ) && $meta['finished'] ){
					$reset = true;
				}
				
				// no reiteration process to user playlists				
				if( 0 == $meta['processed'] && $result ){
					if( isset( $meta['first_video'] ) && $meta['first_video'] != $result[0]['video_id'] ){
						$meta['last_video'] = $meta['first_video'];				
					}			
					$meta['first_video'] = $result[0]['video_id'];
				}
				// remove unneccessary videos if no reiteration is needed
				if( isset( $meta['last_video'] ) ){
					// we assume no video was skipped
					foreach ( $result as $key => $entry ){
						if( $entry['video_id'] == $meta['last_video'] ){
							$reset 	= true;
							$result = array_slice( $result, 0, $key );
							break;
						}						
					}
				}				
			break;
			case 'playlist':
				
			break;	
		}
		
		// import the videos
		$response = $CBC_POST_TYPE->run_import( $result, $meta );			
		
		// if we should reset, set processed back to 0
		if( $reset ){
			$meta['processed'] = 0;
		}else{
			$meta['processed'] += count( $result );
			if( $meta['processed'] >= $feed->get_total_items() ){
				$meta['finished'] = true;
				if( $reiterate ){				
					$meta['processed'] = 0;
				}	
			}
		}
		
		$meta['imported']  += $response['imported'];	
		$meta['updated'] 	= date('d M Y, H:i:s');
		$meta['total']		= $feed->get_total_items();
		
		update_post_meta($post_id, $CBC_POST_TYPE->get_playlist_meta_name(), $meta);		
	}
	
	/**
	 * Checks if delay was met to run the update of playlists
	 */
	private function run_update(){
		// prevent automatic updates on manual import page
		if( ( is_admin() && isset($_GET['page']) && 'cbc_import' == $_GET['page'] ) || (defined('DOING_AJAX') && DOING_AJAX) ){
			return false;
		}
		
		$data = get_transient( $this->transient );
		if( !$data ){
			set_transient( $this->transient, time(), $this->get_delay() );
			return true;
		}
		
		return false;		
	}
	
	public function get_update(){
		$option = get_option($this->last_updated, array());
		return $option;
	}

	public function get_delay(){
		
		$settings 			= cbc_get_settings();
		$delay 				= $settings['import_frequency'];
		$registered_delays 	= cbc_automatic_update_timing();
		
		if( !array_key_exists( $delay, $registered_delays ) ){
			$defaults = cbc_plugin_settings_defaults();
			$delay = $defaults['import_frequency'];
		}
		
		// delay is set in minutes and we need it in seconds
		$delay *= 60;
		return $delay;
	}
	
	public function update_transient(){
		$update_data = $this->get_update();
		if( isset( $update_data['time'] ) ){
			$update_data['time'] = time();
			update_option($this->last_updated, $update_data);
		}		
		set_transient( $this->transient, time(), $this->get_delay() );
	}
	
	public function get_transient_time(){
		return get_transient($this->transient);
	}	
}

$CBC_AUTOMATIC_IMPORT = new CBC_Automatic_Import();