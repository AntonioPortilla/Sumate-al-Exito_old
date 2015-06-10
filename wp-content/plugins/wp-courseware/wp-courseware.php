<?php
/*
 * Plugin Name: WP Courseware
 * Version: 2.4
 * Plugin URI: http://flyplugins.com
 * Description: WP Courseware is WordPress's leading Learning Managment System (L.M.S.) plugin and is so simple you can create an online training course in minutes. It's as simple as drag and drop!
 * Author: Fly Plugins
 * Author URI: http://flyplugins.com
 */
/*
 Copyright 2012-2013 Fly Plugins - Lighthouse Media, LLC

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */


/** The current version of the database. */
define('WPCW_DATABASE_VERSION', 		'2.4');

/** The current version of the database. */
define('WPCW_DATABASE_KEY', 			'WPCW_Version');

/** The key used to store settings in the database. */
define('WPCW_DATABASE_SETTINGS_KEY', 	'WPCW_Settings');

/** The ID used for menus */
define('WPCW_PLUGIN_ID', 				'WPCW_wp_courseware');

/** The ID of the plugin for update purposes, must be the file path and file name. */
define('WPCW_PLUGIN_UPDATE_ID', 		'wp-courseware/wp-courseware.php');

/** The ID used for menus */
define('WPCW_MENU_POSITION', 			384289);

// Create instance of updater for Plugin Updater Functionality
if (WPCW_plugin_hasAdminRights())
{
	include_once 'wplib/utils_update.inc.php';
	$updater_wpcw = new PluginUpdater('wp-courseware', WPCW_PLUGIN_UPDATE_ID, WPCW_DATABASE_VERSION, 'http://updateengine.flyplugins.com?update_tool', 'http://flyplugins.com');
}



/**
 * Are we the site admin? This is if we're not a multi-site, or we are a multi-site but in the network admin area.
 */
function WPCW_plugin_hasAdminRights() {
	// return !is_multisite() || is_network_admin();
	return true; // Not currently working - needs more testing 
}


// Admin Only
if (is_admin())
{
	// External Libs
	include_once 'wplib/utils_pagebuilder.inc.php';
	include_once 'wplib/utils_recordsform.inc.php';
	include_once 'wplib/utils_tablebuilder.inc.php';

	// Plugin-specific
	include_once 'lib/admin_only.inc.php';
	include_once 'lib/class_export.inc.php';
	include_once 'lib/class_import.inc.php';
	include_once 'lib/export_data.inc.php';

	// Documentation
	include_once 'lib/documentation.inc.php';
}

// Frontend Only
else {
	include_once 'lib/frontend_only.inc.php';

	// Shortcodes
	include_once 'lib/shortcodes.inc.php';
}

// AJAX
include_once 'lib/ajax_admin.inc.php';
include_once 'lib/ajax_frontend.inc.php';

// Common
include_once 'lib/common.inc.php';
include_once 'lib/constants.inc.php';
include_once 'lib/email_defaults.inc.php';
include_once 'lib/class_user_progress.inc.php';

include_once 'lib/widget_progress.inc.php';

include_once 'wplib/utils_sql.inc.php';
include_once 'wplib/utils_settings.inc.php';

// Quizzes
include_once 'lib/class_quiz_base.inc.php';
include_once 'lib/class_quiz_multi.inc.php';
include_once 'lib/class_quiz_truefalse.inc.php';
include_once 'lib/class_quiz_open_entry.inc.php';
include_once 'lib/class_quiz_upload.inc.php';


/**
 * Initialisation functions for plugin.
 */
