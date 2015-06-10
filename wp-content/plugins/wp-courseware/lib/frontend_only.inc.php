<?php
/**
 * Only show code on the frontend of the website.
 */

/**
 * Handle showing the box that allows a user to mark a unit as completed.
 */
function WPCW_units_processUnitContent($content)
{
	// #### Ensure we're only showing a course unit, a single item	
	if (!is_single() || 'course_unit' !=  get_post_type()) {
		return $content;
	}
	
	global $post;
	
	// #### Get associated data for this unit. No course/module data, not a unit
	$parentData = WPCW_units_getAssociatedParentData($post->ID);
	if (!$parentData) {
		return $content;
	}
	
	// #### Ensure we're logged in	
	$user_id = get_current_user_id();
	if ($user_id == 0) { 
		// Return no access error without content
		return WPCW_units_createErrorMessage($parentData->course_message_unit_not_logged_in);
	}
	
	$userProgress = new UserProgress($parentData->course_id, $user_id);
		
	
	// #### User not allowed access to content, so certainly can't say they've done this unit.
	if (!$userProgress->canUserAccessCourse())
	{
		// Return no access error without content
		return WPCW_units_createErrorMessage($parentData->course_message_unit_no_access);
	}
	
	// #### Is user allowed to access this unit yet?
	if (!$userProgress->canUserAccessUnit($post->ID)) 
	{
		// Return no access error without content
		return WPCW_units_createErrorMessage($parentData->course_message_unit_not_yet);
	}
	
	// #### Determine if this unit has been completed or not.
	$progressDetails = WPCW_units_getUserUnitDetails($user_id, $post->ID);
	$completed = ($progressDetails && $progressDetails->unit_completed_status == 'complete');
	
	
	// #### Show completion box
	if ($completed) {
		$completionBox = WPCW_units_getCompletionBox_complete($parentData, $post->ID, $user_id);
	}	
	
	// Not completed
	else {
		$completionBox = WPCW_units_getCompletionBox_pending($parentData, $post->ID);
	}	
	
	// #### Show the navigation box (disable the next item if not completed yet).
	$navigationBox = WPCW_units_getNavigationBox($parentData, $post->ID, $userProgress, (!$completed));

	return $content . $completionBox . $navigationBox;
}



/**
 * Create a simple error if something goes wrong. 
 */
function WPCW_units_getCompletionBox_error()
{
	return sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_error">%s</div></div>', 
		__('An error occured, so your unit progress was not updated. Please refresh the page and try again.', 'wp_courseware')
	);
}

/**
 * Creates a box to show that a unit is currently pending.
 * 
 * @param Object $parentData A copy of the parent course and module details for this unit.
 * @param Integer $unitID The ID of the unit that's currently pending.
 */
function WPCW_units_getCompletionBox_pending($parentData, $unitID)
{
	// See if we have a quiz for this unit? If so, render it and allow the trainee to complete it.	
	$quizDetails = WPCW_quizzes_getAssociatedQuizForUnit($unitID);
	$html = false;
	
	
	// See if we have a quiz for this unit, if we do, see if the user has completed it or not. 
	if ($quizDetails && $quizDetails->questions && count($quizDetails) > 0)
	{
		// Got the user progress, determine if it's pending marking or not. 
		$quizProgress = WPCW_quizzes_getUserResultsForQuiz(get_current_user_id(), $unitID, $quizDetails->quiz_id);
		
		if ($quizProgress && 'retake_quiz' == $quizProgress->quiz_next_step_type)
		{
			// Show a generic message that the quiz needs to be re-taken.
			$messageToShow =  wpautop(__('The course instructor has required that you retake this quiz.'));
			$messageToShow .= '<p>' . sprintf(__('Your previous grade was: <b>%s</b>'), number_format($quizProgress->quiz_grade, 1) . '%') . '</p>'; 
						
			// Add the custom message if there was one, which is personalised from the instructor.
			if ($quizProgress->quiz_next_step_msg) {
				$messageToShow .= wpautop($quizProgress->quiz_next_step_msg);
			}
						
			$html .= WPCW_units_createWarningMessage($messageToShow);
		}

		
		// User has completed this quiz, so we need to indicate if it's been marked or not. If it's not been marked
		// we show a message saying they are blocked until it's marked.
		if ($quizProgress && $quizProgress->quiz_needs_marking > 0) 
		{
			// Blocking quiz - show a status message saying that they can't continue until the quiz is graded. 
			if ('quiz_block' == $quizDetails->quiz_type) {
				$html .= WPCW_units_createSuccessMessage($parentData->course_message_quiz_open_grading_blocking);
			} 			
		}
		
		// No quiz progress, so show the quiz to be rendered for completion by the user.
		else {
			$html .= WPCW_quizzes_handleQuizRendering($unitID, $quizDetails);
		}
	}
	
	// Manually mark the unit as complete as there are no quiz questions.
	else 
	{
		// Render the message
		$html .= sprintf('
			<div class="wpcw_fe_progress_box_wrap" id="wpcw_fe_unit_complete_%d">
				<div class="wpcw_fe_progress_box wpcw_fe_progress_box_pending wpcw_fe_progress_box_updating">
					<div class="wpcw_fe_progress_box_mark">
						<img src="%s/ajax_loader.gif" class="wpcw_loader" style="display: none;" />
						<a href="#" class="fe_btn fe_btn_completion btn_completion" id="unit_complete_%d">%s</a>
					</div>
				%s</div>
			</div>', 
				$unitID, 
				WPCW_plugin_getPluginPath() . 'img',
				$unitID, __('Mark as Completed', 'wp_courseware'), 
				$parentData->course_message_unit_pending
			);
	}
		
	return $html;
}


