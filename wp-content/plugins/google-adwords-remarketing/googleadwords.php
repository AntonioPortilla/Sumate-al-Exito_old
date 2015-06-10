<?php
/*
Plugin Name: Google AdWords Remarketing
Plugin URI: http://wordpress.org/extend/plugins/google-adwords-remarketing/
Description: Adds <a href="http://www.google.com/adwords/">Google AdWords Remarketing</a> code on all pages.
Version: 1.0
Author: Audrius Dobilinskas
Author URI: http://onlineads.lt/author/audrius
*/

if (!defined('WP_CONTENT_URL'))
      define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if (!defined('WP_CONTENT_DIR'))
      define('WP_CONTENT_DIR', ABSPATH.'wp-content');
if (!defined('WP_PLUGIN_URL'))
      define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
if (!defined('WP_PLUGIN_DIR'))
      define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

function activate_google_adwords_remarketing() {
  add_option('remarketing_code', 'Paste your AdWords Remarketing code here');
}

function deactive_google_adwords_remarketing() {
  delete_option('remarketing_code');
}

function admin_init_google_adwords_remarketing() {
  register_setting('google_adwords_remarketing', 'remarketing_code');
}

function admin_menu_google_adwords_remarketing() {
  add_options_page('Google AdWords Remarketing', 'Google AdWords Remarketing', 'manage_options', 'google_adwords_remarketing', 'options_page_google_adwords_remarketing');
}

function options_page_google_adwords_remarketing() {
  include(WP_PLUGIN_DIR.'/google-adwords-remarketing/options.php');  
}

function google_adwords_remarketing() {
  $remarketing_code = get_option('remarketing_code');
?>

<?php echo $remarketing_code ?>

<?php
}

register_activation_hook(__FILE__, 'activate_google_adwords_remarketing');
register_deactivation_hook(__FILE__, 'deactive_google_adwords_remarketing');

if (is_admin()) {
  add_action('admin_init', 'admin_init_google_adwords_remarketing');
  add_action('admin_menu', 'admin_menu_google_adwords_remarketing');
}

if (!is_admin()) {
  add_action('wp_footer', 'google_adwords_remarketing');
}

?>