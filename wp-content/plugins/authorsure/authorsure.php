<?php
/*
 * Plugin Name: AuthorSure
 * Plugin URI: http://www.authorsure.com
 * Description: Makes it easier to authenticate Authorship with Google using use rel=me, rel=author and rel=publisher links
 * Version: 1.5
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com/about/
 * License: GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
define('AUTHORSURE_VERSION', '1.5');
define('AUTHORSURE', 'authorsure');
define('AUTHORSURE_ADMIN', 'authorsure_admin');
define('AUTHORSURE_ARCHIVE', 'authorsure_archive');
define('AUTHORSURE_PROFILE', 'authorsure_profile');
define('AUTHORSURE_POST', 'authorsure_post');
define('AUTHORSURE_HOME', 'http://www.authorsure.com/');
define('AUTHORSURE_PRO', 'http://www.authorsure.com/');
define('AUTHORSURE_PLUGIN_URL', plugins_url(AUTHORSURE).'/');
define('AUTHORSURE_IMAGES_URL', AUTHORSURE_PLUGIN_URL.'images/');
define('AUTHORSURE_GOOGLEPLUS_URL', 'https://plus.google.com/');


class authorsure {

    private static $extended_bio_metakey = 'authorsure_extended_bio';
    private static $hide_author_box_metakey = 'authorsure_hide_author_box'; //used for exceptions where the default is to show the author box
    private static $show_author_box_metakey = 'authorsure_show_author_box'; //used for exceptions where the default is to hide the author box
    private static $show_author_metakey = 'authorsure_show_author_on_list';
    private static $include_css_metakey = 'authorsure_include_css';
 	private static $authorsure_count = 0;
 	    
	private static $defaults = array(
 		'author_rel' => 'byline',  //menu, byline, footnote, box
 		'publisher_rel' => '',  //Google Plus URL of publisher	    
 	    'footnote_last_updated_by' => 'Last updated by',
 	    'footnote_last_updated_at' => 'at',
 		'footnote_show_updated_date' => true,
 	    'box_about' => 'About', 		
 	    'box_gravatar_size' => 60,
 	    'box_nofollow_links' => true,
 	    'hide_box_on_pages' => false,
		'hide_box_on_front_page' => false,
 	    'menu_about_page' => '',
 	    'menu_primary_author' => '', 	  
 	    'author_page_hook' => 'loop_start',
 	    'author_page_hook_index' => '1',
		'author_page_filter_bio' => false,   
 	    'author_show_title' => true,
 	    'author_show_avatar' => false,
 	    'author_about' => 'About', 		
 	    'author_bio' => 'summary',
 	    'author_bio_nofollow_links' => true,
	    'author_find_more' => 'Find more about me on:',
		'author_profiles_image_size' => 16,	
		'author_profiles_no_labels' => false,	 
	    'author_archive_heading'=> 'Here are my most recent posts',
		'archive_link' => 'publisher', //publisher, top or bottom
		'archive_intro_enabled' => false,
		'archive_last_updated_by' => 'Last updated by',
		'archive_author_id' => 0, 
		'archive_term_options' => array()
    );    
	private static $pro_defaults = array(
	    'facebook' => array('Facebook','Facebook URL'), 
	    'flickr' => array('Flickr', 'Flickr Profile URL'),
	    'googleplus'=> array('Google Plus', 'Google Plus Profile URL'), 
	    'linkedin' => array('LinkedIn', 'Linked In Profile URL'), 
	    'pinterest' => array('Pinterest', 'Pinterest Profile URL'), 
	    'skype' => array('Skype', 'Skype Name'),
	    'twitter'=> array('Twitter','Twitter Profile URL'),
	    'youtube'=> array('YouTube', 'YouTube Channel URL')
    );    

    private static $options = array();   
    private static $pro_options = array();    
    
    private static function get_defaults() {
		return self::$defaults;
    }

    private static function get_pro_defaults() {
		return self::$pro_defaults;
    }

	public static function get_pro_options ($cache = true) {
		if ($cache && (count(self::$pro_options) > 0)) return self::$pro_options;
		$defaults = self::get_pro_defaults();
		$options = get_option('authorsure_pro_options');
		self::$pro_options = empty($options) ? $defaults : wp_parse_args($options, $defaults); 
   		return self::$pro_options;
	}

	public static function get_options ($cache = true) {
		if ($cache && (count(self::$options) > 0)) return self::$options;
		$defaults = self::get_defaults();
		$options = get_option('authorsure_options');
		self::$options = empty($options) ? $defaults : wp_parse_args($options, $defaults); 
   		return self::$options;
	}

	public static function get_option($option_name) {
	    $options = self::get_options();
	    if ($option_name && $options && array_key_exists($option_name,$options))
	        return $options[$option_name];
	    else
	        return false;
	}
	
	public static function get_author_page_hook() {
		$hook = self::get_option('author_page_hook');
		if (empty($hook)) $hook = self::$defaults['author_page_hook']; 
		return $hook;
	}

	public static function get_author_page_hook_index() {
		$hook_index = self::get_option('author_page_hook_index');
		if (empty($hook_index)) $hook_index = self::$defaults['author_page_hook_index']; 
		return $hook_index;
	}

	public static function get_show_author_key() {
		    return self::$show_author_metakey;
	}

	public static function get_extended_bio_key() {
		    return self::$extended_bio_metakey;
	}

	public static function get_hide_author_box_key() {
		    return self::$hide_author_box_metakey;
	}

	public static function get_show_author_box_key() {
		    return self::$show_author_box_metakey;
	}
	
	public static function get_include_css_key() {
		    return self::$include_css_metakey;
	}
	
	public static function get_publisher() {
		return self::get_option('publisher_rel');	
	}

	public static function get_archive_option($term_id, $key) {
		if (!$term_id || !$key) return false;
		$options = self::get_option('archive_term_options');
		$arc_options= (is_array($options) && array_key_exists($term_id, $options)) ? $options[$term_id] : array();
		return is_array($arc_options) && array_key_exists($key, $arc_options) ? $arc_options[$key] : false;
	}
	
	public static function save_options ($options) {
		$result = update_option('authorsure_options',$options);
		self::get_options(false); //update cache
		return $result;
	}

	public static function save_archive_option ($term_id, $values) {
		if (! $term_id || ! $values || !is_array($values) || !is_numeric($term_id)) return false;
	    $all_options = self::get_options(false); //get all options
		$arc_options = $all_options['archive_term_options']; //get the option to update	    
		$arc_options[$term_id] = $values; //update it
	    $all_options['archive_term_options']= $arc_options; //update the set of all options
		return self::save_options($all_options); //save to the database 
	}

	public static function save_pro_options ($options) {
		$result = update_option('authorsure_pro_options',$options);
		self::get_pro_options(false); //update cache
		return $result;
	}

	private static function allow_img($allowedtags) {
		if ( !array_key_exists('img', $allowedtags) 
		|| (array_key_exists('img',$allowedtags) && !array_key_exists('src', $allowedtags['img']))) {
			$allowedtags['img']['src'] = array ();
			$allowedtags['img']['title'] = array ();
			$allowedtags['img']['alt'] = array ();
			$allowedtags['img']['height'] = array ();			
			$allowedtags['img']['width'] = array ();	
		}
		return $allowedtags;
	}
	
	private static function allow_arel($allowedtags) {
		if ( !array_key_exists('a', $allowedtags) 
		|| (array_key_exists('a',$allowedtags) && !array_key_exists('rel', $allowedtags['a'])))
			$allowedtags['a']['rel'] = array ();
		return $allowedtags;
	}	
	
	public static function wordpress_allow_img() {
		global $allowedtags;
		$allowedtags = self::allow_img($allowedtags);
	}

	public static function wordpress_allow_arel() {
		global $allowedtags;
		$allowedtags = self::allow_arel($allowedtags);
	}

	public function genesis_allow_arel($allowedtags) {
		return self::allow_arel($allowedtags);
	}	

	public static function genesis_allow_img($allowedtags) {
		return self::allow_img($allowedtags);
	}

	public static function is_author($user) {		
		return user_can($user, 'edit_posts');
	}

	public static function get_icon($profile, $label, $size ) {
		return sprintf('<img src="%1$s" alt="%2$s" />%3$s',
			AUTHORSURE_PLUGIN_URL.'images/'.$size.'px/'.$profile.'.png', $profile, $label);
	}

	public static function list_authors() {
		$s='';
		$authors = get_users(array('who' => 'authors', orderby => 'display_name'));
		foreach ($authors as $author) {
			if (get_user_option(self::get_show_author_key(), $author->ID))		
				$s .= self::get_box($author->ID);
		}
		return $s;
	}
	
	public static function add_contactmethods_profile( $contactmethods) {
		return self::add_contactmethods( $contactmethods, 1, 16);
	}
	
	public static function add_contactmethods_nolabels( $contactmethods) {
		return self::add_contactmethods( $contactmethods, -1);
	}	
	
	public static function add_contactmethods( $contactmethods, $label_index=0, $size=0) {
		if ($size==0) $size = self::get_option('author_profiles_image_size');
		$profiles = self::get_pro_options();
		if (is_array($profiles)) 
			foreach ($profiles as $profile => $labels) 
				//if (!array_key_exists($profile,$contactmethods)) 
					$contactmethods[$profile] = self::get_icon($profile, $label_index<0 ? '' : ('&nbsp;'.$labels[$label_index]),$size);
		return $contactmethods;
	}

	public static function get_blog_author_link($id) {
		return '<a rel="me" href="'. get_author_posts_url($id).'">'.get_bloginfo().'</a>';
	}
	
	private static function get_author_link($id) {
		return '<a rel="author" href="'. get_author_posts_url($id).'" class="authorsure-author-link">'.get_the_author_meta('display_name', $id ).'</a>';
	}

	private static function get_avatar($id) {
		return get_avatar( get_the_author_meta('email', $id), self::get_option('box_gravatar_size') );
	}

	private static function about_author($id) {
		return sprintf( '<h4>%1$s %2$s</h4>', self::get_option('box_about'), self::get_author_link($id));
	}

	private static function get_title($id) {
		if  (self::get_option('author_show_title')) {
			$author_name = get_the_author_meta('display_name',$id);
			if ($prefix = self::get_option('author_about'))
				$title = $prefix . ' ' .  $author_name;
			else
				$title = $author_name;
			return sprintf( '<h2 class="authorsure-author-title">%1$s</h2>',$title);				
		} else {
			return '';
		}
	}

	private static function get_bio($id) {
	    $nofollow = self::get_option('author_bio_nofollow_links'); //setting for author page
		switch (authorsure::get_option('author_bio')) {
			case 'summary':  return self::get_summary_bio($id, $nofollow); break;
			case 'extended':  return self::get_extended_bio($id, $nofollow); break;
		}
		return '';
	}	
	
	private static function get_summary_bio($id, $nofollow = true ) {
		return self::filter_links(wpautop( get_the_author_meta('description',$id) ), $nofollow );
	}	

	private static function get_extended_bio($id, $nofollow = true) { //return extended bio if present else return standard bio
		$bio =  self::filter_links(wpautop( get_the_author_meta(authorsure::$extended_bio_metakey,$id)), $nofollow);
		return empty($bio) ? self::get_summary_bio($id, $nofollow) : $bio ;
	}

	private static function get_box($id) {
		return sprintf('<div class="authorsure-author-box">%1$s%2$s%3$s</div><div class="clear"></div>',
			self::get_avatar($id), self::about_author($id), self::get_summary_bio($id, self::get_option('box_nofollow_links') ) );
	}
	
	private static function get_footnote($id) {	
		$author = sprintf( '<span style="float:none" class="author vcard"><span class="fn">%1$s</span></span>', self::get_author_link($id) );
		$updated_at = self::get_option('footnote_show_updated_date') ?
			sprintf( ' %1$s <time itemprop="dateModified" datetime="%2$s">%3$s</time>',self::get_option('footnote_last_updated_at'),get_post_modified_time('c'),get_the_modified_date()) : '';
		return sprintf( '<p class="updated" itemscope itemtype="http://schema.org/WebPage" itemid="%1$s">%2$s %3$s%4$s.</p>', 
			get_permalink(), self::get_option('footnote_last_updated_by'), $author, $updated_at);
	}
	
	private static function skype_me ($name, $img, $nolabels) {
		wp_enqueue_script('skypeCheck', 'http://download.skype.com/share/skypebuttons/js/skypeCheck.js',array(),'v2.2',true);
		if ($pos = strpos($name,'/status')) $name = substr($name,0,$pos) ;
		if (($nolabels==false) && ($pos > 0)) {
			$img .= sprintf('&nbsp;<img src="http://mystatus.skype.com/bigclassic/%1$s" style="border: none;" width="100" height="24" alt="My status" />',$name);
		}		
		return sprintf('<li style="list-style-type: none;"><a 
						href="skype:%1$s?call" title="Contact me on Skype">%2$s</a></li>', $name, $img);		
	}
	
	private static function get_profiles($user) {
		$s='';
		$profiles = self::get_pro_options();
		$no_labels = self::get_option('author_profiles_no_labels');
		add_filter('user_contactmethods', array(AUTHORSURE,$no_labels?'add_contactmethods_nolabels':'add_contactmethods'),10,1);
		foreach (_wp_get_user_contactmethods( $user ) as $name => $desc) {
			if (array_key_exists($name,$profiles) && !empty($user->$name))
				if ('skype'==$name)
					$s .= self::skype_me($user->$name,$desc,$no_labels);
				else
					$s .= sprintf('<li style="list-style-type: none;"><a href="%1$s" rel="me" title="Follow me on %2$s">%3$s</a></li>',
						$user->$name,ucwords($name),$desc);
		}	
		if (empty($s))
			return '';
		elseif ($no_labels)
			return sprintf('<ul class="single-line"><span>%1$s</span>%2$s</ul>',self::get_option('author_find_more'), $s);
		else
			return sprintf('<p>%1$s</p><ul>%2$s</ul>',self::get_option('author_find_more'), $s);
	}
	

	private static function get_archive_term_id() {
		global $wp_query;
		if (is_archive() && ($term = $wp_query->get_queried_object()))
			return $term->term_id;
		else
			return false;
	}

	private static function get_archive_author() {
		if ($author = self::get_archive_option(self::get_archive_term_id(), 'author'))
			return $author; //return author explicitly chosen for this archive
		else
			return self::get_option('archive_author_id'); //return the default author for archives
	}
	
	private static function get_archive_intro() {
		if ($intro = self::get_archive_option(self::get_archive_term_id(),'intro'))
			return sprintf ('<div class="authorsure-archive-intro">%1$s</div>',stripslashes($intro));
		else
			return '';
	}
	
	//add a section to archive page with rel=author to primary author
	private static function show_archive_primary_author($at_top ) {

		if (is_archive() && (!is_author()) //it's an archive page that is not an author page
			&& (self::$authorsure_count == 0) // and we have not already added a link 
			&& ($author = self::get_archive_author())) { //and we have a primary author
	    		self::$authorsure_count += 1;
	    		if ($at_top && self::get_option('archive_intro_enabled') && ($intro = self::get_archive_intro())) echo $intro;
	    		echo self::get_footnote($author);	
		}
	}

	private static function show_author_profile($user) {
		$id = $user->ID;
		if ($archive_heading = self::get_option('author_archive_heading'))
			$subtitle = sprintf('<p id="authorsure-posts-heading">%1$s</p>',$archive_heading);
		else 
			$subtitle = '';
		$title = self::get_title($id);
		if (self::get_option('author_show_avatar')) $title .= self::get_avatar($id);
		if 	( self::is_author($user))
			echo sprintf('<div id="authorsure-author-profile">%1$s%2$s%3$s<div class="clear"></div>%4$s</div>',
				$title, self::get_bio($id), self::get_profiles($user), $subtitle);
	}
	
	private static function fetch_footnote($author) {
		return self::show_author_rel($author) ? self::get_footnote($author) : '';
	}
	
	//is the post/page suitable to show rel=author?
	public static function show_author_rel($author_id, $show = true) {
		return $show && (is_single() || is_page()) && self::is_author($author_id) ;
	}

	//link (rel="author") the post/post to the author page in a post footnote 
	public static function append_post_author_footnote($content) {
		global $post;
		return $content . self::fetch_footnote($post->post_author);
	}

	//obtain user fron parameter or context
	private static function derive_user($attr) {
		if (is_array($attr) && array_key_exists('id',$attr)) {
			$id= $attr['id'];
		} else { //try looking in the post
			global $post;
			$id = ($post && property_exists($post,'post_author') && isset($post->post_author)) ? $post->post_author : 0;
		}
		if ($id > 0)
			$user_obj = new WP_User($id);
		else //try the URL
			$user_obj = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
		
		return ($user_obj && ($user_obj->ID > 0)) ? $user_obj : false	;
	}

	//shortcode for adding author profiles into a page
	public static function show_author_profiles($attr) {
		if ($user = self::derive_user($attr)) 
			return self::get_profiles($user) ;
		else
			return '';	
	}

	//shortcode for adding author box into a page
	public static function show_author_box($attr) {
		if ($user = self::derive_user($attr)) 
			return self::get_box($user->ID) ;
		else
			return '';	
	}

    private static function get_author_box_visibility($post_id,$is_post) {
		if ($is_post) {
			return ! get_post_meta($post_id, self::$hide_author_box_metakey, true);
		} else {			
			$hide = self::get_option('hide_box_on_pages') ;
			if ($hide)
				return get_post_meta($post_id, self::$show_author_box_metakey, true);
			else
				return ! get_post_meta($post_id, self::$hide_author_box_metakey, true);
		}
    }

	//link (rel="author") the post/post to the author page in an author box at the foot of the post
	public static function append_post_author_box($content) {
		global $post;
		$show = false;
		if (is_single()) 
			$show = self::get_author_box_visibility($post->ID,true); //show unless specific flag to hide
		elseif (is_page()) {
			if (is_front_page() && self::get_option('hide_box_on_front_page')) 
				$show = false; //hide front page is specifically disabled
			else
				$show = self::get_author_box_visibility($post->ID,false);
		}
		if (self::show_author_rel($post->post_author,$show)) $content .= self::get_box($post->post_author);	
		return $content;
	}

	//add primary author contact links to the about page
	public static function append_primary_author($content) {
		global $post;
		$about_page = self::get_option('menu_about_page');
		$primary = self::get_option('menu_primary_author');
		if ($primary && $about_page && is_page($about_page)) {
			$author = new WP_User($primary);
			$content .=  sprintf('<div id="authorsure-author-profile">%1$s</div>', self::get_profiles($author));
		}
		return $content;
	}

	//add a header to author page to link to Google (rel="me")
	public static function insert_author_bio() {
		global $post;
		if (is_author() && !is_feed()) {  //we're on an author page and it is not a feed
			$author_hook_index = self::get_author_page_hook_index(); 
	    	self::$authorsure_count += 1;
	    	if ($author_hook_index == self::$authorsure_count)  { //only add the bio once on the specified instance
				$curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
				self::show_author_profile($curauth);
			}
		}
	}
	
	//add a section to the top of archive page with rel=author to primary author
	public static function insert_archive_primary_author() {
		self::show_archive_primary_author(true);
	}

	//add a section to the foot of archive page with rel=author to primary author
	public static function append_archive_primary_author() {
		self::show_archive_primary_author(false);
	}

	//link the home page and possibly the archive pages to GooglePlus Page (rel="publisher")
	public static function add_publisher_rel() {
		if (($publisher = self::get_publisher())
		&& (is_front_page() || (is_archive() && ('publisher'==self::get_option('archive_link'))))) 
			echo ('<link rel="publisher" href="'.AUTHORSURE_GOOGLEPLUS_URL.$publisher.'" />');
	}
	
	public static function add_css() {
		global $post;
		$author_rel = self::get_option('author_rel');
		$about_page = self::get_option('menu_about_page');	
		if (('box'==$author_rel) 
		|| is_author() 
		|| ($about_page && is_page($about_page))
		|| ((is_page() || is_single()) && ($id = get_queried_object_id()) && get_post_meta($id,self::$include_css_metakey))) { 
			//include css for author boxes and on author page
    		wp_enqueue_style( AUTHORSURE, AUTHORSURE_PLUGIN_URL.'authorsure.css',array(),AUTHORSURE_VERSION);
		}
	}
	
    //** filter for use at the get_the_author_description hook **/
	public static function append_profiles($content, $user_id = false) {
		if 	(is_author()) { //only run on author pages
			if ($user_id && ($user_id > 0)) 
				$user = new WP_User( $user_id );  //use user_id is passed
			else
				$user = self::derive_user(false); //otherwise derive it

			if (self::is_author($user))
				$content = sprintf('%1$s<div id="authorsure-author-profile">%2$s</div>', $content, self::get_profiles($user));
		} 
		return $content;
	}	
	
	public static function init() {		

		//additions to head section
		add_action('wp', array(AUTHORSURE, 'add_css'));
		if (self::get_publisher()) add_action('wp_head', array(AUTHORSURE,'add_publisher_rel')) ; //add publisher link

		//additions to posts and pages
		$author_rel = self::get_option('author_rel');
		switch($author_rel) {
			case 'menu': add_filter('the_content', array(AUTHORSURE,'append_primary_author')); break;
			case 'footnote': add_filter('the_content', array(AUTHORSURE,'append_post_author_footnote')); break;
			case 'box': add_filter('the_content', array(AUTHORSURE,'append_post_author_box')); break;
			default: 	
		}

		//additions to author pages 
		if ($author_rel != 'menu') 
			if (self::get_option('author_page_filter_bio')) 
				add_filter('get_the_author_description', array(AUTHORSURE,'append_profiles'),10,2); //append profiles to existing bio
			else
				add_action(self::get_author_page_hook(), array(AUTHORSURE,'insert_author_bio')); //add bio to author page

		//additions to archive pages
		$archive_link = self::get_option('archive_link');
		if ('top'==$archive_link) add_action('loop_start', array(AUTHORSURE, 'insert_archive_primary_author')); //add archive link at top
		if ('bottom'==$archive_link) add_action('loop_end', array(AUTHORSURE, 'append_archive_primary_author')); //add archive link at bottom
	}

    static function add_footer_filter() {
 		add_filter('wp_list_bookmarks', array(self::CLASSNAME,'filter_links'),20); //nofollow links in custom footer widgets
    }    
         
    static function filter_links( $content, $nofollow = true) {
    	return $nofollow ? 
			preg_replace_callback( '/<a([^>]*)>(.*?)<\/a[^>]*>/is', array( AUTHORSURE, 'nofollow_link' ), $content ) : $content ;
    }		

    static function nofollow_link($matches) { //make link nofollow
		$attrs = shortcode_parse_atts( stripslashes ($matches[ 1 ]) );
		$atts='';
		foreach ( $attrs AS $key => $value ) {
			$key = strtolower($key);
			if ('rel' != $key) $atts .= sprintf('%1$s="%2$s" ', $key, $value);
		}
		$atts = substr( $atts, 0, -1 );
		return sprintf('<a rel="nofollow" %1$s>%2$s</a>', $atts, $matches[ 2 ]);
	}

}
add_action( 'wp_loaded', array(AUTHORSURE,'wordpress_allow_arel') );
add_filter( 'genesis_formatting_allowedtags', array(AUTHORSURE,'genesis_allow_arel') );

$thisdir = dirname(__FILE__) . '/';
if (is_admin()) {
	require_once($thisdir.'authorsure-admin.php');
	require_once($thisdir.'authorsure-archive.php');
	require_once($thisdir.'authorsure-profile.php');
	require_once($thisdir.'authorsure-post.php');
} else {
	add_action('init', array(AUTHORSURE,'init'));
	add_shortcode('authorsure_authors', array(AUTHORSURE,'list_authors'));	
	add_shortcode('authorsure_author_box', array(AUTHORSURE,'show_author_box'));
	add_shortcode('authorsure_author_profiles', array(AUTHORSURE,'show_author_profiles'));
}
?>