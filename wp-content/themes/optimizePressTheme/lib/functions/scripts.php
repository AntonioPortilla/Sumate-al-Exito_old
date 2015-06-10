<?php

/**
 * Loads validation script
 *
 * This script is not part of any other script due to plugin not loading scripts because it is not using "op_footer"
 * (otherwise I would put it in global.js)
 * @author Luka Peharda <luka.peharda@gmail.com>
 * @return void
 */
function op_validation_script()
{
    if (is_admin() || wp_script_is('op-validation')) {
        return;
    }

    wp_enqueue_script('op-validation', OP_JS . 'validation'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION, true);
    wp_localize_script('op-validation', 'OPValidation', array(
        'labels'    => array(
            'email' => __('Please enter valid email address', OP_SN),
            'text'  => __('Please fill all fields', OP_SN),
        ),
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('op_gtw_nonce')
    ));
}

add_filter('widget_text', 'do_shortcode');

function op_check_exit_redirect(){
    $chk = op_get('op_exit_redirect');
    if($chk && $chk == 'true'){
        $url = op_current_url();
        $url = str_replace('op_exit_redirect=true','',$url);
        $url = rtrim($url,'?');
        echo '
            <script type="text/javascript">
            top.location.href = \''.$url.'\';
            </script>
        ';
    }
}

add_action('wp_head','op_check_exit_redirect');

/*
 * Attaching on 'wp_head' to render Google Analytics tracking code
 */
add_action('wp_head', 'add_ga_tracking_code', 2);

/**
 * Add GA tracking code from Dashboard - Analytics and tracking - Analytics & Tracking - Google Analytics tracking code
 *
 * @author Luka Peharda <luka.peharda@gmail.com>
 * @since 2.1.1
 * @return void
 */
function add_ga_tracking_code()
{
    $tracking = op_default_option('analytics_and_tracking', 'google_analytics_tracking_code');
    if (!empty($tracking)) {
        echo stripslashes($tracking);
    }
}

/*
 * Attaching on 'wp_footer' to render other sitewide tracking code
 */
add_action('wp_footer', 'add_other_tracking_code', 11);

/**
 * Add sitewide JS tracking code from Dashboard - Analytics and tracking - Analytics & Tracking - Sitewide tracking code
 *
 * @author Luka Peharda <luka.peharda@gmail.com>
 * @since 2.1.1
 * @return void
 */
function add_other_tracking_code()
{
    $tracking = op_default_option('analytics_and_tracking', 'sitewide_tracking_code');
    if (!empty($tracking)) {
        echo stripslashes($tracking);
    }
}

