<?php
/**
 * Load WP_List_Table class
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CBC_Video_List_Table extends WP_List_Table{
	
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
	function column_default( $item, $column  ){
		return $item[$column];
	}
	
	/**
	 * Title
	 * @param array $item
	 */
	function column_post_title( $item ){
		
		$meta = get_post_meta( $item['ID'], '__cbc_video_data', true );
		
		$label = sprintf( '<label for="cbc-video-%1$s" id="title%1$s" class="cbc_video_label">%2$s</label>', $item['ID'], $item['post_title'] );
		
		$settings = ccb_get_video_settings( $item['ID'] );
		
		$form = '<div class="single-video-settings" id="single-video-settings-'.$item['ID'].'">';
		$form.= '<h4>'.$item['post_title'].' ('.cbc_human_time( $meta['duration'] ).')</h4>';
		$form.= '<label for="cbc_volume'.$item['ID'].'">'.__('Volume', 'cbc_video').'</label> <input size="3" type="text" name="volume['.$item['ID'].']" id="cbc_volume'.$item['ID'].'" value="'.$settings['volume'].'" /><br />';
		$form.= '<label for="cbc_width'.$item['ID'].'">'.__('Width', 'cbc_video').'</label> <input size="3" type="text" name="width['.$item['ID'].']" id="cbc_width'.$item['ID'].'" value="'.$settings['width'].'" /><br />';
		
		$aspect_select = cbc_select(
			array(
				'options' => array(
					'4x3' 	=> '4x3',
					'16x9' 	=> '16x9'
				),
				'name' 		=> 'aspect_ratio['.$item['ID'].']',
				'id' 		=> 'cbc_aspect_ratio'.$item['ID'],
				'selected' 	=> $settings['aspect_ratio']
			), false
		);
		$form.= '<label for="cbc_aspect_ratio'.$item['ID'].'">'.__('Aspect ratio', 'cbc_video').'</label> '.$aspect_select.'<br />';
		$form.= '<input type="checkbox" name="autoplay['.$item['ID'].']" id="cbc_autoplay'.$item['ID'].'" value="1"'.cbc_check( (bool)$settings['autoplay'], false ).' /> <label class="inline" for="cbc_autoplay'.$item['ID'].'">'.__('Auto play', 'cbc_video').'</label><br />';
		$form.= '<input type="checkbox" name="controls['.$item['ID'].']" id="cbc_controls'.$item['ID'].'" value="1"'.cbc_check( (bool)$settings['controls'] , false ).' /> <label class="inline" for="cbc_controls'.$item['ID'].'">'.__('Show player controls', 'cbc_video').'</label><br />';
		$form.= '<input type="button" id="shortcode'.$item['ID'].'" value="'.__('Insert shortcode', 'cbc_video').'" class="button cbc-insert-shortcode" />';
		$form.= '<input type="button" id="cancel'.$item['ID'].'" value="'.__('Cancel', 'cbc_video').'" class="button cbc-cancel-shortcode" />';
		$form.= '<div style="width:100%; display:block; clear:both"></div>';
		$form.= '</div>';
		
		// row actions
    	$actions = array(
    		'shortcode' => sprintf( '<a href="#" id="cbc-embed-%1$s" class="cbc-show-form">%2$s</a>'.$form, $item['ID'], __('Get video shortcode', 'cbc_video') ),
    	);
    	
    	return sprintf('%1$s %2$s',
    		$label,
    		$this->row_actions( $actions )
    	);	
		
	}
	
	/**
	 * Checkbox column
	 * @param array $item
	 */
	function column_cb( $item ){
		return sprintf(
			'<input type="checkbox" name="%1$s" value="%2$s" id="%3$s" class="cbc-video-checkboxes">',
			'cbc_video[]',
			$item['ID'],
			'cbc-video-'.$item['ID']
		);
	}
	
	/**
	 * YouTube video ID column
	 * @param array $item
	 */
	function column_video_id( $item ){
		$meta = get_post_meta( $item['ID'], '__cbc_video_data', true );
		return $meta['video_id'];
	}
	
	/**
	 * Video duration column
	 * @param array $item
	 */
	function column_duration( $item ){
		$meta = get_post_meta( $item['ID'], '__cbc_video_data', true );
		return '<span id="duration'.$item['ID'].'">'.cbc_human_time($meta['duration']).'</span>';
	}
	
	/**
	 * Display video categories
	 * @param array $item
	 */
	function column_category( $item ){
		global $CBC_POST_TYPE;	
		$taxonomy = $CBC_POST_TYPE->get_post_tax();
		if ( $terms = get_the_terms( $item['ID'], $taxonomy ) ) {
			$out = array();
			foreach ( $terms as $t ) {
				$url = add_query_arg(
					array(
						'post_type' => $CBC_POST_TYPE->get_post_type(),
						'page' 		=> 'cbc_videos',
						'cat'		=> $t->term_id
					)
				, 'edit.php');
				
				$out[] = sprintf('<a href="%s">%s</a>', $url, $t->name);
			}
			return implode(', ', $out);
		}else {
			return '&#8212;';
		}
	}
	
	/**
	 * Date column
	 * @param array $item
	 */
	function column_post_date( $item ){
		
		$output = sprintf( '<abbr title="%s">%s</abbr><br />', $item['post_date'], mysql2date( __( 'Y/m/d' ), $item['post_date'] ) );
		$output.= 'publish' == $item['post_status'] ? __('Published', 'cbc_video') : '';
		return $output;
		
	}
	
	function extra_tablenav($which){
		
		if( 'top' !== $which ){
			return ;
		}
		
		$selected = false;
		if( isset( $_GET['cat'] ) ){
			$selected = $_GET['cat'];
		}
		
		global $CBC_POST_TYPE;
		$taxonomy = $CBC_POST_TYPE->get_post_tax();
		if( isset( $_GET['pt'] ) && 'post' == $_GET['pt'] ){
			$taxonomy = 'category';
		}
		
		
		$args = array(
			'show_option_all' => __('All categories', 'cbc_video'),
			'show_count' 	=> 1,
			'taxonomy' 		=> $taxonomy,
			'name'			=> 'cat',
			'id'			=> 'cbc_video_categories',
			'selected'		=> $selected,
			'hide_if_empty'	=> true,
			'echo'			=> false
		);
		$categories_select = wp_dropdown_categories($args);
		if( !$categories_select ){
			return;
		}		
		?>
		<label for="cbc_video_categories"><?php _e('Categories', 'cbc_video');?> :</label>
		<?php echo $categories_select;?>
		<?php submit_button( __( 'Filter', 'cbc_video' ), 'button-secondary apply', 'filter_videos', false );?>
		<?php		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_views()
	 */
	function get_views(){
		
		$uri = remove_query_arg('cat', $_SERVER['REQUEST_URI']);
		
		$video_active = ' class="current"';
		$post_active = '';
		if( isset( $_GET['pt'] ) && 'post' == $_GET['pt'] ){
			$post_active = ' class="current"';
			$video_active = '';
		}
		
		global $CBC_POST_TYPE;
		$video_type = $CBC_POST_TYPE->get_post_type();
		
		$actions = array(
			'video' => sprintf('<a href="%1$s" title="%2$s"%3$s>%2$s</a>', add_query_arg(array('pt' => $video_type), $uri), __('Videos'), $video_active),
			'post'	=> sprintf('<a href="%1$s" title="%2$s"%3$s>%2$s</a>', add_query_arg(array('pt' => 'post'), $uri), __('Posts'), $post_active)
		);
		
		return $actions;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns(){
		$columns = array(
			'cb'			=> '<input type="checkbox" class="ccb-video-list-select-all" />',
			'post_title'	=> __('Title', 'cbc_video'),
			'video_id'		=> __('Video ID', 'cbc_video'),
			'duration'		=> __('Duration', 'cbc_video'),
			'category'	=> __('Category', 'cbc_video'),
			'post_date' 	=> __('Date', 'cbc_video'),
		);    	
    	return $columns;
	}
	
	/**
     * (non-PHPdoc)
     * @see WP_List_Table::prepare_items()
     */    
    function prepare_items() {
    	
    	$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        global $CBC_POST_TYPE;
        $this->_column_headers = array($columns, $hidden, $sortable);
                
    	$per_page 		= 20;
    	$current_page 	= $this->get_pagenum();
        
    	$search_for = '';
    	if( isset($_REQUEST['s']) ){
    		$search_for = esc_attr( stripslashes( $_REQUEST['s'] ) );
    	}

    	$category = false;
    	if( isset( $_GET['cat'] ) && $_GET['cat'] ){
    		$category = (int)$_GET['cat'];
    	}
    	
    	$post_type = $CBC_POST_TYPE->get_post_type();
    	if( isset( $_GET['pt'] ) && 'post' == $_GET['pt']){
    		$post_type = 'post';
    	}
    	
        $args = array(
			'post_type'			=> $post_type,
			'orderby' 			=> 'post_date',
		    'order' 			=> 'DESC',
	    	'posts_per_page'	=> $per_page,
	    	'offset'			=> ($current_page-1) * $per_page,
        	'post_status'		=> 'publish',
			's'					=> $search_for        	
        );
        
        // for regular posts stored as video, run meta query
        if( 'post' == $post_type ){
        	$args['meta_query'] = array(
        		array(
        			'key' => '__cbc_is_video',
        			'value' => true
        		)
        	);
        }
        
        if( $category ){
        	if( 'post' == $post_type ){
        		$args['cat'] = $category;
        	}else{        	
	        	$args['tax_query'] = array(
	        		array(
	        			'taxonomy' => 'videos', 
	        			'field' => 'id', 
	        			'terms' => $category
	        		)
	        	);
        	}	
        }
        
        
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