function WPCW_plugin_init()
{
	// Load translation support
	$plugin_dir = basename(dirname(__FILE__)) . '/language/';
 	load_plugin_textdomain('wp_courseware', false, $plugin_dir);
	
	// Run setup
	WPCW_plugin_setup(false); 
		
	// Change preferences for updater
	if (WPCW_plugin_hasAdminRights())
	{
		global $updater_wpcw;
		$updater_wpcw->setAccessKey(TidySettings_getSettingSingle(WPCW_DATABASE_SETTINGS_KEY, 'licence_key'));

		//$updater_wpcw->plugin_msg_blocked = false; // No message if blocked licence.
		$updater_wpcw->plugin_msg_expired = __('Your licence key has expired. Please visit <a href="http://www.flyplugins.com" target="_blank">FlyPlugins.com</a> to renew your licence.', 'wp_courseware');
		$updater_wpcw->plugin_msg_invalid = __('Please enter a valid licence key or visit <a href="http://www.flyplugins.com" target="_blank">FlyPlugins.com</a> to purchase a licence.', 'wp_courseware');
		$updater_wpcw->plugin_msg_limit_reached = __('You\'ve reached the maximum number of websites for this licence. Please visit <a href="http://www.flyplugins.com" target="_blank">FlyPlugins.com</a> to upgrade your licence.', 'wp_courseware');
	}



	// ### Admin
	if (is_admin())
	{
		// Menus
		add_action('admin_menu', 								'WPCW_menu_MainMenu');
		add_action('admin_head', 								'WPCW_menu_MainMenu_cleanUnwantedEntries');
		
		// Network Only
		//add_action('network_admin_menu', 						'WPCW_menu_MainMenu_NetworkOnly');

		// Scripts and styles
		add_action('admin_print_scripts', 						'WPCW_addCustomScripts_BackEnd');
		add_action('admin_print_styles',  						'WPCW_addCustomCSS_BackEnd');

		// See if export has been requested
		WPCW_Export::tryExportCourse();

		// Post Related
		add_action( 'save_post', 								'WPCW_units_saveUnitPostMetaData', 10, 2);

		// User Related
		add_action('manage_users_columns',						'WPCW_users_manageColumns');
		add_action('manage_users_custom_column',				'WPCW_users_addCustomColumnContent',10,3);

		// Unit Related
		add_filter('manage_course_unit_posts_columns', 			'WPCW_units_manageColumns', 10);
		add_action('manage_course_unit_posts_custom_column', 	'WPCW_units_addCustomColumnContent', 10, 2);

		// Unit Deletion
		add_filter('delete_post', 								'WPCW_units_deleteUnitHandler');

		// Meta boxes
		add_action('add_meta_boxes', 							'WPCW_units_showConversionMetaBox');

		// AJAX - Admin
		add_action('wp_ajax_wpcw_handle_unit_ordering_saving', 	'WPCW_AJAX_handleUnitOrderingSaving');

		// AJAX - Frontend (yeah, WP requires they go here)
		add_action('wp_ajax_wpcw_handle_unit_track_progress', 	'WPCW_AJAX_units_handleUserProgress');
		add_action('wp_ajax_wpcw_handle_unit_quiz_response', 	'WPCW_AJAX_units_handleQuizResponse');

		// Notices about permalinks
		add_action('admin_notices', 							'WPCW_plugin_permalinkCheck');
		add_action('admin_notices', 							'WPCW_plugin_multisiteCheck');
		
		// CSV Export
		add_action('wp_loaded', 								'WPCW_data_handleDataExport');
	}

	// ### Frontend
	else
	{
		// Scripts and styles
		WPCW_addCustomScripts_FrontEnd();

		// Shortcodes
		add_shortcode('wpcourse', 'WPCW_shortcodes_showTrainingCourse');

		// Post Content
		add_filter('the_content', 'WPCW_units_processUnitContent');

	}

	// Action when admin has updated the course details.
	add_action('wpcw_course_details_updated', 				'WPCW_actions_courses_courseDetailsUpdated');

	// Action when user has completed a unit/module/course
	add_action('wpcw_user_completed_unit',					'WPCW_actions_users_unitCompleted', 10, 3);
	add_action('wpcw_user_completed_module',				'WPCW_actions_users_moduleCompleted', 10, 3);
	add_action('wpcw_user_completed_course',				'WPCW_actions_users_courseCompleted', 10, 3);

	// Modified modules - when a module is created or edited
	add_action('wpcw_modules_modified',						'WPCW_actions_modules_modulesModified');

	// Action called when user has been created, and we check to see if that user should be added to
	// any of the defined courses.
	add_action('user_register', 							'WPCW_actions_users_newUserCreated');
	
	// Action called when user has been deleted
	add_action('delete_user', 								'WPCW_actions_users_userDeleted');
	
	// Action called when quiz has been completed and needs grading.
	add_action('wpcw_quiz_needs_grading',					'WPCW_actions_userQuizNeedsGrading_notifyAdmin', 10, 2);
	
	// Action called when quiz has been graded.
	add_action('wpcw_quiz_graded',							'WPCW_actions_userQuizGraded_notifyUser', 10, 4);


	// Common
	WPCW_plugin_registerCustomPostTypes();


	// Create correct URL for unit
	add_filter('post_type_link', 'WPCW_units_createCorrectUnitURL', 1, 3);


	// Create permalink for course units
	global $wp_rewrite;
	$unit_structure = '/%module_number%/%course_unit%/';

	// Handle module and course unit tags
	$wp_rewrite->add_rewrite_tag("%module_number%", '(module-[^/]+)', "module_number=");
	$wp_rewrite->add_rewrite_tag("%course_unit%", '([^/]+)', "course_unit=");

	// Make it happen to format links automatically for course units.
	$wp_rewrite->add_permastruct('course_unit', $unit_structure, false);

	// Ensure the URLs are flushed for the first time
	$flushRules = get_option('wpcw_flush_rules');
	if (!$flushRules) {
		update_option('wpcw_flush_rules', 'done');
		$wp_rewrite->flush_rules();
	}
		
	// Now load any extensions
	//do_action('wpcw_extensions_load');	
}
add_action('init', 'WPCW_plugin_init'); 

add_action('widgets_init', create_function('', 'register_widget("WPCW_CourseProgress");'));



/**
 * Install the plugin, initialise the default settings, and create the tables for the websites and groups.
 */
