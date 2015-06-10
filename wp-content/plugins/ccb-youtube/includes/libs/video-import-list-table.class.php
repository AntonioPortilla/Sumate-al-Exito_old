<?php
class Video_Import_List_Table extends WP_List_Table{
	
	private $feed_errors;
	
	function __construct( $args = array() ){
		parent::__construct( array(
			'singular' => 'video',
			'plural'   => 'videos',
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}
	
	/**
	 * Default column
	 * @param array $item
	 * @param string $column
	 */
	function column_default( $item, $column ){
		if( array_key_exists($column, $item) ){
			return $item[ $column ];
		}else{
			return '<span style="color:red">'.sprintf( __('Column <em>%s</em> was not found.', 'cbc_video'), $column ).'</span>';
		}
	}
	
	/**
	 * Checkbox column
	 * @param array $item
	 */
	function column_cb( $item ){
		
		$output = sprintf( '<input type="checkbox" name="cbc_import[]" value="%1$s" id="cbc_video_%1$s" />', $item['video_id'] );
		return $output;
		
	}
	
	/**
	 * Title column
	 * @param array $item
	 */
	function column_title( $item ){	
		
		$label = sprintf( '<label for="cbc_video_%1$s" class="cbc_video_label">%2$s</label>', $item['video_id'], $item['title'] );

		
		// row actions
    	$actions = array(
    		'view' 		=> sprintf( '<a href="http://www.youtube.com/watch?v=%1$s" target="_cbc_youtube_open">%2$s</a>', $item['video_id'], __('View on YouTube', 'cbc_video') ),
    	);
    	
    	return sprintf('%1$s %2$s',
    		$label,
    		$this->row_actions( $actions )
    	);		
	}
	
	
	
	/**
	 * Column for video duration
	 * @param array $item
	 */
	function column_duration( $item ){		
		return cbc_human_time( $item['duration'] );
	}
	
	/**
	 * Rating column
	 * @param array $item 
	 */
	function column_rating( $item ){		
		if( 0 == $item['stats']['rating_count'] ){
			return '-';
		}
		
		return number_format( $item['stats']['rating'], 2 ) . sprintf( __(' (%d votes)', 'cbc_video'), $item['stats']['rating_count'] );
	}
	
	/**
	 * Views column
	 * @param array $item
	 */
	function column_views( $item ){
		if( 0 == $item['stats']['views'] ){
			return '-';
		}		
		return number_format( $item['stats']['views'], 0, '.', ',');		
	}
	
	/**
	 * Date when the video was published
	 * @param array $item
	 */
	function column_published( $item ){
		$time = strtotime( $item['published'] );
		return date('M dS, Y @ H:i:s', $time);
	}
		
	/**
     * (non-PHPdoc)
     * @see WP_List_Table::get_bulk_actions()
     */
    function get_bulk_actions() {    	
    	$actions = array(
    		/*'import' => __('Import', 'cbc_video')*/
    	);
    	
    	return $actions;
    }
	
	/**
     * Returns the columns of the table as specified
     */
    function get_columns(){
        
		$columns = array(
			'cb'		=> '<input type="checkbox" />',
			'title'		=> __('Title', 'cbc_video'),
			'category'	=> __('Category', 'cbc_video'),
			'video_id'	=> __('Video ID', 'cbc_video'),
			'uploader'	=> __('Uploader', 'cbc_video'),
			'duration'	=> __('Duration', 'cbc_video'),
			'rating'	=> __('Rating', 'cbc_video'),
			'views'		=> __('Views', 'cbc_video'),
			'published' => __('Published', 'cbc_video'),
		);    	
    	return $columns;
    }
    
    function extra_tablenav( $which ){
    	
    	$suffix = 'top' == $which ? '_top' : '2';
    	// plugin options
    	$options = cbc_get_settings();
    	// set selected category
   		$selected = false;
		if( isset( $_GET['cat'] ) ){
			$selected = $_GET['cat'];
		}
		// dropdown arguments
    	$args = array(
			'show_count' 	=> 1,
    		'hide_empty'	=> 0,
			'taxonomy' 		=> 'videos',
			'name'			=> 'cat'.$suffix,
			'id'			=> 'cbc_video_categories'.$suffix,
			'selected'		=> $selected,
    		'hide_if_empty' => true,
    		'echo'			=> false
		);
		// if importing as theme compatible posts
		if( isset( $_REQUEST['cbc_theme_import'] ) ){
			$theme_import = cbc_check_theme_support();
			if( $theme_import ){
				if( !$theme_import['taxonomy'] && 'post' == $theme_import['post_type']  ){
					$args['taxonomy'] = 'category';
				}else{
					$args['taxonomy'] = $theme_import['taxonomy'];
				}
			}
		}else if( isset( $options['post_type_post'] ) && $options['post_type_post'] ){ // plugin should import as regular post
			// set args for default post categories
			$args['taxonomy'] = 'category';
		}
		
		if( isset( $options ) && $options['import_categories'] ){
			$args['show_option_all'] = __('Create categories from YouTube', 'cbc_video');
		}else{
			$args['show_option_all'] = __('Select category (optional)', 'cbc_video');
		}
		// get dropdown output
		$categ_select = wp_dropdown_categories($args);
		// users dropdown
		$users = wp_dropdown_users(array(
			'show_option_all' 			=> __('Current user', 'cbc_video'),
			'echo'						=> false,
			'name'						=> 'user'.$suffix,
			'id'						=> 'cbc_video_user'.$suffix,
			'hide_if_only_one_author' 	=> true
		));		
		?>
    	<select name="action<?php echo $suffix;?>" id="action_<?php echo $which;?>">
    		<option value="-1"><?php _e('Bulk actions', 'cbc_video');?></option>
    		<option value="import"><?php _e('Import', 'cbc_video');?></option>
    	</select>
    	
    	<?php if( $categ_select ):?>
    	<label for="cbc_video_categories<?php echo $suffix;?>"><?php _e('Import into category', 'cbc_video');?> :</label>
		<?php echo $categ_select;?>
		<?php endif;?>
		
		<?php if( $users ):?>
		<label for="cbc_video_user<?php echo $suffix;?>"><?php _e('Import as user', 'cbc_video');?> :</label>
		<?php echo $users;?>
		<?php endif;?>
		
		<?php submit_button( __( 'Apply' ), 'action', false, false, array( 'id' => "doaction$suffix" ) );?>		
    	<span class="cbc-ajax-response"></span>
    	<?php
    }
    
    function no_items(){
    	
    	_e('YouTube feed is empty.', 'cbc_video');    	
    	if( is_wp_error( $this->feed_errors ) ){
    		echo '<br />';
    		printf( __(' <strong>API error</strong>: %s', 'cbc_video') , $this->feed_errors->get_error_message() ) ;
    	}    	
    }
    
    /**
     * (non-PHPdoc)
     * @see WP_List_Table::prepare_items()
     */    
    function prepare_items() {
    	$settings 	 = cbc_get_settings();
        $per_page 	 = $settings['manual_import_per_page'];
		$total_items = (int)$_GET['cbc_results'];
		$current_page = $this->get_pagenum();
		
		$args = array(
			'source' 	=> $_GET['cbc_source'],
			'feed'		=> $_GET['cbc_feed'],
			'query'		=> $_GET['cbc_query'],
			'order'		=> $_GET['cbc_order'],
    		'duration'	=> isset( $_GET['cbc_duration'] ) ? $_GET['cbc_duration'] : '',
			'results'	=> $per_page,
			'start-index' => ( $current_page - 1 ) * $per_page + 1
		);		
		
		require_once CBC_PATH.'includes/libs/video-import.class.php';
		$import = new CBC_Video_Import($args);
		$videos = $import->get_feed();
        
		$this->feed_errors = $import->get_errors();
		
		$total_yt_items = $import->get_total_items();
		if( $total_yt_items < $total_items ){
			$total_items = $total_yt_items;
		}
		
    	$this->items 	= $videos;
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                     
            'total_pages' => ceil( $total_items / $per_page )  
        ) );
    }   
    
    public function get_feed_errors(){
    	return $this->feed_errors;
    }
    
}