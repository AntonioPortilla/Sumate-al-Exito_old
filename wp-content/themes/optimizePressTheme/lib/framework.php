<?php

if(!function_exists('op_define_vars')){
    function op_define_vars(){
        //Init constants
        define('OP_VERSION', '2.2.3.1');

        define('OP_TYPE','theme');
        define('OP_SN','optimizepress'); //Short/safe name
        define('OP_DIR',rtrim(get_template_directory(),'/').'/');
        define('OP_URL',rtrim(get_template_directory_uri(),'/').'/');

        //Lib directory constants
        define('OP_LIB',OP_DIR.'lib/');
        define('OP_LIB_URL',OP_URL.'lib/');
        define('OP_ADMIN',OP_LIB.'admin/');
        define('OP_MOD',OP_LIB.'modules/');
        define('OP_MOD_URL',OP_URL.'lib/modules/');
        define('OP_ASSETS',OP_DIR.'lib/assets/');
        define('OP_ASSETS_URL',OP_URL.'lib/assets/');
        define('OP_DEFAULTS',OP_LIB.'defaults/');
        define('OP_FUNC',OP_LIB.'functions/');
        define('OP_TPL',OP_LIB.'tpl/');
        define('OP_THEMES',OP_DIR.'themes/');

        //Pages directory constants
        define('OP_PAGES',OP_DIR.'pages/');
        define('OP_PAGES_URL',OP_URL.'pages/');

        //Script constants
        define('OP_JS',OP_URL.'lib/js/');
        define('OP_JS_PATH',OP_LIB.'js/');
        define('OP_CSS',OP_LIB_URL.'css/');

        //Image constants
        define('OP_IMG',OP_LIB_URL.'images/');
        define('OP_IMG_DIR',OP_LIB.'images/');
        define('OP_THUMB',OP_IMG_DIR.'thumbs/');
        define('OP_THUMB_URL',OP_IMG.'thumbs/');

        //Notification constants
        define('OP_NOTIFY_SUCCESS', 0);
        define('OP_NOTIFY_WARNING', 1);
        define('OP_NOTIFY_ERROR', 2);

        //Date constants
        define('OP_DATE_MYSQL', 'Y-m-d');
        define('OP_DATE_POSTS', 'F j, Y');
        define('OP_DATE_TIME_PICKER_GMT', 'Y/m/d G:i:s O');

        //Font constants
        define('OP_FONT_FAMILY', 'Source Sans Pro, sans-serif');
        define('OP_FONT_SIZE', 15);
        define('OP_FONT_STYLE', 'normal');
        define('OP_FONT_SPACING', '');
        define('OP_FONT_SHADOW', 'none');
        define('OP_FONT_COLOR', '#444');

        //Font strings
        define('OP_STRING_FONT_FAMILY', 'Font');
        define('OP_STRING_FONT_SIZE', 'Size');
        define('OP_STRING_FONT_STYLE', 'Style');
        define('OP_STRING_FONT_SPACING', 'Spacing');
        define('OP_STRING_FONT_SHADOW', 'Shadow');
        define('OP_STRING_FONT_COLOR', 'Color');
        define('OP_STRING_FONT_DECORATION', 'Decoration');
        define('OP_STRING_FONT_THEME_DEFAULT', 'Theme Default');

        //Logo and Image Constants
        define('OP_HEADER_LOGO_WIDTH', 250);
        define('OP_HEADER_LOGO_HEIGHT', 50);

        //AWeber Oauth authorizing URL
        define('OP_AWEBER_AUTH_URL', 'op-aweber-authorize');

        //iContact App ID
        define('OP_ICONTACT_APP_ID', 'bxqNtsRX17VsWHc437VGAjmwS9keS2er');

        //1ShoppingCart
        define('OP_ONESHOPPINGCART_CONNECT_URL', 'op-1-shopping-cart-connect');

        //GoToWebinar
        define('OP_GOTOWEBINAR_AUTH_URL', 'op-gotowebinar-authorize');
        define('OP_GOTOWEBINAR_EXPIRE_NOTICE', 1209600);

        //OP Social Networking Account Names
        define('OP_SOCIAL_ACCT_TWITTER', 'optimizepress');
        define('OP_SOCIAL_ACCT_FACEBOOK', 'optimizepress');
        define('OP_SOCIAL_ACCT_GOOGLEPLUS', '111273444733787349971');

        //SL cache lifetime (in seconds)
        //wp_nonce lasts 24 hours (for optin forms)
        define('OP_SL_ELEMENT_CACHE_LIFETIME', 172800);

        //CSS Classes
        define('OP_CSS_CLASS_CLOSE_MODAL', 'close-optin-modal');

        //Globals
        $GLOBALS['OP_LIVEEDITOR_FONT_STR'] = array();
        $GLOBALS['OP_LIVEEDITOR_DEPTH'] = 0;
        $GLOBALS['OP_PARSED_SHORTCODE'] = '';
        $GLOBALS['OP_LIVEEDITOR_DISABLE_NEW'] = true;

        // link to our support page
        define('OP_SUPPORT_LINK', 'http://help.optimizepress.com');

        // SEO ENABLED
        $seo_enabled = unserialize(get_option(OP_SN . '_seo'));
        if (!empty($seo_enabled) && isset($seo_enabled['enabled'])) {
            define('OP_SEO_ENABLED', $seo_enabled['enabled']);
        } else {
            define('OP_SEO_ENABLED', 'Y');
        }

        //Minified resources (script and style)
        if (!defined('OP_SCRIPT_DEBUG')) {
            define('OP_SCRIPT_DEBUG', '.min');
        }

        // how many revisions to keep, can be overriden in wp-config by defining this constant
        if (!defined('OP_REVISION_NUMBER')) {
            define('OP_REVISION_NUMBER', 10);
        }

        // autosave interval in seconds, can be overriden in wp-config by defining this constant
        if (!defined('OP_AUTOSAVE_INTERVAL')) {
            define('OP_AUTOSAVE_INTERVAL', 300); // 5 minutes
        }
    }

    /*
     * Updates post_layouts table structure if needed
     * 2.1.9 - added status and modified fields needed for revision system
     * IMPORTANT: if you add fields, you have to add it to lib/admin/install.php, too !!!
     */
    function op_update_post_layouts_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . "optimizepress_post_layouts";
        $structure = "CREATE TABLE `" . $table . "` (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                post_id bigint(20) unsigned NOT NULL,
                type varchar(255) NOT NULL,
                layout longtext NOT NULL,
                status varchar(30) DEFAULT 'publish' NOT NULL,
                modified timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($structure);
    }

    add_action('op_init', 'op_update_post_layouts_table');
    add_action('op_init','op_define_vars');

    function admin_bar_links() {
        if (!is_admin() && current_user_can('administrator')) {
            global $wp_admin_bar;
            global $post;

            if ($post->post_type == 'page') {
                $wp_admin_bar->add_menu( array(
                    'parent' => false, // use 'false' for a root menu, or pass the ID of the parent menu
                    'id' => 'optimizepress', // link ID, defaults to a sanitized title value
                    'title' => 'OptimizePress', // link title
                    'href' => '', // name of file
                    'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
                ));
                if (get_post_meta($post->ID,'_'.OP_SN.'_pagebuilder',true) == 'Y') {
                    $wp_admin_bar->add_menu( array(
                        'parent' => 'optimizepress',
                        'id' => 'op_live_editor',
                        'title' => __('Live Editor', OP_SN),
                        'href' => admin_url("admin.php?page=optimizepress-page-builder&page_id=".$post->ID."&step=5"),
                        'meta' => array('class' => 'op-pagebuilder')
                    ));
                }
                /*$wp_admin_bar->add_menu( array(
                        'parent' => 'optimizepress',
                        'id' => 'op_pagebuilder',
                        'title' => __('Page Builder', OP_SN),
                        'href' => admin_url("admin.php?page=optimizepress-page-builder&page_id=".$post->ID),
                        'meta' => array('class' => 'op-pagebuilder')
                    ));*/
            }
        }

    }
    add_action('wp_before_admin_bar_render', 'admin_bar_links');

    function op_include_files(){
        require_once OP_FUNC.'widgets.php';
        require_once OP_FUNC.'options.php';
        require_once OP_FUNC.'page_options.php';
        require_once OP_FUNC.'general.php';
        require_once OP_FUNC.'scripts.php';
        require_once OP_FUNC.'assets.php';
        require_once OP_FUNC.'fonts.php';
        require_once OP_FUNC.'sl_api.php';

        op_textdomain();
        require_once OP_FUNC.'templates.php';

        _op_assets();
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'post-thumbnails' );
        if(is_admin()){
            require_once OP_FUNC.'admin.php';
            require_once OP_ADMIN.'init.php';
        } else {
            do_action('op_pre_template_include');

            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            /*
             * DAP
             */
            if (is_plugin_active('DAP-WP-LiveLinks/DAP-WP-LiveLinks.php')) {
                add_filter('op_check_page_availability', 'dap_allowed_page');
            }

            /*
             * Fast Member
             */
            if (is_plugin_active('fastmember/fastmember.php')) {
                add_filter('op_check_page_availability', 'fast_member_allowed_page');
            }

            /*
             * iMember
             */
            if (class_exists('infusionWP')) {
                add_filter('op_check_page_availability', 'imember_allowed_page');
            }

            add_filter('template_include', 'op_template_include');
            /*
             * External plugins (WP e-commerce) are skiping the 'template_include' hook
             * and are instead loading theme "index.php" template which shows "Finish blog setup" screen
             * Added a checkbox in Dashboard -- Global settings -- external plugin compatibility
             * This works only if that checkbox is on! to deal with some issues
             */
            $val = op_get_option('op_other_plugins');
            if ('on' === $val) {
                add_filter('index_template', 'op_template_include');
            }

            do_action('op_setup');
            // op_localize_script('front');
        }
    }

    add_action('op_init','op_include_files');
    add_action('wp_head','op_localize_script', 10, 'front');

    do_action('op_init');

    function op_template_include($template,$use_template=true){
        /*
         * Assuring that we don't run this method twice (once on the template_include and once on the index_template hook)
         */
        static $passed;
        if (isset($passed) && true === $passed && !empty($template)) {
            return $template;
        }
        $passed = true;

        if(op_get_option('blog_enabled') != 'Y' || op_get_option('installed') != 'Y'){
            global $post;
            if (!empty($post) && 'page' != $post->post_type) {
                return OP_DIR.'index.php';
            }
        }
        if($use_template){
            if($id = get_queried_object_id()){
                $status = get_post_status($id);
                if ( $status == 'publish' || (current_user_can('edit_posts') || current_user_can('edit_pages')) ){
                    if(get_post_meta($id,'_'.OP_SN.'_pagebuilder',true) == 'Y'){
                        op_init_page($id);
                        if(op_page_option('launch_funnel','enabled') == 'Y' && $launch_info = op_page_option('launch_suite_info')){
                            require_once OP_FUNC.'launch.php';
                        }
                        $theme = op_page_option('theme');
                        $file = OP_PAGES.$theme['type'].'/'.$theme['dir'].'/template.php';
                        if(file_exists($file)){
                            return apply_filters('op_check_page_availability', $file);
                        }
                    } else {
                        op_init_theme();
                        if($tpl = get_post_meta($id,'_op_page_template',true)){
                            if(file_exists(OP_THEME_DIR.$tpl.'.php')){
                                return OP_THEME_DIR.$tpl.'.php';
                            }
                        }
                    }
                } else {
                    op_init_theme();
                }
            } else {
                op_init_theme();
            }
        }
        $checks = array(
            'is_404' => '404',
            'is_search' => 'search',
            'is_front_page' => 'front_page',
            'is_home' => 'home',
            'is_single' => 'single',
            'is_page' => 'page',
            'is_category' => 'category',
            'is_tag' => 'tag',
            'is_author' => 'author',
            'is_archive' => 'archive',
            'is_paged' => 'paged'
        );
        $checks = apply_filters('op_template_include_checks',$checks);
        foreach ($checks as $check => $type) {
            if ($check()) {
                $files = apply_filters('op_template_include-' . $type,array($type));
                foreach ($files as $file) {
                    if (file_exists(OP_THEME_DIR . $file . '.php')) {
                        return OP_THEME_DIR . $file . '.php';
                    }
                }
            }
        }

        if (defined('OP_THEME_DIR')) {
            return OP_THEME_DIR . 'index.php';
        } else {
            return OP_DIR . 'index.php';
        }
    }

    /**
     * Multi-byte Unserialize
     *
     * UTF-8 will screw up a serialized string
     *
     * @access private
     * @param string
     * @return string
     */
    function mb_unserialize($string) {
        if (is_array($string)) {
            return $string;
        }
        $string = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $string);
        return unserialize($string);
    }

    /*
     * Attaching on template redirect action for processing of optin form
     */
    add_action('template_redirect', 'processOptinForm', 20);

    /**
     * Processing optin form, subscribing users
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @return void
     */
    function processOptinForm()
    {
        global $wp;
        /*
         * We are checking for our processing URL slug
         */
        if ($wp->request === 'process-optin-form') {

            $type = op_post('provider');
            /*
             * Infusionsoft needs to be redirected with 307 to keep all of its required POST vars
             */
            if ($type === 'infusionsoft') {

                $list = op_post('list');
                $email = op_post('inf_field_Email');

                $webinar = op_post('gotowebinar');
                /*
                 * Triggering GoToWebinar
                 */
                if (false !== $webinar) {
                    do_action('gotowebinar', $webinar, $email);
                }

                /*
                 * Redirecting user with all its POST data (needed for GoToWebinar interception)
                 */
                wp_redirect(op_post('redirect_url'), 307);
            } else if (false !== $type) {

                require_once(OP_MOD . 'email/ProviderFactory.php');

                $list = op_post('list');
                $email = op_post('email');

                $webinar = op_post('gotowebinar');
                /*
                 * Triggering GoToWebinar
                 */
                if (false !== $webinar) {
                    do_action('gotowebinar', $webinar, $email);
                }

                $provider = OptimizePress_Modules_Email_ProviderFactory::getFactory($type);
                $provider->subscribe(array('list' => $list, 'email' => $email));

                header("HTTP/1.1 200 OK");
                header("Location: " . op_post('redirect_url'));
            } else {
                $email = op_post(op_post('email_field'));
                $webinar = op_post('gotowebinar');
                /*
                 * Triggering GoToWebinar
                 */
                if (false !== $webinar) {
                    do_action('gotowebinar', $webinar, $email);
                }

                /*
                 * Redirecting user with all its POST data (needed for GoToWebinar interception)
                 */
                wp_redirect(op_post('redirect_url'), 307);
            }
            exit();
        }
    }

    /*
     * Admin action for Aweber authorization
     */
    add_action('admin_action_' . OP_AWEBER_AUTH_URL, 'aweberAuthorize');

    /**
     * Authorize Aweber using OAuth
     * @author  Luka Peharda <luka.peharda@gmail.com>
     * @return [type] [description]
     */
    function aweberAuthorize()
    {
        require_once(OP_MOD . 'email/ProviderFactory.php');

        $provider = OptimizePress_Modules_Email_ProviderFactory::getFactory('aweber');
        $provider->authorize();
    }

    /*
     * Admin action for GoToWebinar authorization
     */
    add_action('admin_action_' . OP_GOTOWEBINAR_AUTH_URL, 'gotowebinarAuthorize');

    /**
     * Authorize gotowebinar using OAuth
     * @author  Luka Peharda <luka.peharda@gmail.com>
     * @return [type] [description]
     */
    function gotowebinarAuthorize()
    {
        require_once(OP_MOD . 'email/ProviderFactory.php');

        $provider = OptimizePress_Modules_Email_ProviderFactory::getFactory('gotowebinar');
        $provider->authorize();
    }

    /*
     * Admin action for enabling 1 Shopping Cart
     */
    add_action('admin_action_' . OP_ONESHOPPINGCART_CONNECT_URL, 'oneshoppingcartAuthorize');

    /**
     * Enable 1 Shopping cart
     * @author  Luka Peharda <luka.peharda@gmail.com>
     * @return [type] [description]
     */
    function oneshoppingcartAuthorize()
    {
        require_once(OP_MOD . 'email/ProviderFactory.php');

        $provider = OptimizePress_Modules_Email_ProviderFactory::getFactory('oneshoppingcart');
        $provider->authorize();
    }

    /**
     * Checks GET vars for 'op_' prefixed parameters to fill the value or call 'op_optin_field_value' filter
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @param  string $name
     * @return string
     */
    function getOptinUrlValue($name)
    {
        $value = op_get('op_' . strtolower($name));
        if (false !== $value) {
            $value = op_attr($value);
        } else if ('FNAME' == $name) {
            $value = getOptinUrlValue('name');
        } else {
            $value = apply_filters('op_optin_field_value', $value, $name);
        }
        return $value;
    }

    /*
     * Attaching to scheduled delete to clean up expired DB transients
     */
    add_action('wp_scheduled_delete', 'deleteExpiredDbTransients');

    /**
     * Deletes expired DB transients as WP currently doesn't do garbage cleaning
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @return void
     */
    function deleteExpiredDbTransients()
    {
        global $wpdb, $_wp_using_ext_object_cache;

        if ($_wp_using_ext_object_cache) {
            return;
        }

        $time = isset ($_SERVER['REQUEST_TIME']) ? (int)$_SERVER['REQUEST_TIME'] : time();
        $expired = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout%' AND option_value < $time;");

        foreach ($expired as $transient) {
            delete_transient(str_replace('_transient_timeout_', '', $transient));
        }
    }

    /*
     * Adding ping-pong action
     */
    add_action('ping_pong', 'op_sl_ping');

    add_action('gotowebinar', 'processGoToWebinar', 10, 2);

    /*
     * GoToWebinar token is active only for a year. We are showing user notice when the token is about to expire
     */
    add_action('admin_notices', 'goToWebinarTokenExpiry');

    /**
     * Checks that 'optimizepress_gotowebinar_access_token' is defined and if 'optimizepress_gotowebinar_expires_in' is larger smaller than two weeks
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @return void
     */
    function goToWebinarTokenExpiry()
    {
        $accessToken = op_get_option('gotowebinar_access_token');
        $expiresIn = op_get_option('gotowebinar_expires_in');
        $expiryTime = (int) $expiresIn - time();

        if (false !== $accessToken && false !== $expiresIn && $expiryTime < OP_GOTOWEBINAR_EXPIRE_NOTICE) {
            if ($expiryTime > 0) {
                echo '<div class="update-nag">' . sprintf(__('GoToWebinar access token is going to expire in %1$d day(s). Please <a href="%2$s">re-authorize</a>.', OP_SN), intval($expiryTime / 86400), admin_url('admin.php?action=' . OP_GOTOWEBINAR_AUTH_URL) . '&authorize=1') . '</div>';
            } else {
                echo '<div class="update-nag">' . sprintf(__('GoToWebinar access token is expired. Please <a href="%1$s">re-authorize</a>.', OP_SN), admin_url('admin.php?action=' . OP_GOTOWEBINAR_AUTH_URL) . '&authorize=1') . '</div>';
            }
        }
    }

    add_action('admin_notices', 'checkBlogThemeInstalled');

    /**
     * Checks Blog setup finished
     * @return void
     */
    function checkBlogThemeInstalled()
    {
        if (!op_get_option('theme')) {
            /*
             * We are nagging the user with our message
            */
            echo '<div class="update-nag">' . sprintf(__('In order to use all the features of OptimizePress Theme, you have to finish the  <a href="%s">Blog setup</a>.', OP_SN), admin_url('admin.php?page=optimizepress-setup-wizard')) . '</div>';
        }
        return;

    }

    /*
     * Checking if API key is valid and displaying admin notice if not
     */
    add_action('admin_notices', 'checkApiKeyValidity');

    /**
     * Checks API key and URL combo validity
     *
     * Saves intermediate results to transient to avoid constant pinging of OP SL
     * @return void
     */
    function checkApiKeyValidity()
    {
        /*
         * Transient is not found
         */
        if (false === $validity = get_transient(OptimizePress_Sl_Api::OPTION_API_KEY_STATUS)) {
            $validity = array(
                'api_key'           => op_sl_get_key(),
                'installation_url'  => op_sl_get_url(),
                'status'            => op_sl_ping() === true ? 1 : 0,
            );
        }

        /*
         * If API key and URL combo is valid we are done
         */
        if ((int)$validity['status'] === 1
        && $validity['api_key'] === op_sl_get_key()
        && $validity['installation_url'] === op_sl_get_url()) {

            set_transient(OptimizePress_Sl_Api::OPTION_API_KEY_STATUS, $validity, (HOUR_IN_SECONDS * 6));
            return;
        }

        /*
         * We are nagging the user with our message
         */
        echo '<div class="update-nag">' . sprintf(__('There seems to be a problem with your OptimizePress API Key.  Please recheck it is entered correctly and if you still have problems, <a href="https://optimizepress.zendesk.com/hc/en-us/articles/201687173" target="_blank">see this guide for help</a> or <a href="%s" target="_blank">contact support</a>.', OP_SN), OP_SUPPORT_LINK) . '</div>';
    }

    /**
     * Checks if user is eligible for updates and show him nag message
     * @since  2.2.2
     * @return void
     */
    function checkEligibility()
    {
        /*
         * User can disable eligibility notice, in that case nag won't be shown
         */
        if ('disabled' === get_transient(OptimizePress_Sl_Api::OPTION_ELIGIBILITY_STATUS . '_disabled')) {
            return;
        }

        /*
         * We need to skip if user hasn't added API key yet
         */
        $apiKey = op_sl_get_key();
        if (empty($apiKey)) {
            return;
        }

        /*
         * Lets check transients
         */
        if (false === $eligibility = get_transient(OptimizePress_Sl_Api::OPTION_ELIGIBILITY_STATUS)) {
            $eligibility = array(
                // 'api_key'           => op_sl_get_key(),
                'installation_url'  => op_sl_get_url(),
                'status'            => op_sl_eligible() === true ? 1 : 0,
            );
        }

        set_transient(OptimizePress_Sl_Api::OPTION_ELIGIBILITY_STATUS, $eligibility, (MINUTE_IN_SECONDS * 15));

        /*
         * If API key and URL combo is valid we are done
         */
        if ((int)$eligibility['status'] === 1
        // && $eligibility['api_key'] === op_sl_get_key()
        && $eligibility['installation_url'] === op_sl_get_url()) {
            return;
        }

        /*
         * We are nagging the user with our message
         */
        echo '<div class="update-nag">' . sprintf(__('You are not eligible for new updates. You can <a href="%s" target="_blank">prolong your subscription</a> or <a href="%s">disable this notification</a>.', OP_SN), 'http://www.optimizepress.com/updates-renewal/', admin_url('admin.php?action=optimizepress-disable-eligibility')) . '</div>';
    }

    /*
     * Checks if user is eligible for updates and show him nag message
     */
    add_action('admin_notices', 'checkEligibility');

    /**
     * Disables eligibility check (sets WP option to 'disabled')
     * @since  2.2.2
     * @return void
     */
    function disableEligibilityCheck()
    {
        set_transient(OptimizePress_Sl_Api::OPTION_ELIGIBILITY_STATUS . '_disabled', 'disabled', 4 * WEEK_IN_SECONDS);
        wp_redirect(admin_url('index.php'));
    }

    /*
     * Hooking on admin action (for the purpose of disabling eligibility check as well as nagging user with notice)
     */
    add_action('admin_action_optimizepress-disable-eligibility', 'disableEligibilityCheck');

    /*
     * called on admin_init
     */
    function load_themes_screen() {
        add_thickbox();
        add_action( 'admin_notices', 'update_nag_screen');
    }

    /*
    * checking and adding admin notices for plugin update
    * @return void
    */
    function update_nag_screen() {
        //THEME
        if(function_exists('wp_get_theme')){
            $theme_data = wp_get_theme(get_option('template'));
            $theme_version = $theme_data->Version;
        } else {
            $theme_data = get_theme_data( TEMPLATEPATH . '/style.css');
            $theme_version = $theme_data['Version'];
        }
        $theme_base = get_option('template');

        $response = get_transient('op_theme_update');

        if (false === $response)
            return;

        $update_url = wp_nonce_url('update.php?action=upgrade-theme&amp;theme=' . urlencode( $theme_base ), 'upgrade-theme_' . $theme_base);
        $update_onclick = '';

        if (isset($response->new_version) && version_compare( $theme_version, $response->new_version, '<' ) ) {
            echo '<div id="update-nag">';
            printf(__('<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', OP_SN),
            $theme_data->Name . ' Theme',
            $response->new_version,
            '#TB_inline?width=640&amp;inlineId=' . $theme_base . '_changelog',
            $theme_data->Name,
            $update_url,
            $update_onclick
            );
            echo '</div>';
            if (!empty($response->sections['changelog'])) {
                echo '<div id="' . $theme_base . '_' . 'changelog" style="display:none;">';
                echo wpautop($response->sections['changelog']);
                echo '</div>';
            }
        }
    }

    /**
     * Check SL service for new version
     * @param array existing WordPress transient array
     * @return bool|WP_Error
     */
    function checkUpdate($transient)
    {
        if (!defined('OP_FUNC')) {
            return $transient;
        }
        //THEME
        if(function_exists('wp_get_theme')){
            $theme_data = wp_get_theme(get_option('template'));
            $theme_version = $theme_data->Version;
        } else {
            $theme_data = get_theme_data( TEMPLATEPATH . '/style.css');
            $theme_version = $theme_data['Version'];
        }
        $theme_slug = get_option('template');

        if (!function_exists('op_sl_update')) {
            require_once OP_FUNC.'options.php';
            require_once OP_FUNC.'sl_api.php';
        }
        $apiResponse = op_sl_update('theme');

        if (is_wp_error($apiResponse)) {
            return $transient;
        }

        if (version_compare($theme_version, $apiResponse->new_version, '<')) {
            //prepare object for WordPress
            $obj                = new stdClass();
            $obj->slug          = $theme_slug;
            $obj->new_version   = $apiResponse->new_version;
            $obj->url           = $apiResponse->url;
            $obj->package       = $apiResponse->package;
            $obj->sections      = array(
                                    'description' => $apiResponse->section->description,
                                    'changelog' => $apiResponse->section->changelog,
                                );

            $transient->response[$theme_slug] = (array) $obj;

            // set transient for 12 hours
            set_transient('op_theme_update', $obj, strtotime('+12 hours'));
        }

        return $transient;
    }

    //this is for debug only, DON'T USE IN PRODUCTION
    //set_site_transient('update_themes', null); //check version in every request, but also check op_theme_update transient if is set, nothing will happen

    add_filter('pre_set_site_transient_update_themes', 'checkUpdate');
    add_action('admin_init', 'load_themes_screen');

    /*
     * Hooking on admin action (for the purpose of page cloning)
     */
    add_action('admin_action_optimizepress-page-cloning', 'clonePage');

    /**
     * Clones the page
     * @return void
     */
    function clonePage()
    {
        $id = (int) filter_input(INPUT_GET, 'page_id', FILTER_SANITIZE_NUMBER_INT);
        if (empty($id)) {
            wp_die(__('No page ID to duplicate has been provided!', OP_SN));
        }

        require_once OP_ADMIN . 'clone_page.php';

        $newId = OptimizePress_Admin_ClonePage::getInstance()->clonePage($id);

        wp_redirect(admin_url('post.php?action=edit&post=' . $newId));
    }

    /*
     * Registering directory filter
     */
    add_filter('op_dir_filter', 'filterDirectory', 10, 2);

    /**
     * Adding hidden folder/directory on a list based on a parent directory
     *
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @since 2.1.4
     * @param  array $hiddenDirs
     * @param  string $dir
     * @return array
     */
    function filterDirectory($hiddenDirs, $dir)
    {
        /*
         * This is beeing used for filtering out pages/landing/4 for example, we created new theme which is created with normal
         * elements but needs to keep this one (4) for legacy purposes
         */
        if (strpos($dir, 'pages/landing')) {
            $hiddenDirs[] = '4';
        }

        return $hiddenDirs;
    }

    /*
     * Triggering deleting of custom post meta on post delete action
     */
    add_action('delete_post', 'deletePostMetaInOpTables');

    /**
     * Deletes post meta in OptimizePress custom DB tables (optimizepress_post_layouts)
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @since 2.1.4
     * @param int $postId
     * @return bool
     */
    function deletePostMetaInOpTables($postId)
    {
        global $wpdb;

        /*
         * optimizepress_post_layouts
         */
        if ($wpdb->get_var( $wpdb->prepare('SELECT post_id FROM ' . $wpdb->prefix . 'optimizepress_post_layouts WHERE post_id = %d', $postId))) {
            return $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'optimizepress_post_layouts WHERE post_id = %d', $postId));
        }

        /*
         * optimizepress_launchfunnels_pages
         */
        if ($wpdb->get_var( $wpdb->prepare('SELECT page_id FROM ' . $wpdb->prefix . 'optimizepress_launchfunnels_pages WHERE page_id = %d', $postId))) {
            return $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'optimizepress_launchfunnels_pages WHERE page_id = %d', $postId));
        }

        return true;
    }

    /**
     * Adding 'op-live-editor-page' class to <body /> on pages created with LE
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @param  array $classes
     * @return array
     */
    function leBodyClass($classes)
    {
        if (is_page()) {
            global $wp_query;
            $page_id = $wp_query->get_queried_object_id();
            if ('Y' === get_post_meta($page_id, '_optimizepress_pagebuilder', true) && !in_array('op-live-editor-page', $classes)) {
                $classes[] = 'op-live-editor-page';
            }
        }

        $classes[] = 'op-' . OP_TYPE;

        return $classes;
    }

    /*
     * Attaching on body class filter to add custom class for LE created pages
     */
    add_filter('body_class', 'leBodyClass', 10);

    /**
     * Checking if PHP version is greater or equall than recommended version (5.3)
     *
     * Additional checks can be implemented later on
     * @author Luka Peharda <luka.peharda@gmail.com>
     * @since 2.1.6
     * @return void
     */
    function compatibilityCheck()
    {
        if (version_compare(PHP_VERSION, '5.3', '<')) {
            echo '<div class="update-nag">' . sprintf(__('Your PHP version (%s) is lower than recommended (%s) by OptimizePress and therefore some features might not work. <a href="%s">Check compatibility</a>.', OP_SN), PHP_VERSION, '5.3', admin_url('admin.php?page=optimizepress#global_settings--compatibility_check')) . '</div>';
        }
    }

    /*
     * Attaching on admin_notices to check if PHP version is greater or equall than recommended (5.3)
     */
    add_action('admin_notices', 'compatibilityCheck');

    /*
     * Default filter value for addon row options
     */
    add_filter('op_inside_row', '__return_empty_string', 111);

    /**
     * Akismet 3.0 and higher fix, we are deregistering their comment_form script and returning it in /lib/functions/scripts.php
     * in op_register_scripts method
     */
    add_action('wp_print_scripts', 'akismet_fix');
    function akismet_fix()
    {
        if (defined('AKISMET_VERSION') && version_compare(AKISMET_VERSION, '3.0.0') >= 0) {
            wp_deregister_script('akismet-form');
        }
    }
}