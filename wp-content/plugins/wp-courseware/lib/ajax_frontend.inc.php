<?php
/**
 * Frontend only AJAX functions.
 */
include_once 'frontend_only.inc.php'; // Ensure we have frontend functions


/**
 * Function called when the user is marking a unit as complete. 
 */
function WPCW_AJAX_units_handleUserProgress() 
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'progress_nonce'), 'wpcw-progress-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	$unitID = WPCW_arrays_getValue($_POST, 'id');
	
	// Validate the course ID
	if (!preg_match('/unit_complete_(\d+)/', $unitID, $matches)) {
		echo WPCW_units_getCompletionBox_error();
		die();
	}
	$unitID = $matches[1];
	$user_id = get_current_user_id();
	
	// #### Get associated data for this unit. No course/module data, not a unit
	$parentData = WPCW_units_getAssociatedParentData($unitID);
	if (!$parentData) {		
		// No error, as not a valid unit.
		die();
	}
	
	// #### User not allowed access to content, so certainly can't say they've done this unit.
	if (!WPCW_courses_canUserAccessCourse($parentData->course_id, $user_id)) {
		// No error, as not a valid unit.
		die();		
	}
	
	WPCW_units_saveUserProgress_Complete($user_id, $unitID, 'complete');
	
	// Unit complete, check if course/module is complete too.
	do_action('wpcw_user_completed_unit', $user_id, $unitID, $parentData);

	
	echo WPCW_units_getCompletionBox_complete($parentData, $unitID, $user_id);
	die();
}


/**
 * Marks a unit as complete for the specified user. No error checking is made to check that the user
 * is allowed to update the record, it's assumed that the permission checking has been done before this step.
 * 
 * @param Integer $userID The ID of the user that's completed the unit.
 * @param Integer $unitID The ID of the unit that's been completed.
 */
function WPCW_units_saveUserProgress_Complete($userID, $unitID)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	$keyColumns = array('user_id', 'unit_id');
		
	$data = array();
	$data['unit_completed_status'] 	= 'complete';	
	$data['unit_completed_date']  	= current_time('mysql');
	$data['user_id'] 				= $userID;
	$data['unit_id'] 				= $unitID;
	
	$progress = doesRecordExistAlready($wpcwdb->user_progress, $keyColumns, array($userID, $unitID));
	if ($progress)  
	{
		// Has it been marked as complete? If so, we don't want to do that again to preserve the date.
		// We generally shouldn't get here, but protect anyway.
		if ($progress->unit_completed_status == 'complete') {
			return false;
		}
	
		$SQL = arrayToSQLUpdate($wpcwdb->user_progress, $data, $keyColumns);	
	} 
	
	// Insert
	else {
		$SQL = arrayToSQLInsert($wpcwdb->user_progress, $data);
	}
	
	$wpdb->query($SQL);
}



/**
 * Function called when a user is submitting quiz answers via
 * the frontend form. 
 */
function WPCW_AJAX_units_handleQuizResponse() 
{
	// Security check
	if (!wp_verify_nonce(WPCW_arrays_getValue($_POST, 'progress_nonce'), 'wpcw-progress-nonce')) {
        die (__('Security check failed!', 'wp_courseware'));
	}
	
	// Quiz ID and Unit ID are combined in the single CSS ID for validation.
	// So validate both are correct and that user is allowed to access quiz.
	$quizAndUnitID = WPCW_arrays_getValue($_POST, 'id');
	
	// e.g. quiz_complete_69_1 or quiz_complete_17_2 (first ID is unit, 2nd ID is quiz)
	if (!preg_match('/quiz_complete_(\d+)_(\d+)/', $quizAndUnitID, $matches)) {
		echo WPCW_units_getCompletionBox_error();
		die();
	}

	// Use the extracted data for further validation
	$unitID = $matches[1];
	$quizID = $matches[2];
	$user_id = get_current_user_id();
	
	
	// #### Get associated data for this unit. No course/module data, not a unit
	$parentData = WPCW_units_getAssociatedParentData($unitID);
	if (!$parentData) {		
		// No error, as not a valid unit.
		die();
	}
	
	// #### User not allowed access to content, so certainly can't say they've done this unit.
	if (!WPCW_courses_canUserAccessCourse($parentData->course_id, $user_id)) {
		// No error, as not a valid unit.
		die();		
	}
	
	// #### Check that the quiz is valid and belongs to this unit
	$quizDetails = WPCW_quizzes_getQuizDetails($quizID, true);
	if (!($quizDetails && $quizDetails->parent_unit_id == $unitID)) {
		die();
	}
	
	// Validate the quiz answers... which means we might have to 
	// send back the form to be re-filled.
	$canContinue = WPCW_quizzes_handleQuizRendering_canUserContinueAfterQuiz($quizDetails, $_POST, $user_id);


	// Check that user is allowed to progress.
	if ($canContinue) {
		WPCW_units_saveUserProgress_Complete($user_id, $unitID, 'complete');
	
		// Unit complete, check if course/module is complete too.
		do_action('wpcw_user_completed_unit', $user_id, $unitID, $parentData);
		
		// Only complete if allowed to continue.
		echo WPCW_units_getCompletionBox_complete($parentData, $unitID, $user_id);
	}	
	
	
	die();
}

?>