/**
 * Shows the quiz for the unit, based on the type being shown.
 * 
 * @param Integer $unitID The ID of the unit that's currently pending.
 * @param Object $quizDetails The details of the quiz to show.
 * @param Array $resultsList The list of checked answers, wrong answers, and error messages for the quiz form.
 * 
 * @return String The HTML that renders the quiz for answering.
 */
function WPCW_quizzes_handleQuizRendering($unitID, $quizDetails, $resultsList = false)
{
	// Hopefully not needed, but just in case.
	if (!$quizDetails) {
		return false;
	}
	
	// Create an array so that we can use isset($answerList['key']).
	if (!$resultsList) 
	{
		$resultsList = array(
			'answer_list'		=> array(),
			'wrong_answer_list'	=> array(),
			'error_answer_list'	=> array(),
		);
	}
	
	
	// Render the wrapper for the quiz using the pending message section
	// Use the Quiz ID and Unit ID to validate permissions.
	$html = sprintf('<div class="wpcw_fe_quiz_box_wrap" id="wpcw_fe_quiz_complete_%d_%d">', $unitID, $quizDetails->quiz_id);
			
		// enctype="multipart/form-data" for file uploads.
		$html .= sprintf('<form method="post" enctype="multipart/form-data" id="quiz_complete_%d_%d">', $unitID, $quizDetails->quiz_id); 
		$html .= '<div class="wpcw_fe_quiz_box wpcw_fe_quiz_box_pending">';
		
			// #### 1 - Quiz Title - constant for all quizzes
			$html .= sprintf('<div class="wpcw_fe_quiz_title">%s</div>', $quizDetails->quiz_title);
			
			// #### 2 - Pass mark - just needed for blocking quizes
			if ('quiz_block' == $quizDetails->quiz_type)
			{
				$totalQs = count($quizDetails->questions);
				$passQs  = ceil(($quizDetails->quiz_pass_mark / 100) * $totalQs);
			
				$html .= '<div class="wpcw_fe_quiz_pass_mark">';
				$html .= sprintf(__('You\'ll need to correctly answer at least <b>%d of the %d</b> questions below (<b>at least %d%%</b>) to progress to the next unit.', 'wp_courseware'),
							$passQs, $totalQs, $quizDetails->quiz_pass_mark);
				$html .= '</div>';
			}
			
			// Header before questions
			$html .= '<div class="wpcw_fe_quiz_q_hdr"></div>';
			
			
			// #### 3 - The actual question form.
			if ($quizDetails->questions && count($quizDetails->questions) > 0)
			{
				$questionNum = 1;
				
				foreach ($quizDetails->questions as $question) 
				{
					// See if we want to show an answer
					$selectAnswer = false;
					if (isset($resultsList['answer_list'][$question->question_id])) {
						$selectAnswer = $resultsList['answer_list'][$question->question_id];
					}
					
					switch ($question->question_type)
					{
						case 'multi':
							$quObj = new WPCW_quiz_MultipleChoice($question);
							break;
							
						case 'open':					
							$quObj = new WPCW_quiz_OpenEntry($question);
							break;
							
						case 'upload':
							$quObj = new WPCW_quiz_FileUpload($question);
							break;
							
						case 'truefalse':
							$quObj = new WPCW_quiz_TrueFalse($question);
							break;
							
							
						// Not expecting anything here... so not handling the error case.
						default:					
							break;
					}
										
					// Use the objects to render the questions, showing an answer as wrong if appropriate.
					$showAsError = false;
					
					// Only worry about errors if actual data has been submitted.
					$errorToShow = false;
					if (isset($_POST['submit']))
					{
						// Something went wrong
						if (isset($resultsList['error_answer_list'][$question->question_id]))
						{
							$errorToShow = $resultsList['error_answer_list'][$question->question_id]; 
							$showAsError = 'error';														
						}
						
						// No answer yet
						else if (!isset($resultsList['answer_list'][$question->question_id])) {
							$showAsError = 'missing';
						}
						
						// Answer is wrong
						else if (isset($resultsList['wrong_answer_list'][$question->question_id])) {
							$showAsError = 'wrong';	
						}
					}
					
					// Use object to render
					$html .= $quObj->renderForm_toString($quizDetails, $questionNum++, $selectAnswer, $showAsError, $errorToShow);

				}				
			} 			
			
			// #### 4 - The submit answers button. //<a href="#" class="fe_btn fe_btn_completion btn_completion" id="quiz_complete_%d_%d">%s</a>
			$html .= sprintf('<div class="wpcw_fe_quiz_submit">
	
						<div class="wpcw_fe_upload_progress">
					        <div class="wpcw_fe_upload_progress_bar"></div>
					        <div class="wpcw_fe_upload_progress_percent">0%%</div>
					    </div>
					    
						<div class="wpcw_fe_submit_loader wpcw_loader">
							<img src="%simg/ajax_loader.gif" />
						</div>
						
						<input type="submit" class="fe_btn fe_btn_completion btn_completion" name="submit" value="%s">						
					</div>', 
					
					WPCW_plugin_getPluginPath(), 					
					__('Submit Answers', 'wp_courseware')
				);	
	
		$html .= '</div>'; // .wpcw_fe_quiz_box
		$html .= '</form>'; 
	$html .= '</div>'; // .wpcw_fe_quiz_box_wrap
	
	return $html;
}



