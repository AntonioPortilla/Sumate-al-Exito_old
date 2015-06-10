<?php


/**
 * Update the quiz results in the database. Assume that the data exists when doing this update.
 * @param Object $quizResultsSoFar The updated list of results data.
 */
function WPCW_quizzes_updateQuizResults($quizResultsSoFar)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$markinglistCount = 0;
	$markinglist = false;
	
	// Relabel variables for clarity.
	$needsMarkingList = $quizResultsSoFar->quiz_needs_marking_list;
	$newQuizData 	  = $quizResultsSoFar->quiz_data;
	
	// Got items that need marking.
	if (!empty($needsMarkingList)) 
	{
		$markinglist = serialize($needsMarkingList);
		$markinglistCount = count($needsMarkingList);
	}
	
	$dataToUpdate = array(
		'user_id' 					=> $quizResultsSoFar->user_id,
		'unit_id' 					=> $quizResultsSoFar->unit_id,
		'quiz_id' 					=> $quizResultsSoFar->quiz_id,
		'quiz_needs_marking_list' 	=> $markinglist,
		'quiz_needs_marking' 		=> $markinglistCount,
		'quiz_data' 				=> serialize($newQuizData),
		'quiz_grade'				=> -1,
		'quiz_attempt_id'			=> $quizResultsSoFar->quiz_attempt_id
	);
	
	// Update with the quiz grade
	$dataToUpdate['quiz_grade'] = WPCW_quizzes_calculateGradeForQuiz($newQuizData, $markinglistCount);
	
	$SQL = arrayToSQLUpdate($wpcwdb->user_progress_quiz, $dataToUpdate, array('user_id', 'unit_id', 'quiz_id', 'quiz_attempt_id'));
	$wpdb->query($SQL);
}



/**
 * Simple debug function to echo a variable to the page.
 * @param Array $showvar The variable to echo.
 * @param Boolean $return If true, then return the information rather than echo it.
 * @return String The HTML to render the array as debug output.  
 */
function WPCW_debug_showArray($showvar, $return = false)
{
	$html = "<pre style=\"background: #FFF; margin: 10px; padding: 10px; border: 2px solid grey; clear: both; display: block;\">";
	$html .= print_r($showvar, true);
	$html .= "</pre>";
 
	if (!$return) {
		echo $html;
	}
	return $html;
}


/**
 * Safe method to get the value from an array using the specified key.
 * @param Array $array The array to search.
 * @param String $key The key to use to index the array.
 * @param Mixed $returnDefault Return this value if the value is not found.
 * @return String The array value.
 */
function WPCW_arrays_getValue($array, $key, $returnDefault = false)
{
	if ($array && isset($array[$key])) {
		return $array[$key];
	}
	
	return $returnDefault;
}


/**
 * Function to get all of the course details.
 * @param Integer $courseID The ID of the course for which we want to get details.
 * @return Object The details of the course as an object.
 */