function WPCW_plugin_setup($force)
{
	$installed_ver  = get_option(WPCW_DATABASE_KEY) + 0;
	$current_ver    = WPCW_DATABASE_VERSION + 0;

	// Performing an upgrade
	if ($current_ver != $installed_ver || $force)
	{
		global $wpdb, $wpcwdb;
		$wpcwdb = new WPCW_Database();

		// If settings don't already exist, create new settings based on defaults
		// only when plugin activates.
		$existingSettings = TidySettings_getSettings(WPCW_DATABASE_SETTINGS_KEY);
		
		// The default settings that should exist on initialisation.
		$defaultSettings = array(

			// General settings
			'show_powered_by'		=> 'show_link',
			'use_default_css' 		=> 'show_css',
		
			// Certificates
			'cert_background_type'	=> 'use_default',
			'cert_logo_enabled'		=> 'no_cert_logo',
			'cert_logo_enabled'		=> 'no_cert_logo',
			'cert_signature_type'	=> 'text',
			'cert_sig_text'			=> get_bloginfo('name'), // Site name for instructor
		);
		
		// No settings at all, so save all settings direct to the database.
		if (!$existingSettings) {
			TidySettings_saveSettings($defaultSettings, WPCW_DATABASE_SETTINGS_KEY);	
		}
		
		// We have some settings. Ensure we have settings for all of them.
		else 
		{
			// Check all settings
			foreach ($defaultSettings as $key => $value)
			{
				if (!isset($existingSettings[$key])) {
					$existingSettings[$key] = $value;
				}
			}
			
			// Save modified existing settings back to the settings
			TidySettings_saveSettings($existingSettings, WPCW_DATABASE_SETTINGS_KEY);
		}

		// Remove the flag for flushing rules
		delete_option('wpcw_flush_rules');

		// Upgrade database tables if version change.
		WPCW_database_upgradeTables($installed_ver, $force);

		// Update settings once upgrade has happened
		update_option(WPCW_DATABASE_KEY, WPCW_DATABASE_VERSION);
		
		// Create upload directory
		WPCW_files_createFileUploadDirectory_base();
	}
}



/**
 * Creates the correct URL for course units, showing module and course names.
 *
 * @param String $post_link The current permalinkf for the unit (which includes %module_number%).
 * @param Object $post The object of the post for which a URL is requested.
 */
function WPCW_units_createCorrectUnitURL($post_link, $post = 0, $leavename = FALSE)
{
	// Only filter if found module number
	if (strpos('%module_number%', $post_link) === 'FALSE') {
		return $post_link;
	}

	// Ensure we have access to the post object
	if (is_object($post)) {
		$post_id = $post->ID;
	} else {
		$post_id = $post;
		$post = get_post($post_id);
	}

	// Check that we've got access to the right course unit post type
	if (!is_object($post) || $post->post_type != 'course_unit') {
		return $post_link;
	}

	// Work out the module number for the unit.
	$moduleID = get_post_meta($post->ID, 'wpcw_associated_module', true) + 0;
	
	// V1.22 Fix - Using module NUMBER not module ID for the URL.
	$moduleDetails = WPCW_modules_getModuleDetails($moduleID);

	// Not found the right module, so remove the prefix.
	if (!$moduleDetails) {
		return str_replace('%module_number%', 'module-unassigned', $post_link);
	}
	
	// Put new slug in place of %module_number%
	return str_replace('%module_number%', 'module-'.$moduleDetails->module_number, $post_link);
}




/**
 * Create the main menu.
 */
