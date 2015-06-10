<?php

/**
 * Custom post type taxonomy widget
 */
class CBC_Videos_Taxonomy_Widget extends WP_Widget{
	/**
	 * Constructor
	 */
	function CBC_Videos_Taxonomy_Widget(){
		/* Widget settings. */
		$widget_options = array( 
			'classname' 	=> 'widget_categories', 
			'description' 	=> __('Video categories.', 'cbc_video') 
		);

		/* Widget control settings. */
		$control_options = array( 
			'id_base' => 'cbc-taxonomy-video-widget' 
		);

		/* Create the widget. */
		$this->WP_Widget( 
			'cbc-taxonomy-video-widget', 
			__('Video Categories', 'cbc_video'), 
			$widget_options, 
			$control_options 
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ){
		
		extract($args);
		
		global $CBC_POST_TYPE;
				
		$widget_title = '';
		if( isset( $instance['cbc_widget_title'] ) && !empty( $instance['cbc_widget_title'] ) ){
			$widget_title = $before_title . apply_filters('widget_title', $instance['cbc_widget_title']) . $after_title;
		}
		
		$args = array(
			'show_option_all'    => false,
			'orderby'            => 'name',
			'order'              => 'ASC',
			'style'              => 'list',
			'show_count'         => (bool)$instance['cbc_post_count'],
			'hide_empty'         => true,
			'use_desc_for_title' => true,
			'hierarchical'       => (bool)$instance['cbc_hierarchy'],
			'title_li'           => false,
			'show_option_none'   => __('No video categories', 'cbc_video'),
			'number'             => null,
			'echo'               => 1,
			'depth'              => 0,
			'current_category'   => 0,
			'pad_counts'         => 0,
			'taxonomy'           => $CBC_POST_TYPE->get_post_tax()
		);
				
		echo $before_widget;
		
		if( !empty( $instance['cbc_widget_title'] ) ){		
			echo $before_title . apply_filters('widget_title', $instance['cbc_widget_title']) . $after_title;
		}
		?>
		<ul>
			<?php wp_list_categories( $args );?>
		</ul>		
		<?php 
		echo $after_widget;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update($new_instance, $old_instance){

		$instance = $old_instance;
		$instance['cbc_widget_title'] 	= $new_instance['cbc_widget_title'];
		$instance['cbc_post_count']	  	= (bool)$new_instance['cbc_post_count'];
		$instance['cbc_hierarchy'] 	= (bool)$new_instance['cbc_hierarchy'];
				
		return $instance;		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ){
		
		$defaults 	= $this->get_defaults();;
		$options 	= wp_parse_args( (array)$instance, $defaults );
		
		?>
	<div class="cbc-player-settings-options">	
		<p>
			<label for="<?php echo  $this->get_field_id('cbc_widget_title');?>"><?php _e('Title', 'cbc_video');?>: </label>
			<input type="text" name="<?php echo  $this->get_field_name('cbc_widget_title');?>" id="<?php echo  $this->get_field_id('cbc_widget_title');?>" value="<?php echo $options['cbc_widget_title'];?>" class="widefat" />
		</p>
		<p>
			<input class="checkbox cbc_post_count" type="checkbox" name="<?php echo $this->get_field_name('cbc_post_count');?>" id="<?php echo $this->get_field_id('cbc_post_count')?>"<?php cbc_check((bool)$options['cbc_post_count']);?> />
			<label for="<?php echo $this->get_field_id('cbc_post_count')?>"><?php _e('Show video counts', 'cbc_video');?></label>
		</p>
		<p>
			<input class="checkbox cbc_hierarchy" type="checkbox" name="<?php echo $this->get_field_name('cbc_hierarchy');?>" id="<?php echo $this->get_field_id('cbc_hierarchy')?>"<?php cbc_check((bool)$options['cbc_hierarchy']);?> />
			<label for="<?php echo $this->get_field_id('cbc_hierarchy')?>"><?php _e('Show hierarchy', 'cbc_video');?></label>
		</p>
	</div>	
		<?php 		
	}
	
	/**
	 * Default widget values
	 */
	private function get_defaults(){
		$player_defaults = cbc_get_player_settings();		
		$defaults = array(
			'cbc_widget_title' 	=> __('Video categories', 'cbc_video'),
			'cbc_post_count' 	=> false,
			'cbc_hierarchy'		=> false
		);
		return $defaults;
	}
}