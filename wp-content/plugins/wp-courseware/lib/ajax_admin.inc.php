<?php
/**
 * Admin only AJAX functions.
 */


/**
 * Function called when the unit ordering is being saved via AJAX.
 * This function will save the order of the modules, units and any unassigned units.
 * 
 */
function WPCW_AJAX_handleUnitOrderingSaving() 
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'order_nonce'), 'wpcw-order-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	// Get list of modules to save, check IDs are what we expect, and abort if nothing to do.
	$moduleList = WPCW_arrays_getValue($_POST, 'moduleList');
	if (!$moduleList || count($moduleList) < 1) {
		die();
	}
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	$parentCourseID = 0;
	
	// Save new module ordering to database
	$moduleOrderCount = 0;
	
	// Ordering of units is absolute to the whole course
	$unitOrderCount = 0; 
	
	//error_log(print_r($_POST, true));
	
	// Need a course ID for resetting the ordering.
	foreach ($moduleList as $moduleID) 
	{
		// Validate we have an actual module
		if (preg_match('/^wpcw_mod_(\d+)$/', $moduleID, $matches))
		{
			// Get course ID from module
			$moduleDetails = WPCW_modules_getModuleDetails($matches[1]);
			if ($moduleDetails) {
				$parentCourseID = $moduleDetails->parent_course_id;
				break;
			}
		}
	}	
	
	// If there's no associated parent course, there's an issue.
	if (!$parentCourseID) {
		error_log('WPCW_AJAX_handleUnitOrderingSaving(). No associated parent course ID, so aborting.');
		die();
	}
	
	
	// 2013-05-01 - Bug with orphan modules being left in the units_meta
	// Fix - Clean out existing units in this course, resetting them. 
	// Then update the ordering using the loops below.
	$SQL = $wpdb->prepare("
		UPDATE $wpcwdb->units_meta
		   SET unit_order = 0, parent_module_id = 0, 
		   	   parent_course_id = 0, unit_number = 0
		WHERE parent_course_id = %d
	", $parentCourseID);
	
	$wpdb->query($SQL);
	
	foreach ($moduleList as $moduleID)
	{		
		// ### Check module name matches expected format.
		if (preg_match('/^wpcw_mod_(\d+)$/', $moduleID, $matches))
		{
			$moduleOrderCount++;
			$moduleIDClean = $matches[1];
			
			// Update module list with new ordering
			$SQL = $wpdb->prepare("
				UPDATE $wpcwdb->modules
				   SET module_order = %d, module_number = %d
				WHERE module_id = %d
			", $moduleOrderCount, $moduleOrderCount, $moduleIDClean);
			
			$wpdb->query($SQL);
			
			
			// ### Check units associated with this module			
			$unitList = WPCW_arrays_getValue($_POST, $moduleID);
			if ($unitList && count($unitList) > 0)
			{
				$unitNumber = 0;
				foreach ($unitList as $unitID)
				{
					$unitNumber++;
					
					// Check unit name matches expected format.
					if (preg_match('/^wpcw_unit_(\d+)$/', $unitID, $matches))
					{
						$unitOrderCount += 10;
						$unitIDClean = $matches[1];

						// Update database with new association and ordering.
						$SQL = $wpdb->prepare("
							UPDATE $wpcwdb->units_meta
							   SET unit_order = %d, parent_module_id = %d, 
							   	   parent_course_id = %d, unit_number = %d
							WHERE unit_id = %d
						", $unitOrderCount, $moduleIDClean,  
						$parentCourseID, $unitNumber,
						$unitIDClean);
						
						$wpdb->query($SQL);
						
						// 2013-05-01 - Updated to use the module ID, rather than the module order.
						update_post_meta($unitIDClean, 'wpcw_associated_module', $moduleIDClean);						
					}
				}// end foreach 
			} // end of $unitList check
		}
	}
	
	
	// #### Check for any units that have associated quizzes
	foreach ($_POST as $key => $value)
	{
		// Check any post value that has a unit in it
		if (preg_match('/^wpcw_unit_(\d+)$/', $key, $matches)) 
		{
			$unitIDClean = $matches[1];
			
			// Try to extract the unit ID
			// [wpcw_unit_71] => Array
        	// (
            //	[0] => wpcw_quiz_2
        	//)			
			$quizIDRaw = false;
			if ($value && is_array($value)) {
				$quizIDRaw = $value[0];
			}
				
			// Got a matching quiz ID
			if (preg_match('/^wpcw_quiz_(\d+)$/', $quizIDRaw, $matches)) 
			{
				$quizIDClean = $matches[1];
				
				// Grab parent course ID from unit. Can't assume all units are in same course.
				$parentData = WPCW_units_getAssociatedParentData($unitIDClean);
				$parentCourseID = $parentData->parent_course_id;

				// Update database with new association and ordering.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->quiz
					   SET parent_unit_id = %d, parent_course_id = %d
					WHERE quiz_id = %d
				", $unitIDClean, $parentCourseID, $quizIDClean);
				
				$wpdb->query($SQL);			

				// Add new associated unit information to the user quiz progress,
				// keeping any existing quiz results.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->user_progress_quiz
					   SET unit_id = %d
					WHERE quiz_id = %d
				", $unitIDClean, $quizIDClean);
				
				$wpdb->query($SQL);
			}
		}
	}
	
	
	// #### Check for any unassigned units, and ensure they're de-associated from modules.
	$unitList = WPCW_arrays_getValue($_POST, 'unassunits');
	if ($unitList && count($unitList) > 0)
	{
		foreach ($unitList as $unitID)
		{
			// Check unit name matches expected format.
			if (preg_match('/^wpcw_unit_(\d+)$/', $unitID, $matches))
			{
				$unitIDClean = $matches[1];

				// Update database with new association and ordering.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->units_meta
					   SET unit_order = 0, parent_module_id = 0, parent_course_id = 0, unit_number = 0
					WHERE unit_id = %d
				", $unitIDClean);
				
				$wpdb->query($SQL);
				
				// Update post meta to remove associated module detail
				update_post_meta($unitIDClean, 'wpcw_associated_module', 0);
				
				// Remove progress for this unit, as likely to be associated with something else.
				$SQL = $wpdb->prepare("
					DELETE FROM $wpcwdb->user_progress
					WHERE unit_id = %d
				", $unitIDClean);
				
				$wpdb->query($SQL);
			}
		} // end foreach ($unitList as $unitID)
	}
	
	// #### Check for any unassigned quizzes, and ensure they're de-associated from units.
	$quizList = WPCW_arrays_getValue($_POST, 'unassquizzes');
	if ($quizList && count($quizList) > 0)
	{
		foreach ($quizList as $quizID)
		{
			// Check unit name matches expected format.
			if (preg_match('/^wpcw_quiz_(\d+)$/', $quizID, $matches))
			{
				$quizIDClean = $matches[1];

				// Update database with new association and ordering.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->quiz
					   SET parent_unit_id = 0, parent_course_id = 0
					WHERE quiz_id = %d
				", $quizIDClean);
				
				$wpdb->query($SQL);
				
				
				// Remove the associated unit information from the user quiz progress.
				// But keep the quiz results for now.
				$SQL = $wpdb->prepare("
					UPDATE $wpcwdb->user_progress_quiz
					   SET unit_id = 0
					WHERE quiz_id = %d
				", $quizIDClean);
				
				$wpdb->query($SQL);
			}
		} // end foreach ($quizList as $quizID)
	}
	
	// Update course details
	$courseDetails = WPCW_courses_getCourseDetails($parentCourseID);
	if ($courseDetails) {
		do_action('wpcw_course_details_updated', $courseDetails);
	}
	
	//error_log(print_r($matches, true));
	die(); 	
}


?>