function WPCW_menu_MainMenu()
{
	add_menu_page('WP Courseware',
	__('Training Courses', 'wp_courseware'),
					'manage_options', WPCW_PLUGIN_ID, 'WPCW_showPage_Dashboard',  WPCW_plugin_getPluginPath().'img/icon_training_16.png', WPCW_MENU_POSITION);

	// ### Course Add/Modify
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - New Course', 'wp_courseware'),
	__('Add Course', 'wp_courseware'),
					'manage_options', 'WPCW_showPage_ModifyCourse', 'WPCW_showPage_ModifyCourse');
	
	// ### GradeBook for Course
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Gradebook', 'wp_courseware'),
	__('Gradebook', 'wp_courseware'),
					'manage_options', 'WPCW_showPage_GradeBook', 'WPCW_showPage_GradeBook');

	// ### Module Add/Modify
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Modify Module', 'wp_courseware'),
	__('Add Module', 'wp_courseware'),
					'manage_options', 'WPCW_showPage_ModifyModule', 'WPCW_showPage_ModifyModule');	

	// Spacer
	add_submenu_page(WPCW_PLUGIN_ID, false, '<span class="wpcw_menu_section" style="display: block; margin: 1px 0 1px -5px; padding: 0; height: 1px; line-height: 1px; background: #CCC;"></span>', 'manage_options', '#', false);


	// ### Quiz Add/Modify
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Modify Quiz', 'wp_courseware'),
	__('Add Quiz/Survey', 'wp_courseware'),
						'manage_options', 'WPCW_showPage_ModifyQuiz', 'WPCW_showPage_ModifyQuiz');	

	// ### Quiz Summary
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Quiz Summary', 'wp_courseware'),
	__('Quiz Summary', 'wp_courseware'),
						'manage_options', 'WPCW_showPage_QuizSummary', 'WPCW_showPage_QuizSummary');

	// ### Module and Unit Ordering
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Module &amp; Unit Ordering', 'wp_courseware'),
	__('Module &amp; Unit Ordering', 'wp_courseware'),
						'manage_options', 'WPCW_showPage_CourseOrdering', 'WPCW_showPage_CourseOrdering');					

	// Spacer
	add_submenu_page(WPCW_PLUGIN_ID, false, '<span class="wpcw_menu_section" style="display: block; margin: 1px 0 1px -5px; padding: 0; height: 1px; line-height: 1px; background: #CCC;"></span>', 'manage_options', '#', false);

	
	
	// ### Handle menu items for extensions
	$extensionList = array();
	$extensionList = apply_filters('wpcw_extensions_menu_items', $extensionList);
	if (count($extensionList) > 0)
	{
		foreach ($extensionList as $extensionItem)
		{
			add_submenu_page(WPCW_PLUGIN_ID,
				__('WP Courseware - ', 'wp_courseware') . $extensionItem['page_title'], 
				$extensionItem['menu_label'], 
				'manage_options', 
				$extensionItem['id'], $extensionItem['menu_function']);		
		}		
		
		add_submenu_page(WPCW_PLUGIN_ID, false, '<span class="wpcw_menu_section" style="display: block; margin: 1px 0 1px -5px; padding: 0; height: 1px; line-height: 1px; background: #CCC;"></span>', 'manage_options', '#', false);
	}
		
	
	
	
	
	// #### Import/export course stuff.
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Import/Export Course', 'wp_courseware'),
	__('Import/Export', 'wp_courseware'),
						'manage_options', 'WPCW_showPage_ImportExport', 'WPCW_showPage_ImportExport');	

	// Spacer
	add_submenu_page(WPCW_PLUGIN_ID, false, '<span class="wpcw_menu_section" style="display: block; margin: 1px 0 1px -5px; padding: 0; height: 1px; line-height: 1px; background: #CCC;"></span>', 'manage_options', '#', false);
	

	// #### Convert post/page to a course unit
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Convert Page/Post to Course Unit', 'wp_courseware'),
	__('Convert Page/Post', 'wp_courseware'),
						'manage_options', 'WPCW_showPage_ConvertPage', 'WPCW_showPage_ConvertPage');				

	// ### Settings
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Settings', 'wp_courseware'),
	__('Settings', 'wp_courseware'),
						'manage_options', 'WPCW_showPage_Settings', 'WPCW_showPage_Settings');
	
	
	// ### Certificate Settings
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Certificates', 'wp_courseware'),
	__('Certificates', 'wp_courseware'),
					'manage_options', 'WPCW_showPage_Certificates', 'WPCW_showPage_Certificates');
	

	// #### Documentation Page
	add_submenu_page(WPCW_PLUGIN_ID,
	__('WP Courseware - Documentation', 'wp_courseware'),
	__('Documentation', 'wp_courseware'),
						'manage_options', 'WPCW_showPage_Documentation', 'WPCW_showPage_Documentation');

	// ### Create page to allow admin to change package for users.
	add_users_page( __('WP Courseware - Update User Course Access Permissions', 'wp_courseware'),
	__('Update Course Access', 'wp_courseware'),
					'manage_options', 'WPCW_showPage_UserCourseAccess', 'WPCW_showPage_UserCourseAccess');
		
	// ### Detailed user progress
	add_users_page( __('WP Courseware - View User Progress', 'wp_courseware'),
	__('View User Progress', 'wp_courseware'),
					'manage_options', 'WPCW_showPage_UserProgess', 'WPCW_showPage_UserProgess');
		
	// ### Detailed quiz progress
	add_users_page( __('WP Courseware - View Quiz/Survey Results', 'wp_courseware'),
	__('View Quiz Results', 'wp_courseware'),
					'manage_options', 'WPCW_showPage_UserProgess_quizAnswers', 'WPCW_showPage_UserProgess_quizAnswers');

}

/**
 * Create the main menu.
 */
function WPCW_menu_MainMenu_NetworkOnly()
{
	add_menu_page('WP Courseware', __('WP Courseware', 'wp_courseware'), 'manage_options', WPCW_PLUGIN_ID, 'WPCW_showPage_Settings_Network',  WPCW_plugin_getPluginPath().'img/icon_training_16.png', WPCW_MENU_POSITION);
}


/**
 * Add the styles needed for the page for this plugin.
 */
function WPCW_addCustomCSS_BackEnd()
{
	// Shown on all admin pages
	wp_enqueue_style('wpcw-admin-users', 	WPCW_plugin_getPluginPath() . 'css/wpcw_admin_users.css', false, WPCW_DATABASE_VERSION);
	
	if (!WPCW_areWeOnPluginPage())
	return;

	// Standard styles
	wp_enqueue_style('thickbox');
		
	// Our plugin-specific CSS
	wp_enqueue_style('wpcw-admin', 			WPCW_plugin_getPluginPath() . 'css/wpcw_admin.css', false, WPCW_DATABASE_VERSION);
}


/**
 * Add the scripts needed for the page for this plugin.
 */
