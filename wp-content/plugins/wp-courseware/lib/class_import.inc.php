<?php

/**
 * A class for importing an XML file and creating a training course from it.
 */
class WPCW_Import 
{
	/**
	 * Import a course using the specified XML filename. Returns no errors if it worked correctly.
	 * 
	 * @param String $xmlFileName The name of the file to import.
	 * @return Array An array containing 'errors' with a list of errors, and 'course_id' of the newly created course ID.
	 */
	public static function importTrainingCourseFromXML($xmlFileName)
	{
		$errorList = array();
		
		libxml_use_internal_errors(true);
		$xml = simplexml_load_file($xmlFileName);
		
		// Replaced with actual ID of newly created course.
		$newCourseID = false;		
		
		
		// Error loading XML file, store errors and return them.
		if (!$xml)
		{	    	
		    foreach(libxml_get_errors() as $error) {
		        $errorList[] = sprintf(__('Line %d, Column %d, Error: %s', 'wp_courseware'), $error->line, $error->column, $error->message);
		    }
		}
		
		// No problems loading XML
		else {  
			$import = new WPCW_Import($xml);
			
			// At some point, might pass back errors from here.
			$errorList = $import->importCourseIntoDatabase();
			if (!$errorList) {			
				$newCourseID = $import->getNewCourseID();
			}
		}
        
		
        // Return false if no errors, for easier error checking
		if (count($errorList) == 0) {
			$errorList = false;
		}

		// Return detials back to code for processing
        return array(
        	'errors' 	=> $errorList, 
        	'course_id' => $newCourseID
       	);
	}
	
	
	/**
	 * Reference to XML object used to import the course data.
	 */
	private $xml;
	
	/**
	 * A list of any errors encountered.
	 * @var Array
	 */
	private $errorList;
	
	/**
	 * The ID of the newly created course, or 0 if the course has not been created yet.
	 * @var Integer
	 */
	private $course_id;
	
	/**
	 * Stores the current ordering of the unit for the course. This is reset
	 * each time a course is imported.
	 * @var Integer
	 */
	private $unit_order;
	
	/**
	 * Default constructor - takes a valid XML object as a parameter.
	 * 
	 * @param Object $xml The XML object with the training course data.
	 */	
	function __construct($xml)
	{
		$this->errorList = array();
		$this->xml = $xml;
		$this->course_id = false;
	}	
	
	/**
	 * Function that performs the actual course import.
	 * @return Integer The ID of the newly imported course.
	 */
	public function importCourseIntoDatabase()
	{
		if (!current_user_can('manage_options')) {
			return $this->returnErrorList(__('Sorry, you\'re not allowed to import courses into WordPress.', 'wp_courseware'));
		}
		
		// ### 1) Extract XML data into a clean array of information.
		// The functions below may handle detailed verification of data in 
		// future releases.
		$courseData = $this->loadCourseData();
		$moduleData = $this->loadModuleData();

		// ### 2) Turn the course into an actual database entry
		if (count($courseData) == 0) {			
			return $this->returnErrorList(__('There was no course data to import.', 'wp_courseware'));
		}
		
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		$queryResult = $wpdb->query(arrayToSQLInsert($wpcwdb->courses, $courseData));
		
		// Check query succeeded.
		if ($queryResult === FALSE) {
			return $this->returnErrorList(__('Could not create course in database.', 'wp_courseware'));
		}
		$this->course_id = $wpdb->insert_id;
		
		// Track how many units we add
		$unitCount = 0;
		$this->unit_order = 0;
		
		// ### 3) Check for the module data, and then try to add this
		if ($moduleData)
		{
			
			$moduleCount = 0;
			
			foreach ($moduleData as $moduleItem)
			{
				// Extract Unit Data from module info, so it doesn't interfere with database add
				$unitData = $moduleItem['units'];
				unset($moduleItem['units']);
				
				$moduleCount++;
				
				// Add parent course details, plus order details.
				$moduleItem['parent_course_id'] 	= $this->course_id;
				$moduleItem['module_order'] 		= $moduleCount;
				$moduleItem['module_number'] 		= $moduleCount;
										
				$queryResult = $wpdb->query(arrayToSQLInsert($wpcwdb->modules, $moduleItem));
				
				// Check query succeeded.
				if ($queryResult === FALSE) {
					return $this->returnErrorList(__('There was a problem inserting the module into the database.', 'wp_courseware'));
				}
				
				$currentModuleID = $wpdb->insert_id;

				// ### 4) Check for any units
				$unitCount += $this->addUnitsToDatabase($unitData, $currentModuleID);				
			}
		} // end if $moduleData
		

		// Update unit counts
		// 31st May 2013 - V1.26 - Fix - Incorrectly referring to $course_id - which is empty.
		// Changed to $this->course_id to fix issue.
		$courseDetails = WPCW_courses_getCourseDetails($this->course_id);
		do_action('wpcw_course_details_updated', $courseDetails);
	}
	
	
	/**
	 * Try to add the units to the database.
	 * @param Array $unitData The list of units to add
	 * @param Integer $moduleID The ID of the parent module
	 * @return Integer The number of units added.
	 */
	private function addUnitsToDatabase($unitData, $moduleID) 
	{
		if (!$unitData || count($unitData) < 1)
			return 0;
			
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
			
		$unitCount = 0;
		foreach ($unitData as $singleUnit)
		{
			// ### 1 - Create unit as a WP Post
			$unitPost = array(
			     'post_title' 	=> $singleUnit['post_title'],
			     'post_content' => $singleUnit['post_content'],
				 'post_name' 	=> $singleUnit['post_name'],
			     'post_status' 	=> 'publish',
			     'post_type' 	=> 'course_unit'
			  );
			
			// Insert the post into the database
			$unitID = wp_insert_post($unitPost);
			if (!$unitID) {
				$this->errorList[] = sprintf(__('Could not create course unit "%s". So this was skipped.', 'wp_courseware'), $singleUnit['post_title']);
				continue;
			}
			
			// ### 2 - Update the post with the meta of the related module
			update_post_meta($unitID, 'wpcw_associated_module', $moduleID);
			
			// ### 3 - Create the meta data for WPCW for this unit
			$unitCount++;
			$this->unit_order += 10;
			
			$unitmeta = array();
			$unitmeta['unit_id'] 			= $unitID;
			$unitmeta['parent_module_id'] 	= $moduleID;
			$unitmeta['parent_course_id'] 	= $this->course_id;
			$unitmeta['unit_order'] 		= $this->unit_order; // The order overall in whole course
			$unitmeta['unit_number'] 		= $unitCount; 		 // The number of the unit within module			
			
			// This is an update, as wp_insert_post will create meta entry.
			$queryResult = $wpdb->query(arrayToSQLUpdate($wpcwdb->units_meta, $unitmeta, 'unit_id'));
				
			// Check query succeeded.
			if ($queryResult === FALSE) {
				return $this->returnErrorList(__('There was a problem adding unit meta data into the database.', 'wp_courseware'));
			}
		}

		return $unitCount;
	}
	
	
	/**
	 * Function that only returns a list of errors if there were any errors, or false
	 * if there are none.
	 * 
	 * @param String $messageToAdd If specified, add this message first before returning it.
	 */
	private function returnErrorList($messageToAdd = false)
	{
		if ($messageToAdd) {
			$this->errorList[] = $messageToAdd;
		}
		
		if (count($this->errorList) > 0) {
			return $this->errorList;
		}
		return false;
	}
	