/**
 * Having entered some details into the quiz, may the user progress to the next unit? If
 * there are any problems with the quiz, then they are dealt with via AJAX.
 * 
 * @param Object $quizDetails The potential quiz details.
 * @param Array $potentialAnswers The potential answers that need checking. 
 * @param Integer $userID The ID of the user that we're saving progress for.
 * 
 * @return Boolean True if the user may progress, false otherwise.
 */
function WPCW_quizzes_handleQuizRendering_canUserContinueAfterQuiz($quizDetails, $potentialAnswers, $userID)
{
	$resultsList = array(
		'answer_list'		=> array(),
		'wrong_answer_list'	=> array(),
		'error_answer_list'	=> array(),
	);
	
	$resultDetails = array(
		'correct'	=> array(),
		'wrong'		=> array()
	);
	
	
	// #### 1A Extract a list of actual answers from the potential answers. There will
	// be some noise in that data.
	foreach ($potentialAnswers as $key => $value)
	{
		// Only considering answers to questions. Format of answer field is:
		// question_16_truefalse_48 (first ID is quiz, 2nd ID is question, middle string
		// is the question type.
		if (preg_match('/^question_(\d+)_([a-z]+)_(\d+)$/', $key, $matches)) 
		{
			$quizID     	= $matches[1];
			$questionID 	= $matches[3];
			$questionType 	= $matches[2];
			
			// Again, check that answer matches quiz we're expecting.
			// Probably a little paranoid, but it's worth checking
			// to ensure there's nothing naughty going on.			
			if ($quizID != $quizDetails->quiz_id) {
				continue;  
			}
			
			// Clean up the submitted data based on the type of quiz using the static checks in each 
			// of the questions (to save loading whole class). If the data is valid, add the valid 
			// answer to the list of fully validate danswers.
			switch ($questionType)
			{
				case 'multi':
						$resultsList['answer_list'][$questionID] = WPCW_quiz_MultipleChoice::sanitizeAnswerData($value);
					break;
			
				case 'truefalse':
						$resultsList['answer_list'][$questionID] = WPCW_quiz_TrueFalse::sanitizeAnswerData($value);
					break;
					
				case 'open':
						$resultsList['answer_list'][$questionID] = WPCW_quiz_OpenEntry::sanitizeAnswerData($value);
					break;

				// Ignore uploads as a $_POST field, simply because the files should be stored in $_FILES
				// not in $_POST. So if we have a file in $_FILES, that's clearly an issue.
				case 'upload':					
					break;
			}

		} // end of question check
	} // end of potential answers loop
	

	
	// ### 1B Check for file uploads if the quiz requires them. Only check for uploads
	// if the quiz details say there should be some uploads.
	if ($quizDetails->want_uploads)
	{
		$uploadResultList = WPCW_quiz_FileUpload::validateFiles($_FILES, $quizDetails);
		
		// Merge the valid results.
		// Basically if a file has been uploaded correctly, that answer is marked as being set.
		if (count($uploadResultList['upload_valid']) > 0) {			
			$resultsList['answer_list'] = $resultsList['answer_list'] + $uploadResultList['upload_valid'];
		}
		
		// Merge the error results
		if (count($uploadResultList['upload_errors']) > 0) {
			$resultsList['error_answer_list'] = $resultsList['error_answer_list'] + $uploadResultList['upload_errors'];
		}
	}
		
	// ### 2 - Check that we have enough answers given how many questions there are.
	// If there are not enough answers, then re-render the form with the answered questions 
	// marked, and highlight the fields that have errors.
	if ($quizDetails->questions && count($resultsList['answer_list']) < count($quizDetails->questions)) 
	{
		// Error - not all questions are answered
		echo WPCW_units_createErrorMessage(__('Please provide an answer for all of the questions.', 'wp_courseware'));
		
		// Show the form with the questions that have been completed already.
		echo WPCW_quizzes_handleQuizRendering($quizDetails->parent_unit_id, $quizDetails, $resultsList);
		
		// User may not continue - as quiz is not complete.
		return false; 
	}	
	
	// Flag to indicate if grading is needed before the user continues.
	$gradingNeeded = false;
	$gradingNeededBeforeContinue = false;
	
	// ### 3 - Do we need to check for correct answers?
	if ('survey' == $quizDetails->quiz_type)
	{
		// Never try to show answers. There aren't any.
		$quizDetails->quiz_show_answers = 'hide_answers'; 

		// No answers to check. Say thanks
		echo WPCW_units_createSuccessMessage(__('Thank you for your responses. This unit is now complete.', 'wp_courseware'));
	}
	
	// #### Quiz Mode - so yes, we do check for correct answers.
	// We should answers for all questions by this point.
	else 
	{
		$resultDetails = WPCW_quizzes_checkForCorrectAnswers($quizDetails, $resultsList['answer_list']);
		
		
		// #### Step A - have open-ended questions that need marking.
		if (!empty($resultDetails['needs_marking']))
		{ 
			$gradingNeeded = true;
			$courseDetails = WPCW_courses_getCourseDetails($quizDetails->parent_course_id);
			
			// Non-blocking quiz - so allowed to continue, but will be graded later. 
			if ('quiz_noblock' == $quizDetails->quiz_type) {
				echo WPCW_units_createSuccessMessage($courseDetails->course_message_quiz_open_grading_non_blocking);
			} 
			
			// Blocking quiz - not allowed to continue until the work is graded.
			else {
				echo WPCW_units_createSuccessMessage($courseDetails->course_message_quiz_open_grading_blocking);
				
				// Grading is needed before they continue, but don't want them to re-take the quiz.
				$gradingNeededBeforeContinue = true;
			}
		}
		
		// #### Step B - we have no-open-ended questions, just T/F or Multiple-choice
		else 
		{
			// Copy over the wrong answers.
			$resultsList['wrong_answer_list'] = $resultDetails['wrong'];
			
			// Some statistics
			$correctCount 		= count($resultDetails['correct']); 
			$totalQuestions    	= count($quizDetails->questions);		
			$correctPercentage  = number_format(($correctCount / $totalQuestions)*100, 1);
			
			// Non-blocking quiz. 
			if ('quiz_noblock' == $quizDetails->quiz_type)
			{	
				// Store user quiz results  
				echo WPCW_units_createSuccessMessage(sprintf(__('You got <b>%d out of %d (%d%%)</b> questions correct! This unit is now complete.', 'wp_courseware'),
						$correctCount, $totalQuestions, $correctPercentage
					));
					
				// Notify the user of their grade.
				do_action('wpcw_quiz_graded', $userID, $quizDetails, $correctPercentage, false);
			} 
			
			// Blocking quiz (quiz_type == quiz_block).
			else 
			{
				$minPassQuestions = $totalQuestions * ($quizDetails->quiz_pass_mark / 100);
				
				// They've passed. Report how many they got right. 
				if ($correctPercentage >= $quizDetails->quiz_pass_mark)
				{
					echo WPCW_units_createSuccessMessage(sprintf(__('You got <b>%d out of %d (%d%%)</b> questions correct! This unit is now complete.', 'wp_courseware'),
						$correctCount, $totalQuestions, $correctPercentage
					));
					
					
					// Notify the user of their grade.
					do_action('wpcw_quiz_graded', $userID, $quizDetails, $correctPercentage, false);
				}
				
				// They've failed. Report which questions are correct or incorrect.
				else 
				{
					echo WPCW_units_createErrorMessage(sprintf(__('Unfortunately, you only got <b>%d out of %d (%d%%)</b> questions correct. You need to correctly answer <b>at least %d questions (%d%%)</b>.', 'wp_courseware'),
						$correctCount, $totalQuestions, $correctPercentage,
						$minPassQuestions, $quizDetails->quiz_pass_mark
					));
					
					// Show form with error answers.
					echo WPCW_quizzes_handleQuizRendering($quizDetails->parent_unit_id, $quizDetails, $resultsList);
					
					// Errors, so the user cannot progress yet.
					return false;
				}
				
			} // end of blocking quiz check
		}
		
		
	}	// end of survey check
	

	// ### 4 - Show the correct answers to the user? 
	if ('show_answers' == $quizDetails->quiz_show_answers) {
		echo WPCW_quizzes_showAllCorrectAnswers($quizDetails);
	}	

	
	// ### 5 - Save the user progress 
	WPCW_quizzes_saveUserProgress($userID, $quizDetails, $resultDetails, $resultsList['answer_list']);
	
	// Questions need grading, notify the admin
	if ($gradingNeeded)
	{
		// Notify the admin that questions need answering.
		do_action('wpcw_quiz_needs_grading', $userID, $quizDetails);
	}
	
	// Questions need grading, so don't allow user to continue
	if ($gradingNeededBeforeContinue) {
		return false;
	}

	
	// If we get this far, the user may progress to next unit
	return true;
}