function WPCW_addCustomScripts_BackEnd()
{
	if (!WPCW_areWeOnPluginPage())
	return;
	
	wp_enqueue_media();

	// Our plugin-specific JS
	wp_enqueue_script('wpcw-admin', WPCW_plugin_getPluginPath() . 'js/wpcw_admin.js', array('jquery', 'media-upload', 'thickbox', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-sortable'), WPCW_DATABASE_VERSION);

	// Variable declarations
	wp_localize_script(
				'wpcw-admin', 		// What we're attaching too
				'wpcw_be_ajax',		// Handle for this code 
	array(
			'order_nonce' 	=> wp_create_nonce('wpcw-order-nonce') 	// Nonce security token
	));
}


/**
 * Add the scripts we want loaded in the header.
 */
function WPCW_addCustomScripts_FrontEnd()
{
	if (is_admin()) {
		return;
	}

	// Our plugin-specific scripts

	// Don't use CSS for frontend if setting says so.
	if (TidySettings_getSettingSingle(WPCW_DATABASE_SETTINGS_KEY, 'use_default_css') != 'hide_css') {
		wp_enqueue_style('wpcw-frontend', 			WPCW_plugin_getPluginPath() . 'css/wpcw_frontend.css', false, WPCW_DATABASE_VERSION);
	}
	
	// AJAX Form Script for quizzes
	wp_enqueue_script('wpcw-jquery-form', 		WPCW_plugin_getPluginPath() . 'js/jquery.form.js', array('jquery'), WPCW_DATABASE_VERSION);

	// Plugin-specific JS
	wp_enqueue_script('wpcw-frontend', 			WPCW_plugin_getPluginPath() . 'js/wpcw_front.js', array('jquery', 'wpcw-jquery-form'), WPCW_DATABASE_VERSION);


	// Variable declarations
	wp_localize_script(
				'wpcw-frontend', 	// What we're attaching too
				'wpcw_fe_ajax',		// Handle for this code 
	array(
			'ajaxurl' 				=> admin_url('admin-ajax.php'),				// URL for admin AJAX
			'progress_nonce' 		=> wp_create_nonce('wpcw-progress-nonce'), 	// Nonce security token
			'str_uploading'			=> __('Uploading:', 'wp_courseware'),		// Uploading message.
			'str_quiz_all_fields'	=> __('Please provide an answer for all of the questions.', 'wp_courseware') 
	));
}



/**
 * Return the URL for the page Icon.
 * @return String The URL for the page icon.
 */
function WPCW_icon_getPageIconURL() {
	return WPCW_plugin_getPluginPath() . 'img/icon_training_32.png';
}


/**
 * Get the URL for the plugin path including a trailing slash.
 * @return String The URL for the plugin path.
 */
function WPCW_plugin_getPluginPath() {
	$folder = basename(dirname(__FILE__));
	return plugins_url($folder) . '/';
}


/**
 * Get the directory path for the plugin path including a trailing slash.
 * @return String The URL for the plugin path.
 */
function WPCW_plugin_getPluginDirPath() {
	$folder = basename(dirname(__FILE__));
	return WP_PLUGIN_DIR . "/" . trailingslashit($folder);
}


/**
 * Determine if we're on a page just related to this plugin in the admin area.
 * @return Boolean True if we're on an admin page, false otherwise.
 */
function WPCW_areWeOnPluginPage()
{
	// Checks for admin.php?page=
	if ($currentPage = WPCW_arrays_getValue($_GET, 'page'))
	{
		// This handles any admin page for our plugin.
		if (substr($currentPage, 0, 5) == 'WPCW_') {
			return true;
		}
	}

	return false;
}


/**
 * Flushes the rules to add our custom rules.
 */
function WPCW_search_FlushRules()
{
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

// Only flush when activated.
register_activation_hook( __FILE__, 'WPCW_search_FlushRules' );
//add_filter('wp_loaded','WPCW_search_FlushRules');


/**
 * Creates the course unit post type.
 */
function WPCW_plugin_registerCustomPostTypes()
{
	$labels = array(
        'name' 					=> __( 'Course Units', 						'wp_courseware'),
        'singular_name' 		=> __( 'Course Unit', 						'wp_courseware'),
        'add_new' 				=> __( 'Add New', 							'wp_courseware'),
        'add_new_item' 			=> __( 'Add New Course Unit', 				'wp_courseware'),
        'edit_item'	 			=> __( 'Edit Course Unit', 					'wp_courseware'),
        'new_item' 				=> __( 'New Course Unit', 					'wp_courseware'),
        'view_item' 			=> __( 'View Course Unit', 					'wp_courseware'),
        'search_items' 			=> __( 'Search Course Units', 				'wp_courseware'),
        'not_found' 			=> __( 'No course units found', 			'wp_courseware'),
        'not_found_in_trash'	=> __( 'No course units found in Trash', 	'wp_courseware'),
        'parent_item_colon' 	=> __( 'Parent Course Unit:', 				'wp_courseware'),
        'menu_name' 			=> __( 'Course Units', 						'wp_courseware'),
	);

	$args = array(
        'labels' 				=> $labels,
        'hierarchical' 			=> false,

        'supports'				=> array( 'title', 'editor', 'revisions' ),

        'public' 				=> true,
        'show_ui' 				=> true,
        'show_in_menu' 			=> true,
        'menu_position' 		=> 100,
        'menu_icon' 			=> WPCW_plugin_getPluginPath().'img/icon_training_16.png',
        'show_in_nav_menus' 	=> false,
        'publicly_queryable' 	=> true,
        'exclude_from_search' 	=> false,
        'has_archive' 			=> false,
        'query_var' 			=> true,
        'can_export' 			=> true,
        'rewrite' 				=> false,
        'capability_type' 		=> 'post',
    	'menu_position'			=> WPCW_MENU_POSITION+1
	);

	register_post_type( 'course_unit', $args );
}


/**
 * Hide items from the menu we don't want, but still want access to.
 */
function WPCW_menu_MainMenu_cleanUnwantedEntries()
{
	global $submenu;
	
	// Rename the Training Courses page to include a count of quizzes that need marking.
	$quizPendingCount = WPCW_quizzes_getPendingGradingCount_all();
	if ($quizPendingCount > 0)
	{
		if (isset($submenu[WPCW_PLUGIN_ID])) {
			$submenu[WPCW_PLUGIN_ID][0][0] .= sprintf('<span class="update-plugins count-%d"><span class="update-count">%s</span></span>', $quizPendingCount, $quizPendingCount);
		}
	}
	
	// Hide context pages
	WPCW_menu_removeSubmenuItem(WPCW_PLUGIN_ID, 'WPCW_showPage_CourseOrdering');
	WPCW_menu_removeSubmenuItem(WPCW_PLUGIN_ID, 'WPCW_showPage_ConvertPage');
	WPCW_menu_removeSubmenuItem(WPCW_PLUGIN_ID, 'WPCW_showPage_GradeBook');
	

	// Hide User Menus
	WPCW_menu_removeSubmenuItem('users.php', 'WPCW_showPage_UserCourseAccess');
	WPCW_menu_removeSubmenuItem('users.php', 'WPCW_showPage_UserProgess');
	WPCW_menu_removeSubmenuItem('users.php', 'WPCW_showPage_UserProgess_quizAnswers');
}


/**
 * Function to upgrade the database tables.
 * 
 * @param Integer $installedVersion The version that exists prior to the upgrade.
 * @param Boolean $forceUpgrade If true, we force an upgrade.
 * @param Boolean $showErrors If true, show any debug errors.
 */
function WPCW_database_upgradeTables($installedVersion, $forceUpgrade, $showErrors = false)
{
	global $wpdb, $wpcwdb;
		
	if ($showErrors) {
		$wpdb->show_errors();
	}
	
	// Always upgrade tables. Conditionally execute any other table changes.
	$upgradeNow = true;
		
	// Only enable if debugging
	//$wpdb->show_errors();

	// #### Courses Table
	$SQL = "CREATE TABLE $wpcwdb->courses (
			  course_id int(10) unsigned NOT NULL AUTO_INCREMENT,
			  course_title varchar(150) NOT NULL,
			  course_desc text NOT NULL,
			  course_opt_completion_wall varchar(20) NOT NULL,
			  course_opt_use_certificate varchar(20) NOT NULL DEFAULT 'no_certs',
			  course_opt_user_access varchar(20) NOT NULL,
			  course_unit_count int(10) unsigned NULL DEFAULT '0',
			  course_from_name varchar(150) NOT NULL,
			  course_from_email varchar(150) NOT NULL,
			  course_to_email varchar(150) NOT NULL,
			  course_message_unit_complete text NOT NULL,
			  course_message_course_complete text NOT NULL,
			  course_message_unit_not_logged_in text NOT NULL,
			  course_message_unit_pending text NOT NULL,
			  course_message_unit_no_access text NOT NULL,
			  course_message_unit_not_yet text NOT NULL,
			  course_message_quiz_open_grading_blocking text NOT NULL,
			  course_message_quiz_open_grading_non_blocking text NOT NULL,
			  email_complete_module_option_admin varchar(20) NOT NULL,
			  email_complete_module_option varchar(20) NOT NULL,
			  email_complete_module_subject varchar(300) NOT NULL,
			  email_complete_module_body text NOT NULL,
			  email_complete_course_option_admin varchar(20) NOT NULL,
			  email_complete_course_option varchar(20) NOT NULL,
			  email_complete_course_subject varchar(300) NOT NULL,
			  email_complete_course_body text NOT NULL,			  
			  email_quiz_grade_option varchar(20) NOT NULL,
			  email_quiz_grade_subject varchar(300) NOT NULL,
			  email_quiz_grade_body text NOT NULL,
			  email_complete_course_grade_summary_subject varchar(300) NOT NULL,
			  email_complete_course_grade_summary_body text NOT NULL,
			  PRIMARY KEY  (course_id)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

	WPCW_database_installTable($wpcwdb->courses, $SQL, $upgradeNow);
	
	// Added in 1.22 - New course completed message
	if ($forceUpgrade || $installedVersion < 1.22)
	{
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses SET course_message_course_complete = %s 
			WHERE course_message_course_complete IS NULL 
			   OR course_message_course_complete = ''", __("You have now completed the whole course. Congratulations!", 'wp_courseware')));
	}

	// Added in 1.24 - New quiz messages
	if ($forceUpgrade || $installedVersion < 1.24)
	{		
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses 
			  SET course_message_quiz_open_grading_blocking = %s 
			WHERE course_message_quiz_open_grading_blocking IS NULL 
			   OR course_message_quiz_open_grading_blocking = ''", 
				__('Your quiz has been submitted for grading by the course instructor. Once your grade has been entered, you will be able access the next unit.', 'wp_courseware')
		));
		
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses 
			  SET course_message_quiz_open_grading_non_blocking = %s 
			WHERE course_message_quiz_open_grading_non_blocking IS NULL 
			   OR course_message_quiz_open_grading_non_blocking = ''", 
				__('Your quiz has been submitted for grading by the course instructor. You have now completed this unit.', 'wp_courseware')
		));
		
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses 
			  SET email_quiz_grade_option = %s 
			WHERE email_quiz_grade_option IS NULL 
			   OR email_quiz_grade_option = ''", 
				'send_email'
		));
		
		// After grade completion email.
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses 
			  SET email_quiz_grade_subject = %s 
			WHERE email_quiz_grade_subject IS NULL 
			   OR email_quiz_grade_subject = ''", 
				EMAIL_TEMPLATE_QUIZ_GRADE_SUBJECT
		));
		
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses 
			  SET email_quiz_grade_body = %s 
			WHERE email_quiz_grade_body IS NULL 
			   OR email_quiz_grade_body = ''", 
				EMAIL_TEMPLATE_QUIZ_GRADE_BODY
		));
		
		// Grade summary email
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses 
			  SET email_complete_course_grade_summary_subject = %s 
			WHERE email_complete_course_grade_summary_subject IS NULL 
			   OR email_complete_course_grade_summary_subject = ''", 
				EMAIL_TEMPLATE_COURSE_SUMMARY_WITH_GRADE_SUBJECT
		));
		
		$SQL = $wpdb->query($wpdb->prepare("
			UPDATE $wpcwdb->courses 
			  SET email_complete_course_grade_summary_body = %s 
			WHERE email_complete_course_grade_summary_body IS NULL 
			   OR email_complete_course_grade_summary_body = ''", 
				EMAIL_TEMPLATE_COURSE_SUMMARY_WITH_GRADE_BODY
		));
	}
	
	

	// #### Modules Table
	$SQL = "CREATE TABLE $wpcwdb->modules (
			  module_id int(10) unsigned NOT NULL AUTO_INCREMENT,
			  parent_course_id int(10) unsigned NOT NULL DEFAULT '0',
			  module_title varchar(150) NOT NULL,
			  module_desc text NOT NULL,
			  module_order int(10) unsigned NOT NULL DEFAULT '10000',
			  module_number int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY  (module_id)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->modules, $SQL, $upgradeNow);


	// #### Units Meta Table
	$SQL = "CREATE TABLE $wpcwdb->units_meta (
			  unit_id int(10) unsigned NOT NULL,
			  parent_module_id int(10) unsigned NOT NULL DEFAULT '0',
			  parent_course_id int(10) unsigned NOT NULL DEFAULT '0',
			  unit_order int(10) unsigned NOT NULL DEFAULT '0',
			  unit_number int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY  (unit_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->units_meta, $SQL, $upgradeNow);


	// #### User Courses Allocations Table
	$SQL = "CREATE TABLE $wpcwdb->user_courses (
			  user_id int(10) unsigned NOT NULL,
			  course_id int(10) unsigned NOT NULL,
			  course_progress int(11) NOT NULL DEFAULT '0',
			  course_final_grade_sent VARCHAR(30) NOT NULL DEFAULT '',
			  UNIQUE KEY user_id (user_id,course_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->user_courses, $SQL, $upgradeNow);


	// #### User Progress Table
	$SQL = "CREATE TABLE $wpcwdb->user_progress (
			  user_id int(10) unsigned NOT NULL,
			  unit_id int(10) unsigned NOT NULL,
			  unit_completed_date datetime DEFAULT NULL,
			  unit_completed_status varchar(20) NOT NULL,
			  PRIMARY KEY  (user_id,unit_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->user_progress, $SQL, $upgradeNow);


	// #### Quizzes
	$SQL = "CREATE TABLE $wpcwdb->quiz (
			  quiz_id int(10) unsigned NOT NULL AUTO_INCREMENT,
			  quiz_title varchar(150) NOT NULL,
			  quiz_desc text NOT NULL,
			  parent_unit_id int(10) unsigned NOT NULL DEFAULT '0',
			  parent_course_id int(11) NOT NULL DEFAULT '0',			  
			  quiz_type varchar(15) NOT NULL,
			  quiz_pass_mark int(11) NOT NULL DEFAULT '0',
			  quiz_show_answers varchar(15) NOT NULL DEFAULT 'no_answers',
			  PRIMARY KEY  (quiz_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->quiz, $SQL, $upgradeNow);


	// #### Quiz - Questions
	$SQL = "CREATE TABLE $wpcwdb->quiz_qs (
			  question_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  parent_quiz_id bigint(20) unsigned NOT NULL DEFAULT '0',
			  question_type varchar(20) NOT NULL DEFAULT 'multi',
			  question_question text NOT NULL,
			  question_answers text NOT NULL,
			  question_correct_answer varchar(300) NOT NULL,
			  question_answer_type varchar(50) NOT NULL DEFAULT '',
			  question_order int(11) unsigned NOT NULL DEFAULT '0',
			  question_answer_hint text NOT NULL DEFAULT '',
			  question_answer_file_types varchar(300) NOT NULL DEFAULT '',
			  PRIMARY KEY  (question_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->quiz_qs, $SQL, $upgradeNow);

	// #### Added in 1.24 - Quiz - User Progress - remove old unique index by checking for it first.
	if ($forceUpgrade || $installedVersion < 1.24)
	{
		$SQL = "SHOW INDEX FROM $wpcwdb->user_progress_quiz WHERE KEY_NAME = 'user_id'";
		if ($wpdb->get_row($SQL)) 
		{	
			$SQL = "ALTER TABLE $wpcwdb->user_progress_quiz DROP INDEX user_id";
			$wpdb->query($SQL);
		}
	}

	// #### Quiz - User Progress
	$SQL = "CREATE TABLE $wpcwdb->user_progress_quiz (
			  user_id int(11) NOT NULL,
			  unit_id int(11) NOT NULL,
			  quiz_id bigint(20) NOT NULL,
			  quiz_attempt_id int(11) NOT NULL DEFAULT '0',			  
			  quiz_completed_date datetime NOT NULL,
			  quiz_correct_questions int(10) unsigned NOT NULL,
			  quiz_grade FLOAT(8,2) NOT NULL DEFAULT '-1',
			  quiz_question_total int(10) unsigned NOT NULL,
			  quiz_data text NOT NULL,
			  quiz_is_latest VARCHAR(50) DEFAULT 'latest',
			  quiz_needs_marking int(10) unsigned NOT NULL DEFAULT '0',
			  quiz_needs_marking_list TEXT NOT NULL,
			  quiz_next_step_type VARCHAR(50) DEFAULT '',			  
			  quiz_next_step_msg TEXT DEFAULT '',			  
			  UNIQUE KEY unique_progress_item (user_id,unit_id,quiz_id,quiz_attempt_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->user_progress_quiz, $SQL, $upgradeNow);

	// #### Added in 1.24 - Quiz - User Progress - grade data added to the table.
	if ($forceUpgrade || $installedVersion < 1.24)
	{
		set_time_limit(0);
		
		// All quizzes with a grade of -1 and all items are graded.
		$SQL_NEED_UPDATE = "
			SELECT user_id, unit_id, quiz_id, quiz_attempt_id, quiz_data
			FROM $wpcwdb->user_progress_quiz 
			WHERE quiz_grade = -1
			  AND quiz_needs_marking = 0";
		
		$quizProgressToUpdate = $wpdb->get_row($SQL_NEED_UPDATE);
		while ($quizProgressToUpdate)
		{
			$quizData = maybe_unserialize($quizProgressToUpdate->quiz_data);
			$newGrade = WPCW_quizzes_calculateGradeForQuiz($quizData);
			
			if ($newGrade > -1)
			{
				$wpdb->query($wpdb->prepare("
					UPDATE $wpcwdb->user_progress_quiz 
					  SET quiz_grade = %s
					WHERE unit_id = %d
					  AND user_id = %d
					  AND quiz_id = %d
					  AND quiz_attempt_id = %d
				", $newGrade, 
					$quizProgressToUpdate->unit_id, $quizProgressToUpdate->user_id, 
					$quizProgressToUpdate->quiz_id, $quizProgressToUpdate->quiz_attempt_id
				)); 
			}
			
			$quizProgressToUpdate = $wpdb->get_row($SQL_NEED_UPDATE);
			flush();	
		}
	}
	
	
	
	// #### Mapping of membership levels
	$SQL = "CREATE TABLE $wpcwdb->map_member_levels (
			  	course_id int(11) NOT NULL,
  				member_level_id varchar(100) NOT NULL,
  				UNIQUE KEY course_id (course_id,member_level_id)  				
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->map_member_levels, $SQL, $upgradeNow);
	
	
	// #### Mapping of certificates
	$SQL = "CREATE TABLE $wpcwdb->certificates (
			  cert_user_id int(11) NOT NULL,
			  cert_course_id int(11) NOT NULL,
			  cert_access_key varchar(50) NOT NULL,
			  cert_generated datetime NOT NULL,
			  UNIQUE KEY cert_user_id (cert_user_id,cert_course_id)			
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

	WPCW_database_installTable($wpcwdb->certificates, $SQL, $upgradeNow);
}


