<?php
class OptimizePress_Blog_Seo_Module extends OptimizePress_Modules_Base {
	
	function __construct($config=array()){
		parent::__construct($config);
		if (OP_SEO_ENABLED == 'Y') {
			$seoOption = unserialize(get_option(OP_SN . '_seo'));
			if (empty($seoOption)) {
				$seo['enabled'] = 'Y';
				$this->update_option('seo', $seo);
			}
			add_action('wp_head',array($this,'output_metas'),0);
			add_action('admin_init',array($this,'add_meta_boxes'));
		} else if (!is_admin()) {
			add_action('wp_head', array($this, 'output_title'), 1);
		}
	}
	
	function add_meta_boxes(){
		//add_action(OP_SN.'-post_page-metas', array($this,'meta_box'));
		add_action( 'add_meta_boxes', array($this, 'add_seo_meta_boxes'));
		add_action( 'save_post', array($this,'save_meta_box'));
	}
	
	/* Adds a box to the main column on the Post and Page edit screens */
	function add_seo_meta_boxes() {
	    $screens = array( 'post', 'page' );
	    foreach ($screens as $screen) {
	        add_meta_box(
	            'op_seo_meta_box',
	            __( 'SEO', OP_SN ),
	            array($this, 'meta_box'),
	            $screen
	        );
	    }
	}
	
	function output_title()
	{
		$title = wp_title(' ', false, 'right' );
		if (empty($title)) {
			$site_title = '';
			if(!$site_title = op_get_option('seo','title')){
				$site_title = get_bloginfo('name');
				/*$site_description = get_bloginfo( 'description', 'display' );
				if ( $site_description && ( is_home() || is_front_page() ) )
					$site_title .= ' &mdash; '.$site_description;*/
			}		
			$title .= $site_title;	
		}
		if ( (isset($paged) && $paged >= 2) || (isset($page) && $page >= 2) ) {
			$title .= ' &mdash; ' . sprintf( __( 'Page %s', OP_SN ), max( $paged, $page ) );
		}

		if (!defined('GENESIS_LIB_DIR')) { // Genesis themes has their SEO that breaks titles if our title is echoed
			echo '<title>' . $title . '</title>';
		}
	}
	
	function meta_box($post){
		$id = 'op_seo_';
		$name = 'op[seo]';
		$seo = maybe_unserialize(get_post_meta($post->ID,'_'.OP_SN.'_seo',true));
		$seo = is_array($seo) ? $seo : array();
		wp_nonce_field( 'op_seo_meta_box', 'op_seo_meta_box');
		echo '
		<div id="op-meta-seo">
			<h4>'.__('SEO Meta Tags',OP_SN).'</h4>
			<table class="form-table">
			<tr>
			<th scope="row"><label for="'.$id.'title">'.__('Meta Title',OP_SN).'</label></th>
			<td><input style="width: 100%;height: 30px;" type="text" name="'.$name.'[title]" id="'.$id.'title" value="'.esc_attr(op_get_var($seo,'title')).'" /></td>
			</tr>
			
			<tr>
			<th scope="row"><label for="'.$id.'description">'.__('Meta Description',OP_SN).'</label></th>
			<td><textarea style="width: 100%;" name="'.$name.'[description]" id="'.$id.'description" cols="32" rows="5">'.esc_attr(op_get_var($seo,'description')).'</textarea></td>
			</tr>
			
			<tr>
			<th scope="row"><label for="'.$id.'keywords">'.__('Meta Keywords',OP_SN).'</label></th>
			<td><input style="width: 100%;height: 30px;" type="text" name="'.$name.'[keywords]" id="'.$id.'keywords" value="'.esc_attr(op_get_var($seo,'keywords')).'" /></td>
			</tr>
			
			<tr>
			<th scope="row"><label for="'.$id.'ogtitle">'.__('Facebook Title',OP_SN).'</label></th>
			<td><input style="width: 100%;height: 30px;" type="text" name="'.$name.'[ogtitle]" id="'.$id.'ogtitle" value="'.esc_attr(op_get_var($seo,'ogtitle')).'" /></td>
			</tr>
			<tr>
			<th scope="row"><label for="'.$id.'ogdescription">'.__('Facebook Description',OP_SN).'</label></th>
			<td><input style="width: 100%;height: 30px;" type="text" name="'.$name.'[ogdescription]" id="'.$id.'ogdescription" value="'.esc_attr(op_get_var($seo,'ogdescription')).'" /></td>
			</tr>
			<tr>
			<th scope="row"><label for="'.$id.'ogimage">'.__('Facebook Image',OP_SN).'</label></th><td>';
			op_upload_field('op[seo][ogimage]', esc_attr(op_get_var($seo,'ogimage')));
			echo '</td></tr></table>';
		echo '</div>';
	}
	
