<?php


/**
 * The base quiz class.
 */
class WPCW_quiz_base
{
	/**
	 * Contains the raw information to render the quiz item.
	 * @var Object
	 */
	protected $quizItem;
	
	/**
	 * If true, then show errors on the form (effects frontend or backend)
	 * @var Boolean
	 */
	public $showErrors;
	
	/**
	 * If true, then show the user the correct answers.
	 * @var Boolean
	 */
	public $needCorrectAnswers;
	
	/**
	 * Contains any CSS classes to add to the rendered form.
	 * @var String
	 */
	public $cssClasses;	

	/**
	 * If true, then the form contains an error.
	 * @var Boolean
	 */
	public $gotError;
	
	/**
	 * Any extra quiz HTML to add before the closing section for the quiz.
	 * @var String
	 */
	public $extraQuizHTML;
	
	/**
	 * Stores the type of question as a simple string.
	 * @var String
	 */
	public $questionType;
	
	/**
	 * Default constructors
	 * @param Object $quizItem The quiz item details.
	 */
	function __construct($quizItem)
	{
		$this->quizItem 			= $quizItem;
		$this->showErrors 			= false;
		$this->needCorrectAnswers 	= false;
		$this->cssClasses			= false;
		$this->gotError				= false;
		$this->answerList			= false;
		$this->extraQuizHTML		= false;
	}
	
	
	/**
	 * Returns the buttons that can be used to control the quiz item.
	 */
	function getActionButtons($columnCount)
	{
		return sprintf('
			<tr>
				<td colspan="%d" class="wpcw_question_actions">
					<a href="#" class="wpcw_delete_icon" rel="%s">%s</a>
					<a href="#" class="wpcw_move_icon">%s</a>
				</td>
			</tr>
			', $columnCount, 		
			__('Are you sure you wish to delete this question?', 'wp_courseware'),
			__('Delete', 'wp_courseware'), 
			__('Move', 'wp_courseware')
			);
	}
	
	/**
	 * Output the form that allows questions to be configured.
	 */	
	function editForm_toString() {
		return false;
	}
	
	
	/**
	 * Create the form that the user can complete when completing their answers.
	 * 
	 * @param Object $parentQuiz The parent quiz object.
	 * @param Integer $questionNum The current question number.
	 * @param String $selectedAnswer If an answer is selected already, this is what's been selected.
	 * @param Boolean $showAsError If set to 'missing', field is missing. If set to 'wrong', then the answer is wrong.
	 * @param String $errorToShow Optional parameter which contains the error message if something went wrong.
	 */
	public function renderForm_toString($parentQuiz, $questionNum, $selectedAnswer, $showAsError, $errorToShow = false)
	{
		return $this->renderForm_toString_withClass($parentQuiz, $questionNum, $selectedAnswer, $showAsError, false, $errorToShow);
	}
	
	
	/**
	 * Create the form that the user can complete when completing their answers.
	 * 
	 * @param Object $parentQuiz The parent quiz object.
	 * @param Integer $questionNum The current question number.
	 * @param String $selectedAnswer If an answer is selected already, this is what's been selected.
	 * @param Boolean $showAsError If set to 'missing', field is missing. If set to 'wrong', then the answer is wrong.
	 * @param String $cssClass Extra CSS Classes to add to the wrapper
	 * @param String $errorToShow Optional parameter which contains the error message if something went wrong. 
	 */
	protected function renderForm_toString_withClass($parentQuiz, $questionNum, $selectedAnswer, $showAsError, $cssClass, $errorToShow = false)
	{
		$fieldID = sprintf('wpcw_fe_wrap_question_%d_%s_%d', $parentQuiz->quiz_id, $this->questionType, $this->quizItem->question_id);
		
		$html = false;
		$html .= sprintf('<div class="wpcw_fe_quiz_q_single %s%s" id="%s">', $cssClass, ($showAsError ? ' wpcw_fe_quiz_q_error' : ''), $fieldID);
		
			// Is the answer wrong?
			$wrongAnswerState = ('wrong' == $showAsError);
		
			// Question title
			$html .= sprintf('<div class="wpcw_fe_quiz_q_title">%s #%d - %s%s</div>', 
				__('Question', 'wp_courseware'),
				$questionNum,
				$this->quizItem->question_question,
				($wrongAnswerState ? '<span class="wpcw_fe_quiz_status">(' . __('Incorrect', 'wp_courseware') . ')</span>' : '')
			);
			
			// Got an error? Show the error just beneath the question, before the entry section.
			if ($showAsError && $errorToShow) {
				$html .= sprintf('<div class="wpcw_fe_quiz_q_single_error">%s</div>', $errorToShow);
			}
			
			// Got any extra HTML to add?
			if ($this->extraQuizHTML) {
				$html .= $this->extraQuizHTML; 
			}					
						
			// Render the list of answers if we have any as radio items.
			$html .= $this->renderForm_toString_answerList($parentQuiz, $questionNum, $selectedAnswer, $showAsError, $cssClass);
			
		
		$html .= '</div>';
		return $html;
	}
	
	
	/**
	 * Handle the rendering of the list of answers to choose from (used by T/F questions and multiple answers).
	 * 
	 * @param Object $parentQuiz The parent quiz object.
	 * @param Integer $questionNum The current question number.
	 * @param String $selectedAnswer If an answer is selected already, this is what's been selected.
	 * @param Boolean $showAsError If set to 'missing', field is missing. If set to 'wrong', then the answer is wrong.
	 * @param String $cssClass Extra CSS Classes to add to the wrapper
	 */
	protected function renderForm_toString_answerList($parentQuiz, $questionNum, $selectedAnswer, $showAsError, $cssClass)
	{
		$html = false;
		
		// This is done for T/F and Multiple Choice Questions
		if ($this->answerList)
		{	
			// Creating a list using <UL> rather than tables for simplicity. Should render
			// fine on nearly all browsers/themes.			
			$html .= '<ul class="wpcw_fe_quiz_q_answers">';
			
			foreach ($this->answerList as $answerItem => $answerValue)
			{
				// Generate the ID of the field, also used for the CSS ID
				$fieldID = sprintf('question_%d_%s_%d', $parentQuiz->quiz_id, $this->questionType, $this->quizItem->question_id);					
				
				$html .= sprintf('<li><input type="radio" name="%s" id="%s" value="%s" %s> <label for="%s">%s</label></li>',
					 $fieldID, $fieldID,
					 $answerValue,
					 ($selectedAnswer == $answerValue ? 'checked="checked"' : false), // Mark the correct item as checked
					 $fieldID,
					 $answerItem
				); 
			}
			
			$html .= '</ul>';
		}
			
		return $html;
	}
	
	
	/**
	 * Clean the answer data and return it to the user.
	 * Designed to be overridden by child classes to add class-specific functionality.
	 * 
	 * @param String $rawData The data that's being cleaned.
	 * @return String The cleaned data.
	 */
	public static function sanitizeAnswerData($rawData)
	{
		return false;
	}
}



?>