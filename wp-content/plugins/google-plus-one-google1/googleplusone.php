<?php

/*

Plugin Name: WordPress plugin - Google +1 Button
Plugin URI: http://www.appointy.com/

Description: New Google+1 button from Google. As seen on MASHABLE, TECHCRUNCH and many other websites. (Has advanced CSS option for better positioning)

Version: 2.0.3
Author: Appointy.com
Author URI: http://www.appointy.com

*/

$parser ='';
$write=0;

if ( !defined('WP_CONTENT_URL') ) {
	define('GOOGLEONE_URL',get_option('siteurl').'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/');
} else {
	define('GOOGLEONE_URL',WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
}

include_once(ABSPATH . WPINC . '/rss.php');

function googleone_options() {
	add_menu_page('Google +1', 'Google +1', 8, basename(__FILE__), 'googleone_options_page');
	add_submenu_page(basename(__FILE__), 'Settings', 'Settings', 8, basename(__FILE__), 'googleone_options_page');
}

// Manual output

function googleone_share_manual() {

    if (get_option('googleone_where') == 'manual') {
        return googleone_generate_button();
    } else {
        return false;
    }
}


function xh_google_get_featured_image($pageID)
{
	if ($pageID != ''){
			echo ('pageIn-->'.$pageID );
			if (has_post_thumbnail( $pageID )){
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $pageID ), 'single-post-thumbnail' ); 
				if ($image !='')			
					return $image[0];
				else
					return false;}
			else 
				echo('No Thumbnail Image');
				return false; }
	else
			return false;	
}