	function save_meta_box($post_id){
		if(!op_can_edit_page($post_id) || !isset($_POST['op_seo_meta_box']) || !wp_verify_nonce( $_POST['op_seo_meta_box'], 'op_seo_meta_box' ) ){
			return;
		}
		if($seo = op_post('op','seo')){
			$newseo = array(
				'title' => stripslashes(op_get_var($seo,'title')),
				'description' => stripslashes(op_get_var($seo,'description')),
				'keywords' => stripslashes(op_get_var($seo,'keywords')),
				'ogtitle' => stripslashes(op_get_var($seo,'ogtitle')),
				'ogdescription' => stripslashes(op_get_var($seo,'ogdescription')),
				'ogimage' => stripslashes(op_get_var($seo,'ogimage'))
			);
			update_post_meta($post_id, '_'.OP_SN.'_seo', maybe_serialize($newseo));
		}
	}

	function display_settings($section_name,$config=array(),$return=false){
		if (isset($_GET['page']) && $_GET['page'] != 'optimizepress') {
		$id = 'op_'.$section_name.'_';
		$name = 'op['.$section_name.']';
		/*if(defined('OP_PAGEBUILDER')){
			$messages = array(
				'Customize the SEO settings for this page. These options will override any options set in the global OptimizePress SEO settings',
				'Set the title for your page. this will show in your visitors\' browser and is also important for SEO.',
				'Set the description for this page. Some search engines will pull this information to show next to your site listing in their search results',
				'Set the individual keywords for this page. Each keyword should be separated with a comma. E.g. keyword 1, keyword 2 etc',
			);
		} else {*/
		$messages = array(
			__('Customize the SEO settings for this page.', OP_SN),
			__('Set the site wide title for your blog.', OP_SN),
			__('Set the default site wide description for your site.  Some search engines will pull this information to show next to your site listing in their search results', OP_SN),
			__('Set the default site wide keywords for your site.', OP_SN),
			__('Image must be minimum of 200x200px', OP_SN)
		);
		/*}*/
	?>
    <div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar">
	    <div class="entry-content">
        	<p><?php _e($messages[0], OP_SN) ?></p>
            <div>
            	<label for="<?php echo $id ?>title" class="form-title"><?php _e('Meta Title', OP_SN) ?></label>
	            <p class="op-micro-copy"><?php _e($messages[1],OP_SN) ?></p>
	            <input type="text" name="<?php echo $name ?>[title]" id="<?php echo $id ?>title" value="<?php echo $this->default_attr($section_name,'title') ?>" />
            </div>
            <div>
            	<label for="<?php echo $id ?>description" class="form-title"><?php _e('Meta Description', OP_SN) ?></label>
	            <p class="op-micro-copy"><?php _e($messages[2],OP_SN) ?></p>
	            <textarea name="<?php echo $name ?>[description]" id="<?php echo $id ?>description" cols="40" rows="5"><?php echo stripslashes($this->default_attr($section_name,'description')); ?></textarea>
            </div>
            <div>
	            <label for="<?php echo $id ?>title" class="form-title"><?php _e('Meta Keywords', OP_SN) ?></label>
    	        <p class="op-micro-copy"><?php _e($messages[3],OP_SN) ?></p>
    	        <input type="text" name="<?php echo $name ?>[keywords]" id="<?php echo $id ?>keywords" value="<?php echo $this->default_attr($section_name,'keywords') ?>" />
            </div>
            
            <div>
	            <label for="<?php echo $id ?>ogtitle" class="form-title"><?php _e('Facebook Title', OP_SN) ?></label>
    	        <p class="op-micro-copy"><?php //_e($messages[3],OP_SN) ?></p>
    	        <input type="text" name="<?php echo $name ?>[ogtitle]" id="<?php echo $id ?>ogtitle" value="<?php echo $this->default_attr($section_name,'ogtitle') ?>" />
            </div>
            
            <div>
	            <label for="<?php echo $id ?>ogdescription" class="form-title"><?php _e('Facebook Description', OP_SN) ?></label>
    	        <p class="op-micro-copy"><?php //_e($messages[3],OP_SN) ?></p>
    	        <input type="text" name="<?php echo $name ?>[ogdescription]" id="<?php echo $id ?>ogdescription" value="<?php echo $this->default_attr($section_name,'ogdescription') ?>" />
            </div>
            
            <div>
	            <label for="<?php echo $id ?>ogimage" class="form-title"><?php _e('Facebook Image', OP_SN) ?></label>
    	        <p class="op-micro-copy"><?php _e($messages[4],OP_SN) ?></p>
    	        <?php op_upload_field('op[seo][ogimage]', $this->default_attr($section_name,'ogimage'));?>
            </div>
    	</div>
    </div>
	<?php
		}
	}
	