function op_footer(){
    do_action('op_footer');


    //Init the frontend array, which will then be converted to JSON
    if (!is_admin()) {
        //op_localize_script('front');
        wp_enqueue_script('op-menus', OP_JS.'menus'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    }

    //echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>';

    //Print out script include
    // foreach($js_scripts as $script){
    //  echo '<script type="text/javascript" src="'.OP_JS.$script.'"></script>';
    // }
    wp_enqueue_script(OP_SN.'-tooltipster', OP_JS.'tooltipster.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-selectnav', OP_JS.'selectnav.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-dropkick', OP_JS.'dropkick'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-jqueryui-1.10.3', OP_JS.'jquery/jquery-ui-1.10.3.custom.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-sharrre', OP_JS.'jquery/jquery.sharrre-1.3.4.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-reveal', OP_JS.'jquery/jquery.reveal.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-countdown', OP_JS.'jquery/countdown'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-global', OP_JS.'global'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);

    //Print out footer scripts
    op_print_footer_scripts('front');
    wp_footer();

    //Return (which will not allow user in), if the user does not have permissions
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;
    if (!get_user_option('rich_editing')) return;

    //If we are previewing, run the following script
    $preview = (!empty($_GET['preview']) ? $_GET['preview'] : false);
    echo ($preview ? '
        <script type="text/javascript">
            (function ($) {
                $(\'#TB_window\', window.parent.document).css({marginLeft: \'-\' + parseInt((1050 / 2),10) + \'px\',width:\'1050px\',height:\'600px\'});
                $(\'#TB_iframeContent\', window.parent.document).css({width:\'1050px\',height:\'600px\'});
            }(opjq));
        </script>
    ' : '');
}
function op_default_localize(){
    //Defaults for localized PHP to JS variables
    return $default = array(
        'flowplayerHTML5' => apply_filters('flowplayer_license', OP_MOD_URL.'blog/video/flowplayerHTML5/flowplayer.swf', 'swf_file'),
        'flowplayerKey' => apply_filters('flowplayer_license', '', 'license_key'),
        'flowplayerLogo' => apply_filters('flowplayer_license', '', 'custom_logo'),
        'flowplayer' => OP_MOD_URL.'blog/video/flowplayer/flowplayer-3.2.7.swf',
        'flowplayer_control' => OP_MOD_URL.'blog/video/flowplayer/flowplayer.controls-3.2.5.swf',
        'mediaelementplayer' => OP_MOD_URL.'blog/video/mediaelement/',
        'SN' => OP_SN,
        'op_autosave_interval' => OP_AUTOSAVE_INTERVAL,
        'paths' => array(
            'url' => OP_URL,
            'img' => OP_IMG,
            'js' => OP_JS,
            'css' => OP_CSS
        ),
        'social' => array(
            'twitter' => OP_SOCIAL_ACCT_TWITTER,
            'facebook' => OP_SOCIAL_ACCT_FACEBOOK,
            'googleplus' => OP_SOCIAL_ACCT_GOOGLEPLUS
        ),
    );
}
function op_localize_script($types=''){
    //Get the localized variables
    $localize = apply_filters(OP_SN.'-script-localize',op_default_localize());

    //Get localized variables if a type is set
    if(!empty($types)){
        //Init types
        $types = (is_array($types) ? $types : $types = array($types));

        //Loop through types and get variables
        foreach($types as $type){
            $localize = apply_filters(OP_SN.'-script-localize-'.$type,$localize);
        }
    }

    //Finally, assuming we have data to localize, we print out the data in a JSON object
    echo ((count($localize) > 0) ? '<script type="text/javascript">OP = (typeof(OP)=="undefined" ? '.json_encode($localize).' : OP);</script>' : '');
}

function op_print_scripts($types=''){
    _op_print_scripts($types,'-print-scripts');
}

function op_print_footer_scripts($types=''){
    _op_print_scripts($types,'-print-footer-scripts');
}

function _op_print_scripts($types='',$action){
    do_action(OP_SN.$action);
    if(!empty($types)){
        if(!is_array($types)){
            $types = array($types);
        }
        if(is_admin()){
            $types[] = 'admin';
        }
        foreach($types as $type){
            do_action(OP_SN.$action.'-'.$type);
        }
    }
}


// function op_modernizr_selectivizr(){
//  echo '
//      <script type="text/javascript" src="'.OP_JS.'modernizr-2.5.3.min.js?2.5.3"></script>
//  ';
// }
// add_action('wp_head', 'op_modernizr_selectivizr',2);
//add_action(OP_SN.'-print-scripts','op_modernizr_selectivizr');

function op_html5shiv(){
    echo '
        <!--[if (gte IE 6)&(lte IE 8)]>
        <script type="text/javascript" src="'.OP_JS.'selectivizr-1.0.2-min.js?ver=1.0.2"></script>
        <![endif]-->
        <!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    ';
}
add_action('wp_head', 'op_html5shiv');
//add_action(OP_SN.'-print-scripts','op_html5shiv');

function op_load_favicon(){
    $url = op_get_option('favicon');
    $favicon_setup = op_default_option('favicon_setup');
    if (!empty($favicon_setup)) $url = $favicon_setup;

    if(!empty($url)){
        echo "\n".'<link rel="shortcut icon" href="'.$url.'" />' . "\n";
    }
    wp_enqueue_style( OP_SN.'-fancybox', OP_JS.'fancybox/jquery.fancybox'.OP_SCRIPT_DEBUG.'.css', false, OP_VERSION);
}
add_action('wp_head', 'op_load_favicon', 1);

function op_css_font_str($field,$val){
    $font = '';
    if(empty($val)){
        return '';
    }
    if($field == 'font'){
        if($font_str = op_font_str($val)){
            $font .= 'font-family:'.$font_str.';';
        }
    } elseif($field == 'style'){
        switch($val){
            case 'bold italic':
                $font .= 'font-style:italic;';
            case 'bold':
                $font .= 'font-weight:bold;';
                break;
            case 'italic':
                $font .= 'font-style:italic;';
                break;
            case 'normal':
                $font .= 'font-style:normal;font-weight:normal;';
                break;
            case '300':
                $font .= 'font-style:normal;font-weight:300;';
                break;
        }
    } elseif($field == 'spacing'){
        $font .= 'letter-spacing:'.(int)$val.'px;';
    } elseif($field == 'color'){
        $font .= 'color:'.$val.';';
    } elseif($field == 'shadow'){
        switch($val){
            case 'light':
                $font .= 'text-shadow:1px 1px 0px #fff;text-shadow:1px 1px 0px rgba(255,255,255,0.5);';
                break;
            case 'dark':
                $font .= 'text-shadow:1px 1px 0px #000;text-shadow:1px 1px 0px rgba(0,0,0,0.5);';
                break;
        }
    } else {
        $font .= 'font-'.$field.':'.$val.($field=='size'?'px':'').';';
    }
    return $font;
}

function op_typography_output($css,$config=array()){
    $func = 'op_default_option';
    if(defined('OP_PAGEBUILDER')){
        $func = 'op_default_page_option';
    }
    if(count($config) > 0){
        $typography = $config;
    } elseif(!$typography = $func('typography')){
        return $css;
    }
    $elements = op_typography_output_elements();
    $first = true;
    if(isset($elements['font_elements']) && isset($typography['font_elements'])){
        foreach($elements['font_elements'] as $selector => $option){
            if(isset($typography['font_elements'][$option])){
                $el = $typography['font_elements'][$option];
                $font = '';
                if ($first === false && 'default' == $option) {
                    $checks = array('font');
                } else {
                    $checks = array('style','size','font','color');
                }
                foreach($checks as $check){
                    if(!empty($el[$check])){
                        $font .= op_css_font_str($check,$el[$check]);
                    }
                }
                if ('default' == $option) {
                    $first = false;
                }
                $font = rtrim($font,';');
                if(!empty($font)){
                    $css .= $selector.'{'.$font.'}';
                }
            }
        }
    }
    if(isset($elements['color_elements']) && isset($typography['color_elements'])){
        foreach($elements['color_elements'] as $selector => $option){
            if(isset($typography['color_elements'][$option])){
                $el = $typography['color_elements'][$option];
                if(is_array($el)){
                    $str = '';
                    foreach($el as $prop => $value){
                        if($prop == 'text_decoration'){
                            $prop = 'text-decoration';
                            if($value == ''){
                                $value = 'none';
                            }
                            $str .= $prop.':'.$value.';';
                        } elseif($value != ''){
                            $str .= $prop.':'.$value.';';
                        }
                    }
                    if($str != ''){
                        $css .= $selector.'{'.$str.'}';
                    }
                } else {
                    if(!empty($el)){
                        $css .= $selector.'{color:'.$el.'}';
                    }
                }
            }
        }
    }
    return $css;
}
add_filter('op_output_css','op_typography_output',10,2);


function op_typography_elements(){
    static $typography_elements;
    if(!isset($typography_elements)){
        $typography_elements = array(
            'font_elements' => array(
                'site_title' => array(
                    'name' => __('Site Title', OP_SN),
                    'help' => __('Set the styling for your site title (if activated in the blog header options)', OP_SN),
                ),
                'tagline' => array(
                    'name' => __('Site Tagline', OP_SN),
                    'help' => __('Set the styling for your site tagline (shows below site title)', OP_SN),
                ),
                'default' => array(
                    'name' => __('Theme Text/Paragraph Styles', OP_SN),
                    'help' => __('Set the styling for the blog paragraph styling and general text styles', OP_SN),
                ),
                'h1' => array(
                    'name' => __('H1 Heading Styles', OP_SN),
                    'help' => __('Set the font, styling, size and colour for the H1 Headings', OP_SN),
                ),
                'h2' => array(
                    'name' => __('H2 Heading Styles', OP_SN),
                    'help' => __('Set the font, styling, size and colour for the H2 Headings', OP_SN),
                ),
                'h3' => array(
                    'name' => __('H3 Heading Styles', OP_SN),
                    'help' => __('Set the font, styling, size and colour for the H3 Headings', OP_SN),
                ),
                'h4' => array(
                    'name' => __('H4 Heading Styles', OP_SN),
                    'help' => __('Set the font, styling, size and colour for the H4 Headings', OP_SN),
                ),
                'h5' => array(
                    'name' => __('H5 Heading Styles', OP_SN),
                    'help' => __('Set the font, styling, size and colour for the H5 Headings', OP_SN),
                ),
                'h6' => array(
                    'name' => __('H6 Heading Styles', OP_SN),
                    'help' => __('Set the font, styling, size and colour for the H6 Headings', OP_SN),
                ),
            ),
            'color_elements' => array(
                /*'link_color' => array(
                    'name' => __('Link Colour', OP_SN),
                    'help' => __('Set the link colour for your blog', OP_SN),
                    'text_decoration' => true,
                ),
                'link_hover_color' => array(
                    'name' => __('Link Hover Colour', OP_SN),
                    'help' => __('Set the link hover colour for your blog', OP_SN),
                    'text_decoration' => true,
                ),*/
                //'header_link_color' => __('Top Header Link Colour', OP_SN),
                'footer_text_color' => array(
                    'name' => __('Footer Text Colour', OP_SN),
                    'help' => __('Set the colour of the text/copyright message in the footer', OP_SN),
                )
            )
        );
        $typography_elements = apply_filters('op_typography_elements',$typography_elements);
    }
    return $typography_elements;
}

function op_typography_output_elements(){
    static $typography_elements;
    if(!isset($typography_elements)){
        $typography_elements = array(
            'font_elements' => array(
                'p, .single-post-content li, #content_area li, .op-popup-button .default-button' => 'default',
                'a, blockquote' => 'default',
                'h1,.main-content h1,.single-post-content h1,.full-width.featured-panel h1,.latest-post h1.the-title' => 'h1',
                'h2,.main-content h2,.single-post-content h2,.op-page-header h2,.featured-panel h2,.featured-posts .post-content h2,.featured-posts .post-content h2 a,.latest-post h2 a' => 'h2',
                'h3,.main-content h3,.single-post-content h3' => 'h3',
                'h4,.main-content h4,.single-post-content h4,.older-post h4 a' => 'h4',
                'h5,.main-content h5,.single-post-content h5' => 'h5',
                'h6,.main-content h6,.single-post-content h6' => 'h6',
                'h1.site-title,h1.site-title a' => 'site_title',
                'h2.site-description' => 'tagline',
                '.banner h2.site-description' => 'tagline',
            ),
            'color_elements' => array(
                '.latest-post .continue-reading a, .post-content .continue-reading a, .older-post .continue-reading a,.main-content-area .single-post-content a,.featured-panel a,.sub-footer a, .main-sidebar a' => 'link_color',
                '.latest-post .continue-reading a:hover, .post-content .continue-reading a:hover, .older-post .continue-reading a:hover,.main-content-area .single-post-content a:hover,.featured-panel a:hover,.sub-footer a:hover, .main-sidebar a:hover' => 'link_hover_color',
                //'.header-nav li a' => 'header_link_color',
                '.footer,.footer p,.footer a' => 'footer_text_color',
            )
        );
        $typography_elements = apply_filters('op_typography_output_elements',$typography_elements);
    }
    return $typography_elements;
}

function op_output_css(){
    /*
     * Don't remove this, otherwise LE colour and typography settings won't work
     */
    $css = apply_filters('op_output_css','');

    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    $css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
    $css = str_replace(array(' {',':',';}'), array('{',':','}'), $css);
    if(!empty($css)){
        echo '
<style type="text/css" id="op_header_css">
'.$css.'
</style>
';
    }

    //Echo out custom CSS if entered
    $custom_css = op_default_option('custom_css');
    if (!empty($custom_css)) echo '
<style id="op_custom_css">
'.stripslashes($custom_css).'
</style>
';
}
add_action('wp_head','op_output_css',10);

/*
 * Don't remove this, otherwise LE colour and typography settings won't work
 */
function op_filter_ajax_css($css){
    if(defined('OP_AJAX')){
        return $css;
    }

    return $css;
}
add_filter('op_output_css','op_filter_ajax_css');

function op_add_theme_css()
{
    $url = '';
    if(defined('OP_PAGE_URL')){
        $url = OP_PAGE_URL.'style'.OP_SCRIPT_DEBUG.'.css';
        $style_name = '-page-style';
    } elseif(defined('OP_THEME_URL')){
        $url = OP_THEME_URL.'style'.OP_SCRIPT_DEBUG.'.css';
        $style_name = '-theme-style';
    }
    wp_enqueue_style(OP_SN.'-wp', OP_CSS.'wp.css', false, OP_VERSION);
    wp_enqueue_style(OP_SN.$style_name, $url, false, OP_VERSION);
}
add_action('wp_head', 'op_add_theme_css', 7);


function op_opengraph_meta(){
    /*$metas = array(
        //'og:url' => get_bloginfo('wpurl'),
        'og:type' => 'article',
    );
    $site_title = '';
    if(!$site_title = op_get_option('seo','title')){
        $site_title = get_bloginfo('name');
        $site_description = get_bloginfo( 'description', 'display' );
        if ( $site_description && ( is_home() || is_front_page() ) )
            $site_title .= ' &mdash; '.$site_description;
    }
    $metas['og:site_name'] = $site_title;
    if(is_single()){
        global $post;
        while(have_posts()){
            the_post();

            $metas['og:url'] = get_permalink($post->ID);

            $seo = get_post_meta($post->ID,'op_seo',true);
            $seo = is_array($seo) ? $seo : array();
            $title = op_get_var($seo,'title');
            $description = op_get_var($seo,'description');


            $metas['og:title'] = empty($title) ? $post->post_title : $title;
            $metas['og:description'] = empty($description) ? wp_trim_excerpt($post->post_excerpt) : $description;
            if(has_post_thumbnail($post->ID)){
                $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'list-image');
                $metas['og:image'] = $thumbnail[0];
            }
        }
        rewind_posts();
    }*/
    $metas = array();
    $appId = op_get_option('comments','facebook','id');
    if(!empty($appId)){
        $metas['fb:app_id'] = op_get_option('comments','facebook','id');
        if($notify = op_get_option('comments','facebook','notify') && !empty($notify)){
            $metas['fb:admins'] = $notify;
        }
    }
    //$metas = apply_filters('op_meta_tags',$metas);
    foreach($metas as $property => $content){
        echo '
            <meta property="'.$property.'" content="'.esc_attr($content).'" />
        ';
    }
}
add_action('wp_head','op_opengraph_meta');

function op_admin_body_class($class=''){
    global $wp_version;
    if(version_compare($wp_version,'3.3','<')){
        $class .= ' op-less-than-3-3';
    }
    return $class;
}
add_filter('admin_body_class','op_admin_body_class');

function op_register_scripts()
{
    // AKISMET 3.0 and higher fix... we are deregistering their comment_form script in framework, and returning it here
    // under a different handle
    if (defined('AKISMET_VERSION') && version_compare(AKISMET_VERSION, '3.0.0') >= 0) {
        wp_register_script('akismet-form-2', AKISMET__PLUGIN_URL . '_inc/form.js', array(OP_SN.'-noconflict-js'), AKISMET_VERSION);
        wp_enqueue_script('akismet-form-2');
    }
    //If jQuery version is higher than 1.9 we require jQuery migrate plugin (which is by default registered in WP versions that come with jQuery 1.9 or higher)
    if (wp_script_is('jquery-migrate', 'registered')) {
        wp_enqueue_script(OP_SN.'-noconflict-js', OP_JS.'jquery/jquery.noconflict'.OP_SCRIPT_DEBUG.'.js', array('jquery', 'jquery-migrate'), OP_VERSION);
    } else {
        wp_enqueue_script(OP_SN.'-noconflict-js', OP_JS.'jquery/jquery.noconflict'.OP_SCRIPT_DEBUG.'.js', array('jquery'), OP_VERSION);
    }
    wp_enqueue_script(OP_SN.'-loadScript', OP_JS.'jquery/jquery.loadScript'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_register_script(OP_SN.'-backstretch', OP_JS.'jquery/jquery.backstretch'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-placeholder', OP_JS.'jquery/jquery.placeholder.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION, true);
    wp_enqueue_script(OP_SN.'-fancybox', OP_JS.'fancybox/jquery.fancybox.pack'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION, true);
    wp_register_script( OP_SN.'-fancybox-op', OP_JS.'fancybox/helpers/jquery.fancybox-op'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js', OP_SN.'-fancybox'), OP_VERSION);

    // Fancybox for images
    $fancybox_images = op_default_option('fancybox_images');
    if (is_array($fancybox_images) && $fancybox_images['enabled'] === 'Y') {
        wp_enqueue_script(OP_SN.'-fancybox-images', OP_JS.'fancybox_images'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js', OP_SN.'-fancybox'), OP_VERSION, true);
    }

}
add_action('init','op_register_scripts');

function op_font_style_str($vars,$prefix='font_'){
    $font = '';
    $font_vars = array('size','font','style','color','spacing','shadow');
    foreach($font_vars as $f){
        $var = op_get_var($vars,$prefix.$f);
        $font .= op_css_font_str($f,$var);
    }
    return $font;
}
function op_fix_embed_url_shortcodes($content){
    return preg_replace(array('|\](https?://[^\s"]+)|im','|(https?://[^\s"]+)\[|im'),array("]\n$1","$1\n["), $content);
}
function op_process_asset_content($content){
    if(isset($GLOBALS['OP_LIVEEDITOR_FONT_STR']) && count($GLOBALS['OP_LIVEEDITOR_FONT_STR']) == 2){
        extract($GLOBALS['OP_LIVEEDITOR_FONT_STR']);
        foreach($elements as $el){
            $content = preg_replace_callback('{<'.$el.'\s(?=[^>]*style=(["\']*)(.*?)\1)[^>]*>}i','op_process_asset_change_quotes',$content);
            $content = preg_replace(array('{<'.$el.'\s(?![^>]*style=)((?!href=\"#add_element\"))[^>]*}i','{<'.$el.'\s*>}i'),array('$0 style=\''.$style_str.'\'','<'.$el.' style=\''.$style_str.'\'>'),$content);
        }
    }
    return $content;
}
function op_process_asset_change_quotes($matches){
    $style = trim($matches[2]);
    if(substr($style,-1) != ';'){
        $style .= ';';
    }
    $style = $GLOBALS['OP_LIVEEDITOR_FONT_STR']['style_str'].$style;
    return str_replace('style='.$matches[1].$matches[2].$matches[1],"style='".str_replace("'",'"',$style)."'",$matches[0]);
}

/**
 * Loads video script
 *
 * This script is not part of any other script due to plugin not loading scripts because it is not using "op_footer"
 * (otherwise I would put it in global.js)
 * @author Zoran Jambor
 * @return void
 */
function op_video_player_script()
{
    //Flowplayer (video)
    wp_enqueue_script(OP_SN.'flowplayerhtml5', apply_filters('flowplayer_license', OP_MOD_URL.'blog/video/flowplayerHTML5/flowplayer.min.js', 'js_file'), array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_style(OP_SN.'flowplayerminimalist', OP_MOD_URL.'blog/video/flowplayerHTML5/skin/minimalist'.OP_SCRIPT_DEBUG.'.css', false, OP_VERSION);

    //MediaPlayer (audio)
    wp_enqueue_script(OP_SN.'mediaelement', OP_MOD_URL.'blog/video/mediaelement/mediaelement-and-player.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_style(OP_SN.'mediaelementplayer', OP_MOD_URL.'blog/video/mediaelement/mediaelementplayer'.OP_SCRIPT_DEBUG.'.css', false, OP_VERSION);

    //Initialize audio and video players
    wp_enqueue_script(OP_SN.'HTML5videoAudioPlayer', OP_MOD_URL.'blog/video/video-audio-player'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js', OP_SN.'flowplayerhtml5', OP_SN.'mediaelement'), OP_VERSION);
}

/**
 * Enqueues countdown element dependencies (necessery for plugin version)
 * @return void
 */
function op_countdown_scripts()
{
    wp_enqueue_script(OP_SN.'-tooltipster', OP_JS.'tooltipster.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-selectnav', OP_JS.'selectnav.min.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-countdown', OP_JS.'jquery/countdown'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
    wp_enqueue_script(OP_SN.'-global', OP_JS.'global'.OP_SCRIPT_DEBUG.'.js', array(OP_SN.'-noconflict-js'), OP_VERSION);
}

function op_flowplayer_license($default, $type = 'js_file')
{
    $flowplayer_license = op_get_option('flowplayer_license');

    if (!empty($flowplayer_license[$type]) && !empty($flowplayer_license['license_key'])) {
        return $flowplayer_license[$type];
    } else {
        return $default;
    }
}

add_filter('flowplayer_license', 'op_flowplayer_license', 10, 2);

function op_allow_swf_upload($mimes)
{
    $mimes['swf'] = 'application/x-shockwave-flash';
    return $mimes;
}

add_filter('upload_mimes','op_allow_swf_upload');