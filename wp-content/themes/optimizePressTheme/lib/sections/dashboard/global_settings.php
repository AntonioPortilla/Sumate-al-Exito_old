<?php
class OptimizePress_Sections_Global_Settings {
    function sections(){
        static $sections;
        if(!isset($sections)){
            $sections = array(
                'header_logo_setup' => array(
                    'title' => __('Header & Logo Setup', OP_SN),
                    'action' => array($this,'header_logo_setup'),
                    'save_action' => array($this,'save_header_logo_setup')
                ),
                'favicon_setup' => array(
                    'title' => __('Favicon Setup', OP_SN),
                    'action' => array($this,'favicon_setup'),
                    'save_action' => array($this,'save_favicon_setup')
                ),
                'site_footer' => array(
                    'title' => __('Site Footer', OP_SN),
                    'action' => array($this,'site_footer'),
                    'save_action' => array($this,'save_site_footer')
                ),
                'seo' => array(
                    'title' => __('SEO Options', OP_SN),
                    'module' => 'seo',
                    //'options' => op_theme_config('mod_options','seo'),
                    'on_off' => true,
                ),
                'promotion' => array(
                    'title' => __('Promotion Settings', OP_SN),
                    'module' => 'promotion',
                    'options' => op_theme_config('mod_options','promotion')
                ),
                'custom_css' => array(
                    'title' => __('Custom CSS (Sitewide)', OP_SN),
                    'action' => array($this,'custom_css'),
                    'save_action' => array($this,'save_custom_css')
                ),
                'typography' => array(
                    'title' => __('Typography', OP_SN),
                    'action' => array($this,'typography'),
                    'save_action' => array($this,'save_typography')
                ),
                'api_key' => array(
                    'title' => __('API Key', OP_SN),
                    'action' => array($this,'api_key'),
                    'save_action' => array($this,'save_api_key')
                ),
                'advanced_filter' => array(
                    'title' => __('Advanced WP Filter Settings', OP_SN),
                    'action' => array($this,'advanced_filter'),
                    'save_action' => array($this,'save_advanced_filter')
                ),
                'external_plugin_compatibility' => array(
                    'title' => __('External Plugin Compatibility', OP_SN),
                    'action' => array($this,'external_plugin_compatibility'),
                    'save_action' => array($this,'save_external_plugin_compatibility')
                ),
                'templates_reset' => array(
                    'title' => __('Content Templates', OP_SN),
                    'action' => array($this,'templates_reset'),
                    'save_action' => array($this,'content_templates_reset')
                ),
                'flowplayer_license' => array(
                    'title' => __('Flowplayer License', OP_SN),
                    'action' => array($this, 'flowplayer_license'),
                    'save_action' => array($this, 'save_flowplayer_license'),
                ),
                'fancybox_images' => array(
                    'title' => __('Fancybox for Images', OP_SN),
                    'module' => 'fancybox_images',
                    'options' => op_theme_config('mod_options','fancybox_images')
                ),
                'compatibility_check' => array(
                    'title' => __('Compatibility Check', OP_SN),
                    'action' => array($this, 'compatibility_check'),
                ),
            );
            $sections = apply_filters('op_edit_sections_global_settings',$sections);
        }
        return $sections;
    }

    /* Content templates reset section*/
    function templates_reset()
    {
        echo op_load_section('templates_reset');
    }

    function content_templates_reset($op)
    {
        $reset = op_get_var($op, 'content_templates_reset');
        if (!empty($reset)) {
            global $wpdb;

            // removing old templates from db
            $sql = "delete from " . $wpdb->prefix . "optimizepress_predefined_layouts";
            $wpdb->query($sql);
            // removing option
            delete_option(OP_SN . '_content_templates_version');
            require_once (OP_ADMIN . 'install.php');
            $install = new OptimizePress_Install();
            $install->install_content_templates();
        }
    }

    /* API key Section */
    function api_key(){
        echo op_load_section('api_key');
    }

    function save_api_key($op){
        $key = op_get_var($op, OptimizePress_Sl_Api::OPTION_API_KEY_PARAM);
        $status = op_sl_register($key);
        if (is_wp_error($status)) {
            op_group_error('global_settings');
            op_section_error('global_settings_api_key');
            op_tpl_error('op_sections_' . OptimizePress_Sl_Api::OPTION_API_KEY_PARAM, __('API key is invalid. Please re-check it.', OP_SN));
        } else {
            op_sl_save_key($key);
        }
    }

    /* Advanced filter settings */
    function advanced_filter()
    {
        echo op_load_section('advanced_filter');
    }

    function save_advanced_filter($op)
    {
        if ($advancedFilter = op_get_var($op, 'advanced_filter')) {
            op_update_option('advanced_filter', $advancedFilter);
        }
    }

    /* External plugin compatibility */
    function external_plugin_compatibility()
    {
        echo op_load_section('external_plugin_compatibility');
    }