	function save_settings($section_name,$config=array(),$op){
		if (isset($_GET['page']) && $_GET['page'] == 'optimizepress') {
			if (isset($op['enabled']) && $op['enabled'] == 'Y') {
				//op_update_option('seo_enabled', 'Y');
				$seo['enabled'] = 'Y';
				$this->update_option($section_name, $seo);
			} else {
				//op_update_option('seo_enabled', 'N');
				$seo['enabled'] = 'N';
				$this->update_option($section_name, $seo);
			}
		} else {
			if(($seo = $this->get_option($section_name)) === false){
				$seo = array();
			}
			foreach(array('title','description','keywords','ogtitle','ogdescription','ogimage') as $field){
				$seo[$field] = stripslashes(op_get_var($op,$field));
			}
			$this->update_option($section_name,$seo);
		}
	}
	
	function get_metas(){
		global $page, $paged, $post;
		$title = $keywords = $description = '';
		if(is_single() || is_page()){
			$seo = maybe_unserialize(get_post_meta(get_queried_object_id(),'_'.OP_SN.'_seo',true));
			$seo = is_array($seo) ? $seo : array();
			$title = op_get_var($seo,'title');
			$keywords = op_get_var($seo,'keywords');
			$description = op_get_var($seo,'description');
			$ogtitle = op_get_var($seo,'ogtitle');
			$ogdescription = op_get_var($seo,'ogdescription');
			$ogimage = op_get_var($seo,'ogimage');
		}
		if(empty($title)){
			$titleFull = false;
			$title = wp_title('&mdash;', false, 'right');
            $temp = explode('&mdash;', $title);
            if (isset($temp[1]) && strlen($temp[1]) == 1) {
                $title .= get_bloginfo('name');
            }
		} else {
			$titleFull = true;
			//$title .= ' &mdash; ';
		}
		$site_title = '';
		if(!$site_title = op_get_option('seo','title')){
			$site_title = get_bloginfo('name');
            /*$site_description = get_bloginfo( 'description', 'display' );
            if ( $site_description && ( is_home() || is_front_page() ) )
                $site_title .= ' &mdash; ' . $site_description;*/
		}
		if ($titleFull) {
            if (!empty($site_title)) {
			    $title .= ' &mdash; ' . $site_title;
            }
		}
		if ( $paged >= 2 || $page >= 2 )
			$title .= ' &mdash; ' . sprintf( __( 'Page %s', OP_SN ), max( $paged, $page ) );
			
		if(empty($keywords)){
			$keywords = '';
		}
		if(empty($description)){
			if (!empty($post->post_content)) {
				$cont = substr(strip_tags(strip_shortcodes($post->post_content)), 0, 150);
				$description = trim($cont);
			} else {
				$description = '';
			}
		}
		if (empty($ogtitle)) {
			$ogtitle = $title;
		}
		if (empty($ogdescription)) {
			$ogdescription = $description;
		}
		if (empty($ogimage)) {
			if(has_post_thumbnail($post->ID)){
				$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'list-image');
				$ogimage = $thumbnail[0];
			} else {
				$ogimage = '';
			}
		}
		return array(
			'title' => $title,
			'description' => $description,
			'keywords' => $keywords,
			'ogtitle' => $ogtitle,
			'ogdescription' => $ogdescription,
			'ogimage' => $ogimage
		);
	}
	
	function output_metas(){
		// removing GENESIS wp_title filter
		remove_filter( 'wp_title', 'genesis_doctitle_wrap', 20 );
		extract($this->get_metas());
		echo '<!-- OptimizePress SEO options -->'."\n";
		echo '<title>'.$title.'</title>' . "\n";
		if (!empty($description)) {
			echo '<meta name="description" content="'.esc_attr($description).'" />'. "\n";
		}
		if (!empty($keywords)) {
			echo '<meta name="keywords" content="'.esc_attr($keywords).'" />'. "\n";
		}
        echo '<meta property="og:type" content="article" />'. "\n";
        echo '<meta property="og:url" content="'.op_current_url().'" />'. "\n";
		echo '<meta property="og:title" content="'.esc_attr($ogtitle).'" />'. "\n";
		if (!empty($ogdescription)) {
			echo '<meta property="og:description" content="'.esc_attr($ogdescription).'" />'. "\n";
		}
		if (!empty($ogimage)) {
			echo '<meta property="og:image" content="'.esc_attr($ogimage).'" />'. "\n";
		}
		echo '<!-- OptimizePress SEO options end -->'."\n";
	}
}