function googleone_options_page() {

?>

	 <div class="wrap" style="font-size:13px;">
	 <div class="wrap" style="font-size:13px;">
	   <h2>Settings for Google+1 Integration</h2>

			<div id="googleone_canvas" style="width:800px;float:left">

			<p>This plugin will install the Google +1 Share widget for each of your blog posts in both the content of your posts and page.</p>

			<form method="post" action="options.php">

			<p>
			  <?php

				// New way of setting the fields, for WP 2.7 and newer

				if(function_exists('settings_fields')){

					settings_fields('googleone-options');

				} else {

					wp_nonce_field('update-options');

?>   
			  <input type="hidden" name="action" value="update" />
			  
			  <input type="hidden" name="page_options" value="googleone_button_type, googleone_where,googleone_style,googleone_display_page,googleone_display_front" />
			  
			  <?php
        }

    ?>
			</p>
			<div><strong>Select</strong></div>
<div style="float:left;width:50px;">
					<div style="padding:4px 20px;border:1px solid #f5f5f5;">            	   
           	        <strong> &nbsp;</strong></div>
   	    <div style="padding:7px 20px;border:1px solid #f5f5f5;">     
            	      <input type="radio" name="googleone_button_type" value="1" id="googleone_button_type_1" <?php if (get_option('googleone_button_type') == '1') echo 'checked="checked"'; ?> />
        </div><div style="padding:8px 20px;border:1px solid #f5f5f5;">     
            	      <input type="radio" name="googleone_button_type" value="2" id="googleone_button_type_2" <?php if (get_option('googleone_button_type') == '2') echo 'checked="checked"'; ?>/>
            	      </div><div style="padding:9px 20px;border:1px solid #f5f5f5;">     
            	      <input type="radio" name="googleone_button_type" value="3" id="googleone_button_type_3" <?php if (get_option('googleone_button_type') == '3') echo 'checked="checked"'; ?>/>
            	      </div><div style="padding:10px 20px;border:1px solid #f5f5f5;">     
            	      <input type="radio" name="googleone_button_type" value="4" id="googleone_button_type_4" <?php if (get_option('googleone_button_type') == '4') echo 'checked="checked"'; ?>/>
            	      </div><div style="padding:11px 20px;border:1px solid #f5f5f5;">     
            	      <input type="radio" name="googleone_button_type" value="5" id="googleone_button_type_5" <?php if (get_option('googleone_button_type') == '5') echo 'checked="checked"'; ?>/>
            	      </div><div style="padding:12px 20px;border:1px solid #f5f5f5;">     
            	      <input type="radio" name="googleone_button_type" value="6" id="googleone_button_type_6" <?php if (get_option('googleone_button_type') == '6') echo 'checked="checked"'; ?>/>
            	      </div><div style="padding:29px 20px;border:1px solid #f5f5f5;">     
            	      <input type="radio" name="googleone_button_type" value="7" id="googleone_button_type_7" <?php if (get_option('googleone_button_type') == '7') echo 'checked="checked"'; ?>/>
            	      </div>
       	      </div><div style="float:left">
            <img src="<?php echo GOOGLEONE_URL; ?>/images/google_plus_one.png" width="282" height="320" /></div>
            
            <div style="clear:both;padding:10px 0px;"><strong>Language</strong> <br /><select id="googleone_lang" name="googleone_lang">
                  <option value="ar" <?php if (get_option('googleone_lang') == 'ar') echo 'selected="selected"'; ?>>
                    Arabic
                  </option>
                  <option value="bg" <?php if (get_option('googleone_lang') == 'bg') echo 'selected="selected"'; ?>>
                    Bulgarian
                  </option>
                  <option value="ca" <?php if (get_option('googleone_lang') == 'ca') echo 'selected="selected"'; ?>>
                    Catalan
                  </option>
                  <option value="zh-CN" <?php if (get_option('googleone_lang') == 'zh-CN') echo 'selected="selected"'; ?>>
                    Chinese (Simplified)
                  </option>
                  <option value="zh-TW" <?php if (get_option('googleone_lang') == 'zh-TW') echo 'selected="selected"'; ?>>
                    Chinese (Traditional)
                  </option>
                  <option value="hr" <?php if (get_option('googleone_lang') == 'hr') echo 'selected="selected"'; ?>>
                    Croatian
                  </option>
                  <option value="cs" <?php if (get_option('googleone_lang') == 'cs') echo 'selected="selected"'; ?>>
                    Czech
                  </option>
                  <option value="da" <?php if (get_option('googleone_lang') == 'da') echo 'selected="selected"'; ?>>
                    Danish
                  </option>
                  <option value="nl" <?php if (get_option('googleone_lang') == 'nl') echo 'selected="selected"'; ?>>
                    Dutch
                  </option>
                  <option value="en-US" <?php if (get_option('googleone_lang') == 'en-US') echo 'selected="selected"'; ?>>
                    English (US)
                  </option>
                  <option value="en-GB" <?php if (get_option('googleone_lang') == 'en-GB') echo 'selected="selected"'; ?>>
                    English (UK)
                  </option>
                  <option value="et" <?php if (get_option('googleone_lang') == 'et') echo 'selected="selected"'; ?>>
                    Estonian
                  </option>
                  <option value="fil" <?php if (get_option('googleone_lang') == 'fil') echo 'selected="selected"'; ?>>
                    Filipino
                  </option>
                  <option value="fi" <?php if (get_option('googleone_lang') == 'fi') echo 'selected="selected"'; ?>>
                    Finnish
                  </option>
                  <option value="fr" <?php if (get_option('googleone_lang') == 'fr') echo 'selected="selected"'; ?>>
                    French
                  </option>
                  <option value="de" <?php if (get_option('googleone_lang') == 'de') echo 'selected="selected"'; ?>>
                    German
                  </option>
                  <option value="el" <?php if (get_option('googleone_lang') == 'el') echo 'selected="selected"'; ?>>
                    Greek
                  </option>
                  <option value="iw" <?php if (get_option('googleone_lang') == 'iw') echo 'selected="selected"'; ?>>
                    Hebrew
                  </option>
                  <option value="hi" <?php if (get_option('googleone_lang') == 'hi') echo 'selected="selected"'; ?>>
                    Hindi
                  </option>
                  <option value="hu" <?php if (get_option('googleone_lang') == 'hu') echo 'selected="selected"'; ?>>
                    Hungarian
                  </option>
                  <option value="id" <?php if (get_option('googleone_lang') == 'id') echo 'selected="selected"'; ?>>
                    Indonesian
                  </option>
                  <option value="it" <?php if (get_option('googleone_lang') == 'it') echo 'selected="selected"'; ?>>
                    Italian
                  </option>
                  <option value="ja" <?php if (get_option('googleone_lang') == 'ja') echo 'selected="selected"'; ?>>
                    Japanese
                  </option>
                  <option value="ko" <?php if (get_option('googleone_lang') == 'ko') echo 'selected="selected"'; ?>>
                    Korean
                  </option>
                  <option value="lv" <?php if (get_option('googleone_lang') == 'lv') echo 'selected="selected"'; ?>>
                    Latvian
                  </option>
                  <option value="lt" <?php if (get_option('googleone_lang') == 'lt') echo 'selected="selected"'; ?>>
                    Lithuanian
                  </option>
                  <option value="ms" <?php if (get_option('googleone_lang') == 'ms') echo 'selected="selected"'; ?>>
                    Malay
                  </option>
                  <option value="no" <?php if (get_option('googleone_lang') == 'no') echo 'selected="selected"'; ?>>
                    Norwegian
                  </option>
                  <option value="fa" <?php if (get_option('googleone_lang') == 'fa') echo 'selected="selected"'; ?>>
                    Persian
                  </option>
                  <option value="pl" <?php if (get_option('googleone_lang') == 'pl') echo 'selected="selected"'; ?>>
                    Polish
                  </option>
                  <option value="pt-BR" <?php if (get_option('googleone_lang') == 'pt-BR') echo 'selected="selected"'; ?>>
                    Portuguese (Brazil)
                  </option>
                  <option value="pt-PT" <?php if (get_option('googleone_lang') == 'pt-PT') echo 'selected="selected"'; ?>>
                    Portuguese (Portugal)
                  </option> 
                  <option value="ro" <?php if (get_option('googleone_lang') == 'ro') echo 'selected="selected"'; ?>>
                    Romanian
                  </option>
                  <option value="ru" <?php if (get_option('googleone_lang') == 'ru') echo 'selected="selected"'; ?>>
                    Russian
                  </option>
                  <option value="sr" <?php if (get_option('googleone_lang') == 'sr') echo 'selected="selected"'; ?>>
                    Serbian
                  </option>
                  <option value="sv" <?php if (get_option('googleone_lang') == 'sv') echo 'selected="selected"'; ?>>
                    Swedish
                  </option>
                  <option value="sk" <?php if (get_option('googleone_lang') == 'sk') echo 'selected="selected"'; ?>>
                    Slovak
                  </option>
                  <option value="sl" <?php if (get_option('googleone_lang') == 'sl') echo 'selected="selected"'; ?>>
                    Slovenian
                  </option>
                  <option value="es" <?php if (get_option('googleone_lang') == 'es') echo 'selected="selected"'; ?>>
                    Spanish
                  </option>
                  <option value="es-419" <?php if (get_option('googleone_lang') == 'es-419') echo 'selected="selected"'; ?>>
                    Spanish (Latin America)
                  </option>
                  <option value="th" <?php if (get_option('googleone_lang') == 'th') echo 'selected="selected"'; ?>>
                    Thai
                  </option>
                  <option value="tr" <?php if (get_option('googleone_lang') == 'tr') echo 'selected="selected"'; ?>>
                    Turkish
                  </option>
                  <option value="uk" <?php if (get_option('googleone_lang') == 'uk') echo 'selected="selected"'; ?>>
                    Ukrainian
                  </option>
                  <option value="vi" <?php if (get_option('googleone_lang') == 'vi') echo 'selected="selected"'; ?>>
                    Vietnamese
                  </option>
                </select></div>
            
            
            <div style="padding:10px 0px;"><strong>Where</strong><br />
              <input type="checkbox" value="1" <?php if (get_option('googleone_display_page') == '1') echo 'checked="checked"'; ?> name="googleone_display_page" id="googleone_display_page" group="googleone_display"/>
              <label for="googleone_display_page">Display the button on pages</label>
              <br />
              <input type="checkbox" value="1" <?php if (get_option('googleone_display_front') == '1') echo 'checked="checked"'; ?> name="googleone_display_front" id="googleone_display_front" group="googleone_display"/>
              <label for="googleone_display_front">Display the button on the front page (home)</label>
              
            </div>
            
            <div style="padding:10px 0px;">
              <strong>Display</strong>
<select name="googleone_where" id="googleone_where">
          <option <?php if (get_option('googleone_where') == 'before') echo 'selected="selected"'; ?> value="before">Before</option>
          <option <?php if (get_option('googleone_where') == 'after') echo 'selected="selected"'; ?> value="after">After</option>
          <option <?php if (get_option('googleone_where') == 'beforeandafter') echo 'selected="selected"'; ?> value="beforeandafter">Before and After</option>
          </select>
            </div>
            
            <div style="padding:10px 0px;">
              <strong>Styling</strong>
              <input name="googleone_style" type="text" id="googleone_style" value="<?php echo htmlspecialchars(get_option('googleone_style')); ?>" size="50" />
            <span class="setting-description"><br />

                  Add style to the div that surrounds the button E.g. <code>float: left; margin-right: 10px;</code></span>

                    <br /><br />

              <span class="setting-description"> If you use tweetmeme button and wants to show facebook button below it like on mashable.com then use <code>clear:left; float: left; margin-right: 10px; margin-top:10px; </code>(Note: Your tweetmeme  button should also be left aligned)</span></div>
            
            
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>

		</div> <!--End of googleone_canvas-->

		<div id="google_sidebar" style="width:200px;float:right;width:256px;margin-right:20px;">

		<div style="background:transparenturl(../images/flo-head.jpg) repeat-x scroll 0 0;-moz-background-clip:border;-moz-background-inline-policy:continuous;-moz-background-origin:padding;border:1px solid #DBDBDB;float:left;height:27px;padding:10px 10px 0px 10px;width:256px;font-weight:bold"><img class="box-icons" alt="" src="<?php echo GOOGLEONE_URL;?>images/plug.png" style="padding-right:3px" />OTHER WORDPRESS PLUGINS</div><div style=";-moz-background-clip:border;-moz-background-inline-policy:continuous;-moz-background-origin:padding;border:1px solid #DBDBDB;padding:10px;width:256px">
		  <p><strong>FACEBOOK SHARE BUTTON</strong></p>
		  <p> Just like Google+1 is for increasing your rating on Google Search engine, Facebook Share /Facebook Like is for sharing your content on Facebook. Both Plugin together can increase traffic on your blog significantly.<br /><br />
		  <a href="http://wordpress.org/extend/plugins/facebook-share-new/" target="_blank"> Read more about this Plugin...</a></p>
        </div>
        
        <div style=";-moz-background-clip:border;-moz-background-inline-policy:continuous;-moz-background-origin:padding;border:1px solid #DBDBDB;padding:10px;width:256px">
          <p><strong>ONLINE SCHEDULING PLUGIN</strong></p>
          <p>Convert your visitors into clients. Enable your customers to book appointments in your schedule directly from your wordpress sites. This is a must install plugin for every website.<br /><br />

            <a href="http://wordpress.org/extend/plugins/appointy-appointment-scheduler" target="_blank"><img src="http://www.appointy.com/Affiliate/AffiliateImages/GraphicLogo5.jpg" border="0" /></a><br /><br />
          <a href="http://wordpress.org/extend/plugins/appointy-appointment-scheduler" target="_blank"> Read more about this Plugin...</a><br />
          <br />
          WEBMASTERS:  Appointy has one of the best affiliate program. With 150 referrals, you can earn up to $3000 a month.</p>
          <p>Email us at contact@appointy.com for more information.</p>
        </div>

		</div>

</div>
<?php

}