	/**
	 * Returns the newly creatd course ID.
	 */
	public function getNewCourseID() {
		return $this->course_id;
	}
	
	/**
	 * Loads the data needed to create the course.
	 */
	private function loadCourseData()
	{
        $fieldsToUse = array(
        	'course_title', 
        	'course_desc', 
        	'course_opt_completion_wall', 
        	'course_opt_user_access',
        	'course_from_name',
        	'course_from_email',
        	'course_to_email',
        	'course_message_unit_complete',
        	'course_message_unit_not_logged_in',
        	'course_message_unit_pending',
        	'course_message_unit_no_access',
        	'course_message_unit_not_yet',
        	'email_complete_module_option_admin',
        	'email_complete_module_option',
        	'email_complete_module_subject',
        	'email_complete_module_body',
        	'email_complete_course_option_admin',
        	'email_complete_course_option',
        	'email_complete_course_subject',
        	'email_complete_course_body',
        
        	// 31st May 2013 - V1.26 - Fix - Added, as missing from V1.25 
        	'course_message_course_complete',
        	'course_opt_use_certificate',
        );
        
        $dbdata = array();
        foreach ($fieldsToUse as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($this->xml->settings->$fieldName) ? (string)$this->xml->settings->$fieldName : '');
        }
        
        // Update course title to use 'imported'
        $dbdata['course_title'] .= __(' (Imported)', 'wp_courseware');
        
        return $dbdata;
	}
	
	
	/**
	 * Loads the data needed to create the modules and units.
	 */
	private function loadModuleData()
	{	
		// Need at least 1 module to continue.
		if (!isset($this->xml->modules) && !isset($this->xml->modules->module[0])) {
			return false;
		}
		
		$moduleData = array();
		
		// Modules will contain unit data if units are being exported too. 
		foreach ($this->xml->modules->module as $singleModule)
		{
			$moduleData[] = $this->loadModuleData_Single($singleModule);
		}
		
		return $moduleData;
	}
	
	
	
	/**
	 * Extract details from the XML for this single module. 
	 */
	private function loadModuleData_Single($singleModule)
	{
        $fieldsToUse = array(
        	'module_title', 
        	'module_desc', 
        	'module_order', 
        	'module_number',
        );
        
        $dbdata = array();
        foreach ($fieldsToUse as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleModule->$fieldName) ? (string)$singleModule->$fieldName : '');
        }
        
        // Check for units in this module
        if (isset($singleModule->units) && isset($singleModule->units->unit[0]))
        {
        	$dbdata['units'] = array();
        	foreach ($singleModule->units->unit as $singleUnit)  	
        	{
        		$dbdata['units'][] = $this->loadUnitData_Single($singleUnit);
        	}
        }
        
        else {
        	$dbdata['units'] = false;	
        }
        
        return $dbdata;
	}
	
	
	/**
	 * Extract details from the XML for this single unit. 
	 */
	private function loadUnitData_Single($singleUnit)
	{
        $fieldsToUse = array(
        	'post_title', 
        	'post_content', 
        	'post_name'
        );
        
        $dbdata = array();
        foreach ($fieldsToUse as $fieldName)
        {
        	// Put data into database, but assume data is blank if not set in XML.
        	$dbdata[$fieldName] = (isset($singleUnit->$fieldName) ? html_entity_decode((string)$singleUnit->$fieldName) : '');
        }
        
        return $dbdata;
	}	
	
}