<?php
/**
 * Load WP_List_Table class
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CBC_Playlists_List_Table extends WP_List_Table{
	
	function __construct( $args = array() ){
		parent::__construct( array(
			'singular' => 'playlist',
			'plural'   => 'playlists',
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );		
	}
	
	/**
	 * Default column
	 * @param array $item
	 * @param string $column
	 */
	function column_default( $item, $column  ){
		return $item[$column];
	}
	
	/**
	 * Checkbox column
	 * @param array $item
	 */
	function column_cb( $item ){
		return sprintf(
			'<input type="checkbox" name="%1$s" value="%2$s" id="%3$s" class="cbc-video-checkboxes">',
			'cbc_playlist[]',
			$item['ID'],
			'cbc-playlist-'.$item['ID']
		);
	}
	
	function column_post_title( $item ){
		
		global $CBC_POST_TYPE;
		
		$page_args = array(
				'post_type' => $CBC_POST_TYPE->get_post_type(),
				'page'		=> 'cbc_auto_import',
				'action' 	=> 'edit',
				'id'		=> $item['ID']
		);		
		$edit_url = add_query_arg( $page_args, 'edit.php' );
		
		$page_args['action'] = 'delete';
		$delete_url = add_query_arg( $page_args, 'edit.php' );
		
		$page_args['action'] = 'reset';
		$reset_url = add_query_arg( $page_args, 'edit.php' );
		
		$page_args['action'] = 'queue';
		$queue_url = add_query_arg( $page_args, 'edit.php' );
		$queue_text = 'draft' == $item['post_status'] ? __('Start', 'cbc_video') : __('Pause', 'cbc_video');
		
		// row actions
    	$actions = array(
    		'edit' 		=> sprintf( '<a href="%s">%s</a>', $edit_url, __('Edit', 'cbc_video') ),
    		'delete'	=> sprintf( '<a href="%s">%s</a>', wp_nonce_url( $delete_url ), __('Delete', 'cbc_video') ),
    		'reset'		=> sprintf('<a href="%s">%s</a>', wp_nonce_url($reset_url), __('Reset', 'cbc_video') ),
    		'queue'		=> sprintf( '<a href="%s">%s</a>', wp_nonce_url($queue_url), $queue_text),
    	);
    	
    	$text = 'draft' == $item['post_status'] ? '<em>'.$item['post_title'].'</em>' : '<strong>'.$item['post_title'].'</strong>';
    	
    	return sprintf('%1$s %2$s',
    		sprintf('<a href="%s" title="">%s</a>', $edit_url, $text),
    		$this->row_actions( $actions )
    	);	
		
	}
	
	function column_playlist_type( $item ){
		global $CBC_POST_TYPE;
		$meta_key = $CBC_POST_TYPE->get_playlist_meta_name();
		$meta = get_post_meta( $item['ID'], $meta_key, true );
		
		switch( $meta['type'] ){
			case 'user':
				return __('User uploads', 'cbc_video');
			break;
			case 'playlist':
				return __('Video playlist', 'cbc_video');
			break;	
		}		
	}
	
	function column_playlist_id( $item ){
		
		global $CBC_POST_TYPE;
		$meta_key = $CBC_POST_TYPE->get_playlist_meta_name();
		$meta = get_post_meta( $item['ID'], $meta_key, true );
		
		return $meta['id'];	
	}
	
	function column_videos_imported( $item ){
		global $CBC_POST_TYPE;
		$meta_key = $CBC_POST_TYPE->get_playlist_meta_name();
		$meta = get_post_meta( $item['ID'], $meta_key, true );
		
		return $meta['imported'];	
	}
	
	function column_videos_processed( $item ){
		global $CBC_POST_TYPE;
		$meta_key = $CBC_POST_TYPE->get_playlist_meta_name();
		$meta = get_post_meta( $item['ID'], $meta_key, true );
		$settings = cbc_get_settings();
		
		if( !$meta['total'] ){
			return __('no videos', 'cbc_video');
		}
		
		$from = $meta['processed'] + 1;
		$to = $meta['processed'] + $settings['import_quantity'];
		
		if( $to > $meta['total'] ){
			$to = $meta['total'];
		}
		if( $from > $meta['total'] ){
			$from = $meta['total'];
		}
		
		$message = sprintf( __('%d - %d', 'cbc_video'), $from, $to);
		return $message;	
	}
	
	function column_total_videos( $item ){
		global $CBC_POST_TYPE;
		$meta_key = $CBC_POST_TYPE->get_playlist_meta_name();
		$meta = get_post_meta( $item['ID'], $meta_key, true );
		
		return $meta['total'];	
	}
	
	function column_not_older($item){
		global $CBC_POST_TYPE;
		$meta_key = $CBC_POST_TYPE->get_playlist_meta_name();
		$meta = get_post_meta( $item['ID'], $meta_key, true );
		
		$result = empty($meta['start_date']) ? '<em style="color:#999;">' . __('not set', 'cbc_video') . '</em>' : $meta['start_date'];
		return $result;
	}
	
	function column_last_query( $item ){
		global $CBC_POST_TYPE;
		$meta_key = $CBC_POST_TYPE->get_playlist_meta_name();
		$meta = get_post_meta( $item['ID'], $meta_key, true );
		
		if( !$meta['updated'] ){
			return __('never', 'cbc_video');
		}
		
		return $meta['updated'];	
	}
	
	function column_status( $item ){
		
		if( 'draft' == $item['post_status'] ){
			return '<span style="color:red; font-style:italic;">'.__('Not in queue', 'cbc_video').'</span>';
		}
		
		global $CBC_POST_TYPE;
		$meta_key 		= $CBC_POST_TYPE->get_playlist_meta_name();
		$meta 			= get_post_meta( $item['ID'], $meta_key, true );
		$plugin_options = cbc_get_settings();
		
		$import_type_message 	= '';	
		$is_theme_import 		= false;
		
		if( $meta['theme_import'] ){
			$theme = cbc_check_theme_support();
			if( $theme ){			
				$import_type_message 	= '<span>'.sprintf( __('Import as posts compatible with <strong>%s</strong>.', 'cbc_video'), $theme['theme_name'] ).'</span>';
				$is_theme_import 		= true;
			}else{
				$import_type_message = __('Theme not active. Videos will be imported as plugin custom posts.', 'cbc_video');
			}			
		}else{
			$import_type_message = '<span>'.__('Import as custom post.').'</span>';			
		}
		
		if( isset( $meta['import_error'] ) ){
			$import_type_message .= '<br /><span style="color:red;"><strong>Error: </strong>'.$meta['import_error'].'</span>';
		}
		
		return $import_type_message;
	}
	
	function column_import_category( $item ){
		
		global $CBC_POST_TYPE;
		$meta_key 		= $CBC_POST_TYPE->get_playlist_meta_name();
		$meta 			= get_post_meta( $item['ID'], $meta_key, true );
		
		if( !isset($meta['theme_import']) || !$meta['theme_import'] ){
			if( isset($meta['native_tax']) && $meta['native_tax'] ){
				
				$options = cbc_get_settings();
				$taxonomy = isset( $options['post_type_post'] ) && $options['post_type_post'] ? 'category' : $CBC_POST_TYPE->get_post_tax();				
				$term = get_term( $meta['native_tax'], $taxonomy );
				if( $term && !is_wp_error( $term ) ){
					return '<em>'.$term->name.'</em>';
				}else{
					return '-';
				}				
			}else{
				return '-';
			}
		}else{
			if( isset( $meta['theme_tax'] ) && $meta['theme_tax'] ){
				$theme = cbc_check_theme_support();
				if( !$theme ){
					return '-';
				}
				
				$term = get_term( $meta['theme_tax'], ( !$theme['taxonomy'] ? 'category' : $theme['taxonomy'] ) );
				if( $term && !is_wp_error( $term ) ){
					return $term->name;
				}else{
					return '-';
				}				
				
			}else{
				return '-';
			}
		}		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns(){
		$columns = array(
			'cb'				=> '<input type="checkbox" />',
			'post_title'		=> __('Title', 'cbc_video'),
			'playlist_type' 	=> __('Playlist type', 'cbc_video'),
			'import_category'	=> __('Import in category', 'cbc_video'),
			'not_older'			=> __('Newer than', 'cbc_video'),
			'playlist_id'		=> __('Playlist ID', 'cbc_video'),
			'videos_processed'	=> __('Next to process', 'cbc_video'),
			'videos_imported' 	=> __('Imported', 'cbc_video'),
			'total_videos'		=> __('Total videos', 'cbc_video'),
			'last_query'		=> __('Queried on', 'cbc_video'),
			'status'			=> __('Status', 'cbc_video')
		);    	
    	return $columns;
	}
	
	/**
     * (non-PHPdoc)
     * @see WP_List_Table::get_bulk_actions()
     */
    function get_bulk_actions() {    	
    	$actions = array(
    		'delete' 		=> __('Delete', 'cbc_video'),
    		'stop-import'	=> __('Remove from import queue', 'cbc_video'),
    		'start-import'	=> __('Add to import queue', 'cbc_video')
    	);
    	return $actions;
    }
	
    /**
     * (non-PHPdoc)
     * @see WP_List_Table::get_views()
     */
    function get_views(){
    	
    	$url = menu_page_url('cbc_auto_import', false).'&view=%s';
    	$lt = '<a href="'.$url.'" title="%s" class="%s">%s<span class="count"> (%d)</span></a>';
    	
    	global $CBC_POST_TYPE;
    	$totals = wp_count_posts( $CBC_POST_TYPE->get_playlist_post_type() );
    	$all = $totals->publish + $totals->draft;
    	
    	$this->active = $totals->publish;
    	
    	$views = array(
    		'all'		=> sprintf( $lt, 'all', __('All automatic importers', 'cbc_video'), ( !isset($_GET['view']) || 'all' == $_GET['view'] ? 'current' : '' ) , __('All', 'cbc_video'), $all),
    		'active' 	=> sprintf( $lt, 'active', __('Active automatic importers', 'cbc_video'), ( isset( $_GET['view'] ) && 'active' == $_GET['view'] ? 'current' : '' ), __('Active', 'cbc_video'), $totals->publish ),
    		'inactive' 	=> sprintf( $lt, 'inactive', __('Inactive automatic importers', 'cbc_video'), ( isset( $_GET['view'] ) && 'inactive' == $_GET['view'] ? 'current' : '' ), __('Inactive', 'cbc_video'), $totals->draft )
    	);
    	
    	return $views;
    }
    
	/**
     * (non-PHPdoc)
     * @see WP_List_Table::prepare_items()
     */    
    function prepare_items() {
    	
    	$per_page 		= 20;
    	$current_page 	= $this->get_pagenum();
    	
    	global $CBC_POST_TYPE;
    	
    	$status = array('publish', 'draft');
    	if( isset( $_GET['view'] ) ){
    		switch( $_GET['view'] ){
    			case 'active':// active playlists
    				$status = 'publish';
    			break;
    			case 'inactive'://inactive playlists
					$status = 'draft';
    			break;	
    		}
    	}
    	
    	$args = array(
			'post_type'			=> $CBC_POST_TYPE->get_playlist_post_type(),
			'orderby' 			=> 'ID',
		    'order' 			=> 'ASC',
	    	'posts_per_page'	=> $per_page,
	    	'offset'			=> ($current_page-1) * $per_page,
        	'post_status'		=> $status   	
        );
    	
        $query = new WP_Query( $args );
    	$data = array();    
        if( $query->posts ){
        	foreach($query->posts as $k => $item){
        		$data[$k] = (array)$item;
        	}
        }
        
        $total_items = $query->found_posts;
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                     
            'total_pages' => ceil($total_items/$per_page)  
        ) );        
    }	
}