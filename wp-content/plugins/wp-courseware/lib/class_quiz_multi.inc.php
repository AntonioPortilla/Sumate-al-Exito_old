<?php

/**
 * The class that represents a true/false answer.
 */
class WPCW_quiz_MultipleChoice extends WPCW_quiz_base
{
	/**
	 * Default constructor
	 * @param Object $quizItem The quiz item details.
	 */
	function __construct($quizItem)
	{
		parent::__construct($quizItem);
		$this->questionType = 'multi';		
	}
	
	
	/**
	 * Output the form that allows questions to be configured.
	 */	
	function editForm_toString()
	{
		$answerList = false;
		if ($this->quizItem->question_answers) {
			$answerList = explode("\n", $this->quizItem->question_answers);
		}	
			
		$html = false;
		
		// Extra CSS for errors
		$errorClass_Question 	= false;	
		$errorClass_CorAnswer 	= false;
		
		// Error Check - Have we got an issue with a lack of question? 
		if ($this->showErrors) 
		{ 
			if (!$this->quizItem->question_question) {	
				$errorClass_Question = 'wpcw_quiz_missing';	
				$this->gotError = true;
			}
			if ($this->needCorrectAnswers && !$this->quizItem->question_correct_answer) {	
				$errorClass_CorAnswer = 'wpcw_quiz_missing';
				$this->gotError = true;	
			}
		}	
		
		// Track columns needed to show question details
		$columnCount = 3;
		
		
		// Render just the question area
		$html .= sprintf('<li id="wpcw_quiz_details_%s" class="%s"><table class="wpcw_quiz_details_questions_wrap" cellspacing="0">', $this->quizItem->question_id, $this->cssClasses);
			$html .= sprintf('<tr class="wpcw_quiz_row_question">');
			
			
				$html .= sprintf('<th class="%s">%s</th>', $errorClass_Question, __('Question', 'wp_courseware'));
				
				$html .= sprintf('<td class="%s">', $errorClass_Question);
					$html .= sprintf('<input type="text" name="question_question_%s" value="%s" />',	$this->quizItem->question_id, $this->quizItem->question_question);
					$html .= sprintf('<input type="hidden" name="question_type_%s" value="multi" />', 	$this->quizItem->question_id);
					
					// Field storing order of question among other questions
					$html .= sprintf('<input type="hidden" name="question_order_%s" value="%s" class="wpcw_question_hidden_order" />', 		
								$this->quizItem->question_id, 
								$this->quizItem->question_order + 0
							);				
					
				$html .= sprintf('</td>');
							
				// Only show column if need correct answers.
				if ($this->needCorrectAnswers) {
					$html .= sprintf('<td class="wpcw_quiz_details_tick_correct %s">%s</td>', $errorClass_CorAnswer, __('Correct<br/>Answer?', 'wp_courseware'));
					$columnCount++;
				}
				
				// Column for add/remove buttons
				$html .= '<td>&nbsp;</td>';						
				
			$html .= sprintf('</tr>');
		
	
		// Render the list of answers if we have any.	
		if ($answerList)
		{
			$count = 0;
			$odd = true;
			foreach ($answerList as $answerItem)
			{
				$answerItem = trim($answerItem);			
				$count++;
				
				// Show an error if the field is still blank.
				$errorClass_Answer = false;
				if ($this->showErrors) 
				{ 
					// Check that answer contains some characters.
					if (strlen($answerItem) == 0) {	
						$errorClass_Answer 	= 'wpcw_quiz_missing';	
						$this->gotError = true;
					}
				}	
				
				$html .= sprintf('<tr class="wpcw_quiz_row_answer %s">', $errorClass_Answer);
					$html .= sprintf('<th>%s <span>%d</span></th>', __('Answer', 'wp_courseware'), $count);
					$html .= sprintf('<td><input type="text" name="question_answer_%s[%d]" value="%s" /></td>', $this->quizItem->question_id, $count, $answerItem);
					
					// Correct answer column
					if ($this->needCorrectAnswers) 
					{
						$html .= sprintf('<td class="wpcw_quiz_details_tick_correct %s">
										<input type="radio" name="question_answer_sel_%s" value="%s" %s />
									  </td>', $errorClass_CorAnswer, $this->quizItem->question_id, $count, ($this->quizItem->question_correct_answer == $count ? 'checked="checked"' : false));
					}
					
					// Buttons for add/remove questions
					$html .= sprintf('
					<td class="wpcw_quiz_add_rem">
						<a href="#" title="%s" class="wpcw_question_add"><img src="%simg/icon_add_32.png" /></a>
						<a href="#" title="%s" class="wpcw_question_remove"><img src="%simg/icon_remove_32.png" /></a>
					</td>', 
						__('Add a new answer...', 'wp_courseware'), WPCW_plugin_getPluginPath(), 
						__('Remove this answer...', 'wp_courseware'), WPCW_plugin_getPluginPath()
					);
													
				$html .= sprintf('</tr>');
				
				$odd = !$odd;
			}
		}
	
	
		// Add icons for adding or removing a question.
		$html .= $this->getActionButtons($columnCount);

		// All done
		$html .= sprintf('</table></li>');
		
		return $html;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see WPCW_quiz_base::renderForm_toString()
	 */
	function renderForm_toString($parentQuiz, $questionNum, $selectedAnswer, $showAsError, $errorToShow = false)
	{
		$this->answerList = false;
		
		// Process all answers to give them an index. Count must be 1 indexed to avoid disappearing
		// due to 0 evaluating to false.
		$answerCount = 1;
		if ($this->quizItem->question_answers) 
		{
			$this->answerListRaw = explode("\n", $this->quizItem->question_answers);
			
			// Got answers, so break up into a list of answer => value 
			if ($this->answerListRaw) 
			{
				$this->answerList = array();
				foreach ($this->answerListRaw as $answerItem) {							
					$this->answerList[trim($answerItem)] = 'ans_' . $answerCount++;
				}
			}
		} // end of answer check
		
		// Handover to parent. All multiple choice answers are prefixed with 'ans_'.
		return parent::renderForm_toString_withClass($parentQuiz, $questionNum, 'ans_' . $selectedAnswer, $showAsError, 'wpcw_fe_quiz_q_multi', $errorToShow);
	}
	
	
	/**
	 * Extract the list of correct answers for a Multiple Choice question when saving changes to a question, 
	 * using the specified answer key to check $_POST.
	 * 
	 * @param String $answerListKey The key to use to extract the list of answers.
	 * @return String The list of answers, if found.
	 */
	public static function editSave_extractAnswerList($answerListKey)
	{
		$qAns = false;
		
		// ###  Get the list of answers if we have them			
		if (isset($_POST[$answerListKey]) && is_array($_POST[$answerListKey]))
		{
			// Validate each of the answers actually contain something, removing them if not.
			$qAns = $_POST[$answerListKey];  
			foreach ($qAns as $idx => $answer) 
			{
				// 2013-06-10 - Changed from (!trim($answer)) to if (strlen(trim($answer)) == 0) { to allow for
				// answers that are literally the number '0'.
				if (strlen(trim($answer)) == 0) {
					unset($qAns[$idx]);
				} 
				
				// Clean up each answer if slashes used for escape characters.
				else {
					$qAns[$idx] = stripslashes($answer);
				}
			} // end foreach
						
						
						
		} // end if answers are in an array

		
		// How many items are there in the list? None? Then make it false.
		if (count($qAns) == 0) {
			$qAns = false;
		}	

		return $qAns;
	}	
		
	
	/**
	 * Extract the correct answer for a Multiple Choice question, using the specified answer key to check $_POST.
	 * 
	 * @param String $correctAnswerKey The key to use to extract a correct answer.
	 * @param Array The list of questions to check that the correct answer falls into.
	 * 
	 * @return String The correct answer, if it was found.
	 */
	public static function editSave_extractCorrectAnswer($qAns, $correctAnswerKey)
	{
		$qAnsCor = false;
		
		// ### See if we have a correct answer, and it matches one of the items in the list.
		if (isset($_POST[$correctAnswerKey]) && preg_match('/^([0-9]+)$/', $_POST[$correctAnswerKey], $matches)) {
			$qAnsCor = $matches[1];
		}
		
		// No correct answer if no answers, or specified answer is not in list of potential
		// answers.
		if (!$qAnsCor || !$qAns || !isset($qAns[$qAnsCor])) {
			$qAnsCor = false;
		}
		
		return $qAnsCor;
	}

	/**
	 * Clean the answer data and return it to the user. Check for an answer that looks like ans_%d.
	 * 
	 * @param String $rawData The data that's being cleaned.
	 * @return String The cleaned data (just the index of the answer).
	 */
	public static function sanitizeAnswerData($rawData)
	{
		if (preg_match('/^ans_(\d+)$/', $rawData, $matches_a)) {
			return $matches_a[1];
		}
		
		return false;
	}
}



?>