function googleone_generate_button()

{

	global $post;

    $url = '';

	$buttontype = get_option('googleone_button_type');
	$buttonlang = get_option('googleone_lang');
	$count = true;
	
	$buttonsize = 'tall';
	
	if ($buttontype <= 2) {
		$buttonsize = 'small';}
	else {
		if ($buttontype <= 4) {
			$buttonsize = 'medium'; }
		else {
				if ($buttontype <= 6) {
					$buttonsize = 'standard'; }
			}
	}
		
	
		
	if ($buttontype % 2){
			$count = false;
	} 
	
	if ($buttonsize == 'tall') {
			$count = true;	
	}
	
    if (get_post_status($post->ID) == 'publish') {
        $url = get_permalink();
    }
	
		$button = '<div name="googleone_share_1" style="position:relative;z-index:5;'.get_option('googleone_style').'"><g:plusone size="'.$buttonsize.'" count="'.$count.'" href="'.$url.'">';
		
		if ($buttonlang != 'en-US' && $buttonlang != '' )
		{
			$button = $button . '{lang: \'' .$buttonlang. '\'}';
		}
		
		$button = $button . '</g:plusone></div>';

	return $button . $content; 

}



function googleone_generate_static_button()

{
	if (get_post_status($post->ID) == 'publish') {
        $url = get_permalink();
    }
	
	$buttontype = get_option('googleone_button_type');
	$buttonlang = get_option('googleone_lang');
	$count = true;
	
	$buttonsize = 'tall';
	
	if ($buttontype <= 2) {
		$buttonsize = 'small';}
	else {
		if ($buttontype <= 4) {
			$buttonsize = 'medium'; }
		else {
				if ($buttontype <= 6) {
					$buttonsize = 'standard'; }
			}
	}
		
	
	if ($buttontype % 2 || ($buttonsize == 'tall')){
			$count = false;
	} 
	
	
	$button = '<div name="googleone_share_1" style="position:relative;z-index:5;'.get_option('googleone_style').'"><g:plusone size="'.$buttonsize.'" count="'.$count.'" href="'.$url.'">';
	
	if ($buttonlang != 'en-US' && $buttonlang != '' )
		{
			$button = $button . '{lang: \'' .$buttonlang. '\'}';
		}
		
		$button = $button . '</g:plusone></div>';
	
}