/**
 * Save the user's quiz progress to the database using their verbose answers.
 * 
 * @param Integer $userID The ID of the user completing the quiz.
 * @param Array $quizDetails The details of the quiz that's been completed.
 * @param Array $resultDetails The full result details for the user who's completed the quiz.
 * @param Array $checkedAnswerList The full list of checked answers.
 */
function WPCW_quizzes_saveUserProgress($userID, $quizDetails, $resultDetails, $checkedAnswerList)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	$data = array();
	$data['quiz_correct_questions'] = count($resultDetails['correct']);
	$data['quiz_question_total'] 	= count($quizDetails->questions);
	$data['quiz_completed_date']  	= current_time('mysql');
	$data['user_id'] 				= $userID;
	$data['unit_id'] 				= $quizDetails->parent_unit_id;
	$data['quiz_id'] 				= $quizDetails->quiz_id;
	
	// Store which questions need marking
	$data['quiz_needs_marking'] 		= false;
	$data['quiz_needs_marking_list'] 	= false;

	
	// Generate a full list of the quiz and the answers given.
	$fullDetails = array(); 
	foreach ($quizDetails->questions as $singleQuestion)
	{
		$qItem = array();				
		$qItem['title'] = $singleQuestion->question_question;

		$openEndedQuestion = false;
		
		// We know we have enough answers at this point, so know
		switch ($singleQuestion->question_type)
		{
			// There's definitely a right or wrong answer, so determine that now.
			case 'truefalse':
			case 'multi':
					$qItem['their_answer'] 	= WPCW_quizzes_getCorrectAnswer($singleQuestion, $checkedAnswerList[$singleQuestion->question_id]);			
				break;
				
			// Uploaded files and open-ended questions need marking, so it's just their raw answer.
			case 'upload':
			case 'open':
					$openEndedQuestion = true;
					$qItem['their_answer'] 	= $checkedAnswerList[$singleQuestion->question_id];
				break;
		}
		
		
		// If a survey, there are no correct answers.
		if ('survey' == $quizDetails->quiz_type || $openEndedQuestion)
		{
			$qItem['correct']   = false;
			$qItem['got_right'] = false;
		}
		
		// Normal quiz with multiple-choice.
		else 
		{		
			// Get the correct answer
			$qItem['correct'] 		= WPCW_quizzes_getCorrectAnswer($singleQuestion);
			
			// Did the answers match?
			$qItem['got_right']		= ($qItem['their_answer'] == $qItem['correct'] ? 'yes' : 'no');				
		}
		
		$fullDetails[$singleQuestion->question_id] = $qItem;
	}
	
	// Track how many questions need marking 
	$data['quiz_needs_marking'] 		= count($resultDetails['needs_marking']);
	$data['quiz_needs_marking_list'] 	= serialize(array_keys($resultDetails['needs_marking'])); // Only store the IDs.
	
	
	// Use serialised data for storing full results.
	$data['quiz_data'] 	= serialize($fullDetails);
	$data['quiz_grade'] = WPCW_quizzes_calculateGradeForQuiz($fullDetails, $data['quiz_needs_marking']);
	
	
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_progress_quiz
		WHERE user_id = %d
		  AND unit_id = %d
		  AND quiz_id = %d
		ORDER BY quiz_attempt_id DESC 
		LIMIT 1
	", $userID, $quizDetails->parent_unit_id, $quizDetails->quiz_id);
		
	// Already exists, so increment the quiz_attempt_id
	// If it doesn't exist, we'll just use the database default of 0.
	if ($existingProgress = $wpdb->get_row($SQL))
	{
		// Mark all previous progress items as not being the latest.
		$SQL = $wpdb->prepare("
			UPDATE $wpcwdb->user_progress_quiz
			SET quiz_is_latest = ''
			WHERE user_id = %d
			  AND unit_id = %d
			  AND quiz_id = %d
		", $userID, $quizDetails->parent_unit_id, $quizDetails->quiz_id);
		$wpdb->query($SQL);
		
		// Ensure this is marked as the latest.
		$data['quiz_is_latest']  = 'latest';
		$data['quiz_attempt_id'] = $existingProgress->quiz_attempt_id + 1;
	}
	
	$SQL = arrayToSQLInsert($wpcwdb->user_progress_quiz, $data);
	$wpdb->query($SQL);
}