function WPCW_courses_getCourseDetails($courseID)
{
	if (!$courseID) {
		return false;
	}	
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("SELECT * 
			FROM $wpcwdb->courses
			WHERE course_id = %d 
			", $courseID);
	
	return $wpdb->get_row($SQL);
}


/**
 * Function to get all of the module details.
 * @param Integer $moduleID The ID of the module for which we want to get details.
 * @return Object The details of the module as an object.
 */
function WPCW_modules_getModuleDetails($moduleID)
{
	if (!$moduleID) {
		return false;
	}	
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("SELECT * 
			FROM $wpcwdb->modules
			WHERE module_id = %d 
			", $moduleID);
	
	return $wpdb->get_row($SQL);
}



/**
 * Function to get all of the quiz details.
 * @param Integer $quizID The ID of the quiz for which we want to get details.
 * @param Boolean $getQuestionsToo If true, get the questions for the quiz too. 
 * 
 * @return Object The details of the quiz as an object.
 */
function WPCW_quizzes_getQuizDetails($quizID, $getQuestionsToo = false)
{
	if (!$quizID) {
		return false;
	}	
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("SELECT * 
			FROM $wpcwdb->quiz	
			WHERE quiz_id = %d 
			", $quizID);
	
	$quizObj = $wpdb->get_row($SQL);
	
	// Nothing found
	if (!$quizObj) {
		return false;
	}
	
	// Something found, so return the questions for this quiz too.
	$quizObj->questions = WPCW_quizzes_getListOfQuestions($quizObj->quiz_id);
	
	
	// Are we expecting any uploads? If so, set a flag to make answer processing
	// faster.
	$quizObj->want_uploads = false; 
	if (!empty($quizObj->questions)) 
	{
		foreach ($quizObj->questions as $quizID => $quizItem)
		{
			if ('upload' == $quizItem->question_type) {
				$quizObj->want_uploads = true;
				break;
			}
		}		
	}
	
	return $quizObj;
}

/**
 * Get the associated quiz for a unit.
 * @param Integer $unitID The ID of the unit to get the associated quiz for.
 * @return Object The Object of the associated quiz, or false if no quiz found.
 */
function WPCW_quizzes_getAssociatedQuizForUnit($unitID)
{
	if (!$unitID) {
		return false;
	}	
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("SELECT * 
			FROM $wpcwdb->quiz	
			WHERE parent_unit_id = %d 
			", $unitID);
	
	$quizObj = $wpdb->get_row($SQL);
	
	// Nothing found
	if (!$quizObj) {
		return false;
	}
	
	// Something found, so return the questions for this quiz too.
	$quizObj->questions = WPCW_quizzes_getListOfQuestions($quizObj->quiz_id);
	return $quizObj;
}


/**
 * Get a list of quiz post objects that match the specified parent unit ID.
 * @param Integer $unitID The ID of the unit to get the quizzes for (0 = unassigned units).
 * @return Array A list of quiz objects in the order that they appear.
 */
function WPCW_quizzes_getListOfQuizzes($unitID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("
		SELECT *
		FROM $wpcwdb->quiz
		WHERE parent_unit_id = %d 
	", $unitID);
	
	// No list of associated IDs? Abort, and return false, as no quizzes objects.
	$rawQuizzes = $wpdb->get_results($SQL);
	if (!$rawQuizzes) {
		return false;
	}
		
	// Re-order post objects so that they are ID => Object details, rather than 0 => Object, 1 => Object
	$quizObjList = array();
	foreach ($rawQuizzes as $obj) 
	{
		$quizObjList[$obj->quiz_id] = $obj;
	}	
	
	return $quizObjList; 
}


/**
 * Get a list of questions that match the specified quiz ID.
 * @param Integer $quizID The ID of the quiz to get the questions for.
 * @return Array A list of questions objects in the order that they appear within the quiz.
 */
function WPCW_quizzes_getListOfQuestions($quizID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("
			SELECT * 
			FROM $wpcwdb->quiz_qs
			WHERE parent_quiz_id = %d
			ORDER BY question_order ASC
		", $quizID);
	
	// No list of associated IDs? Abort, and return false, as no question objects.
	$rawQuestions = $wpdb->get_results($SQL);
	if (!$rawQuestions) {
		return false;
	}
		
	// Re-order post objects so that they are ID => Object details, rather than 0 => Object, 1 => Object
	$questionObjList = array();
	foreach ($rawQuestions as $obj) 
	{
		$questionObjList[$obj->question_id] = $obj;
	}	
	
	return $questionObjList; 
}


/**
 * Get the name for the type of quiz being shown.
 * @param String $quizType The type of the quiz.
 * @return String The actual name of the quiz type.
 */
function WPCW_quizzes_getQuizTypeName($quizType)
{
	switch ($quizType)
	{
		case 'survey':
				return __('Survey', 'wp_courseware');
			break;
			
		case 'quiz_block':
				return __('Quiz - Blocking', 'wp_courseware'); 
			break;
			
		case 'quiz_noblock':
				return __('Quiz - Non-Blocking', 'wp_courseware');
			break;
	}
	return false;
}


/**
 * Get all of the quiz details for the specified unit and quiz ID.
 * 
 * @param Integer $userID The ID of the user.
 * @param Integer $unitID The ID of the unit.
 * @param Integer $quizID The ID of the quiz.
 * 
 * @return Object The quiz results as an object.
 */
function WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizID)
{
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Get the latest version of the quiz results, as there may be other versions to check for.
    $SQL = $wpdb->prepare("
    	SELECT *, UNIX_TIMESTAMP(quiz_completed_date) AS quiz_completed_date_ts
    	FROM $wpcwdb->user_progress_quiz 
    	WHERE user_id = %d 
    	  AND unit_id = %d 
    	  AND quiz_id = %d
    	ORDER BY quiz_attempt_id DESC
    	LIMIT 1
   	", $userID, $unitID, $quizID);
    
    $quizObj = $wpdb->get_row($SQL);
    
    // Sort out the array of quiz data.
    if ($quizObj && $quizObj->quiz_data) {
    	$quizObj->quiz_data = maybe_unserialize($quizObj->quiz_data);
    }
    
    // Unserialize the quiz marking list. 
	if ($quizObj && $quizObj->quiz_needs_marking_list) {
    	$quizObj->quiz_needs_marking_list = unserialize($quizObj->quiz_needs_marking_list);
    }
    
    return $quizObj;
}


/**
 * Calculates the grade for a set of results, taking into account the
 * different types of questions.
 * 
 * @param Array $quizData The list of quiz results data.
 * @param Integer $questionsThatNeedMarking How many questions need marking.
 * 
 * @return Integer The overall grade for the results.
 */
function WPCW_quizzes_calculateGradeForQuiz($quizData, $questionsThatNeedMarking = 0) 
{
	if ($questionsThatNeedMarking > 0) {	
		return '-1';
	}
	
	$questionTotal = 0;
	$gradeTotal = 0;
	foreach ($quizData as $questionID => $questionResults)
	{
		// It's a truefalse/multi question
		if ($questionResults['got_right'])
		{
			// Got it right, so add 100%.
			if ($questionResults['got_right'] == 'yes') {
				$gradeTotal += 100;
			}
		}
		
		// It's a graded question.
		else 
		{
			// Making assumption that the grade number exists
			// Otherwise we'd never get this far as the question still needs marking.
			$gradeTotal += WPCW_arrays_getValue($questionResults, 'their_grade');
		}

		$questionTotal++;
	}

	// Simple calculation that averages the grade.
	$grade = 0;
	if ($questionTotal) {
		$grade = number_format($gradeTotal / $questionTotal, 1);
	}
	
	return $grade; 
}



/**
 * Function to get all of the module details.
 * @param Integer $courseID The ID of the course for which we want to get details.
 * @param Integer $moduleNumber The module number for the module in this course 
 * @return Object The details of the module as an object.
 */
function WPCW_modules_getModuleDetails_byModuleNumber($courseID, $moduleNumber)
{
	if (!$courseID || !$moduleNumber) {
		return false;
	}	
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("SELECT * 
			FROM $wpcwdb->modules
			WHERE module_number = %d
			  AND parent_course_id = %d
			", $moduleNumber, $courseID);
	
	return $wpdb->get_row($SQL);
}

/**
 * Function to get a list of the courses for use in a select list.
 * @param String $addBlank If set, use this string as the blank option at the top of the list.
 * @return Array The list of courses as an array of (courseID => Course name).
 */
function WPCW_courses_getCourseList($addBlank = false)
{
	$list = array();
	if ($addBlank) {	
		$list[] = $addBlank;
	}
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = "SELECT * 
			FROM $wpcwdb->courses
			ORDER BY course_title
			";
	
	$items = $wpdb->get_results($SQL);
	if (count($items) < 1) {
		return false;
	}
	
	foreach ($items as $item) {
		$list[$item->course_id] = $item->course_title;
	}
	
	return $list;
}

/**
 * Get a list of the modules for a training course, in the order required for training.
 * 
 * @param Integer $courseID The ID of the course to get the modules for.
 */
function WPCW_courses_getModuleDetailsList($courseID)
{
	$list = array();	
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("SELECT * 
			FROM $wpcwdb->modules			
			WHERE parent_course_id = %d
			ORDER BY module_order, module_title ASC
			", $courseID);
	
	$items = $wpdb->get_results($SQL);	
	if (count($items) < 1) {
		return false;
	}
	
	// List modules in array using module ID
	foreach ($items as $item)  {
		$list[$item->module_id] = $item;
	}
	
	return $list;
}


/**
 * Get a list of unit post objects that match the specified module ID.
 * @param Integer $moduleID The ID of the module to get the units for (0 = unassigned units).
 * @return Array A list of unit objects in the order that they appear.
 */
function WPCW_units_getListOfUnits($moduleID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("
		SELECT *
		FROM $wpcwdb->units_meta
		WHERE parent_module_id = %d
		ORDER BY unit_order ASC, unit_id ASC 
	", $moduleID);
	
	// No list of associated IDs? Abort, and return false, as no units or unit objects.
	$rawUnits = $wpdb->get_results($SQL);
	if (!$rawUnits) {
		return false;
	}
	
	// Turn list into ID => meta list
	$unitIDList = array();
	foreach ($rawUnits as $rawUnit)
	{
		$unitIDList[$rawUnit->unit_id] = $rawUnit;
	}
	
	// Get list of IDs, and use this for WordPress query to get the full objects
	$unitPostObjsRaw = get_posts(array(
		'post_type' => 'course_unit', 				// Just course units
		'number'	=> -1,							// No limit, i.e. all
		'orderby'	=> 'none',						// 
		'include'	=> array_keys($unitIDList)		// List of posts to get.
	));
	
	if (!$unitPostObjsRaw) {
		return false;
	}
	
	// Re-order post objects so that they are ID => Object details, rather than 0 => Object, 1 => Object
	$unitPostObjs = array();
	foreach ($unitPostObjsRaw as $obj) 
	{
		// Add our metadata
		$obj->unit_meta = $unitIDList[$obj->ID]; 
		
		$unitPostObjs[$obj->ID] = $obj;
	}	
	
	// Use unit ordering from table to return actual ordering list.
	$unitDataRet = array();
	foreach ($unitIDList as $unitID => $unitObj) 
	{
		if (isset($unitPostObjs[$unitID])) {
			$unitDataRet[$unitID] = $unitPostObjs[$unitID];
		}
	}
	
	return $unitDataRet; 
}


/**
 * Get all of the associated parent data for the specified course unit.
 * @param Integer $post_id The ID of the course unit
 * @return Object The details of the parent objects, or false if there is no parent.
 */
function WPCW_units_getAssociatedParentData($post_id)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->units_meta um
		LEFT JOIN $wpcwdb->modules m ON m.module_id = um.parent_module_id
		LEFT JOIN $wpcwdb->courses c ON c.course_id = m.parent_course_id
		WHERE um.unit_id = %d AND course_title IS NOT NULL
	", $post_id);
	
	return $wpdb->get_row($SQL);
}


/**
 * Convert a percentage to a percentage bar
 * @param String $percent The number to show in the progress bar.
 * @param String $title The optional title of the course.
 * 
 * @return String The HTML to render the percentage bar.
 */
function WPCW_stats_convertPercentageToBar($percent, $title = false) 
{
	if ($title) {
		$title = sprintf('<span class="wpcw_progress_bar_title">%s</span>', $title);
	}
	
	return sprintf('
		<div class="wpcw_progress_wrap">
			
			<span class="wpcw_progress_bar">
				<div class="wpcw_progress_value" style="width: %s%%; ">
					<div class="wpcw_progress_text">%s%%</div>	
				</div>
			</span>
			%s
		</div>
	',	
	$percent,
	$percent,
	$title
	);	
}


/** 
 * Check if a user can access the specified training course.
 * 
 * @param Integer $courseID The ID of the course to check.
 * @param Integer $userID The ID of the user to check.
 * @return Boolean True if the user can access this course, false otherwise.
 */
function WPCW_courses_canUserAccessCourse($courseID, $userID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_courses
		WHERE user_id = %d AND course_id = %d
	", $userID, $courseID);
	
	return ($wpdb->get_row($SQL) != false);
}



/**
 * Has the user completed this unit? If so, return all data associated with completion.
 * 
 * @param Integer $userID The ID of the user to check.
 * @param Integer $unitID The ID of the unit to check.
 * 
 * @return Object The details of the completion, or false if the unit has not been completed by the user.
 */
function WPCW_units_getUserUnitDetails($userID, $unitID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("
		SELECT *, UNIX_TIMESTAMP(unit_completed_date) AS unit_completed_date_ts
		FROM $wpcwdb->user_progress
		WHERE user_id = %d AND unit_id = %d
	", $userID, $unitID);

	return $wpdb->get_row($SQL);
}





/**
 * Update the user progress count based on units completed.
 * @param Integer $courseID ID of course.
 * @param Integer $userID ID of user.
 * @param Integer $totalUnitCount The total number of units for this course. 
 */
function WPCW_users_updateUserUnitProgress($courseID, $userID, $totalUnitCount)
{
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    
    // Get number of completed units
    $completed = $wpdb->get_var($wpdb->prepare("
    	SELECT COUNT(*) 
    	FROM $wpcwdb->user_progress up
    		LEFT JOIN $wpcwdb->units_meta um ON up.unit_id = um.unit_id
    	WHERE user_id = %d
    	  AND parent_course_id = %d
    	  AND unit_completed_status = 'complete'
    ", 
    	$userID, 
    	$courseID
    ));
    
    // Calculate progress as a percentage
    $progress = 0;
    if ($totalUnitCount > 0) {
    	$progress = floor(($completed / $totalUnitCount) *100);
    }
        
    // Update database with the completed progress
    $wpdb->query($wpdb->prepare("
    	UPDATE $wpcwdb->user_courses
    	   SET course_progress = %d
    	WHERE user_id = %d
    	  AND course_id = %d
    ", $progress, $userID, $courseID));
}



/**
 * Function called when courses are updated. This will update the metrics associated with the course
 * such as the total number of units.
 * 
 * @param Array $courseDetails The course details that have just been updated.
 */
function WPCW_actions_courses_courseDetailsUpdated($courseDetails)
{
	if (!$courseDetails) {
		return;
	}
	
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Get a total count of units in this course
    $SQL = $wpdb->prepare("
    	SELECT COUNT(*) 
    	FROM $wpcwdb->units_meta 
    	WHERE parent_course_id = %d
    ", $courseDetails->course_id);
    
    $totalUnitCount = $wpdb->get_var($SQL);
	
    // Update database with actual count
    $wpdb->query($wpdb->prepare("
    	UPDATE $wpcwdb->courses
    	   SET course_unit_count = %d
    	 WHERE course_id = %d 
    ", $totalUnitCount, $courseDetails->course_id));
    
    
	// User progress counts will now be out of sync too, particularly with new or deleted units. 
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_courses 
		WHERE course_id = %d
	", $courseDetails->course_id);
	
	$users = $wpdb->get_results($SQL);
	if ($users)
	{
		foreach ($users as $userCourseDetails) {
			WPCW_users_updateUserUnitProgress($userCourseDetails->course_id, $userCourseDetails->user_id, $totalUnitCount);
		}
	}
}


/**
 * Function called when a module in the specified course has been created or editied. Used to 
 * ensure that all modules have a valid module number.
 * 
 * @param Integer $courseID The ID of the course that looks after the module that's been created or edited.
 */
function WPCW_actions_modules_modulesModified($courseID)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	$modules = $wpdb->get_results($wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->modules
		WHERE parent_course_id = %d
		ORDER BY module_order ASC 
	", $courseID));
	
	// Nothing to do
	if (!$modules) {
		return;
	}
	
	$moduleOrderCount = 0;
	foreach ($modules as $module)
	{			
		$moduleOrderCount++;
		
		// Update module list with new ordering
		$SQL = $wpdb->prepare("
			UPDATE $wpcwdb->modules
			   SET module_order = %d, module_number = %d
			WHERE module_id = %d
		", $moduleOrderCount, $moduleOrderCount, $module->module_id);
		
		$wpdb->query($SQL);	
	}
}


/**
 * Function called after a module has been created with the modify module form.
 * 
 * @param Array $formValues The processed form values.
 * @param Array $originalFormValues The raw form values.
 * @param Object $thisObject The reference to the form object doing the saving. 
 */
function WPCW_actions_modules_afterModuleSaved_formHook($formValues, $originalFormValues, $thisObject)
{
	// Modules have been modified. Call action to update module numbers.
	do_action('wpcw_modules_modified', $formValues['parent_course_id']);
}



/**
 * Function called when the user completes a unit
 * @param Integer $userID The ID of the user that's completed the unit.
 * @param Integer $unitID The ID of the unit that's been completed.
 * @param Object $unitParentData The object of parent data associated with the unit, such as module and course.
 */
function WPCW_actions_users_unitCompleted($userID, $unitID, $unitParentData)
{
	if (!$userID || !$unitID || !$unitParentData) {
		return;
	}
	
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Update the user progress count.
	WPCW_users_updateUserUnitProgress($unitParentData->course_id, $userID, $unitParentData->course_unit_count);
    
    // Work out if module/course completed.
    $userProgress = new UserProgress($unitParentData->course_id, $userID);
    
    if ($userProgress->isModuleCompleted($unitID)) {
    	do_action('wpcw_user_completed_module', $userID, $unitID, $unitParentData);
    }
    
	if ($userProgress->isCourseCompleted()) {
    	do_action('wpcw_user_completed_course', $userID, $unitID, $unitParentData);
    }
    
    //error_log('');
    //error_log('Module Completed?: ' . $unitID . ' - ' . ($userProgress->isModuleCompleted($unitID) ? 'yes' : 'no'));
    //error_log('Course Completed?: ' . $unitID . ' - ' . ($userProgress->isCourseCompleted() ? 'yes' : 'no'));
}

/**
 * Function called when the user completes a module.
 * @param Integer $userID The ID of the user that's completed the unit.
 * @param Integer $unitID The ID of the unit that's just been completed.
 * @param Object $unitParentData The object of parent data associated with the unit, such as module and course.
 */
function WPCW_actions_users_moduleCompleted($userID, $unitID, $unitParentData) 
{
	if (!$userID || !$unitID || !$unitParentData) {
		return;
	}
	
	$userDetails = get_userdata($userID);
	
	// Admin wants an email notification, and email address exists. Assumption is that it's valid.
	if ($unitParentData->email_complete_module_option_admin == 'send_email' && $unitParentData->course_to_email)
	{
		$adminSubject = __("Module Complete Notification - {USER_NAME} - Module {MODULE_NUMBER}", 'wp_courseware');
		$adminBody    = __("Hi Trainer! 
							
Just to let you know, {USER_NAME} has just completed 'Module {MODULE_NUMBER} - {MODULE_TITLE}'.

{SITE_NAME}
{SITE_URL}
", 'wp_courseware');
		
		// Do email sending now
		WPCW_email_sendEmail($unitParentData, 
							$userDetails, 									// User who's done the completion
							$unitParentData->course_to_email, 
							$adminSubject, $adminBody);	
	}
	
	// Check if admin wants user to have an email.
	if ($unitParentData->email_complete_module_option == 'send_email')
	{
		WPCW_email_sendEmail($unitParentData, 
							$userDetails,									// User who's done the completion
							$userDetails->user_email, 
							$unitParentData->email_complete_module_subject, // Use subject template in the settings 
							$unitParentData->email_complete_module_body		// Use body template in the settings
						);	
	}
}


/**
 * Function called when the user completes a module.
 * @param Integer $userID The ID of the user that's completed the unit.
 * @param Integer $unitID The ID of the unit that's just been completed.
 * @param Object $unitParentData The object of parent data associated with the unit, such as module and course.
 */
function WPCW_actions_users_courseCompleted($userID, $unitID, $unitParentData) 
{
	if (!$userID || !$unitID || !$unitParentData) {
		return;
	}
	
	// Certificates have been requested, so generate one for this user and course.
	if ($unitParentData->course_opt_use_certificate == 'use_certs')
	{
		// Add a certificate entry to the database for the user.
		WPCW_certificate_generateCertificateEntry($userID, $unitParentData->course_id);	
	}
	
	
	$userDetails = get_userdata($userID);
	
		$adminSubject = __("Course Complete Notification - {USER_NAME} - '{COURSE_TITLE}'", 'wp_courseware');
		$adminBody    = __("Hi Trainer! 
							
Just to let you know, {USER_NAME} has just completed the '{COURSE_TITLE}' course.

{SITE_NAME}
{SITE_URL}
", 'wp_courseware');
	
	// Admin wants an email notification, and email address exists. Assumption is that it's valid.
	if ($unitParentData->email_complete_course_option_admin == 'send_email' && $unitParentData->course_to_email)
	{
		WPCW_email_sendEmail($unitParentData, 
							$userDetails, 									// User who's done the completion
							$unitParentData->course_to_email, 
							$adminSubject, $adminBody);	
	}
	
	// Check if admin wants user to have an email.
	if ($unitParentData->email_complete_course_option == 'send_email')
	{
		
		WPCW_email_sendEmail($unitParentData, 
							$userDetails,									// User who's done the completion
							$userDetails->user_email, 
							$unitParentData->email_complete_course_subject, // Use subject template in the settings 
							$unitParentData->email_complete_course_body		// Use body template in the settings
						);	
	}	
}


/**
 * The function called when a user receives a grade for a quiz, either when marked manually, or 
 * when the question it automatically graded.
 * 
 * Triggered by wpcw_quiz_graded.
 * 
 * @param Integer $userID The ID of the user to notify.
 * @param Object $quizDetails The details of the quiz.
 * @param Integer $grade The grade that they've been given.
 * @param String $additionalDetail Any additional data to add to the message.
 */
function WPCW_actions_userQuizGraded_notifyUser($userID, $quizDetails, $grade, $additionalDetail)
{
	if (!$userID || !$quizDetails) {
		return;
	}

	// User and Unit details
	$userDetails = get_userdata($userID);
	$unitParentData	= WPCW_units_getAssociatedParentData($quizDetails->parent_unit_id);
	
	// Check if admin wants user to have an email.
	if ($unitParentData->email_quiz_grade_option == 'send_email')
	{	
		$emailSubject = $unitParentData->email_quiz_grade_subject;
		$emailBody    = $unitParentData->email_quiz_grade_body;
		
		// Quiz Title
		$emailSubject = str_ireplace('{QUIZ_TITLE}', $quizDetails->quiz_title, $emailSubject);
		$emailBody    = str_ireplace('{QUIZ_TITLE}', $quizDetails->quiz_title, $emailBody);
		
		// Grade
		$emailSubject = str_ireplace('{QUIZ_GRADE}', $grade . '%', $emailSubject);
		$emailBody    = str_ireplace('{QUIZ_GRADE}', $grade . '%', $emailBody);

		// Quiz result detail
		$emailBody    = str_ireplace('{QUIZ_RESULT_DETAIL}', $additionalDetail, $emailBody);
	
		
		// URL to the unit that was completed.
		$emailBody 	  = str_ireplace('{UNIT_URL}', get_permalink($quizDetails->parent_unit_id), $emailBody);
		

		WPCW_email_sendEmail($unitParentData, 
							$userDetails,									// User who's done the completion
							$userDetails->user_email, 
							$emailSubject,
							$emailBody
						);	
	}
}



/**
 * Function called when a user quiz needs grading by the admin.
 * 
 * @param Integer $userID The ID of the user who's quiz needs grading
 * @param Object $quizDetails The details of the quiz that needs grading.
 */
function WPCW_actions_userQuizNeedsGrading_notifyAdmin($userID, $quizDetails)
{	
	if (!$userID || !$quizDetails) {
		return;
	}
	
	$adminSubject = __("Quiz Needs Grading Notification - {USER_NAME} - '{COURSE_TITLE}'", 'wp_courseware');
	$adminBody    = __("Hi Trainer! 
							
Just to let you know, {USER_NAME} has just completed a quiz ({QUIZ_TITLE}), which requires grading. You can grade the question here:
{QUIZ_GRADE_URL}

{SITE_NAME}
{SITE_URL}
", 'wp_courseware');
	
	
	// Generate the quiz name and URL to mark the quiz before passing for email to be sent.
	$quizGradeURL = sprintf('%s&user_id=%d&quiz_id=%d&unit_id=%d', 
		admin_url('users.php?page=WPCW_showPage_UserProgess_quizAnswers'), 
		$userID, $quizDetails->quiz_id, $quizDetails->parent_unit_id
	);
	
	$adminBody = str_ireplace('{QUIZ_TITLE}', $quizDetails->quiz_title, $adminBody);
	$adminBody = str_ireplace('{QUIZ_GRADE_URL}', $quizGradeURL, $adminBody);
	
	// User and Unit details
	$userDetails = get_userdata($userID);
	$unitParentData	= WPCW_units_getAssociatedParentData($quizDetails->parent_unit_id);
	
	// Check admin email address exists before sending.
	if ($unitParentData->course_to_email)
	{
		WPCW_email_sendEmail($unitParentData, 
							$userDetails, 									// User who's done the completion
							$unitParentData->course_to_email, 
							$adminSubject, $adminBody);	
	}
}



/**
 * Send an email out using a template.
 * 
 * @param Object $unitParentData The parent data for a unit.
 * @param Object $userDetails The details of the user who's done the completing.
 * @param String $targetEmail The email address of the recipient.
 * @param String $subjectTemplate The content of the subject template, before substitutions.
 * @param String $bodyTemplate The content of the email body template, before substitutions. 
 */
function WPCW_email_sendEmail($unitParentData, $userDetails, $targetEmail, $subjectTemplate, $bodyTemplate)
{
	//error_log(sprintf('Sending Email - WPCW_email_sendEmail(): %s (%s)',  $targetEmail, $subjectTemplate));	
	
	// Replace content within the email
	$messageSubject = WPCW_email_replaceTags($subjectTemplate, $unitParentData, $userDetails);
	$messageBody 	= WPCW_email_replaceTags($bodyTemplate, $unitParentData, $userDetails);
	
	
	// Construct the from part of the email
	$headers = false; 
	if ($unitParentData->course_from_email) {
		$headers = sprintf('From: %s <%s>' . "\r\n", $unitParentData->course_from_name, $unitParentData->course_from_email);
	}
		
	// Actually send the email
	if (!wp_mail($targetEmail, $messageSubject, $messageBody, $headers)) {
		error_log('WPCW_email_sendEmail() - email did not send.');
	}
}


/**
 * Replace all of the email tags with the actual details.
 * 
 * @param String $template The template to do the string replace for.
 * @param Object $unitParentData The parent data for a unit.
 * @param Object $userDetails The details of the user who's done the completing.
 */
function WPCW_email_replaceTags($template, $unitParentData, $userDetails)
{
	$message = $template;
	
	// User Details
	$message = str_ireplace('{USER_NAME}', 		$userDetails->display_name, $message);
	
	// Site Details
	$message = str_ireplace('{SITE_NAME}', 		get_bloginfo('name'), $message);
	$message = str_ireplace('{SITE_URL}', 		get_bloginfo('url'), $message);
	
	// Course Details	
	$message = str_ireplace('{COURSE_TITLE}', 	$unitParentData->course_title, $message);
	
	// Module Details - may not exist.
	if (isset($unitParentData->module_title)) {
		$message = str_ireplace('{MODULE_TITLE}', 	$unitParentData->module_title, $message);
	}
	
	if (isset($unitParentData->module_number)) {
		$message = str_ireplace('{MODULE_NUMBER}', 	$unitParentData->module_number, $message);
	}
	
	
	// Certificates - generate a link if enabled.
	$certificateLink = false;
	if ('use_certs' == $unitParentData->course_opt_use_certificate)
	{
		$certificateDetails = WPCW_certificate_getCertificateDetails($userDetails->ID, $unitParentData->course_id, false);
		if ($certificateDetails) {
			$certificateLink = WPCW_certificate_generateLink($certificateDetails->cert_access_key);
		}
	}
	
	$message = str_ireplace('{CERTIFICATE_LINK}', $certificateLink, $message);
	
	return $message;
}


/**
 * Action called when a user is deleted in WordPress. Remove all progress
 * and certificate details.
 * 
 * @param Integer $user_id The ID of the user that's just been deleted.
 */
function WPCW_actions_users_userDeleted($user_id)
{
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Course progress summary for user needs to be removed.
    $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->user_courses
				WHERE user_id = %d
			", $user_id));
    
   // User's progress
   $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->user_progress
				WHERE user_id = %d
			", $user_id));
   
   // User's quiz answers
   $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->user_progress_quiz
				WHERE user_id = %d
			", $user_id));
   
   // User's certificates
   $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->certificates
				WHERE cert_user_id = %d
			", $user_id));
}


/**
 * Action called when a new user is created in WordPress. Used to check if we need to 
 * automatically add access for this user to access a training course.
 * 
 * @param Integer $user_id The ID of the user that's just been added.
 */
function WPCW_actions_users_newUserCreated($user_id)
{
	// See if an extension is taking over the checking of access control. If a function is 
	// defined to return true, then this section of code is ignored.
	$ignoreOnNewUser = apply_filters('wpcw_extensions_ignore_new_user', false);
	if ($ignoreOnNewUser) {
		return;
	} 
	
	
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Get a list of all courses that want users added automatically.
    $courses = $wpdb->get_col("
    	SELECT * 
    	FROM $wpcwdb->courses
    	WHERE course_opt_user_access = 'default_show'
    ");

    // None found
    if (!$courses || count($courses) < 1) {
    	return;
    }
        
    // Add access for this user to all courses we're associated with. 
    WPCW_courses_syncUserAccess($user_id, $courses, 'sync');
}



/**
 * Function to add the specified list of course IDs for the specified user.
 * 
 * @param Integer $user_id The ID of the user to update.
 * @param Mixed $courseIDs The ID or array of IDs of the course IDs to give the user access to.
 * @param Boolean $syncMode If 'sync', then remove access to any course IDs not mentioned in $courseIDs parameter. If 'add', then just add access for course IDs that have been specified.
 */
function WPCW_courses_syncUserAccess($user_id, $courseIDs, $syncMode = 'add')
{
	// Not a valid user
	if (!get_userdata($user_id)) {
		return;
	}
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// Check if we want to remove access for courses that are not mentioned.
	// We'll add any they should have access to in a mo.
	if ($syncMode == 'sync')
	{
		// Remove meta for this user all previous courses.
		$wpdb->query($wpdb->prepare("DELETE FROM $wpcwdb->user_courses WHERE user_id = %d", $user_id));
	}
	
	// List of course IDs that actually exist.
	$courseList = array();	
	
	// Got a list of IDs?
	if (is_array($courseIDs)) 
	{
		// List is empty, save a query
		if (count($courseIDs) > 0)
		{
			// Yep, this course actually exists
			foreach ($courseIDs as $potentialCourseID) 
			{
				if ($courseDetails = WPCW_courses_getCourseDetails($potentialCourseID)) {
					$courseList[$potentialCourseID] = $courseDetails;
				}
			}
		}		
	}
	
	// Got a single ID..., so add to list of courses to process having
	// checked ID belongs to a proper course.
	else 
	{
		if ($courseDetails = WPCW_courses_getCourseDetails($courseIDs)) {
			$courseList[$courseIDs] = $courseDetails;
		}
	}

	
	
	// Only process valid course IDs
	if (count($courseList) > 0)
	{
		foreach($courseList as $validCourseID => $courseDetails)
		{
			// See if this is already in the database. No need to check if in sync mode, as already removed prior.
			if ($syncMode == 'sync' || !$wpdb->get_row($wpdb->prepare("SELECT * FROM $wpcwdb->user_courses WHERE user_id = %d AND course_id = %d", $user_id, $validCourseID))) 
			{			
				// Actually add reference in database as it doesn't exist.
				$wpdb->query($wpdb->prepare("INSERT INTO $wpcwdb->user_courses (user_id, course_id, course_progress) VALUES(%d, %d, 0)",
					$user_id, $validCourseID
				));
			}

		    // Get a total count of units in this course
		    $SQL = $wpdb->prepare("
		    	SELECT COUNT(*) 
		    	FROM $wpcwdb->units_meta 
		    	WHERE parent_course_id = %d
		    ", $validCourseID);
		    
		    $totalUnitCount = $wpdb->get_var($SQL);			
			
			// Calculate the user's progress, in case they've still got completed progress
			// in the database.
			WPCW_users_updateUserUnitProgress($validCourseID, $user_id, $totalUnitCount);
		}
	}
}

/**
 * Fetch a list of courses for the specified user.
 * 
 * @param Integer $user_id The ID of the user to get the course list for.
 * @return Array The list of courses for this user (or false if there are none).
 */
function WPCW_users_getUserCourseList($user_id)
{
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    	
    $courseData = $wpdb->get_results($wpdb->prepare("
    	SELECT *
    	FROM $wpcwdb->user_courses uc
    		LEFT JOIN  $wpcwdb->courses c ON c.course_id = uc.course_id
   		WHERE user_id = %d
   		ORDER BY course_title ASC
    ", $user_id));
    
    return $courseData;
}



/**
* Updates the database to generate a certificate entry. If a certificate already exists for the user/course ID,
 * then no new entry is created.
 * 
 * @param Integer $userID The ID of the user that the certificate is being generated for.
 * @param Integer $courseID The ID of the associated course.
 */
function WPCW_certificate_generateCertificateEntry($userID, $courseID)
{
	if (!$userID || !$courseID) {
		return;
	}
	
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Already have a record for this certificate.
    if ($certificateDetails = doesRecordExistAlready($wpcwdb->certificates, array('cert_user_id', 'cert_course_id'), array($userID, $courseID))) {
    	return $certificateDetails;
    }
    
    // Create anonymous entry to allow users to access a certificate when they've completed a course.  Means that certificates
    // stay existing even if units are added to a course.     
    $data = array();
    $data['cert_user_id'] 		= $userID;
    $data['cert_course_id'] 	= $courseID;
    $data['cert_generated'] 	= current_time('mysql');
    $data['cert_access_key'] 	= md5(serialize($data)); // Unique key based on data we've just added
    
	$SQL = arrayToSQLInsert($wpcwdb->certificates, $data);
	$wpdb->query($SQL);
	
	// Return details of the added certificate
	return getRecordDetails($wpcwdb->certificates, array('cert_user_id', 'cert_course_id'), array($userID, $courseID));
}


/**
 * Get the certificate details for a user, or false if not found.
 * 
 * @param Integer $userID The ID of the user to check.
 * @param Integer $courseID The ID of the associated course.
 * @param Boolean $tryToCreate If true, try to create the certificate if details don't exist.
 * 
 * @return Object The certificate details if they were found, or false if not found.
 */
function WPCW_certificate_getCertificateDetails($userID, $courseID, $tryToCreate = true)
{
	global $wpcwdb;
	$certificateDetails = getRecordDetails($wpcwdb->certificates, array('cert_user_id', 'cert_course_id'), array($userID, $courseID));
	
	if ($tryToCreate && !$certificateDetails) {		
		return WPCW_certificate_generateCertificateEntry($userID, $courseID);
	}
	
	return $certificateDetails;
}

/**
 * Get the certificate details for a user, or false if not found.
 * 
 * @param String $accessID The unique access key for the certificate.
 * 
 * @return Object The certificate details if they were found, or false if not found.
 */
function WPCW_certificate_getCertificateDetails_byAccessKey($accessKey)
{
	// Validate for a MD5 hash
	
	if (!preg_match('/^[A-Za-z0-9]{32}$/', $accessKey)) {
		return false;
	}
	
	global $wpcwdb;
	return getRecordDetails($wpcwdb->certificates, 'cert_access_key', $accessKey);
}

/**
 * Gets the user's name if set up, or their username otherwise.
 * @param Object $userDetails The user details as an object.
 * @return String The user's name.
 */
function WPCW_users_getUsersName($userDetails)
{
	if (!$userDetails) {
		return false;
	}
	
	// Generate the name from the user's first and last name. If they don't exist
	// then use the display name as a default.
	$name = $userDetails->user_firstname . ' ' . $userDetails->user_lastname;
	if (!trim($name)) {
		$name = $userDetails->data->display_name;
	}	
	
	return $name;
}

/**
 * Return a URL to download a certificate.
 * 
 * @param String $accessKey The access key for the certificate.
 * @return String The full URL to the certificate.
 */
function WPCW_certificate_generateLink($accessKey){
	return apply_filters('wpcw_certificate_generated_url', WPCW_plugin_getPluginPath() . 'certificate_gen.php?certificate=' . $accessKey);	
}



/**
 * Converts a list of raw file extensions into a list of permitted file extensions.
 * @param String $rawInput The list of raw file extensions.
 * @return Array The list of file extensions.
 */
function WPCW_files_cleanFileExtensionList($rawInput)
{
	$list = array();
	
	// Turn comma list into array of items
	$rawList = explode(',', $rawInput);
	if (!empty($rawList))
	{
		// Check each item
		foreach ($rawList as $ext)
		{			
			$ext = strtolower($ext);
			$ext = preg_replace('/[^a-z0-9]/', '', $ext); // Remove anything other than numbers and letters.
			
			// Got anything left? Add it to the list.
			if ($ext) {
				$list[] = $ext;
			}
		}
	}
	
	return $list;
}

/**
 * Returns the maximally uploadable file size in megabytes.
 *
 * @return  string
 */
function WPCW_files_getMaxUploadSize()
{
   $max_upload    = (int)(ini_get('upload_max_filesize'));
   $max_post      = (int)(ini_get('post_max_size'));
   $memory_limit  = (int)(ini_get('memory_limit'));
   return min($max_upload, $max_post, $memory_limit) . __('MB', 'wp_courseware');      
}



/**
 * Create a directory that can be used to store the uploaded files in for the user completing a specific quiz.
 * 
 * @param Object $quizDetails The details of the quiz being completed.
 * @param Integer $userID The ID of the user completing the quiz.
 * @param Boolean $createItToo If true, then create the new directory.
 * 
 * @return Array The full server path to the newly created upload directory and URL version.
 */
function WPCW_files_getFileUploadDirectory_forUser($quizDetails, $userID, $createItToo = true)
{
	// Create path based on the quiz ID, user ID, and date.
	$keyString = sprintf('%d_%d_%s', $quizDetails->quiz_id, $userID, date('Ymd_His'));
	$pathName  = $keyString . '_' . md5('user_upload_directory' . $keyString);

	// Generate the full file path
	$fullPath = WP_CONTENT_DIR . '/wpcourseware_uploads/' . $pathName . '/';
	
	if ($createItToo && !file_exists($fullPath)) {
		@mkdir($fullPath, 0777, true);		
	}
	
	// Create an empty index page to stop directory listings.
	if (file_exists($fullPath)) {
		touch($fullPath . 'index.php');
	}
	
	// Need URL and directory versions
	return array(
		'dir_path' 	=> $fullPath,
		'path_only'	=> '/wpcourseware_uploads/' . $pathName . '/'
	);
}



/**
 * Generates the upload directory with an empty index.php file to prevent directory listings.
 */
function WPCW_files_createFileUploadDirectory_base()
{
	// Generate the full file path
	$fullPath = WP_CONTENT_DIR . '/wpcourseware_uploads/';
	
	if (!file_exists($fullPath)) {
		@mkdir($fullPath, 0777, true);		
	}
	
	// Create an empty index page to stop directory listings.
	if (file_exists($fullPath)) {
		touch($fullPath . 'index.php');
	}
}

/**
 * Generates the upload directory with an empty index.php file to prevent directory listings.
 */
function WPCW_files_getFileSize_human($fileName)
{
	$fileSizeBytes = filesize(WP_CONTENT_DIR . $fileName);
	if ($fileSizeBytes === FALSE) {
		return __('Not found.', 'wp_courseware');
	}
	
	return WPCW_files_formatBytes($fileSizeBytes, 0);
}

/**
 * Format a size into the right KB, MB, etc
 * 
 * @param Integer $size The size in bytes.
 * @param Integer $precision The number of decimal places.
 * 
 * @return String The file size as a string.
 */
function WPCW_files_formatBytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}

?>