    function save_external_plugin_compatibility($op)
    {
        op_update_option('dap_redirect_url', op_get_var($op, 'dap_redirect_url'));
        op_update_option('fast_member_redirect_url', op_get_var($op, 'fast_member_redirect_url'));
        op_update_option('imember_redirect_url', op_get_var($op, 'imember_redirect_url'));
        if ('theme' === OP_TYPE) {
            op_update_option('op_other_plugins', op_get_var($op, 'op_other_plugins'));
        }
    }

    /* Header & Logo Setup Section */
    function header_logo_setup(){
        echo op_load_section('header_logo_setup', array(), 'global_settings');
    }

    function save_header_logo_setup($op){
        if ($header_logo_setup = op_get_var($op, 'header_logo_setup')){
            op_update_option('header_logo_setup', $header_logo_setup);
        }
    }

    /* Favicon Section */
    function favicon_setup(){
        echo op_load_section('favicon_setup', array(), 'global_settings');
    }

    function save_favicon_setup($op){
        op_update_option('favicon_setup', op_get_var($op,'favicon_setup'));
    }

    /* Site Footer Section */
    function site_footer(){
        echo op_load_section('site_footer', array(), 'global_settings');
    }

    function save_site_footer($op){
        if ($site_footer = op_get_var($op, 'site_footer')){
            op_update_option('site_footer', $site_footer);
        }
    }

    /* Custom CSS Section */
    function custom_css(){
        echo op_load_section('custom_css', array(), 'global_settings');
    }

    function save_custom_css($op){
        //if ($custom_css = op_get_var($op, 'custom_css')){
        op_update_option('custom_css', op_get_var($op, 'custom_css'));
        //}
    }

    /* Typography */
    function typography(){
        echo op_load_section('typography', array(), 'global_settings');
    }

    function save_typography($op){
        if(isset($op['default_typography'])){
            $op = $op['default_typography'];
            $typography = op_get_option('default_typography');
            $typography = is_array($typography) ? $typography : array();
            $typography_elements = op_typography_elements();
            $typography_elements['color_elements'] = array(
                //'link_color' => '',
                //'link_hover_color' => '',
                'footer_text_color' => '',
                'footer_link_color' => '',
                'footer_link_hover_color' => '',
                'feature_text_color' => '',
                'feature_link_color' => '',
                'feature_link_hover_color' => ''
            );
            $typography['font_elements'] = op_get_var($typography,'font_elements',array());
            $typography['color_elements'] = op_get_var($typography,'color_elements',array());
            if(isset($typography_elements['font_elements'])){
                foreach($typography_elements['font_elements'] as $name => $options){
                    $tmp = op_get_var($op,$name,op_get_var($typography['font_elements'],$name,array()));
                    $typography['font_elements'][$name] = array(
                        'size' => op_get_var($tmp,'size'),
                        'font' => op_get_var($tmp,'font'),
                        'style' => op_get_var($tmp,'style'),
                        'color' => op_get_var($tmp,'color'),
                    );
                }
            }
            if(isset($typography_elements['color_elements'])){
                foreach($typography_elements['color_elements'] as $name => $options){
                    $typography['color_elements'][$name] = $op[$name];
                }
            }
            op_update_option('default_typography',$typography);

            //Check for blanks so we can set the defaults.
            //Otherwise a refresh would be necessary to see the defaults.
            // op_set_font_defaults();
        }
    }

    function flowplayer_license()
    {
        echo op_load_section('flowplayer_license', array(), 'global_settings');
    }

    function save_flowplayer_license($op)
    {
        if (empty($op['flowplayer_license']['custom_logo']) && empty($op['flowplayer_license']['license_key'])
        && empty($op['flowplayer_license']['js_file']) && empty($op['flowplayer_license']['swf_file'])) {
            /*
             * If every param is empty, we aren't trying to license flowplayer
             */
            op_delete_option('flowplayer_license');
            return;
        } else if (empty($op['flowplayer_license']['license_key']) || empty($op['flowplayer_license']['js_file']) || empty($op['flowplayer_license']['swf_file'])) {
            op_group_error('global_settings');
            op_section_error('global_settings_flowplayer_license');
            op_tpl_error('op_sections_flowplayer_license', __('To remove Flowplayer watermark and/or to use custom logo, license key, HTML5 and Flash commercial version files needs to be present.', OP_SN));
        }

        op_update_option('flowplayer_license', $op['flowplayer_license']);
    }