/**
 * Render all of the correct answers for the user.
 * @param Object $quizDetails The details of the quiz to show to the user.
 * 
 * @param String The correct answers as HTML.
 */
function WPCW_quizzes_showAllCorrectAnswers($quizDetails)
{
	// Hopefully not needed, but just in case.
	if (!$quizDetails) {
		return false;
	}
	
	// Create a simple DIV wrapper for the correct answers.
	$html = '<div class="wpcw_fe_quiz_box_wrap wpcw_fe_quiz_box_full_answers">';
			
		$html .= '<div class="wpcw_fe_quiz_box wpcw_fe_quiz_box_pending">';
		
			// #### 1 - Quiz Title - constant for all quizzes
			$html .= sprintf('<div class="wpcw_fe_quiz_title"><b>%s</b> %s</div>', __('Correct Answers for: ', 'wp_courseware'), $quizDetails->quiz_title);
			
			// Header before questions
			$html .= '<div class="wpcw_fe_quiz_q_hdr"></div>';			
			
			// #### 3 - The actual question form.
			if ($quizDetails->questions && count($quizDetails->questions) > 0)
			{
				$questionNum = 1;
				
				foreach ($quizDetails->questions as $question) 
				{
					$html .= '<div class="wpcw_fe_quiz_q_single">';
	
					// Question title
					$html .= sprintf('<div class="wpcw_fe_quiz_q_title">%s #%d - %s</div>', 
						__('Question', 'wp_courseware'),
						$questionNum++,
						$question->question_question
					);
					
					// Work out the correct answer
					$correctAnswer = WPCW_quizzes_getCorrectAnswer($question);
					
					// Answer
					$html .= sprintf('<div class="wpcw_fe_quiz_q_correct"><b>%s:</b>&nbsp;&nbsp;%s</div>', 
						__('Correct Answer', 'wp_courseware'),
						$correctAnswer
					);
					
					$html .= '</div>'; // wpcw_fe_quiz_q_single
				}				 
			} 			
				
		$html .= '</div>'; // .wpcw_fe_quiz_box 
	$html .= '</div>'; // .wpcw_fe_quiz_box_wrap
	
	return $html;
}

