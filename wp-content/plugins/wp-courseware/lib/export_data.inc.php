<?php


/**
 * Sends the HTTP headers for CSV content that forces a download.
 */
function WPCW_data_export_sendHeaders_CSV()
{
	$debugMode = false;
	
	if (!$debugMode)
	{
		// Force the file to download
		header('Content-Type: application/csv');  
		header('Content-Disposition: attachment; filename="wpcw-import-users.csv"');
		header("Cache-Control: no-store, no-cache");
	} 
	else {
		// Enable below and disable header() calls above for debugging purposes.
		header('Content-Type: text/plain');
	}
}


/**
 * Function that checks to see if a data export has been triggered.
 */
function WPCW_data_handleDataExport()
{
	// Check for a generic trigger for an export.
	if (isset($_GET['wpcw_export']) && $exportType = $_GET['wpcw_export'])
	{
		// If user is not allowed to edit options, then redirect to home page
		if (!current_user_can('manage_options') ) { 
			wp_redirect(get_bloginfo('url'), 301);
			return; 
		}
		
		// Contains the data type => the function that generates it.
		$exportTypeList = array(
			'csv_import_user_sample'	=> 'WPCW_data_export_userImportSample', 
			'gradebook_csv'				=> 'WPCW_data_export_gradebookData'
		);
		
		// Check the export type matches the only types of export that we handle.
		if (!in_array($exportType, array_keys($exportTypeList))) {
			return;
		}
		
		// Trigger the function that will export this type of file.
		call_user_func($exportTypeList[$exportType]);
		
		// All done.
		die();
	}
}


/**
 * Function that generates a sample CSV file from the database using the relevant course IDs.
 */
function WPCW_data_export_userImportSample()
{
	WPCW_data_export_sendHeaders_CSV();
	
	// Start CSV
	$out = fopen('php://output', 'w');
	
	// The headings
	$headings = array('first_name', 'last_name', 'email_address', 'courses_to_add_to');
	fputcsv($out, $headings);

	// Use existing course IDs to make it more useful. If there are no courses
	// Create some dummy courses to add. 
	$courseList = array();
	$courseList[1] = __('Test Course', 'wp_courseware') . ' A'; 
	$courseList[2] = __('Test Course', 'wp_courseware') . ' B';
	
	$courseListOfIDs = 0;
	foreach ($courseList as $courseID => $courseName)
	{
		$data = array();
		$data[] = 'John';
		$data[] = 'Smith';
		$data[] = get_bloginfo('admin_email');
		
		// Sequentially add each ID to the list
		if ($courseListOfIDs) { 
			$courseListOfIDs .= ',' . $courseID;
		} else {
			$courseListOfIDs = $courseID;
		}
		$data[] = $courseListOfIDs;
		
		// Not removing any courses
		$data[] = false;
		
		fputcsv($out, $data);
	}
		
	// All done
	fclose($out);
}


/**
 * Generates a verbose output of the gradebook data for a specific course.
 */