    function compatibility_check()
    {
        global $wpdb;
        global $wp_version;

        $data = array();

        // PHP
        if (version_compare(PHP_VERSION, '5.3', '<')) {
            $data['php'] = array(
                'status' => 'warning',
                'message' => sprintf(__('Your PHP version (%s) is lower than recommended (%s).', OP_SN), PHP_VERSION, '5.3'),
            );
        } else {
            $data['php'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Your PHP version (%s) meets requirements (%s).', OP_SN), PHP_VERSION, '5.3'),
            );
        }

        // MySQL
        if (version_compare($wpdb->db_version(), '5.0', '<')) {
            $data['mysql'] = array(
                'status' => 'error',
                'message' => sprintf(__('Your MySQL version (%s) is lower than required (%s).', OP_SN), $wpdb->db_version(), '5.0'),
            );
        } else {
            $data['mysql'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Your MySQL version (%s) meets requirements (%s).', OP_SN), $wpdb->db_version(), '5.0'),
            );
        }

        // WP
        if (version_compare($wp_version, '3.5', '<')) {
            $data['wordpress'] = array(
                'status' => 'warning',
                'message' => sprintf(__('Your WordPress version (%s) is lower than recommended (%s).', OP_SN), $wp_version, '3.5'),
            );
        } else {
            $data['wordpress'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Your WordPress version (%s) meets requirements (%s).', OP_SN), $wp_version, '3.5'),
            );
        }

        // Transfer protocols (curl, streams)
        $http = new Wp_Http();
        if (false === $http->_get_first_available_transport(array())) {
            $data['transfer'] = array(
                'status' => 'error',
                'message' => __('There are no transport protocols (curl, streams) that are capable of handling the requests.', OP_SN),
            );
        } else {
            $data['transfer'] = array(
                'status' => 'ok',
                'message' => __('Transfer protocols (curl, streams) are in order.', OP_SN),
            );
        }

        // OP SL
        if (true !== op_sl_ping()) {
            $data['op_sl'] = array(
                'status' => 'error',
                'message' => __('Unable to connect to OptimizePress Security & Licensing service.', OP_SN),
            );
        } else {
            $data['op_sl'] = array(
                'status' => 'ok',
                'message' => __('Connection with OptimizePress Security & Licensing service is in order.', OP_SN),
            );
        }

        // OP Eligibility
        if (true !== op_sl_eligible()) {
            $data['op_sl'] = array(
                'status' => 'warning',
                'message' => sprintf(__('You are not eligible for new updates. You can prolong your subscription <a href="%s" target="_blank">here</a>.', OP_SN), 'http://optimizepress.com'),
            );
        }

        // Permalink structure
        if ('' === $permalink_structure = get_option('permalink_structure', '')) {
            $data['permalink'] = array(
                'status' => 'error',
                'message' => sprintf(__('Permalink structure must not be set to "default" for OptimizePress to work correctly. Please change the <a href="%s">setting</a>.', OP_SN), admin_url('options-permalink.php')),
            );
        } else {
            $data['permalink'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Permalink structure is in order (%s).', OP_SN), trim($permalink_structure, '/')),
            );
        }

        // Memory limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit')) / 1024 / 1024;
        if ($memory_limit < 64) {
            $data['memory'] = array(
                'status' => 'warning',
                'message' => sprintf(__('Your memory limit (%sMB) is lower than recommended (%sMB)', OP_SN), $memory_limit, 64),
            );
        } else {
            $data['memory'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Your memory limit (%sMB) meets recommendation (%sMB)', OP_SN), $memory_limit, 64),
            );
        }

        // Upload limit
        $upload_limit = wp_max_upload_size() / 1024 / 1024;
        if ($upload_limit < 32) {
            $data['upload'] = array(
                'status' => 'warning',
                'message' => sprintf(__('Your upload limit (%sMB) is lower than recommended (%sMB).', OP_SN), $upload_limit, 32),
            );
        } else {
            $data['upload'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Your upload limit (%sMB) meets recommendation (%sMB).', OP_SN), $upload_limit, 32),
            );
        }

        // Max input vars
        $input_vars_limit = ini_get('max_input_vars');
        if ($input_vars_limit < 3000) {
            $data['input_vars'] = array(
                'status' => 'info',
                'message' => sprintf(__('Your "max_input_vars" setting is set to %s. If you plan to have pages with a large number of elements on it, you should raise this setting to at least %s.', OP_SN), $input_vars_limit ? $input_vars_limit : 1000, 3000),
            );
        } else {
            $data['input_vars'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Your "max_input_vars" (%s) meets recommendation (%s).', OP_SN), $input_vars_limit, 3000),
            );
        }

        // Max execution time
        $execution_time = ini_get('max_execution_time');
        if ($execution_time < 60) {
            $data['execution_time'] = array(
                'status' => 'info',
                'message' => sprintf(__('Your "max_execution_time" setting (%s) is lower than recommended (%s).', OP_SN), $execution_time ? $execution_time : 30, 60),
            );
        } else {
            $data['execution_time'] = array(
                'status' => 'ok',
                'message' => sprintf(__('Your "max_execution_time" (%s) meets recommendation (%s).', OP_SN), $execution_time, 60),
            );
        }

        echo op_load_section('compatibility_check', array('compat' => $data), 'global_settings');
    }
}