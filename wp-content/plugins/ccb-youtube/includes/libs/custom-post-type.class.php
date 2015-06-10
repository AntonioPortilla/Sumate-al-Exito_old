<?php
/**
 * Custom post type class. Manages post registering, taxonomies, data saving
 */
class CBC_Video_Post_Type{
	
	private $post_type 	= 'video';
	private $taxonomy 	= 'videos';
	private $help_screens = array(); // store help screens info
	// playlist post type
	private $playlist_type				= 'cbc_yt_playlist';
	private $playlist_meta				= '__cbc_yt_playlist';
	private $ajax_import_action 		= 'cbc_import_videos';
	private $ajax_video_query_action 	= 'cbc_query_video';
	private $ajax_import_thumbnail_action 	= 'cbc_import_video_thumbnail';
	
	public function __construct(){
		// custom post type registration and messages
		add_action('init', array($this, 'register_post'), 10);
		add_filter('post_updated_messages', array($this, 'updated_messages'));
		// for empty imported posts, skip $maybe_empty verification
		add_filter('wp_insert_post_empty_content', array($this, 'force_empty_insert'), 999, 2);
		// create edit meta boxes
		add_action('admin_head', array($this, 'add_meta_boxes'));
		// save data from meta boxes
		add_action('save_post', array($this, 'save_post'), 10, 2);
		
		// add extra menu pages
		add_action('admin_menu', array($this, 'menu_pages'), 1);
		
		// add columns to posts table
		add_filter('manage_edit-'.$this->post_type.'_columns', array( $this, 'extra_columns' ));
		add_action('manage_'.$this->post_type.'_posts_custom_column', array($this, 'output_extra_columns'), 10, 2);
		
		// response to ajax import
		add_action('wp_ajax_'.$this->ajax_import_action, array($this, 'ajax_import_videos'));
		// response to new video ajax query
		add_action('wp_ajax_'.$this->ajax_video_query_action, array($this, 'ajax_video_query'));
		
		add_action('load-post-new.php', array($this, 'post_new_onload'));
		add_action('admin_enqueue_scripts', array($this, 'post_edit_assets'));
		
		// help screens
		add_filter('contextual_help', array( $this, 'contextual_help' ), 10, 3);
	
		// post thumbnails
		add_filter('admin_post_thumbnail_html', array( $this, 'post_thumbnail_meta_panel' ), 10, 2);
		add_action('wp_ajax_'.$this->ajax_import_thumbnail_action, array($this, 'ajax_import_thumbnail'));
		
		// add video post type to homepage list of posts
		add_filter( 'pre_get_posts', array($this, 'add_on_homepage'), 999 );
		
		// add video post type to main RSS feed
		add_filter('request', array($this, 'add_to_main_feed'));
		
		// plugin filters
		add_filter( 'cbc_video_post_content', array($this, 'format_description'), 999, 3 );
		
		// alert if setting to import as post type post by default is set on all plugin pages
		add_action('admin_notices', array($this, 'alert_post_type'));
		// mechanism to remove the alert above
		add_action('admin_init', array($this, 'dismiss_post_type_notice'));
	}
	
	/**
	 * Display an alert to user when he chose to import videos by default as regular posts
	 */
	public function alert_post_type(){
		if( !is_admin() || !import_as_post() || !current_user_can('manage_options') ){
			return;			
		}
		global $pagenow;
		if( !'edit.php' == $pagenow || !isset( $_GET['post_type']) || $this->post_type != $_GET['post_type'] ){
			return;
		}
		
		global $current_user;
		$user_id = $current_user->ID;
		if( !get_user_meta($user_id, 'cbc_ignore_post_type_notice', true) ){
			echo '<div class="updated"><p>';
			$theme_support = cbc_check_theme_support();
			
			printf(__('Please note that you have chosen to import videos as <strong>regular posts</strong> instead of post type <strong>%s</strong>.', 'cbc_video'), $this->post_type);
			echo '<br />' . ( $theme_support ? __('Videos can be imported as regular posts compatible with the plugin or as posts compatible with your theme.', 'cbc_video') : __('Videos will be imported as regular posts.', 'cbc_video') );
						
			$url = add_query_arg(array(
				'cbc_dismiss_post_type_notice' => 1
			), $_SERVER['REQUEST_URI']);
			
			printf(' <a class="button button-small" href="%s">%s</a>', $url, __('Dismiss', 'cbc_video'));
			echo '</p></div>';			
		}		
	}
	
	/**
	 * Dismiss regular post mport notice
	 */
	public function dismiss_post_type_notice(){
		if( !is_admin() ){
			return;
		}
		
		if( isset( $_GET['cbc_dismiss_post_type_notice'] ) && 1 == $_GET['cbc_dismiss_post_type_notice'] ){
			global $current_user;
			$user_id = $current_user->ID;
			add_user_meta($user_id, 'cbc_ignore_post_type_notice', true);
		}
	}
	
	/**
	 * Register video post type and taxonomies
	 */
	public function register_post(){
		$labels = array(
			'name' 					=> _x('Videos', 'Videos', 'cbc_video'),
	    	'singular_name' 		=> _x('Video', 'Video', 'cbc_video'),
	    	'add_new' 				=> _x('Add new', 'Add new video', 'cbc_video'),
	    	'add_new_item' 			=> __('Add new video', 'cbc_video'),
	    	'edit_item' 			=> __('Edit video', 'cbc_video'),
	    	'new_item'				=> __('New video', 'cbc_video'),
	    	'all_items' 			=> __('All videos', 'cbc_video'),
	    	'view_item' 			=> __('View', 'cbc_video'),
	    	'search_items' 			=> __('Search', 'cbc_video'),
	    	'not_found' 			=> __('No videos found', 'cbc_video'),
	    	'not_found_in_trash' 	=> __('No videos in trash', 'cbc_video'), 
	    	'parent_item_colon' 	=> '',
	    	'menu_name' 			=> __('Videos', 'cbc_video')
		);
		
		$options 	= cbc_get_settings();
		$is_public 	= $options['public'];
		
		$args = array(
    		'labels' 				=> $labels,
    		'public' 				=> $is_public,
			'exclude_from_search'	=> !$is_public,
    		'publicly_queryable' 	=> $is_public,
			'show_in_nav_menus'		=> $is_public,
		
    		'show_ui' 				=> true,
			'show_in_menu' 			=> true,
			'menu_position' 		=> 5,
			'menu_icon'				=> CBC_URL.'assets/back-end/images/video.png',	
		
    		'query_var' 			=> true,
    		'capability_type' 		=> 'post',
    		'has_archive' 			=> true, 
    		'hierarchical' 			=> false,
    		'rewrite'				=> array(
				'slug' => $options['post_slug']
			),		
    		'supports' 				=> array( 
    			'title', 
    			'editor', 
    			'author', 
    			'thumbnail', 
    			'excerpt', 
    			'trackbacks',
				'custom-fields',
    			'comments',  
    			'revisions',
    			'post-formats' 
			),			
 		); 
 		
 		register_post_type($this->post_type, $args);
  
  		// Add new taxonomy, make it hierarchical (like categories)
  		$cat_labels = array(
	    	'name' 					=> _x( 'Video categories', 'video', 'cbc_video' ),
	    	'singular_name' 		=> _x( 'Video category', 'video', 'cbc_video' ),
	    	'search_items' 			=>  __( 'Search video category', 'cbc_video' ),
	    	'all_items' 			=> __( 'All video categories', 'cbc_video' ),
	    	'parent_item' 			=> __( 'Video category parent', 'cbc_video' ),
	    	'parent_item_colon'		=> __( 'Video category parent:', 'cbc_video' ),
	    	'edit_item' 			=> __( 'Edit video category', 'cbc_video' ), 
	    	'update_item' 			=> __( 'Update video category', 'cbc_video' ),
	    	'add_new_item' 			=> __( 'Add new video category', 'cbc_video' ),
	    	'new_item_name' 		=> __( 'Video category name', 'cbc_video' ),
	    	'menu_name' 			=> __( 'Video categories', 'cbc_video' ),
		); 	

		register_taxonomy($this->taxonomy, array($this->post_type), array(
			'public'			=> $is_public,
    		'show_ui' 			=> true,
			'show_in_nav_menus' => $is_public,
			'show_admin_column' => true,		
			'hierarchical' 		=> true,
			'rewrite' 			=> array( 
				'slug' => $options['taxonomy_slug'] 
			),
			'capabilities'		=> array('edit_posts'),		
    		'labels' 			=> $cat_labels,    		
    		'query_var' 		=> true    		
  		));
  		
  		// playlists post type  		
  		register_post_type($this->playlist_type, array(
  			'public' 				=> false,
  			'exclude_from_search' 	=> true,
  			'publicly_queryable'	=> false,
  			'show_ui'				=> false,
  			'show_in_nav_menus'		=> false,
  			'show_in_menu'			=> false,
  			'show_in_admin_bar'		=> false
  		));
	}
	