/**
 * Get the correct answer for a question.
 * 
 * @param Object $question The question object.
 * @param Mixed $providedAnswer If specified, use this to specify the correct answer. Otherwise use the correct answer for the question.
 * 
 * @return String The answer for the question.
 */
function WPCW_quizzes_getCorrectAnswer($question, $providedAnswer = false)
{
	$correctAnswer = false;
	if (!$providedAnswer) {
		$providedAnswer = $question->question_correct_answer;
	}
	
	// Just use true or false if a t/f question
	if ('truefalse' == $question->question_type) {
		$correctAnswer = ucwords($providedAnswer);
	} 
	
	// Multiple choice question - so turn list of answers into array
	// then use 1-indexing to get the text of the result.
	else 
	{
		$answerListRaw = explode("\n", $question->question_answers);
		$answerIdx = $providedAnswer - 1;
		
		// Just double check that the selected answer is valid.
		if ($answerIdx >= 0 && $answerIdx < count($answerListRaw)) {
			$correctAnswer = $answerListRaw[$answerIdx];				
		}
	}
	
	return $correctAnswer;
}

/**
 * Checks all of the answers against the list of questions and return which answers are right or wrong.
 * 
 * @param Object $quizDetails The quiz details to check.
 * @param Array $checkedAnswerList The answers to check.
 * 
 * @return Array Lists of the correct and wrong answers. (correct => Array, wrong => Array, 'needs_marking' => Array)
 */