function googleone_share($content)

{

	global $post;


    if (get_option('googleone_where') == 'manual') {
        return $content;
    }

    if (get_option('googleone_display_page') == null && is_page()) {
        return $content;
    }

    if (get_option('googleone_display_front') == null && is_home()) {
        return $content;
    }

		
   	else {
		$button = googleone_generate_button();
		$where = get_option('googleone_where');

		// if we have switched the button off
		if (get_post_meta($post->ID, 'googleonesharenew') == null) {
			if ($where == 'beforeandafter') {
				return $button . $content . $button;

			} else if ($where == 'before') {
				return $button . $content;

			} else {
				return $content . $button;

			}

		} else {

			return $content;

		}
	}
}

function googleone_add_js()
{
	
		echo '<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>';
}


function googleone_init(){

    if(function_exists('register_setting')){
		
		register_setting('googleone-options', 'googleone_button_type');
		register_setting('googleone-options', 'googleone_lang');
        register_setting('googleone-options', 'googleone_display_page');
        register_setting('googleone-options', 'googleone_display_front');   
		register_setting('googleone-options', 'googleone_style');   
		register_setting('googleone-options', 'googleone_where');   
		
		 }

}

function googleone_activate(){
	add_option('googleone_button_type', '7');
	add_option('googleone_lang', 'en-US');
	add_option('googleone_display_page', '1');
    add_option('googleone_display_front', '1');

    add_option('googleone_style', 'float: right; margin-left: 10px;');
    add_option('googleone_where', 'after');
}

add_filter('the_content', 'googleone_share');

if(is_admin()){
    add_action('admin_menu', 'googleone_options');
    add_action('admin_init', 'googleone_init');
}

add_action('wp_head', 'googleone_add_js');

register_activation_hook( __FILE__, 'googleone_activate');



?>