	/**
	 * Custom post type messages on edit, update, create, etc.
	 * @param array $messages
	 */
	public function updated_messages( $messages ){
		global $post, $post_ID;
	
		$messages['video'] = array(
			0 => '', // Unused. Messages start at index 1.
	    	1 => sprintf( __('Video updated <a href="%s">See video</a>', 'cbc_video'), esc_url( get_permalink($post_ID) ) ),
	    	2 => __('Custom field updated.', 'cbc_video'),
	    	3 => __('Custom field deleted.', 'cbc_video'),
	    	4 => __('Video updated.', 'cbc_video'),
	   		/* translators: %s: date and time of the revision */
	    	5 => isset($_GET['revision']) ? sprintf( __('Video restored to version %s', 'cbc_video'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    	6 => sprintf( __('Video published. <a href="%s">See video</a>', 'cbc_video'), esc_url( get_permalink($post_ID) ) ),
	    	7 => __('Video saved.', 'cbc_video'),
	    	8 => sprintf( __('Video saved. <a target="_blank" href="%s">See video</a>', 'cbc_video'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	    	9 => sprintf( __('Video will be published at: <strong>%1$s</strong>. <a target="_blank" href="%2$s">See video</a>', 'cbc_video'),
	      	// translators: Publish box date format, see http://php.net/date
	      	date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	    	10 => sprintf( __('Video draft saved. <a target="_blank" href="%s">See video</a>', 'cbc_video'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),

	    	101 => __('Please select a source', 'cbc_video'),
	    		
	    );
	
		return $messages;
	}
	
	/**
	 * Add subpages on our custom post type
	 */
	public function menu_pages(){
		$video_import = add_submenu_page(
			'edit.php?post_type='.$this->post_type, 
			__('Import videos', 'cbc_video'), 
			__('Import videos', 'cbc_video'), 
			'edit_posts', 
			'cbc_import',
			array($this, 'import_page'));
		
		$automatic_import = add_submenu_page(
			'edit.php?post_type='.$this->post_type, 
			__('Automatic YouTube video import', 'cbc_video'),
			__('Automatic import', 'cbc_video'),
			'edit_posts', 
			'cbc_auto_import',
			array($this, 'automatic_import_page')
		);		

		$settings = add_submenu_page(
			'edit.php?post_type='.$this->post_type, 
			__('Settings', 'cbc_video'), 
			__('Settings', 'cbc_video'), 
			'manage_options', 
			'cbc_settings',
			array($this, 'plugin_settings'));

		$compatibility = add_submenu_page(
			'edit.php?post_type='.$this->post_type,
			__('Info &amp; Help', 'cbc_video'),
			__('Info &amp; Help', 'cbc_video'),
			'manage_options',
			'cbc_help',
			array($this, 'page_help')
		);	

		$videos_list = add_submenu_page(
			null,
			__('Videos', 'cbc_video'), 
			__('Videos', 'cbc_video'), 
			'edit_posts', 
			'cbc_videos',
			array($this, 'videos_list'));	

		add_action( 'load-'.$video_import, array($this, 'video_import_onload') );
		add_action( 'load-'.$settings, array($this, 'plugin_settings_onload') );
		add_action( 'load-'.$compatibility, array($this, 'plugin_help_onload') );
		add_action( 'load-'.$videos_list , array( $this, 'video_list_onload' ) );
		add_action( 'load-'.$automatic_import, array( $this, 'playlists_onload' ) );

		$this->help_screens[ $automatic_import ] = array( 
			array(
				'id'		=> 'cbc_automatic_import_overview',
				'title'		=> __( 'Overview', 'cbc_video' ),
				'content'	=> cbc_get_contextual_help('automatic-import-overview')
			),
			array(
				'id'		=> 'cbc_automatic_import_frequency',
				'title'		=> __('Import frequency', 'cbc_video'),
				'content'	=> cbc_get_contextual_help('automatic-import-frequency')
			),
			array(
				'id'		=> 'cbc_automatic_import_as_post',
				'title'		=> __('Import videos as posts', 'cbc_video'),
				'content'	=> cbc_get_contextual_help('automatic-import-as-post')
			)
		);
		
	}
	
	/**
	 * Display contextual help on plugin pages
	 */
	public function contextual_help( $contextual_help, $screen_id, $screen ){
		// if not hooks page, return default contextual help
		if( !is_array( $this->help_screens ) || !array_key_exists( $screen_id, $this->help_screens )){
			return $contextual_help;
		}
		
		// current screen help screens
		$help_screens = $this->help_screens[$screen_id];
		
		// create help tabs
		foreach( $help_screens as $help_screen ){		
			$screen->add_help_tab($help_screen);		
		}
	}
	
	/**
	 * Automatic import load event
	 */
	public function playlists_onload(){
		
		$action = false;
		if( isset( $_GET['action'] ) ){
			$action = $_GET['action'];
		}else if( isset( $_POST['action'] ) || isset( $_POST['action2'] ) ){
			$action = isset( $_POST['action'] ) ? $_POST['action'] : $_POST['action2'];
		}
		
		
		if( !$action ){
			require_once CBC_PATH.'includes/libs/playlists-list-table.class.php';
			global $CBC_PLAYLISTS_TABLE;
			$CBC_PLAYLISTS_TABLE = new CBC_Playlists_List_Table();
			return;
		}
		
		global $CBC_PLAYLIST_ERRORS;
				
		switch( $action ){
			// reset playlist action
			case 'reset':
				if( isset( $_GET['_wpnonce'] ) ){
					if( wp_verify_nonce( $_GET['_wpnonce'] ) ){
						$post_id = (int)$_GET['id'];
						$meta = get_post_meta( $post_id, $this->playlist_meta, true );
						if( $meta ){
							$meta['updated'] 	= false;
							$meta['imported'] 	= 0;
							$meta['processed']	= 0;
							
							unset( $meta['first_video'] );
							unset( $meta['last_video'] );
							unset( $meta['finished'] );
							
							update_post_meta($post_id, $this->playlist_meta, $meta);	
						}					
					}
				}
				
				$r = add_query_arg(
					array(
						'post_type' => $this->post_type,
						'page'		=> 'cbc_auto_import'
					), 'edit.php'
				);
				wp_redirect( $r );
				
			break;
			// bulk start/stop importing from playlists
			case 'stop-import':
			case 'start-import':	
				if( wp_verify_nonce( $_POST['cbc_nonce'], 'cbc_playlist_table_actions' ) ){
					if( isset( $_POST['cbc_playlist'] ) ){
						$playlists = (array)$_POST['cbc_playlist'];
						
						$status = 'stop-import' == $action ? 'draft' : 'publish';						
						foreach( $playlists as $playlist_id ){							
							wp_update_post( array(
								'ID' 			=> $playlist_id,
								'post_status' 	=> $status
							));							
						}	
					}
				}
				$r = add_query_arg(
					array(
						'post_type' => $this->post_type,
						'page'		=> 'cbc_auto_import'
					), 'edit.php'
				);
				wp_redirect( $r );
			break;	
			// change playlist status action
			case 'queue':
				if( isset( $_GET['_wpnonce'] ) ){
					if( wp_verify_nonce( $_GET['_wpnonce'] ) ){
						$post_id = (int)$_GET['id'];
						$post = get_post( $post_id );
						if( $post && $this->playlist_type == $post->post_type ){
							$status = 'draft' == $post->post_status ? 'publish' : 'draft';
							wp_update_post( array(
								'ID' 			=> $post_id,
								'post_status' 	=> $status
							));	
						}					
					}
				}
				
				$r = add_query_arg(
					array(
						'post_type' => $this->post_type,
						'page'		=> 'cbc_auto_import'
					), 'edit.php'
				);
				wp_redirect( $r );
			break;	
			// delete playlist	
			case 'delete':
				if( isset( $_POST['cbc_nonce'] ) ){
					if( wp_verify_nonce( $_POST['cbc_nonce'], 'cbc_playlist_table_actions' ) ){
						if( isset( $_POST['cbc_playlist'] ) ){
							$playlists = (array)$_POST['cbc_playlist'];
							foreach( $playlists as $playlist_id ){
								wp_delete_post( $playlist_id, true );
							}	
						}
					}
					$r = add_query_arg(
						array(
							'post_type' => $this->post_type,
							'page'		=> 'cbc_auto_import'
						), 'edit.php'
					);
					wp_redirect( $r );
				}else if( isset( $_GET['_wpnonce'] ) ){
					if( wp_verify_nonce( $_GET['_wpnonce'] ) ){
						$post_id = (int)$_GET['id'];
						wp_delete_post( $post_id, true );
					}
					$r = add_query_arg(
						array(
							'post_type' => $this->post_type,
							'page'		=> 'cbc_auto_import'
						), 'edit.php'
					);
					wp_redirect( $r );
				}
			break;	
			// create playlist
			case 'add_new':
				if( isset( $_POST['cbc_wp_nonce'] ) ){
					if( check_admin_referer('cbc-save-playlist', 'cbc_wp_nonce') ){
						
						$defaults = cbc_playlist_settings_defaults();
						foreach( $defaults as $var => $val ){
							if( is_string($val) && empty( $_POST[$var] ) ){
								$CBC_PLAYLIST_ERRORS = new WP_Error();
								$CBC_PLAYLIST_ERRORS->add('cbc_fill_all', __('Please fill all required fields marked with *.', 'cbc_video'));
								break;
							}
						}
						
						if( is_wp_error( $CBC_PLAYLIST_ERRORS ) ){
							return;
						}
						
						$post_id = wp_insert_post(array(
							'post_title' 	=> $_POST['post_title'],
							'post_type' 	=> $this->playlist_type,
							'post_status' 	=> isset( $_POST['playlist_live'] ) ? 'publish' : 'draft'
						));
						
						$meta = array(
							'type' 			=> $_POST['playlist_type'],
							'id'			=> $_POST['playlist_id'],
							'theme_import' 	=> isset( $_POST['theme_import'] ),
							'native_tax'	=> isset( $_POST['native_tax'] ) ? (int)$_POST['native_tax'] : -1,
							'theme_tax'		=> isset( $_POST['theme_tax'] ) ? (int)$_POST['theme_tax'] : -1,
							'import_user'	=> isset( $_POST['import_user'] ) && $_POST['import_user'] ? (int)$_POST['import_user'] : get_current_user_id(),
							'start_date'	=> isset( $_POST['start_date'] ) ? $_POST['start_date'] : false,
							'no_reiterate'  => isset( $_POST['no_reiterate'] ),
							'updated' 		=> false,
							'total'			=> 0,
							'imported'		=> 0,
							'processed'		=> 0,
							'errors'		=> false
						);
						
						if( $post_id ){
							update_post_meta($post_id, $this->playlist_meta, $meta);
						}
						
						
						$r = add_query_arg(
							array(
								'post_type' => $this->post_type,
								'page' 		=> 'cbc_auto_import',
								'action'	=> 'edit',
								'id'		=> $post_id
							),'edit.php'
						);
						
						wp_redirect( $r );
						die();						
					}
				}else{
					wp_enqueue_script(
						'cbc-playlist-manage',
						CBC_URL.'assets/back-end/js/playlist-edit.js',
						array('jquery') 
					);
				}
			break;
			// edit playlist
			case 'edit':
				if( isset( $_POST['cbc_wp_nonce'] ) ){
					if( check_admin_referer('cbc-save-playlist', 'cbc_wp_nonce') ){
						$defaults = cbc_playlist_settings_defaults();
						foreach( $defaults as $var => $val ){
							if( is_string($val) && empty( $_POST[$var] ) ){
								$CBC_PLAYLIST_ERRORS = new WP_Error();
								$CBC_PLAYLIST_ERRORS->add('cbc_fill_all', __('Please fill all required fields marked with *.', 'cbc_video'));
								break;
							}
						}
						
						if( is_wp_error( $CBC_PLAYLIST_ERRORS ) ){
							return;
						}
						
						$post_id = (int)$_GET['id'];

						wp_update_post(array(
							'ID' => $post_id,
							'post_title' => $_POST['post_title'],
							'post_status' 	=> isset( $_POST['playlist_live'] ) ? 'publish' : 'draft'
						));
						
						$o_meta = get_post_meta( $post_id, $this->playlist_meta, true );
						
						$meta = array(
							'type' 			=> $_POST['playlist_type'],
							'id'			=> $_POST['playlist_id'],
							'theme_import' 	=> isset( $_POST['theme_import'] ),
							'native_tax'	=> isset( $_POST['native_tax'] ) ? (int)$_POST['native_tax'] : -1,
							'theme_tax'		=> isset( $_POST['theme_tax'] ) ? (int)$_POST['theme_tax'] : -1,
							'import_user'	=> isset( $_POST['import_user'] ) && $_POST['import_user'] ? (int)$_POST['import_user'] : get_current_user_id(),
							'start_date'	=> isset( $_POST['start_date'] ) ? $_POST['start_date'] : false,
							'no_reiterate'	=> isset( $_POST['no_reiterate'] ),
							'updated' 		=> $o_meta['updated'],
							'total'			=> $o_meta['total'],
							'imported'		=> $o_meta['imported'],
							'processed'		=> $o_meta['processed'],
							'errors'		=> $o_meta['errors']
						);
						
						update_post_meta($post_id, $this->playlist_meta, $meta);
						
						$r = add_query_arg(
							array(
								'post_type' => $this->post_type,
								'page' 		=> 'cbc_auto_import',
								'action'	=> 'edit',
								'id'		=> $post_id
							),'edit.php'
						);
						
						wp_redirect( $r );
						die();						
					}
				}else{
					wp_enqueue_script(
						'cbc-playlist-manage',
						CBC_URL.'assets/back-end/js/playlist-edit.js',
						array('jquery') 
					);
				}
			break;	
		}		
	}
	
	/**
	 * Automatic import page output
	 */
	public function automatic_import_page(){
		$action = isset( $_GET['action'] ) ? $_GET['action'] : false;
		
		switch( $action ){
			case 'add_new':
				$title = __('Add new playlist', 'cbc_video');
				$options = cbc_playlist_settings_defaults();
				
				global $CBC_PLAYLIST_ERRORS;
				if( is_wp_error( $CBC_PLAYLIST_ERRORS ) ){
					$error = $CBC_PLAYLIST_ERRORS->get_error_message();
				}
				
				$form_action = menu_page_url('cbc_auto_import', false).'&action=add_new';
				require CBC_PATH.'views/manage_playlist.php';
			break;
			case 'edit':
				$post_id = (int)$_GET['id'];
				$post = get_post( $post_id );
				$meta = get_post_meta($post_id, $this->playlist_meta, true);
				
				$options = array(
					'post_title' 	=> $post->post_title,
					'playlist_type' => $meta['type'],
					'playlist_id'	=> $meta['id'],
					'playlist_live'	=> 'publish' == $post->post_status,
					'theme_import'	=> $meta['theme_import'],
					'native_tax'	=> isset( $meta['native_tax'] ) ? $meta['native_tax'] : false,
					'theme_tax'		=> isset( $meta['theme_tax'] ) ? $meta['theme_tax'] : false,
					'import_user'	=> isset( $meta['import_user'] ) ? $meta['import_user'] : -1,
					'start_date'	=> isset( $meta['start_date'] ) ? $meta['start_date'] : false,
					'no_reiterate'  => isset( $meta['no_reiterate'] ) ? $meta['no_reiterate'] : false
				);
				
				$title = sprintf( __( 'Edit playlist <em>%s</em>', 'cbc_video' ), $post->post_title );				
				
				$form_action = menu_page_url('cbc_auto_import', false).'&action=edit&id='.$post_id;
				
				$add_new_url = menu_page_url('cbc_auto_import', false).'&action=add_new';
				$add_new_link = sprintf( '<a href="%1$s" title="%2$s" class="add-new-h2">%2$s</a>', $add_new_url, __('Add new', 'cbc_video') );
				
				require CBC_PATH.'views/manage_playlist.php';
			break;	
		}
		// if action is set, don't show the list of playlists
		if( $action ){
			wp_enqueue_script('jquery-ui-datepicker');			
			wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');			
			return;
		}		
		global $CBC_PLAYLISTS_TABLE;
		$CBC_PLAYLISTS_TABLE->prepare_items();
?>		
<div class="wrap">
	<div class="icon32 icon32-posts-video" id="icon-edit"><br></div>
	<h2>
		<?php _e('Automatic import', 'cbc_video')?>
		<a class="add-new-h2" href="<?php menu_page_url('cbc_auto_import');?>&action=add_new"><?php _e('Add New', 'cbc_video');?></a>	
	</h2>	
	<?php cbc_automatic_update_message( '<div class="message updated"><p>', '</p></div>', true );?>		
	<form method="post" action="">
		<?php wp_nonce_field('cbc_playlist_table_actions', 'cbc_nonce');?>
		<?php $CBC_PLAYLISTS_TABLE->views();?>
		<?php $CBC_PLAYLISTS_TABLE->display();?>
	</form>	
		
</div>
<?php 			
	}
	
	/**
	 * Video list is a modal page used for various actions that implie using videos.
	 * Should have no header and should be set as iframe.
	 */
	public function video_list_onload(){
		$_GET['noheader'] = 'true';
		if( !defined('IFRAME_REQUEST') ){
			define('IFRAME_REQUEST', true);
		}
		
		if( isset( $_GET['_wp_http_referer'] ) ){
			wp_redirect( 
				remove_query_arg( 
					array(
						'_wp_http_referer', 
						'_wpnonce',
						'volume',
						'width',
						'aspect_ratio',
						'autoplay',
						'controls',
						'cbc_video',
						'filter_videos'
					), 
					stripslashes( $_SERVER['REQUEST_URI'] ) 
				) 
			);			
		}		
	}
	
	/**
	 * Video list output
	 */
	function videos_list(){
		
		_wp_admin_html_begin();
		printf('<title>%s</title>', __('Video list', 'cbc_video'));		
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'ie' );
		wp_enqueue_script( 'utils' );
		
		wp_enqueue_style(
			'cbc-video-list-modal', 
			CBC_URL.'assets/back-end/css/video-list-modal.css', 
			false, 
			'1.0'
		);
		
		wp_enqueue_script(
			'cbc-video-list-modal',
			CBC_URL.'assets/back-end/js/video-list-modal.js',
			array('jquery'),
			'1.0'	
		);
		
		do_action('admin_print_styles');
		do_action('admin_print_scripts');
		do_action('cbc_video_list_modal_print_scripts');
		echo '</head>';
		echo '<body>';
		
		
		require CBC_PATH.'includes/libs/video-list-table.class.php';
		$table = new CBC_Video_List_Table();
		$table->prepare_items();
		
		global $CBC_POST_TYPE;
		$post_type = $CBC_POST_TYPE->get_post_type();
		if( isset($_GET['pt']) && 'post' == $_GET['pt'] ){
			$post_type = 'post';
		}
		
		?>
		<div class="wrap">
			<form method="get" action="" id="cbc-video-list-form">
				<input type="hidden" name="pt" value="<?php echo $post_type;?>" />
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'];?>" />
				<?php $table->views();?>
				<?php $table->search_box( __('Search', 'cbc_video'), 'video' );?>
				<?php $table->display();?>
			</form>
			<div id="cbc-shortcode-atts"></div>
		</div>	
		<?php
		
		echo '</body>';
		echo '</html>';
		die();
	}
	
	/**
	 * Extra columns in list table
	 * @param array $columns
	 */
	public function extra_columns( $columns ){		
		
		$cols = array();
		foreach( $columns as $c => $t ){
			$cols[$c] = $t;
			if( 'title' == $c ){
				$cols['video_id'] = __('Video ID', 'cbc_video');
				$cols['duration'] = __('Duration', 'cbc_video');	
			}	
		}		
		return $cols;
	}
	
	/**
	 * Extra columns in list table output
	 * @param string $column_name
	 * @param int $post_id
	 */
	public function output_extra_columns($column_name, $post_id){
		
		switch( $column_name ){
			case 'video_id':
				echo get_post_meta( $post_id, '__cbc_video_id', true );
			break;
			case 'duration':
				$meta = get_post_meta( $post_id, '__cbc_video_data', true );
				echo cbc_human_time($meta['duration']);
			break;	
		}
			
	}
	
	/**
	 * Output video importing page
	 */
	public function import_page(){
		
		global $CBC_List_Table;
		
		?>
<div class="wrap">
	<div class="icon32 icon32-posts-video" id="icon-edit"><br></div>
	<h2><?php _e('Import videos', 'cbc_video')?></h2>
		<?php 
		if( !$CBC_List_Table ){		
			require_once CBC_PATH.'views/import_videos.php';
		}else{
			$CBC_List_Table->prepare_items();
		?>	
	<form method="post" action="" class="ajax-submit">
		<?php wp_nonce_field('cbc-import-videos-to-wp', 'cbc_import_nonce');?>
		<input type="hidden" name="action" class="cbc_ajax_action" value="<?php echo $this->ajax_import_action?>" />
		<input type="hidden" name="cbc_source" value="youtube" />
		<?php 
			// import as theme posts - compatibility layer for deTube WP theme
			if( isset( $_REQUEST['cbc_theme_import'] ) ):
		?>
		<input type="hidden" name="cbc_theme_import" value="1" />
		<?php endif;// end of condition for compatibility layer for themes?>
		
		<?php $CBC_List_Table->display();?>
	</form>	
		<?php 	
		}
		?>
</div>		
		<?php 	
	}
	
	/**
	 * On video import page load, perform actions
	 */
	public function video_import_onload(){
		
		$this->video_import_assets();
		
		// search videos result
		if( isset( $_GET['cbc_search_nonce'] ) ){
			if( check_admin_referer( 'cbc-video-import', 'cbc_search_nonce' ) ){				
				
				require_once CBC_PATH.'/includes/libs/video-import-list-table.class.php';
				global $CBC_List_Table;
				
				$screen = get_current_screen();				
				$CBC_List_Table = new Video_Import_List_Table(array('screen' => $screen->id));
							
			}
		}
		
		// import videos / alternative to AJAX import
		if( isset( $_REQUEST['cbc_import_nonce'] ) ){
			if( check_admin_referer('cbc-import-videos-to-wp', 'cbc_import_nonce') ){				
				if( 'import' == $_REQUEST['action_top'] || 'import' == $_REQUEST['action2'] ){
					$this->import_videos();										
				}
				$options = cbc_get_settings();
				wp_redirect('edit.php?post_status='.$options['import_status'].'&post_type='.$this->post_type);
				exit();
			}
		}			
	}
	
	public function run_import( $raw_feed, $playlist_details ){
		
		if( !is_array( $raw_feed ) || !is_array($playlist_details) ){
			return;	
		}
		
		$video_ids = array();
		foreach( $raw_feed as $video ){
			$video_ids[] = $video['video_id'];
		}
		
		$_REQUEST['action_top'] = 'import';
		$_POST['cbc_import'] 	= $video_ids;
		$_POST['cbc_source'] 	= 'youtube';
		
		if( $playlist_details['theme_import'] ){
			$_POST['cbc_theme_import'] = true;
			if( isset( $playlist_details['theme_tax'] ) ){
				$_REQUEST['cat_top'] = $playlist_details['theme_tax'];
			}
		}else{
			if( isset( $playlist_details['native_tax'] ) ){
				$_REQUEST['cat_top'] = $playlist_details['native_tax'];
			}
		}
		
		if( $playlist_details['import_user'] ){
			$_REQUEST['user_top'] = $playlist_details['import_user'];
		}
		
		return $this->import_videos();
		
	}
	
	/**
	 * Import videos to WordPress
	 */	
	private function import_videos(){
		
		if( !isset( $_POST['cbc_import'] ) || !$_POST['cbc_import'] ){
			return false;
		}
		
		$plugin_post_type 	= $this->post_type; // store default plugin post type
		$plugin_taxonomy 	= $this->taxonomy; // store default plugin taxonomy
		
		// override importing as post type video and import as default post
		if( import_as_post() ){
			$plugin_post_type 	= 'post'; // switch to post post type
			$plugin_taxonomy 	= 'category'; // switch to category taxonomy			 
		}
		
		$videos = array_reverse( (array)$_POST['cbc_import'] );
		
		$result = array(
			'imported' 	=> 0,
			'skipped' 	=> 0,
			'total'		=> count( $videos )
		);
		
		// get import options
		$import_options = cbc_get_settings();
		$statuses 		= array('publish', 'draft', 'pending');
		$status 		= in_array( $import_options['import_status'], $statuses ) ? $import_options['import_status'] : 'draft';
		
		$category = false;
		if( isset( $_REQUEST['cat_top'] ) && 'import' == $_REQUEST['action_top'] ){
			$category = $_REQUEST['cat_top'];
		}elseif ( isset($_REQUEST['cat2']) && 'import' == $_REQUEST['action2']){
			$category = $_REQUEST['cat2'];
		}
		
		if( -1 == $category || 0 == $category ){
			$category = false;
		}
		
		$user = false;
		if( isset( $_REQUEST['user_top'] ) && $_REQUEST['user_top'] ){
			$user = (int)$_REQUEST['user_top'];
		}else if( isset( $_REQUEST['user2'] ) && $_REQUEST['user2'] ){
			$user = (int)$_REQUEST['user2'];
		}
		if( $user ){
			$user_data = get_userdata( $user );
			if( !$user_data ){
				$user = false;
			}else{
				$user = $user_data->ID;
			}
		}
		
		$theme_import = false;
		if( isset( $_POST['cbc_theme_import'] ) ){
			$theme_import = cbc_check_theme_support();
		}
		
		$post_type = $theme_import ? $theme_import['post_type'] : $plugin_post_type;
		
		foreach( $videos as $video_id ){
			
			// search if video already exists
			$posts = get_posts(array(
				'post_type' 	=> $post_type,
				'meta_key'		=> '__cbc_video_id',
				'meta_value' 	=> $video_id,
				'post_status' 	=> array('publish', 'pending', 'draft', 'future', 'private')
			));
			
			// video already exists, don't do anything
			if( $posts ){
				$result['skipped'] += 1;
				continue;
			}
			
			// get video details
			$request = cbc_query_video( $video_id );
			if( $request && 200 == $request['response']['code'] ){
				
				$data 	= json_decode( $request['body'], true );
				$video 	= cbc_format_video_entry( $data['data'] );
				
				if( $import_options['import_categories'] && !$category ){
					// if videos are imported for WP theme use
					if( $theme_import ){
						// if theme uses normal posts to store videos
						if(  !$theme_import['taxonomy'] && 'post' == $theme_import['post_type']  ){
							
							$post_category = term_exists( $video['category'], 'category' );
							
							if( 0 == $post_category || null == $post_category ){
								$post_category = wp_insert_term(
									$video['category'], 
									'category', 
									array(
										'description' => '', 
										'parent' => false
									)
								); // wp_create_category( $video['category'] );
							}
							
							if( isset( $post_category['term_id'] ) ){
								$post_category = $post_category['term_id'];
							}							
							
						}else{ // theme uses custom post type to store videos
							$term = term_exists( $video['category'], $theme_import['taxonomy'] );
							if( 0 == $term || null == $term ){
								$term = wp_insert_term($video['category'], $theme_import['taxonomy']);
							}	
						}
					}else{					
						// check if category exists
						$term = term_exists( $video['category'], $plugin_taxonomy );
						if( 0 == $term || null == $term ){
							// create the category
							$term = wp_insert_term( $video['category'], $plugin_taxonomy );
						}
					}	
				}	
				
				/* filter on video description for plugins to hook to */
				$video['description'] = apply_filters('cbc_video_description', $video['description'], $import_options['import_description']);
				
				// post content
				$post_content = '';
				if( 'content' == $import_options['import_description'] || 'content_excerpt' == $import_options['import_description'] ){
					$post_content = $video['description'];
				}
				// post excerpt
				$post_excerpt = '';
				if( 'excerpt' == $import_options['import_description'] || 'content_excerpt' == $import_options['import_description'] ){
					$post_excerpt = $video['description'];
				}
				
				// post title
				$video['title'] = apply_filters('cbc_video_title', $video['title'], $import_options['import_title']);
				$post_title 	= $import_options['import_title'] ? $video['title'] : '';
				
				// action on post insert that allows setting of different meta on post
				do_action('cbc_before_post_insert', $video, $theme_import);
				
				// set post data
				$post_data = array(
					'post_title' 	=> apply_filters('cbc_video_post_title', $post_title, $video, $theme_import),
					'post_content' 	=> apply_filters('cbc_video_post_content', $post_content, $video, $theme_import),
					'post_excerpt'	=> $post_excerpt,
					'post_type'		=> $post_type,
					'post_status'	=> $status
				);
				
				$pd = $import_options['import_date'] ? date('Y-m-d H:i:s', strtotime( $video['published'] )) : current_time( 'mysql' );
				$post_date = apply_filters( 'cbc_video_post_date', $pd, $video, $theme_import );
				
				if( isset( $import_options['import_date'] ) && $import_options['import_date'] ){
					$post_data['post_date_gmt'] = $post_date;
					$post_data['edit_date']		= $post_date;
					$post_data['post_date']		= $post_date;			
				}				
				
				// set user
				if( $user ){
					$post_data['post_author'] = $user;
				}
				
				$post_id = wp_insert_post( $post_data, true );
				
				// import video thumbnail as featured image
				if( $import_options['featured_image'] ){				
					// set featured image on post
					$this->add_featured_image( $post_id, $video['thumbnails'][1], $video['video_id'] );
				}	
				
				if( $theme_import && isset( $theme_import['post_format'] ) && $theme_import['post_format']  ){
					set_post_format( $post_id, $theme_import['post_format'] );
				}else{
					// on default imports, set post format to video
					set_post_format( $post_id, 'video' );
				}
				
				// check if post was created
				if( !is_wp_error($post_id) ){
					$result['imported'] += 1;
					// if category is given by user from existing categories
					if( $category ){
						if( $theme_import ){
							if(  !$theme_import['taxonomy'] && 'post' == $theme_import['post_type']  ){
								wp_set_post_categories( $post_id ,array($category) );								
							}else{
								wp_set_post_terms( $post_id, array( $category ), $theme_import['taxonomy'] );
							}
						}else{
							wp_set_post_terms( $post_id, array( $category ), $plugin_taxonomy );
						}
					}
					// if importing categories from YouTube
					else if( $import_options['import_categories'] ){
						// add category to video						
						if( $theme_import ){
							if(  !$theme_import['taxonomy'] && 'post' == $theme_import['post_type']  ){
								if( $post_category ){
									wp_set_post_categories( $post_id ,array($post_category) );
								}
							}else{
								wp_set_post_terms( $post_id, array( $term['term_id'] ), $theme_import['taxonomy'] );
							}
						}else{						
							wp_set_post_terms( $post_id, array( $term['term_id'] ), $plugin_taxonomy );
						}	
					}
					
					// action on post insert that allows setting of different meta on post
					do_action('cbc_post_insert', $post_id, $video, $theme_import, $post_type);
					
					if( $theme_import ){
						$url 		= 'http://www.youtube.com/watch?v='.$video['video_id'];
						$thumbnail 	= $video['thumbnails'][1];
						// player settings
						$ps = cbc_get_player_settings();
						$customize = implode('&', array(
							'controls='.$ps['controls'],
							'autohide='.$ps['autohide'],
							'fs='.$ps['fs'],
							'theme='.$ps['theme'],
							'color='.$ps['color'],
							'iv_load_policy='.$ps['iv_load_policy'],
							'modestbranding='.$ps['modestbranding'],
							'rel='.$ps['rel'],
							'showinfo='.$ps['showinfo'],
							'autoplay='.$ps['autoplay']
						));				
						$embed_code = '<iframe width="'.$ps['width'].'" height="'.cbc_player_height($ps['aspect_ratio'], $ps['width']).'" src="http://www.youtube.com/embed/'.$video['video_id'].'?'.$customize.'" frameborder="0" allowfullscreen></iframe>';
						
						foreach( $theme_import['post_meta'] as $k => $meta_key ){
							switch( $k ){
								case 'url' :
									update_post_meta($post_id, $meta_key, $url);
								break;	
								case 'thumbnail':
									update_post_meta($post_id, $meta_key, $thumbnail);
								break;
								case 'embed':
									update_post_meta($post_id, $meta_key, $embed_code);
								break;	
							}							
						}
						
						// set video id on post meta to avoid duplicates on future imports
						update_post_meta($post_id, '__cbc_video_id', $video['video_id']);
						// needed by other plugins	
						update_post_meta($post_id, '__cbc_video_url', $url);
						// save video data on post
						update_post_meta($post_id, '__cbc_video_data', $video);
						
					}else{					
						// set some meta on video post
						unset( $video['title'] );
						unset( $video['description'] );
						update_post_meta($post_id, '__cbc_video_id', $video['video_id']);
						// needed by other plugins	
						update_post_meta($post_id, '__cbc_video_url', 'http://www.youtube.com/watch?v='.$video['video_id']);
						
						update_post_meta($post_id, '__cbc_video_data', $video);
						
						if( import_as_post() ){	
							// flag post as video post					
							update_post_meta($post_id, '__cbc_is_video', true);
						}	
					}	
				}				
			}
		}

		return $result;
	}
	
	/**
	 * When trying to insert an empty post, WP is running a filter. Given the fact that
	 * users are allowed to insert empty posts when importing, the filter will return 
	 * false on maybe_empty to allow insertion of video. 
	 * 
	 * Filter is activated inside function import_videos()
	 * 
	 * @param bool $maybe_empty
	 * @param array $postarr
	 */
	public function force_empty_insert($maybe_empty, $postarr){
		if( $this->post_type == $postarr['post_type'] ){
			return false;
		}
	}
	
	/**
	 * Ajax response to video import action
	 */
	public function ajax_import_videos(){
		// import videos
		$response = array(
			'success' 	=> false,
			'error'		=> false
		);
		
		if( isset( $_POST['cbc_import_nonce'] ) ){
			if( check_admin_referer('cbc-import-videos-to-wp', 'cbc_import_nonce') ){				
				if( 'import' == $_POST['action_top'] || 'import' == $_POST['action2'] ){
					$result = $this->import_videos();
					
					if( $result ){					
						$response['success'] = sprintf( 
							__('Out of %d videos, %d were successfully imported and %d were skipped.', 'cbc_video'), 
							$result['total'],
							$result['imported'],
							$result['skipped']
						);
					}else{
						$response['error'] = __('No videos selected for importing. Please select some videos by checking the checkboxes next to video title.', 'cbc_video');
					}													
				}else{
					$response['error'] = __('Please select an action.', 'cbc_video');
				}			
			}else{
				$response['error'] = __("Cheatin' uh?", 'cbc_video');
			}	
		}else{
			$response['error'] = __("Cheatin' uh?", 'cbc_video');
		}	
		
		echo json_encode( $response );
		die();
	}
	
	/**
	 * Enqueue scripts and styles needed on import page
	 */
	private function video_import_assets(){
		// video import form functionality
		wp_enqueue_script(
			'cbc-video-search-js', 
			CBC_URL.'assets/back-end/js/video-import.js', 
			array('jquery'), 
			'1.0'
		);
		wp_localize_script('cbc-video-search-js', 'cbc_importMessages', array(
			'loading' => __('Importing, please wait...', 'cbc_video'),
			'wait'	=> __("Not done yet, still importing. You'll have to wait a bit longer.", 'cbc_video'),
			'server_error' => __('There was an error while importing your videos. The process was not successfully completed. Please try again. <a href="#" id="cbc_import_error">See error</a>', 'cbc_video')
		));
		
		wp_enqueue_style(
			'cbc-video-search-css',
			CBC_URL.'assets/back-end/css/video-import.css',
			array(),
			'1.0'
		);
	}
	
	/**
	 * Output plugin settings page
	 */
	public function plugin_settings(){
		$options = cbc_get_settings();
		$player_opt = cbc_get_player_settings();
		$envato_licence = get_option('_cbc_yt_plugin_envato_licence', '');
		$youtube_api_key = cbc_get_yt_api_key();
		include CBC_PATH.'views/plugin_settings.php';
	}
	
	/**
	 * Output the compatibility page
	 */
	public function page_help(){
		$themes = cbc_get_compatible_themes();
		$theme = cbc_check_theme_support();
		
		if( $theme ){
			$key = array_search($theme, $themes);
			if( $key ){
				$themes[$key]['active'] = true;				
			}	
		}
		
		$installed_themes = wp_get_themes( array('allowed' => true) );
		foreach( $installed_themes as $t ){
			$name = strtolower($t->Name);
			if( array_key_exists($name, $themes) && !isset($themes[$name]['active']) ){
				$themes[$name]['installed'] = true;				
			}
		}
		
		include CBC_PATH.'/views/help.php';
		
	}
	
	/**
	 * Process plugin settings
	 */
	public function plugin_settings_onload(){
		if( isset( $_POST['cbc_wp_nonce'] ) ){
			if( check_admin_referer('cbc-save-plugin-settings', 'cbc_wp_nonce') ){
				cbc_update_settings();
				cbc_update_player_settings();
				if( isset( $_POST['envato_purchase_code'] ) && !empty( $_POST['envato_purchase_code'] ) ){
					update_option('_cbc_yt_plugin_envato_licence', $_POST['envato_purchase_code']);
				}
				if( isset( $_POST['youtube_api_key'] ) ){
					cbc_update_api_key( $_POST['youtube_api_key'] );
				}				
			}
		}
		
		wp_enqueue_script(
			'cbc-video-edit',
			CBC_URL.'assets/back-end/js/video-edit.js',
			array('jquery'),
			'1.0'
		);			
	}
	
	/**
	 * Enqueue assets for compatibility page
	 */
	public function plugin_help_onload(){
		wp_enqueue_style(
			'cbc-admin-compat-style',
			CBC_URL.'assets/back-end/css/help-page.css'
		);		
	}
	
	/**
	 * Add meta boxes on video post type
	 */
	public function add_meta_boxes(){
		
		global $post;
		if( !$post ){
			return;
		}
		
		// add meta boxes to video posts, either default post type is imported as such or video post type	
		if( $this->is_video() ){
			add_meta_box(
				'cbc-video-settings', 
				__('Video settings', 'cbc_video'),
				array( $this, 'post_video_settings_meta_box' ),
				$post->post_type,
				'normal',
				'high'
			);
			
			add_meta_box(
				'cbc-show-video', 
				__('Live video', 'cbc_video'),
				array( $this, 'post_show_video_meta_box' ),
				$post->post_type,
				'normal',
				'high'
			);	
			
		}else{ // for all other post types add only the shortcode embed panel
			add_meta_box(
				'cbc-add-video', 
				__('Video shortcode', 'cbc_video'), 
				array($this, 'post_shortcode_meta_box'),
				$post->post_type,
				'side'
			);	
		}		
	}
	
	/**
	 * Manipulate output for featured image on custom post to allow importing of thumbnail as featured image
	 */
	public function post_thumbnail_meta_panel( $content, $post_id ){
		$post = get_post( $post_id );
		
		if( !$post ){
			return $content;
		}
		
		$video_id = get_post_meta( $post->ID, '__cbc_video_id', true );		
		if( !$video_id ){
			return $content;
		}
		
		$content .= sprintf( '<a href="#" id="cbc-import-video-thumbnail" class="button primary">%s</a>', __('Import YouTube thumbnail', 'cbc_video') );		
		return $content;
	}
	
	/**
	 * Ajax response to thumbnail import as featured image
	 */
	public function ajax_import_thumbnail(){
		
		if( !isset( $_POST['id'] ) ){
			die();
		}
		
		$post_id = absint( $_POST['id'] );
		$thumbnail = cbc_set_featured_image( $post_id, $this->post_type );
		
		if( !$thumbnail ){
			die();
		}
		
		$response = _wp_post_thumbnail_html( $thumbnail['attachment_id'], $thumbnail['post_id'] );
		wp_send_json_success( $response );
		
		die();
	}
	
	/**
	 * Post add shortcode meta box output
	 */
	public function post_shortcode_meta_box(){
		?>
		<p><?php _e('Add video/playlist into post.', 'cbc_video');?><p>
		<a class="button" href="#" id="cbc-shortcode-2-post" title="<?php _e('Add shortcode');?>"><?php _e('Add video shortcode');?></a>
		<?php	
	}
	
	/**
	 * Save post data from meta boxes. Hooked to save_post
	 */
	public function save_post($post_id, $post){
		if( !isset( $_POST['cbc-video-nonce'] ) ){
			return;
		}
		
		// check if post is the correct type		
		if( !$this->is_video() ){
			return;
		}
		// check if user can edit
		if( !current_user_can('edit_post', $post_id) ){
			return;
		}
		// check nonce
		if( !check_admin_referer('cbc-save-video-settings', 'cbc-video-nonce') ){
			return;
		}		
		
		ccb_update_video_settings( $post_id );		
	}
	
	/**
	 * Display live video meta box
	 */
	public function post_show_video_meta_box(){
		global $post;
		$video_id 	= get_post_meta($post->ID, '__cbc_video_id', true);
		$video_data = get_post_meta($post->ID, '__cbc_video_data', true);
	?>	
<script language="javascript">
;(function($){
	$(document).ready(function(){
		$('#ccb-video-preview').CCB_VideoPlayer({
			'video_id' 	: '<?php echo $video_data['video_id'];?>',
			'source'	: 'youtube'
		});
	})
})(jQuery);
</script>
<div id="ccb-video-preview" style="height:315px; width:560px; max-width:100%;"></div>		
	<?php	
	}
	
	public function post_video_settings_meta_box(){
		global $post;		
		$settings = ccb_get_video_settings( $post->ID );		
?>
<?php wp_nonce_field('cbc-save-video-settings', 'cbc-video-nonce');?>
<table class="form-table cbc-player-settings-options">
	<tbody>
		<tr>
			<th><label for="cbc_aspect_ratio"><?php _e('Player size', 'cbc_video');?>:</label></th>
			<td>
				<label for="cbc_aspect_ratio"><?php _e('Aspect ratio');?> :</label>
				<?php 
					$args = array(
						'options' 	=> array(
							'4x3' 	=> '4x3',
							'16x9' 	=> '16x9'
						),
						'name' 		=> 'aspect_ratio',
						'id'		=> 'cbc_aspect_ratio',
						'class'		=> 'cbc_aspect_ratio',
						'selected' 	=> $settings['aspect_ratio']
					);
					cbc_select( $args );
				?>
				<label for="cbc_width"><?php _e('Width', 'cbc_video');?>:</label>
				<input type="text" name="width" id="cbc_width" class="cbc_width" value="<?php echo $settings['width'];?>" size="2" />px
				| <?php _e('Height', 'cbc_video');?> : <span class="cbc_height" id="cbc_calc_height"><?php echo cbc_player_height( $settings['aspect_ratio'], $settings['width'] );?></span>px
			</td>
		</tr>
				
		<tr>
			<th><label for="cbc_video_position"><?php _e('Display video in custom post','cbc_video');?>:</label></th>
			<td>
				<?php 
					$args = array(
						'options' => array(
							'above-content' => __('Above post content', 'cbc_video'),
							'below-content' => __('Below post content', 'cbc_video')
						),
						'name' 		=> 'video_position',
						'id'		=> 'cbc_video_position',
						'selected' 	=> $settings['video_position']
					);
					cbc_select($args);
				?>
			</td>
		</tr>
		<tr>
			<th><label for="cbc_volume"><?php _e('Volume', 'cbc_video');?>:</label></th>
			<td>
				<input type="text" name="volume" id="cbc_volume" value="<?php echo $settings['volume'];?>" size="1" maxlength="3" />
				<label for="cbc_volume"><span class="description">( <?php _e('number between 0 (mute) and 100 (max)', 'cbc_video');?> )</span></label>
			</td>
		</tr>
		<tr>
			<th><label for="cbc_autoplay"><?php _e('Autoplay', 'cbc_video');?>:</label></th>
			<td>
				<input name="autoplay" id="cbc_autoplay" type="checkbox" value="1"<?php cbc_check((bool)$settings['autoplay']);?> />
				<label for="cbc_autoplay"><span class="description">( <?php _e('when checked, video will start playing once page is loaded', 'cbc_video');?> )</span></label>
			</td>
		</tr>
		
		<tr>
			<th><label for="cbc_controls"><?php _e('Show controls', 'cbc_video');?>:</label></th>
			<td>
				<input name="controls" id="cbc_controls" class="cbc_controls" type="checkbox" value="1"<?php cbc_check((bool)$settings['controls']);?> />
				<label for="cbc_controls"><span class="description">( <?php _e('when checked, player will display video controls', 'cbc_video');?> )</span></label>
			</td>
		</tr>
		
		<tr class="controls_dependant"<?php cbc_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_fs"><?php _e('Allow full screen', 'cbc_video');?>:</label></th>
			<td>
				<input name="fs" id="cbc_fs" type="checkbox" value="1"<?php cbc_check((bool)$settings['fs']);?> />
			</td>
		</tr>
		
		<tr class="controls_dependant"<?php cbc_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_autohide"><?php _e('Autohide controls');?>:</label></th>
			<td>
				<?php 
					$args = array(
						'options' => array(
							'0' => __('Always show controls', 'cbc_video'),
							'1' => __('Hide controls on load and when playing', 'cbc_video'),
							'2' => __('Hide controls when playing', 'cbc_video')
						),
						'name' => 'autohide',
						'id' => 'cbc_autohide',
						'selected' => $settings['autohide']
					);
					cbc_select($args);
				?>
			</td>
		</tr>
		
		<tr class="controls_dependant"<?php cbc_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_theme"><?php _e('Player theme', 'cbc_video');?>:</label></th>
			<td>
				<?php 
					$args = array(
						'options' => array(
							'dark' => __('Dark', 'cbc_video'),
							'light' => __('Light', 'cbc_video')
						),
						'name' => 'theme',
						'id' => 'cbc_theme',
						'selected' => $settings['theme']
					);
					cbc_select($args);
				?>
			</td>
		</tr>
		
		<tr class="controls_dependant"<?php cbc_hide((bool)$settings['controls']);?>>
			<th><label for="cbc_color"><?php _e('Player color', 'cbc_video');?>:</label></th>
			<td>
				<?php 
					$args = array(
						'options' => array(
							'red' => __('Red', 'cbc_video'),
							'white' => __('White', 'cbc_video')
						),
						'name' => 'color',
						'id' => 'cbc_color',
						'selected' => $settings['color']
					);
					cbc_select($args);
				?>
			</td>
		</tr>
		
		<tr class="controls_dependant" valign="top"<?php cbc_hide($settings['controls']);?>>
			<th scope="row"><label for="modestbranding"><?php _e('No YouTube logo on controls bar', 'cbc_video')?>:</label></th>
			<td>
				<input type="checkbox" value="1" id="modestbranding" name="modestbranding"<?php cbc_check( (bool)$settings['modestbranding'] );?> />
				<span class="description"><?php _e('Setting the color parameter to white will cause this option to be ignored.', 'cbc_video');?></span>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="iv_load_policy"><?php _e('Annotations', 'cbc_video')?>:</label></th>
			<td>
				<?php 
					$args = array(
						'options' => array(
							'1' => __('Show annotations by default', 'cbc_video'),
							'3'=> __('Hide annotations', 'cbc_video')
						),
						'name' => 'iv_load_policy',
						'selected' => $settings['iv_load_policy']
					);
					cbc_select($args);
				?>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="rel"><?php _e('Show related videos', 'cbc_video')?>:</label></th>
			<td>
				<input type="checkbox" value="1" id="rel" name="rel"<?php cbc_check( (bool)$settings['rel'] );?> />
				<label for="rel"><span class="description"><?php _e('when checked, after video ends player will display related videos', 'cbc_video');?></span></label>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="showinfo"><?php _e('Show video title in player', 'cbc_video')?>:</label></th>
			<td><input type="checkbox" value="1" id="showinfo" name="showinfo"<?php cbc_check( (bool )$settings['showinfo']);?> /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="disablekb"><?php _e('Disable keyboard player controls', 'cbc_video')?>:</label></th>
			<td>
				<input type="checkbox" value="1" id="disablekb" name="disablekb"<?php cbc_check( (bool)$settings['disablekb'] );?> />
				<span class="description"><?php _e('Works only when player has focus.', 'cbc_video');?></span>
			</td>
		</tr>	
		
	</tbody>
</table>
<?php		
	}
	
	/**
	 * Add scripts to custom post edit page
	 * @param unknown_type $hook
	 */
	public function post_edit_assets( $hook ){
		if( 'post.php' !== $hook ){
			return;
		}
		global $post;
		
		// check for video id to see if it was imported using the plugin
		$video_id = get_post_meta($post->ID, '__cbc_video_id', true);		
		if( !$video_id ){
			return;
		}
		
		// some files are needed only on custom post type edit page
		if( $this->is_video() ){		
			// add video player for video preview on post
			ccb_enqueue_player();
			wp_enqueue_script(
				'cbc-video-edit',
				CBC_URL.'assets/back-end/js/video-edit.js',
				array('jquery'),
				'1.0'
			);	
		}
		
		// video thumbnail functionality
		wp_enqueue_script(
			'cbc-video-thumbnail',
			CBC_URL.'assets/back-end/js/video-thumbnail.js',
			array('jquery'),
			'1.0'
		);

		wp_localize_script('cbc-video-thumbnail', 'CBC_POST_DATA', array('post_id' => $post->ID));
	}
	
	/**
	 * New post load action for videos.
	 * Will first display a form to query for the video.
	 */
	public function post_new_onload(){
		if( !isset( $_REQUEST['post_type'] ) || $this->post_type !== $_REQUEST['post_type'] ){
			return;
		}
		
		global $CBC_NEW_VIDEO;
		
		if( isset( $_POST['wp_nonce'] ) ){
			if( check_admin_referer('cbc_query_new_video', 'wp_nonce') ){
				
				$video_id = sanitize_text_field( $_POST['cbc_video_id'] );
				$request = cbc_query_video( $video_id );
				if( $request && !is_wp_error($request) && 200 == $request['response']['code'] ){
					$data 	= json_decode( $request['body'], true );
					$CBC_NEW_VIDEO 	= cbc_format_video_entry( $data['data'] );
					
					// apply filters on title and description
					$import_in_theme = isset( $_POST['single_theme_import'] ) && $_POST['single_theme_import'] ? cbc_check_theme_support() : array();
					$CBC_NEW_VIDEO['description'] 	= apply_filters('cbc_video_post_content', $CBC_NEW_VIDEO['description'], $CBC_NEW_VIDEO, $import_in_theme);
					$CBC_NEW_VIDEO['title'] 		= apply_filters('cbc_video_post_title', $CBC_NEW_VIDEO['title'], $CBC_NEW_VIDEO, $import_in_theme);
					// single post import date
					$import_options = cbc_get_settings();
					$post_date 		= $import_options['import_date'] ? date('Y-m-d H:i:s', strtotime( $CBC_NEW_VIDEO['published'] )) : current_time( 'mysql' );
					$CBC_NEW_VIDEO['post_date'] = apply_filters( 'cbc_video_post_date', $post_date, $CBC_NEW_VIDEO, $import_in_theme );
					
					add_filter('default_content', array( $this, 'default_content' ), 999, 2);
					add_filter('default_title', array( $this, 'default_title' ), 999, 2);
					add_filter('default_excerpt', array( $this, 'default_excerpt' ), 999, 2);
					
					// add video player for video preview on post
					ccb_enqueue_player();	
				}				
				
			}else{
				wp_die("Cheatin' uh?");
			}
		}
		// if video query not started, display the form
		if( !$CBC_NEW_VIDEO ){
			wp_enqueue_script(
				'cbc-new-video-js',
				CBC_URL.'assets/back-end/js/video-new.js',
				array('jquery'),
				'1.0'
			);
			
			$post_type_object = get_post_type_object( $this->post_type );
			$title = $post_type_object->labels->add_new_item;
			
			include ABSPATH .'wp-admin/admin-header.php';
			include CBC_PATH.'views/new_video.php';
			include ABSPATH .'wp-admin/admin-footer.php';
			die();
		}
	}
	
	/**
	 * Set video description on new post
	 * @param string $post_content
	 * @param object $post
	 */
	public function default_content( $post_content, $post ){
		global $CBC_NEW_VIDEO;
		if( !$CBC_NEW_VIDEO ){
			return;
		}
		
		return $CBC_NEW_VIDEO['description'];	
	}
	
	/**
	 * Set video title on new post
	 * @param string $post_title
	 * @param object $post
	 */
	public function default_title( $post_title, $post ){
		global $CBC_NEW_VIDEO;
		if( !$CBC_NEW_VIDEO ){
			return;
		}
		
		return $CBC_NEW_VIDEO['title'];		
	}
	
	/**
	 * Set video excerpt on new post, add taxonomies and save meta
	 * @param string $post_excerpt
	 * @param object $post
	 */
	public function default_excerpt( $post_excerpt, $post ){
		global $CBC_NEW_VIDEO;
		if( !$CBC_NEW_VIDEO ){
			return;
		}
		// set video ID on post meta
		update_post_meta($post->ID, '__cbc_video_id', $CBC_NEW_VIDEO['video_id']);
		// needed by other plugins
		update_post_meta($post->ID, '__cbc_video_url', 'http://www.youtube.com/watch?v='.$CBC_NEW_VIDEO['video_id']);
		// save video data on post
		update_post_meta($post->ID, '__cbc_video_data', $CBC_NEW_VIDEO);
		
		// import video thumbnail as featured image
		$settings = cbc_get_settings();
		if( $settings['featured_image'] ){
			// set featured image on post
			$this->add_featured_image( $post->ID, $CBC_NEW_VIDEO['thumbnails'][1], $CBC_NEW_VIDEO['video_id'] );
		}

		if( isset( $settings['import_date'] ) && $settings['import_date'] ){
			$postarr = array(
				'ID' => $post->ID,
				'post_date_gmt' => $CBC_NEW_VIDEO['post_date'],
				'edit_date'		=> $CBC_NEW_VIDEO['post_date'],
				'post_date'		=> $CBC_NEW_VIDEO['post_date']
			);
			wp_update_post($postarr);
		}
		
		// check if video should be imported as theme post
		$theme_import 	= isset( $_POST['single_theme_import'] ) ? cbc_check_theme_support() : array();
		// action on post insert that allows setting of different meta on post
		do_action('cbc_before_post_insert', $CBC_NEW_VIDEO, $theme_import);
		
		if( $theme_import && isset( $_POST['single_theme_import'] ) ){			
			$cat_id = wp_create_category($CBC_NEW_VIDEO['category']);
			$postarr = array(
				'ID' 			=> $post->ID,
				'post_type' 	=> $theme_import['post_type'],
				'post_content' 	=> $CBC_NEW_VIDEO['description'],
				'post_title'	=> $CBC_NEW_VIDEO['title'],
				'post_status'	=> 'draft'
				
			);
			wp_update_post($postarr);
			
			if( $cat_id ){
				wp_set_post_categories( $post->ID, array( $cat_id ) );
			}
			
			if( isset( $theme_import['post_format'] ) && $theme_import['post_format']  ){
				set_post_format( $post->ID, $theme_import['post_format'] );
			}
			
			$url 		= 'http://www.youtube.com/watch?v='.$CBC_NEW_VIDEO['video_id'];
			$thumbnail 	= $CBC_NEW_VIDEO['thumbnails'][1];
			
			// player settings
			$ps = cbc_get_player_settings();
			$customize = implode('&', array(
				'controls='.$ps['controls'],
				'autohide='.$ps['autohide'],
				'fs='.$ps['fs'],
				'theme='.$ps['theme'],
				'color='.$ps['color'],
				'iv_load_policy='.$ps['iv_load_policy'],
				'modestbranding='.$ps['modestbranding'],
				'rel='.$ps['rel'],
				'showinfo='.$ps['showinfo'],
				'autoplay='.$ps['autoplay']
			));
			
			$embed_code = '<iframe width="'.$ps['width'].'" height="'.cbc_player_height($ps['aspect_ratio'], $ps['width']).'" src="http://www.youtube.com/embed/'.$CBC_NEW_VIDEO['video_id'].'?'.$customize.'" frameborder="0" allowfullscreen></iframe>';
			
			foreach( $theme_import['post_meta'] as $k => $meta_key ){
				switch( $k ){
					case 'url' :
						update_post_meta($post->ID, $meta_key, $url);
					break;	
					case 'thumbnail':
						update_post_meta($post->ID, $meta_key, $thumbnail);
					break;
					case 'embed':
						update_post_meta($post->ID, $meta_key, $embed_code);
					break;	
				}							
			}
			
			$redirect = add_query_arg(array(
				'post' 		=> $post->ID,
				'action' 	=> 'edit'				
			), 'post.php');			
						
		}else{	// process video as plugin custom post type	
			
			$plugin_post_type 	= $this->post_type;
			$plugin_taxonomy 	= $this->taxonomy;
			
			// if imported as regular post, set a few things on the post
			if( import_as_post() ){
				$plugin_post_type 	= 'post';
				$plugin_taxonomy 	= 'category';
				
				$postarr = array(
					'ID' 			=> $post->ID,
					'post_type' 	=> 'post',
					'post_content' 	=> $CBC_NEW_VIDEO['description'],
					'post_title'	=> $CBC_NEW_VIDEO['title'],
					'post_status'	=> 'draft'					
				);				
				wp_update_post($postarr);
				
				update_post_meta($post->ID, '__cbc_is_video', true);
			}
			
			// check if category exists
			$term = term_exists( $CBC_NEW_VIDEO['category'], $plugin_taxonomy );
			if( 0 == $term || null == $term ){
				// create the category
				$term = wp_insert_term( $CBC_NEW_VIDEO['category'], $plugin_taxonomy );
			}		
			// add category to video
			wp_set_post_terms( $post->ID, array( $term['term_id'] ), $plugin_taxonomy );
			
			// on default imports, set post format to video
			set_post_format( $post->ID, 'video' );
			
			if( import_as_post() ){			
				$redirect = add_query_arg(array(
					'post' 		=> $post->ID,
					'action' 	=> 'edit'				
				), 'post.php');
			}			
		}

		// action on post insert that allows setting of different meta on post
		// consistent with action on bulk import
		do_action('cbc_post_insert', $post->ID, $CBC_NEW_VIDEO, $theme_import, $post->post_type);
		if( isset( $redirect ) ){
			wp_redirect($redirect);
			die();
		}		
	}
	
	/**
	 * Sets the given image URL as featured image on a given post_id 
	 * @param int $post_id - ID of post to set featured image on
	 * @param string $image_url - url for image
	 * @param string $video_id - YouTube video ID
	 */
	private function add_featured_image( $post_id, $image_url, $video_id ){
		
		// get the thumbnail	
		$response = wp_remote_get( $image_url, array( 'sslverify' => false ) );
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
		$new_filename = urldecode( basename( get_permalink( $post_id ) ) ) .'-youtube-thumb'. $image_extension;
	
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
			'post_title'		=> get_the_title( $post_id ).' - YouTube thumbnail',
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
		update_post_meta( $attach_id, 'video_thumbnail', $video_id );
			
		// set image as featured for current post
		update_post_meta( $post_id, '_thumbnail_id', $attach_id );
		
		return array(
			'post_id' 		=> $post_id,
			'attachment_id' => $attach_id
		);	
	}
	
	/**
	 * Removes extra text if set in Settings page to check descriptions and if found in
	 * imported description 
	 * 
	 * Callback function for filter 'cbc_video_post_content' set in class constructor
	 * 
	 * @param $content
	 * @param $video
	 * @param $theme_import
	 */
	public function format_description( $content, $video, $theme_import ){
		$settings = cbc_get_settings();
		
		// trim description based on given string delimiter
		$delimiter = false;
		if( isset( $settings['remove_after_text'] ) ){
			$delimiter = trim( esc_attr( cbc_strip_tags( $settings['remove_after_text'] ) ) );
		}
		if( $delimiter && !empty($delimiter) ){
			$position = strpos( $content, $delimiter );
			if( false != $position ){
				$content = substr( $content, 0, $position );
			}			
		}
		
		// make url's clickable if set
		if( isset($settings['make_clickable']) && $settings['make_clickable'] ){
			$content = make_clickable( $content );
		}
		
		return $content;		
	}
	
	/**
	 * Add video post type to homepage list of latest posts
	 * 
	 * Callback function for filter 'pre_get_posts' set in class constructor
	 */
	public function add_on_homepage( $query ){
		// check that page isn't admin page, is homepage and the query
		if ( !is_admin() && is_home() && $query->is_main_query() ){
			// get plugin settings
			$settings = cbc_get_settings();
			if( $settings['public'] && isset( $settings['homepage'] ) && $settings['homepage'] ){
				// get the post types queried
				$post_types = get_query_var('post_type');
				// add video to post type
				if( !is_array($post_types) ){
					$post_types = array( 'post', $this->post_type );
				}else{
					$post_types[] = $this->post_type;
				}
				
				// add video post type to query
				$query->set( 'post_type', $post_types );
			}				
		}
		return $query;	
	}
	
	/**
	 * Adds video post type to main feed.
	 * 
	 * Callback function to filter 'request' set in class constructor.
	 */
	public function add_to_main_feed( $vars ){
		if( isset( $vars['feed'] ) ){		
			$settings = cbc_get_settings();
			if( $settings['public'] && isset( $settings['main_rss'] ) && $settings['main_rss'] ){
				if( !isset( $vars['post_type'] ) ){
					$vars['post_type'] = array('post', $this->post_type);
					// set filter to put the correct taxonomy on custom post type video in feed entry
					add_filter('get_the_categories', array($this, 'set_feed_video_categories'));
				}
			}	
		}		
		return $vars;
	}
	
	/**
	 * Callback function for filter 'get_the_categories' set up in function 'CBC_Video_Post_Type->add_to_main_feed'
	 * When custom post type is inserted into main feed for each post the correct categorties based
	 * on post type taxonomy must be set. This does that otherwise all custom post type categories in 
	 * feed will end up as Uncategorized.
	 *
	 * @param array $categories
	 */
	public function set_feed_video_categories( $categories ){
		
		global $post;
		
		if( !$post || $this->post_type != $post->post_type ){
			return $categories;
		}
		
		$categories = get_the_terms( $post, $this->taxonomy );
		if ( ! $categories || is_wp_error( $categories ) )
			$categories = array();
		
		$categories = array_values( $categories );
		foreach ( array_keys( $categories ) as $key ) {
			_make_cat_compat( $categories[$key] );
		}	
		
		return $categories;
	}
	
	/**
	 * Helper function. Checks is current post is a video post.
	 * Also verifies regular post type and looks for flag variable '__cbc_is_video'
	 */
	private function is_video(){
		global $post;
		if( !$post ){
			return false;
		}
		
		if( $this->post_type == $post->post_type ){
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
	 * Return post type
	 */
	public function get_post_type(){
		return $this->post_type;
	}
	/**
	 * Return taxonomy
	 */
	public function get_post_tax(){
		return $this->taxonomy;
	}
	/**
	 * Return playlist post type
	 */
	public function get_playlist_post_type(){
		return $this->playlist_type;
	}
	
	public function get_playlist_meta_name(){
		return $this->playlist_meta;
	}
}

global $CBC_POST_TYPE;
$CBC_POST_TYPE = new CBC_Video_Post_Type();