/**
 * Install or upgrade a table for this plugin.
 * @param String $tableName The name of the table to upgrade/install.
 * @param String $SQL The core SQL to create or upgrade the table
 * @param String $upgradeTables If true, we're upgrading to a new level of database tables.
 */
function WPCW_database_installTable($tableName, $SQL, $upgradeTables)
{
	global $wpdb;

	// Determine if the table exists or not.
	$tableExists = ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);

	// Table doesn't exist or needs upgrading
	if (!$tableExists || $upgradeTables)
	{
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($SQL);
	}
}

/**
 * Checks to see if the permalinks are using '/%postname%/' for WPCW to work correctly.
 */
function WPCW_plugin_permalinkCheck()
{
	// Check permalink structure is correct
	$permalink_structure = get_option('permalink_structure');
	if ('/%postname%/' != $permalink_structure)
	{
		printf('<div class="updated">
					<p>%s</p>
					<p>%s <b><a href="%s">%s</a></b>.</p>
				</div>',
		__("For <b>WP Courseware</b> unit URLs to work correctly, please ensure your <b>permalinks</b> use just <code>/%postname%/</code>.", 'wp_courseware'),
		__("You can update the permalink settings to use just <b>Post Name</b> on the", 'wp_courseware'),
		admin_url('options-permalink.php'),
		__('Permalink Settings page', 'wp_courseware')
		);
	}
}

/**
 * Message shown to say that multi-site is not currently supported.
 */
function WPCW_plugin_multisiteCheck()
{
	if (!is_multisite()) {
		return;
	}
	
	printf('<div class="updated"><p>%s</p></div>', __('<b>WP Courseware</b> is not currently supported on WordPress Multisite. <b>Yet</b>.', 'wp_courseware'));
}



?>