function WPCW_quizzes_checkForCorrectAnswers($quizDetails, $checkedAnswerList)
{
	$resultDetails = array(
		'correct'		=> array(),
		'wrong'			=> array(),
		'needs_marking' => array(),
	); 
	
	// Run through questions, and check each one for correctness.
	foreach ($quizDetails->questions as $questionID => $question)
	{
		// Got to check the question type, as can't automatically score the open-ended question types.
		switch ($question->question_type)
		{
			case 'truefalse':
			case 'multi':
					// If the answer is correct, add the question and answer to the correct list;
					if ($checkedAnswerList[$questionID] == $question->question_correct_answer) {
						$resultDetails['correct'][$questionID] = $checkedAnswerList[$questionID];
					} 
					// Answer is wrong, so add to wrong list.
					else {
						$resultDetails['wrong'][$questionID] = $checkedAnswerList[$questionID];
					}				
				break;
				
			// Uploaded files and open-ended questions need marking
			case 'upload':
			case 'open':
					$resultDetails['needs_marking'][$questionID] = $checkedAnswerList[$questionID];
				break;
		}
	}
	
	return $resultDetails;
}


/**
 * Creates a box to show that a unit has been completed.
 * 
 * @param Object $parentData A copy of the parent course and module details for this unit.
 * @param Integer $unitID The ID of the unit that's been completed.
 * @param Integer $user_id The ID of the user who has just completed the unit.
 */