function WPCW_data_export_gradebookData()
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
		
	// #### 1 - See if the course exists first.
	$courseDetails = false;
	if (isset($_GET['course_id']) && $courseID = $_GET['course_id']) {
		$courseDetails = WPCW_courses_getCourseDetails($courseID);
	}
	
	// Course does not exist, simply output an error using plain text.
	if (!$courseDetails) 
	{
		header('Content-Type: text/plain');
		_e('Sorry, but that course could not be found.', 'wp_courseware');
		return;	
	}
	
	// #### 2 - Need a list of all quizzes for this course, excluding surveys.
	$quizzesForCourse = WPCW_quizzes_getAllQuizzesForCourse($courseDetails->course_id);	
	
	// Handle situation when there are no quizzes.
	if (!$quizzesForCourse) {
		header('Content-Type: text/plain');
		_e('There are no quizzes for this course, therefore no grade information to show.', 'wp_courseware');
		return;	
	}
	
	// Do we want certificates?
	$usingCertificates = ('use_certs' == $courseDetails->course_opt_use_certificate);
	
	// Create a simple list of IDs to use in SQL queries
	$quizIDList = array();
	foreach ($quizzesForCourse as $singleQuiz)  {
		$quizIDList[] = $singleQuiz->quiz_id;
	}
	
	// Convert list of IDs into an SQL list
	$quizIDListForSQL = '(' . implode(',', $quizIDList) . ')';
	
	// Course does exist, so now we really output the data
	WPCW_data_export_sendHeaders_CSV();
	
	// Start CSV
	$out = fopen('php://output', 'w');
	
	// #### 3 - The headings for the CSV data
	$headings = array(
		__('Name', 							'wp_courseware'), 
		__('Username', 						'wp_courseware'),
		__('Email Address', 				'wp_courseware'),
		__('Course Progress', 				'wp_courseware'),
		__('Cumulative Grade', 				'wp_courseware'),
		__('Has Grade Been Sent?', 			'wp_courseware'),
		
	);	
	
	// Check if we're using certificates or not.
	if ($usingCertificates) {
		$headings[] = __('Is Certificate Available?', 	'wp_courseware');
	}

	// #### 4 - Add the headings for the quiz titles.
	foreach ($quizzesForCourse as $singleQuiz)
	{
		$headings[] = sprintf('%s (quiz_%d)', $singleQuiz->quiz_title, $singleQuiz->quiz_id);
	}	
	
	// #### 6 - Render the headings
	fputcsv($out, $headings);
	
	// #### 7 - Select all users that exist for this course
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL			
		", $courseDetails->course_id);
	
	$userData = $wpdb->get_results($SQL);
	if (!$userData)
	{
		// All done
		fclose($out);
		return;
	}
	
	// #### 8 - Render the specific user details.
	foreach ($userData as $userObj)
	{
		$quizResults = WPCW_quizzes_getQuizResultsForUser($userObj->ID, $quizIDListForSQL);
				
		// Track cumulative data 
		$quizScoresSoFar = 0;
		$quizScoresSoFar_count = 0;
		
		// Track the quiz scores in order
		$thisUsersQuizData = array();
		
		// ### Now render results for each quiz
		foreach ($quizIDList as $aQuizID)
		{
			// Got progress data, process the result
			if (isset($quizResults[$aQuizID])) 
			{
				// Extract results and unserialise the data array.
				$theResults = $quizResults[$aQuizID];
				$theResults->quiz_data = maybe_unserialize($theResults->quiz_data);
				
				// We've got something that needs grading.
				if ($theResults->quiz_needs_marking > 0) {
					$thisUsersQuizData['quiz_' . $aQuizID] = __('Manual Grade Required', 'wp_courseware');
				}
				
				// No quizzes need marking, so show the scores as usual.
				else 
				{
					// Calculate score, and use for cumulative.
					$score = number_format($theResults->quiz_grade);
					$quizScoresSoFar += $score;

					$thisUsersQuizData['quiz_' . $aQuizID] = $score . '%';
						
					$quizScoresSoFar_count++;
				}
			} // end of quiz result check. 
			
			// No progress data - quiz not completed yet
			else {
				$thisUsersQuizData['quiz_' . $aQuizID] = __('Not Taken', 'wp_courseware');
			}
		}	
				
		$dataToOutput = array();
		
		// These must be in the order of the columns specified above for it all to match up.		
		$dataToOutput['name'] 				= $userObj->display_name;
		$dataToOutput['username'] 			= $userObj->user_login;		
		$dataToOutput['email_address'] 		= $userObj->user_email;
		
		// Progress Details
		$dataToOutput['course_progress']	= $userObj->course_progress . '%';
		$dataToOutput['cumulative_grade']	= ($quizScoresSoFar_count > 0 ? number_format(($quizScoresSoFar / $quizScoresSoFar_count), 1) . '%' : __('n/a', 'wp_courseware'));
		$dataToOutput['has_grade_been_sent'] = ('sent' == $userObj->course_final_grade_sent ? __('Yes', 'wp_courseware') : __('No', 'wp_courseware'));
			
		// Show if there's a certificate that can be downloaded.
		if ($usingCertificates)
		{
			$dataToOutput['is_certificate_available'] 	= __('No', 'wp_courseware');				
			if (WPCW_certificate_getCertificateDetails($userObj->ID, $courseDetails->course_id, false))
			{
				$dataToOutput['is_certificate_available'] = __('Yes', 'wp_courseware');
			} 
		}
		
		// Output the quiz summary here..
		$dataToOutput += $thisUsersQuizData;
		
		fputcsv($out, $dataToOutput);
	}
		
	// All done
	fclose($out);
	
}

?>