function WPCW_units_getCompletionBox_complete($parentData, $unitID, $user_id)
{	
	// Work out if course completed.
    $userProgress = new UserProgress($parentData->course_id, $user_id);
    $html = false;
    
    // Unit and course is complete, so show course complete message.
	if ($userProgress->isCourseCompleted()) 
	{
		$certHTML = false;		
		$certificateDetails = WPCW_certificate_getCertificateDetails($user_id, $parentData->course_id);
		
		// Generate certificate button if enabled and a certificate exists for this user.
		if ('use_certs' == $parentData->course_opt_use_certificate && $certificateDetails)
		{		
			$certHTML = sprintf('<div class="wpcw_fe_progress_box_download"><a href="%s" class="fe_btn fe_btn_download">%s</a></div>', 
				WPCW_certificate_generateLink($certificateDetails->cert_access_key), __('Download Certificate', 'wp_courseware')
			);
		}
				
		// Course completion message
		$html .= sprintf('<div class="wpcw_fe_progress_box_wrap">
			<div class="wpcw_fe_progress_box wpcw_fe_progress_box_complete">%s%s</div>
		</div>', $certHTML, $parentData->course_message_course_complete);
	
	}
	
	// Unit is complete, but course isn't
	else
	{
		$html = sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_complete">%s</div></div>', 
			$parentData->course_message_unit_complete
		);	
	}
	
	return $html;
}


/**
 * Creates a box to show that a unit has been completed.
 * 
 * @param Object $parentData A copy of the parent course and module details for this unit.
 * @param Integer $unitID The ID of this unit. 
 * @param Object $userProgress Unit progress information.
 * @param Boolean $disableNextButton If true, then disable the next button (as the user can't continue).
 * 
 * @return String The HTML for the navigation box.
 */
function WPCW_units_getNavigationBox($parentData, $unitID, $userProgress, $disableNextButton = false)
{
	$nextAndPrev = $userProgress->getNextAndPreviousUnit($unitID);
	$html = false;	
	
	if ($nextAndPrev['prev'] > 0) 
	{
		$html .= sprintf('<a href="%s" class="fe_btn fe_btn_navigation">&laquo; %s</a> ',
			get_permalink($nextAndPrev['prev']),
			__('Previous Unit', 'wp_courseware')
		);
	}
	
	if ($nextAndPrev['next'] > 0) 
	{
		// Replace the URL if we're disabling the next button
		//$urlToUse = ($disableNextButton ? '#' : get_permalink($nextAndPrev['next']));
		//($disableNextButton ? 'fe_btn_navigation_disabled' : ''), // Add a disabled class if not active.
		
		$html .= sprintf('<a href="%s" class="fe_btn fe_btn_navigation %s">%s &raquo;</a>',
			get_permalink($nextAndPrev['next']),
			false,
			__('Next Unit', 'wp_courseware')
		);
	}
	
	// Only return navigation if we have any links.
	if ($html) {	
		return sprintf('<div class="wpcw_fe_progress_box_wrap">
			<div class="wpcw_fe_navigation_box">
				%s
			</div>
		</div>', $html);
	}
	
	return false;
}


/**
 * Creates a box to show an error message
 * 
 * @param String $message The message to show.
 * @return String The error message as a formatted string.
 */
function WPCW_units_createErrorMessage($message)
{
	return sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_error">%s</div></div>', $message);
}


/**
 * Creates a box to show a success message
 * 
 * @param String $message The message to show.
 * @return String The success message as a formatted string.
 */
function WPCW_units_createSuccessMessage($message)
{
	return sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_success">%s</div></div>', $message);
}

/**
 * Creates a box to show an warning message
 * 
 * @param String $message The message to show.
 * @return String The success message as a formatted string.
 */
function WPCW_units_createWarningMessage($message)
{
	return sprintf('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_warning">%s</div></div>', $message);
}

/**
 * If the settings permit, generate the powered by link for WP Courseware.
 * @param Array $settings The list of settings from the database.
 * @return String The HTML for rendering the powered by link.
 */
function WPCW_generatedPoweredByLink($settings)
{
	// Show the credit link by default.
	if (isset($settings['show_powered_by']) && $settings['show_powered_by'] == 'hide_link') {
		return false;
	}
	
	$url = 'http://flyplugins.com/wp-courseware-premium-wordpress-plugin/';
	$nofollow = false;
	
	// Have we got a clickbank ID? If so, create an affiliate link
	if (isset($settings['clickbank_id']) && $settings['clickbank_id'])
	{
		$url = str_replace('XXX', $settings['clickbank_id'], 'http://XXX.flyplugins.hop.clickbank.net');
		$nofollow = 'rel="nofollow"';
	}
	
	return sprintf('<div class="wpcw_powered_by">%s <a href="%s" %s target="_blank">%s</a></div>',
		__('Powered By', 'wp_courseware'), 
		$url, $nofollow, 		
		__('WP Courseware', 'wp_courseware')
	);
}

?>