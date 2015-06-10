<?php

/**
 * Page where the modules of a course can be ordered.
 */
function WPCW_showPage_CourseOrdering()
{
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Order Course Modules &amp; Units', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	$courseDetails = false;
	$courseID = false;
	
	// Trying to edit a course	
	if (isset($_GET['course_id'])) 
	{
		$courseID 		= $_GET['course_id'] + 0;
		$courseDetails 	= WPCW_courses_getCourseDetails($courseID);
	}
	
	// Abort if course not found.
	if (!$courseDetails)
	{		
		$page->showMessage(__('Sorry, but that course could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}	
	
	// Title of course being editied
	printf('<div id="wpcw_page_course_title"><span>%s</span> %s</div>', __('Editing Course:', 'wp_courseware'), $courseDetails->course_title);
	
	// Overall wrapper
	printf('<div id="wpcw_dragable_wrapper">');
	
	printf('<div id="wpcw_unassigned_wrapper" class="wpcw_floating_menu">');
	
		// ### Show a list of units that are not currently assigned to a module		
		printf('<div id="wpcw_unassigned_units" class="wpcw_unassigned">');	
		printf('<div class="wpcw_unassigned_title">%s</div>', __('Unassigned Units', 'wp_courseware'));
		
			printf('<ol class="wpcw_dragable_units_connected">');
			
			// Render each unit so that it can be dragged to a module. Still render <ol> list
			// even if there are no units to show so that we can drag units into unassociated list.
			$units = WPCW_units_getListOfUnits(0);
			if ($units)
			{
				foreach ($units as $unassUnit)
				{
					// Has unit got any existing quizzes?
					$existingQuiz = false;
					$quizObj = WPCW_quizzes_getAssociatedQuizForUnit($unassUnit->ID);
					if ($quizObj) {
						$existingQuiz = sprintf('<li id="wpcw_quiz_%d" class="wpcw_dragable_quiz_item">
								<div>%s (ID: %d)
								<div class="wpcw_quiz_des">%s</div>
							</li>', 
							$quizObj->quiz_id, $quizObj->quiz_title, $quizObj->quiz_id, $quizObj->quiz_desc
						);
					}					
					
					printf('<li id="wpcw_unit_%d" class="wpcw_dragable_unit_item">						
						<div>%s (ID: %d)</div>
						<div class="wpcw_dragable_quiz_holder"><ol class="wpcw_dragable_quizzes_connected wpcw_one_only">%s</ol></div>
					</li>', 
					$unassUnit->ID, $unassUnit->post_title, $unassUnit->ID, $existingQuiz);					
				}
			}
			printf('</ol>');	
		printf('</div>');	
		
		
		// ### Show a list of quizzes that are not currently assigned to units		
		printf('<div id="wpcw_unassigned_quizzes" class="wpcw_unassigned">');	
		printf('<div class="wpcw_unassigned_title">%s</div>', __('Unassigned Quizzes', 'wp_courseware'));
		
			printf('<ol class="wpcw_dragable_quizzes_connected">');
			
			// Render each unit so that it can be dragged to a module. Still render <ol> list
			// even if there are no units to show so that we can drag units into unassociated list.
			$quizzes = WPCW_quizzes_getListOfQuizzes(0);
			if ($quizzes)
			{
				foreach ($quizzes as $quiz)
				{
					printf('<li id="wpcw_quiz_%d" class="wpcw_dragable_quiz_item">
								<div>%s (ID: %d)
								<div class="wpcw_quiz_des">%s</div>
							</li>', 
							$quiz->quiz_id, $quiz->quiz_title, $quiz->quiz_id, $quiz->quiz_desc
						);
				}
			}
			printf('</ol>');
		
		printf('</div>');	
	printf('</div>'); // end of printf('<div class="wpcw_unassigned_wrapper">');
	
	
	
	// ### Show list of modules and current units
	$moduleList = WPCW_courses_getModuleDetailsList($courseID);
	
	if ($moduleList) 
	{
		printf('<ol class="wpcw_dragable_modules">');
		foreach ($moduleList as $item_id => $moduleObj) 
		{	
			// Module
			printf('<li id="wpcw_mod_%d" class="wpcw_dragable_module_item"><div><b>%s %d - %s (ID: %d)</b></div>', 
				$item_id, 
				__('Module', 'wp_courseware'), $moduleObj->module_number, $moduleObj->module_title, 
				$item_id
			);
				
			
			// Test Associated Units
			printf('<ol class="wpcw_dragable_units_connected">');
			$units = WPCW_units_getListOfUnits($item_id); 			
			
			if ($units)
			{
				foreach ($units as $unassUnit)
				{
					$existingQuiz = false;
					
					// Has unit got any existing quizzes?
					$quizObj = WPCW_quizzes_getAssociatedQuizForUnit($unassUnit->ID);
					$existingQuiz = false; 
					if ($quizObj) 
					{
						$existingQuiz = sprintf('<li id="wpcw_quiz_%d" class="wpcw_dragable_quiz_item">
								<div>%s (ID: %d)
								<div class="wpcw_quiz_des">%s</div>
							</li>', 
							$quizObj->quiz_id, $quizObj->quiz_title, $quizObj->quiz_id, $quizObj->quiz_desc
						);
					}					
					
					printf('<li id="wpcw_unit_%d" class="wpcw_dragable_unit_item">						
						<div>%s (ID: %d)</div>
						<div class="wpcw_dragable_quiz_holder"><ol class="wpcw_dragable_quizzes_connected wpcw_one_only">%s</ol></div>
					</li>', 
					$unassUnit->ID, $unassUnit->post_title, $unassUnit->ID, $existingQuiz);
				}
			}
			
			printf('</ol></li>');
		}
		printf('</ol>');
	} 
	else {
		_e('No modules yet.', 'wp_courseware');
	}
	
	?>
	<div id="wpcw_sticky_bar" style="display: none">
		<div id="wpcw_sticky_bar_inner">
			<a href="#" id="wpcw_dragable_modules_save" class="button-primary"><?php _e('Save Changes to Ordering', 'wp_courseware'); ?></a>
			<span id="wpcw_sticky_bar_status" title="<?php _e('Ordering has changed. Ready to save changes?', 'wp_courseware'); ?>"></span>
		</div>
	</div>
	<?php 
	
	// Close overall wrapper
	printf('</div>');
	$page->showPageFooter();
}


/**
 * Function that show a summary of the training courses.
 */
function WPCW_showPage_Dashboard() 
{
	$page = new PageBuilder(false);
	$page->showPageHeader(__('My Training Courses', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	// Handle any deletion
	WPCW_handler_processDeletion($page);	
	
	global $wpcwdb, $wpdb;
	$SQL = "SELECT * 
			FROM $wpcwdb->courses
			ORDER BY course_title ASC 
			";
	
	$courses = $wpdb->get_results($SQL);
	if ($courses)  
	{		
		$tbl = new TableBuilder();
		$tbl->attributes = array(
			'id' 	=> 'wpcw_tbl_course_summary',
			'class'	=> 'widefat wpcw_tbl'
		);
		
		$tblCol = new TableColumn(__('ID', 'wp_courseware'), 'course_id');		
		$tblCol->cellClass = "course_id";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Course Title', 'wp_courseware'), 'course_title');
		$tblCol->cellClass = "course_title";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Description', 'wp_courseware'), 'course_desc');
		$tblCol->cellClass = "course_desc";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Settings', 'wp_courseware'), 'course_settings');
		$tblCol->cellClass = "course_settings";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Total Units', 'wp_courseware'), 'total_units');
		$tblCol->cellClass = "total_units";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Modules', 'wp_courseware'), 'course_modules');
		$tblCol->cellClass = "course_modules";
		$tbl->addColumn($tblCol);
				
		$tblCol = new TableColumn(__('Actions', 'wp_courseware'), 'actions');
		$tblCol->cellClass = "actions";
		$tbl->addColumn($tblCol);
		
		// Links
		$editURL 		= admin_url('admin.php?page=WPCW_showPage_ModifyCourse');
		$url_addModule 	= admin_url('admin.php?page=WPCW_showPage_ModifyModule');
		$url_ordering 	= admin_url('admin.php?page=WPCW_showPage_CourseOrdering');
		$url_gradeBook 	= admin_url('admin.php?page=WPCW_showPage_GradeBook');
		
		// Format row data and show it.
		$odd = false;
		foreach ($courses as $course)
		{
			$data = array();
			
			// Basic Details
			$data['course_id']  	= $course->course_id;					
			$data['course_desc']  	= $course->course_desc;
			
			// Editing Link			
			$data['course_title']  	= sprintf('<a href="%s&course_id=%d">%s</a>', $editURL, $course->course_id, $course->course_title);			
						
			// Actions
			$data['actions']	= '<ul>';
			$data['actions']	.= sprintf('<li><a href="%s&course_id=%d" class="button-primary">%s</a></li>', 	$url_addModule, $course->course_id, 	__('Add Module', 'wp_courseware'));
			$data['actions']	.= sprintf('<li><a href="%s&course_id=%d" class="button-secondary">%s</a></li>', $editURL, $course->course_id,			__('Edit Course Settings', 'wp_courseware'));			
			$data['actions']	.= sprintf('<li><a href="%s&course_id=%d" class="button-secondary">%s</a></li>', $url_ordering, $course->course_id,		__('Modules, Units &amp; Quiz Ordering', 'wp_courseware'));
			$data['actions']	.= sprintf('<li><a href="%s&course_id=%d" class="button-secondary">%s</a></li>', $url_gradeBook, $course->course_id,	__('Access Grade Book', 'wp_courseware'));
			$data['actions']	.= '</ul>';
			
			// Settings Summary - to allow user to see a quick overview of the current settings.
			$data['course_settings']  = '<ul class="wpcw_tickitems">';

			// Access control - filtered if membership plugin
			$data['course_settings'] .= apply_filters('wpcw_extensions_access_control_override', 
				sprintf('<li class="wpcw_%s">%s</li>', ('default_show' == $course->course_opt_user_access ? 'enabled' : 'disabled'), __('Give new users access by default', 'wp_courseware'))
			);
			
			// Completion wall
			$data['course_settings'] .= sprintf('<li class="wpcw_%s">%s</li>', ('completion_wall' == $course->course_opt_completion_wall ? 'enabled' : 'disabled'), 
				__('Require unit completion before showing next', 'wp_courseware'));
				
			// Certificate handling
			$data['course_settings'] .= sprintf('<li class="wpcw_%s">%s</li>', ('use_certs' == $course->course_opt_use_certificate ? 'enabled' : 'disabled'), 
				__('Generate certificates on course completion', 'wp_courseware'));
				
			$data['course_settings'] .= '</ul>';
			
			
			// Module list
			$data['course_modules'] = false;
			$moduleList = WPCW_courses_getModuleDetailsList($course->course_id);
			$moduleIDList = array();
			
			if ($moduleList) 
			{
				foreach ($moduleList as $item_id => $moduleObj) 
				{
					$modName = sprintf('%s %d - %s', __('Module'), $moduleObj->module_number, $moduleObj->module_title);
					
					// Create each module item with an edit link.
					$modEditURL = admin_url('admin.php?page=WPCW_showPage_ModifyModule&module_id=' . $item_id);	
					$data['course_modules'] .= sprintf('<li><a href="%s" title="%s \'%s\'">%s</a></li>', 
						$modEditURL, 
						__('Edit Module', 'wp_courseware'), 
						$modName, $modName
					);	
					
					// Just want module IDs
					$moduleIDList[] = $item_id;
				}
			} 
			else {
				$data['course_modules'] = __('No modules yet.', 'wp_courseware');
			}
			
			
			// Unit Count
			if (count($moduleIDList) > 0)  
			{
				$data['total_units'] = $wpdb->get_var("
					SELECT COUNT(*) 
					FROM $wpcwdb->units_meta 
					WHERE parent_module_id IN (" . implode(",", $moduleIDList) . ")");
			}
			
			// No modules, so can't be any units.
			else {
				$data['total_units'] = '0';	
			} 
			
			
			// Odd/Even row colouring.
			$odd = !$odd;
			$rowClass = ($odd ? 'alternate' : '');
			
			// Get a list of all quizzes for the specified parent course.
			$listOfQuizzes = $wpdb->get_col($wpdb->prepare("
				SELECT quiz_id
				FROM $wpcwdb->quiz
				WHERE parent_course_id = %d
			", $course->course_id));
			
			// Determine if there are any quizzes that need marking. If so, how many?
			$countOfQuizzesNeedingGrading = false;
			if (!empty($listOfQuizzes))
			{
				 $quizIDList = '(' . implode(',', $listOfQuizzes) . ')';

				 $countOfQuizzesNeedingGrading = $wpdb->get_var("
					SELECT COUNT(*)
					FROM $wpcwdb->user_progress_quiz
					WHERE quiz_id IN $quizIDList
					  AND quiz_needs_marking > 0
					  AND quiz_is_latest = 'latest'
				");
			}
			
			
			// Show the status message about quizzes needing marking.
			if ($countOfQuizzesNeedingGrading)
			{			
				// Create message that quizzes need marking. 
				$msgQuizzesNeedMarking = 
					__( 'This course has ', 'wp_courseware') . 
					_n( '1 quiz that requires', '%d quizzes that require', $countOfQuizzesNeedingGrading, 'wp_courseware') . 
					__( ' manual grading.', 'wp_courseware');

				$msgQuizzesNeedMarking = '<span>' . sprintf($msgQuizzesNeedMarking, $countOfQuizzesNeedingGrading) . '</span>';
				
				// Create a row that also hides the border below it.
				$tbl->addRow($data, 'wpcw_tbl_row_status_pre ' . $rowClass); 
			
				// Add a row for the status data, hiding the border above it.
				$tblRow = new RowDataSimple('wpcw_tbl_row_status ' . $rowClass, $msgQuizzesNeedMarking, 7);
				$tbl->addRowObj($tblRow);
			}
			
			// Normal course row. No status information below the course detail row.
			// So don't modify the row borders at all.
			else {
				$tbl->addRow($data, $rowClass);
			}
		}
		
		// Finally show table
		echo $tbl->toString();		
	}
	
	else {
		printf('<p>%s</p>', __('There are currently no courses to show. Why not create one?', 'wp_courseware'));
	}
	
	$page->showPageFooter();
}


/**
 * Function that allows a course to be created or edited.
 */
function WPCW_showPage_ModifyCourse() 
{
	$page = new PageBuilder(true);
	
	$courseDetails = false;
	$courseID = false;
	
	// Trying to edit a course	
	if (isset($_GET['course_id'])) 
	{
		$courseID 		= $_GET['course_id'] + 0;
		$courseDetails 	= WPCW_courses_getCourseDetails($courseID);
		
		// Abort if course not found.
		if (!$courseDetails)
		{
			$page->showPageHeader(__('Edit Course', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
			$page->showMessage(__('Sorry, but that course could not be found.', 'wp_courseware'), true);
			$page->showPageFooter();
			return;
		}
		
		// Editing a course, and it was found
		else {
			$page->showPageHeader(__('Edit Course', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
		}
	}
	
	// Adding course
	else {
		$page->showPageHeader(__('Add Course', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	}
	
	
	
	global $wpcwdb;
	
	$formDetails = array(
		'break_course_general' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(false),
			),	
	
		'course_title' => array(
				'label' 	=> __('Course Title', 'wp_courseware'),
				'type'  	=> 'text',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_title',
				'desc'  	=> __('The title of your course.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[^<>]+$/',
					'error'		=> __('Please specify a name for your course, up to a maximum of 150 characters, just no angled brackets (&lt; or &gt;). Your trainees will be able to see this course title.', 'wp_courseware')
				)	
			),				

		'course_desc' => array(
				'label' 	=> __('Course Description', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_desc',
				'desc'  	=> __('The description of this course. Your trainees will be able to see this course description.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the description of your course to 5000 characters.', 'wp_courseware')
				)	 	
			),
			
		/* Maybe useful in future - descoped for now.
		'course_overview_page' => array(
				'label' 	=> __('Course Overview Page', 'wp_courseware'),
				'type'  	=> 'select',
				'required'  => false,
				'desc'  	=> __('The page that links to the list of all modules for the course.', 'wp_courseware'),
				'data'	 	=> WPCW_pages_getPageList() 	 	
			),*/

		'course_opt_completion_wall' => array(
				'label' 	=> __('When do users see the next unit on the course?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'desc'  	=> __('Can a user see all possible course units? Or must they complete previous units before seeing the next unit?', 'wp_courseware'),
				'data'		=> array(
					'all_visible' => __('<b>All Units Visible</b> - All units are visible regardless of completion progress.', 'wp_courseware'),
					'completion_wall' => __('<b>Only Completed/Next Units Visible</b> - Only show units that have been completed, plus the next unit that the user can start.', 'wp_courseware')
				)	 	
			),	
			
		// ###ÊUser Access - Courses
		'break_course_access' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(),
			),	
			
		'course_opt_user_access' => array(
				'label' 	=> __('Granting users access to this course', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'desc'  	=> __('This setting allows you to set how users can access this course. They can either be given access automatically as soon as the user is created, or you can manually give them access. You can always manually remove access if you wish.', 'wp_courseware'),
				'data'		=> array(
					'default_show' => __('<b>Automatic</b> - All newly created users will be given access this course.', 'wp_courseware'),
					'default_hide' => __('<b>Manual</b> - Users can only access course if you grant them access.', 'wp_courseware')
				)	 	
			),				
			
		// ###ÊUser Messages - Modules
		'break_course_messages' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(),
			),			
			
		'course_message_unit_complete' => array(
				'label' 	=> __('Message - Unit Complete', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee once they\'ve <b>completed a unit</b>, which is displayed at the bottom of the unit page. HTML is OK.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),		
			
		'course_message_course_complete' => array(
				'label' 	=> __('Message - Course Complete', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee once they\'ve <b>completed the whole course</b>, which is displayed at the bottom of the unit page. HTML is OK.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),

		'course_message_unit_pending' => array(
				'label' 	=> __('Message - Unit Pending', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee when they\'ve <b>yet to complete a unit</b>. This message is displayed at the bottom of the unit page, along with a button that says "<b>Mark as completed</b>". HTML is OK.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),	
			
		'course_message_unit_no_access' => array(
				'label' 	=> __('Message - Access Denied', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee they are <b>not allowed to access a unit</b>, because they are not allowed to <b>access the whole course</b>.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),	

		'course_message_unit_not_yet' => array(
				'label' 	=> __('Message - Not Yet Available', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee they are <b>not allowed to access a unit yet</b>, because they need to complete a previous unit.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),	

		'course_message_unit_not_logged_in' => array(
				'label' 	=> __('Message - Not Logged In', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee they are <b>not logged in</b>, and therefore cannot access the unit.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),			
			
		'course_message_quiz_open_grading_blocking' => array(
				'label' 	=> __('Message - Open-Question Submitted - Blocking Mode', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee they have submitted an answer to an <b>open-ended or upload question</b>, and you need to grade their answer <b>before they continue</b>.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),	
			
		'course_message_quiz_open_grading_non_blocking' => array(
				'label' 	=> __('Message - Open-Question Submitted - Non-Blocking Mode', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_message',
				'desc'  	=> __('The message shown to a trainee they have submitted an answer to an <b>open-ended or upload question</b>, and you need to grade their answer, but they can <b>continue anyway</b>.', 'wp_courseware'),
				'rows'		=> 2,
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 500,
					'minlen'	=> 1,
					'error'		=> __('Please limit message to 500 characters.', 'wp_courseware')
				)	 	
			),	
			
			

		// ###ÊUser Notifications - From Email Address details
		'break_course_notifications_from_details' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(),
			),		

		'course_from_email' => array(
				'label' 	=> __('Email From Address', 'wp_courseware'),
				'type'  	=> 'text',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email',
				'desc'  	=> __('The email address that the email notifications should be from.<br/>Depending on your server\'s spam-protection set up, this may not appear in the outgoing emails.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'email',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'error'		=> __('Please enter a valid email address.', 'wp_courseware')
				)	
			),		
			
		'course_from_name' => array(
				'label' 	=> __('Email From Name', 'wp_courseware'),
				'type'  	=> 'text',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email',
				'desc'  	=> __('The name used on the email notifications, which are sent to you and your trainees. <br/>Depending on your server\'s spam-protection set up, this may not appear in the outgoing emails.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[^<>]+$/',
					'error'		=> __('Please specify a from name, up to a maximum of 150 characters, just no angled brackets (&lt; or &gt;).', 'wp_courseware')			
				)	
			),	

		'course_to_email' => array(
				'label' 	=> __('Admin Notify Email Address', 'wp_courseware'),
				'type'  	=> 'text',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email',
				'desc'  	=> __('The email address to send admin notifications to.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'email',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'error'		=> __('Please enter a valid email address.', 'wp_courseware')
				)	
			),	
		
		// ###ÊUser Notifications - Modules
		'break_course_notifications_user_module' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(),
			),
			
		'email_complete_module_option_admin' => array(
				'label' 	=> __('Module Complete - Notify You?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email_template_option',
				'data'	 	=> array(
					'send_email'	=> __('<b>Send me an email</b> - when one of your trainees has completed a module.', 'wp_courseware'),
					'no_email'	=> __('<b>Don\'t send me an email</b> - when one of your trainees has completed a module.', 'wp_courseware')
				)
			),				
			
		'email_complete_module_option' => array(
				'label' 	=> __('Module Complete - Notify User?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email_template_option',
				'data'	 	=> array(
					'send_email'	=> __('<b>Send Email</b> - to user when module has been completed.', 'wp_courseware'),
					'no_email'	=> __('<b>Don\'t Send Email</b> - to user when module has been completed.', 'wp_courseware')
				)
			),
			
		'email_complete_module_subject' => array(
				'label' 	=> __('Module Complete - Email Subject', 'wp_courseware'),
				'type'  	=> 'textarea',				
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template_subject',
				'rows'		=> 2,
				'desc'  	=> __('The <b>subject line</b> for the email sent to a user when they complete a <b>module</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email subject to 300 characters.', 'wp_courseware')
				)	 	
			),		
						
		'email_complete_module_body' => array(
				'label' 	=> __('Module Complete - Email Body', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template',
				'desc'  	=> __('The <b>template body</b> for the email sent to a user when they complete a <b>module</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email body to 5000 characters.', 'wp_courseware')
				)	 	
			),	
			
		// ###ÊUser Notifications - Courses			
		'break_course_notifications_user_course' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(),
			),
			
		'email_complete_course_option_admin' => array(
				'label' 	=> __('Course Complete - Notify You?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email_template_option',
				'data'	 	=> array(
					'send_email'	=> __('<b>Send me an email</b> - when one of your trainees has completed the whole course.', 'wp_courseware'),
					'no_email'	=> __('<b>Don\'t send me an email</b> - when one of your trainees has completed the whole course.', 'wp_courseware')
				)
			),				
			
		'email_complete_course_option' => array(
				'label' 	=> __('Course Complete - Notify User?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email_template_option',
				'data'	 	=> array(
					'send_email'	=> __('<b>Send Email</b> - to user when the whole course has been completed.', 'wp_courseware'),
					'no_email'	=> __('<b>Don\'t Send Email</b> - to user when the whole course has been completed.', 'wp_courseware')
				)
			),
			
		'email_complete_course_subject' => array(
				'label' 	=> __('Course Complete - Email Subject', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template_subject',
				'rows'		=> 2,
				'desc'  	=> __('The <b>subject line</b> for the email sent to a user when they complete <b>the whole course</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email subject to 300 characters.', 'wp_courseware')
				)	 	
			),		
						
		'email_complete_course_body' => array(
				'label' 	=> __('Course Complete - Email Body', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template',
				'desc'  	=> __('The <b>template body</b> for the email sent to a user when they complete <b>the whole course</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email body to 5000 characters.', 'wp_courseware')
				)	 	
			),	

		// ###ÊUser Notifications - Quiz Grades			
		'break_course_notifications_user_grades' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(),
			),				
			
		'email_quiz_grade_option' => array(
				'label' 	=> __('Quiz Grade - Notify User?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'cssclass'	=> 'wpcw_course_email_template_option',
				'data'	 	=> array(
					'send_email'	=> __('<b>Send Email</b> - to user after a quiz is graded (automatically or by the instructor).', 'wp_courseware'),
					'no_email'		=> __('<b>Don\'t Send Email</b> - to user when a quiz is graded.', 'wp_courseware')
				),
			),
			
		'email_quiz_grade_subject' => array(
				'label' 	=> __('Quiz Graded - Email Subject', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template_subject',
				'rows'		=> 2,
				'desc'  	=> __('The <b>subject line</b> for the email sent to a user when they receive a <b>grade for a quiz</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email subject to 300 characters.', 'wp_courseware')
				)	 	
			),		
						
		'email_quiz_grade_body' => array(
				'label' 	=> __('Quiz Graded - Email Body', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template',
				'desc'  	=> __('The <b>template body</b> for the email sent to a user when they receive a <b>grade for a quiz</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email body to 5000 characters.', 'wp_courseware')
				)	 	
			),		

		// ###ÊUser Notifications - Final Summary Email			
		'break_course_notifications_user_final' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(),
			),				
			
		'email_complete_course_grade_summary_subject' => array(
				'label' 	=> __('Final Summary - Email Subject', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template_subject',
				'rows'		=> 2,
				'desc'  	=> __('The <b>subject line</b> for the email sent to a user when they receive their <b>grade summary at the end of the course</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email subject to 300 characters.', 'wp_courseware')
				)	 	
			),		
						
		'email_complete_course_grade_summary_body' => array(
				'label' 	=> __('Final Summary - Email Body', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_course_email_template',
				'desc'  	=> __('The <b>template body</b> for the email sent to a user when they receive their <b>grade summary at the end of the course</b>.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the email body to 5000 characters.', 'wp_courseware')
				)	 	
			),	
			
			
		// ###ÊCertificates - Courses			
		'break_course_certificates_user_course' => array(
				'type'  	=> 'break',
				'html'  	=> WPCW_forms_createBreakHTML_tab(__('Course Complete Certificates', 'wp_courseware')),
			),
			
		'course_opt_use_certificate' => array(
				'label' 	=> __('Enable certificates?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'data'	 	=> array(
					'use_certs'	=> __('<b>Yes</b> - generate a PDF certificate when user completes this course.','wp_courseware'),
					'no_certs'	=> __('<b>No</b> - don\'t generate a PDF certificate when user completes this course.','wp_courseware')
				)
			),			


	);
	
	$form = new RecordsForm(
		$formDetails,			// List of form elements
		$wpcwdb->courses, 		// Table for main details
		'course_id' 			// Primary key column name
	);
	
	$form->customFormErrorMsg = __('Sorry, but unfortunately there were some errors saving the course details. Please fix the errors and try again.', 'wp_courseware');
	$form->setAllTranslationStrings(WPCW_forms_getTranslationStrings());

	// Set defaults if adding a new course
	if (!$courseDetails)
	{
		$form->loadDefaults(array(
		
			// Add basic Email Template to defaults when creating a new course.
			'email_complete_module_subject'					=> EMAIL_TEMPLATE_COMPLETE_MODULE_SUBJECT,
			'email_complete_course_subject'					=> EMAIL_TEMPLATE_COMPLETE_COURSE_SUBJECT,
			'email_quiz_grade_subject'						=> EMAIL_TEMPLATE_QUIZ_GRADE_SUBJECT,
			'email_complete_course_grade_summary_subject'	=> EMAIL_TEMPLATE_COURSE_SUMMARY_WITH_GRADE_SUBJECT,
		
			// Email bodies
			'email_complete_module_body'				=> EMAIL_TEMPLATE_COMPLETE_MODULE_BODY,
			'email_complete_course_body'				=> EMAIL_TEMPLATE_COMPLETE_COURSE_BODY,
			'email_quiz_grade_body'						=> EMAIL_TEMPLATE_QUIZ_GRADE_BODY,
			'email_complete_course_grade_summary_body'	=> EMAIL_TEMPLATE_COURSE_SUMMARY_WITH_GRADE_BODY,
		
			// Email address details
			'course_from_name'							=> get_bloginfo('name'),
			'course_from_email'							=> get_bloginfo('admin_email'),
			'course_to_email'							=> get_bloginfo('admin_email'),
		
			// Completion wall default (blocking mode)			
			'course_opt_completion_wall'				=> 'completion_wall',
			'course_opt_user_access'					=> 'default_show',
		
			// Email notification defaults (yes to send email)
			'email_complete_course_option_admin'		=> 'send_email',
			'email_complete_course_option'				=> 'send_email',
			'email_complete_module_option_admin'		=> 'send_email',
			'email_complete_module_option'				=> 'send_email',
			'email_quiz_grade_option'					=> 'send_email',
					
			// Certificate defaults
			'course_opt_use_certificate'				=> 'no_certs',
		
			// User Messages
			'course_message_unit_not_yet'				=> __("You need to complete the previous unit first.", 'wp_courseware'),		
			'course_message_unit_pending'				=> __("Have you completed this unit? Then mark this unit as completed.", 'wp_courseware'),			
			'course_message_unit_complete'				=> __("You have now completed this unit.", 'wp_courseware'),
			'course_message_course_complete'			=> __("You have now completed the whole course. Congratulations!", 'wp_courseware'),
			'course_message_unit_no_access'				=> __("Sorry, but you're not allowed to access this course.", 'wp_courseware'),
			'course_message_unit_not_logged_in'			=> __('You cannot view this unit as you\'re not logged in yet.', 'wp_courseware'),
		
			// User Messages - quizzes
			'course_message_quiz_open_grading_blocking'		=> __('Your quiz has been submitted for grading by the course instructor. Once your grade has been entered, you will be able access the next unit.', 'wp_courseware'),
			'course_message_quiz_open_grading_non_blocking'	=> __('Your quiz has been submitted for grading by the course instructor. You have now completed this unit.', 'wp_courseware'),
		));
	}
	
	// Useful place to go
	$directionMsg = '<br/></br>' . sprintf(__('Do you want to return to the <a href="%s">course summary page</a>?', 'wp_courseware'),
		admin_url('admin.php?page=WPCW_wp_courseware')
	);	
	
	// Override success messages
	$form->msg_record_created = __('Course details successfully created. ', 'wp_courseware') . $directionMsg;
	$form->msg_record_updated = __('Course details successfully updated. ', 'wp_courseware') . $directionMsg;

	
	$form->setPrimaryKeyValue($courseID);	
	$form->setSaveButtonLabel(__('Save ALL Details', 'wp_courseware'));
	

	// Process form	
	$formHTML = $form->getHTML();
	
		
	// Show message about this course having quizzes that require a pass mark.
	// Need updated details for this.
	$courseDetails = WPCW_courses_getCourseDetails($courseID);
	if ($courseDetails && $courseDetails->course_opt_completion_wall == 'all_visible')
	{
		$quizzes = WPCW_quizzes_getAllBlockingQuizzesForCourse($courseDetails->course_id);
		
		// Count how many blocking quizzes there are.
		if ($quizzes && count($quizzes) > 0) {
			$quizCountMessage = sprintf(__('Currently <b>%d of your quizzes</b> are blocking process based on a percentage score <b>in this course</b>.', 'wp_courseware'), count($quizzes));
		} else {
			$quizCountMessage = __('You do not currently have any blocking quizzes for this course.', 'wp_courseware');
		}
			
		printf('<div id="message" class="wpcw_msg_info wpcw_msg"><b>%s</b> - %s<br/><br/>
				%s				
				</div>', 
			__('Important Note', 'wp_courseware'),
			__('You have selected <b>All Units Visible</b>. If you create a quiz blocking progress based on a percentage score, students will have access to the entire course regardless of quiz score.', 'wp_courseware'),
			$quizCountMessage
		);
						
	}
	
	// Generate the tabs.
	$tabList = array( 
		'break_course_general' 						=> __('General Course Details', 'wp_courseware'), 
		'break_course_access' 						=> __('User Access', 'wp_courseware'), 
		'break_course_messages' 					=> __('User Messages', 'wp_courseware'),
		'break_course_notifications_from_details' 	=> __('Email Address Details', 'wp_courseware'),
		'break_course_notifications_user_module' 	=> __('Email Notifications - Module', 'wp_courseware'),
		'break_course_notifications_user_course' 	=> __('Email Notifications - Course', 'wp_courseware'),
		'break_course_notifications_user_grades' 	=> __('Email Notifications - Quiz Grades', 'wp_courseware'),
		'break_course_notifications_user_final' 	=> __('Email Notifications - Final Summary', 'wp_courseware'),
		'break_course_certificates_user_course' 	=> __('Certificates', 'wp_courseware'),
	);
	
    echo '<div class="wpcw_tab_wrapper" id="wpcw_courses_tabs"><div class="wpcw_tab_wrapper_tabs">';
    $currentTab = current(array_keys($tabList)); // Select the first one.
    
    foreach ($tabList as $tabName => $label)
    {
        $class = ($tabName == $currentTab) ? ' wpcw_tab_active' : '';
        printf('<a class="wpcw_tab%s" href="#" data-tab="%s">%s</a>', $class, $tabName, $label);
    }
    echo '</div>'; // .wpcw_tab_wrapper_tabs
	
	
	// Show the form
	echo $formHTML;
	echo '</div>'; // .wpcw_tab_wrapper

	
	$page->showPageMiddle('20%');
	
		
	// Include a link to delete the course
	if ($courseDetails) 	
	{
		$page->openPane('wpcw-deletion-course', __('Delete Course?', 'wp_courseware'));
		
		printf('<a href="%s&action=delete_course&course_id=%d" class="wpcw_delete_item" title="%s">%s</a>',
			admin_url('admin.php?page=WPCW_wp_courseware'),
			$courseID,
			__("Are you sure you want to delete the this course and all its modules?\n\nThis CANNOT be undone!", 'wp_courseware'),			 
			__('Delete this Course', 'wp_courseware')
		);	
		
		printf('<p>%s</p>', __('Units will <b>not</b> be deleted, they will <b>just be disassociated</b> from this course.', 'wp_courseware'));
		
		$page->closePane();
	}	
	
	// Email template tags here...
	$page->openPane('wpcw_docs_email_tags', __('Email Template Tags', 'wp_courseware'));
	
	printf('<h4 class="wpcw_docs_side_mini_hdr">%s</h4>', __('All Email Notifications', 'wp_courseware'));
	printf('<dl class="wpcw_email_tags">');
		
		printf('<dt>{USER_NAME}</dt><dd>%s</dd>', 		__('The display name of the user.', 'wp_courseware'));
		
		printf('<dt>{SITE_NAME}</dt><dd>%s</dd>', 		__('The name of the website.', 'wp_courseware'));
		printf('<dt>{SITE_URL}</dt><dd>%s</dd>', 		__('The URL of the website.', 'wp_courseware'));
		
		printf('<dt>{COURSE_TITLE}</dt><dd>%s</dd>', 	__('The title of the course for the unit that\'s just been completed.', 'wp_courseware'));
		printf('<dt>{MODULE_TITLE}</dt><dd>%s</dd>', 	__('The title of the module for the unit that\'s just been completed.', 'wp_courseware'));
		printf('<dt>{MODULE_NUMBER}</dt><dd>%s</dd>', 	__('The number of the module for the unit that\'s just been completed.', 'wp_courseware'));
		
		printf('<dt>{CERTIFICATE_LINK}</dt><dd>%s</dd>', __('If the course has PDF certificates enabled, this is the link of the PDF certficate. (If there is no certificate or certificates are not enabled, this is simply blank)', 'wp_courseware'));
		
	printf('</dl>');
	
	printf('<h4 class="wpcw_docs_side_mini_hdr">%s</h4>', __('Quiz Email Notifications Only', 'wp_courseware'));
	printf('<dl class="wpcw_email_tags">');
		printf('<dt>{QUIZ_TITLE}</dt><dd>%s</dd>', 			__('The title of the quiz that has been graded.', 'wp_courseware'));
		printf('<dt>{QUIZ_GRADE}</dt><dd>%s</dd>', 			__('The overall percentage grade for a quiz.', 'wp_courseware'));
		printf('<dt>{QUIZ_RESULT_DETAIL}</dt><dd>%s</dd>', 	__('Any optional information relating to the result of the quiz, e.g. information about retaking the quiz.', 'wp_courseware'));
		printf('<dt>{UNIT_URL}</dt><dd>%s</dd>', 			__('The URL of a unit that is associated with the quiz.', 'wp_courseware'));
	printf('</dl>');
	
	printf('<h4 class="wpcw_docs_side_mini_hdr">%s</h4>', __('Final Summary Notifications Only', 'wp_courseware'));
	printf('<dl class="wpcw_email_tags">');
		printf('<dt>{CUMULATIVE_GRADE}</dt><dd>%s</dd>', 	__('The overall cumulative grade that the user has scored from completing all quizzes on the course.', 'wp_courseware'));
		printf('<dt>{QUIZ_SUMMARY}</dt><dd>%s</dd>', 		__('The summary of each quiz, and what the user scored on each.', 'wp_courseware'));
	printf('</dl>');
}



/**
 * Function that allows a module to be created or edited.
 */
function WPCW_showPage_ModifyModule() 
{
	$page = new PageBuilder(true);
	
	$moduleDetails 	= false;
	$moduleID 		= false;
	$adding			= false;
	
	// Trying to edit a course	
	if (isset($_GET['module_id'])) 
	{
		$moduleID 		= $_GET['module_id'] + 0;
		$moduleDetails 	= WPCW_modules_getModuleDetails($moduleID);
		
		// Abort if module not found.
		if (!$moduleDetails)
		{
			$page->showPageHeader(__('Edit Module', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
			$page->showMessage(__('Sorry, but that module could not be found.', 'wp_courseware'), true);
			$page->showPageFooter();
			return;
		}
		
		// Editing a module, and it was found
		else {
			$page->showPageHeader(__('Edit Module', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
		}
	}
	
	// Adding module
	else {
		$page->showPageHeader(__('Add Module', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
		
		$adding = true;
	}
	
	
	global $wpcwdb;
	
	$formDetails = array(
		'module_title' => array(
				'label' 	=> __('Module Title', 'wp_courseware'),
				'type'  	=> 'text',
				'required'  => true,
				'cssclass'	=> 'wpcw_module_title',
				'desc'  	=> __('The title of your module. You <b>do not need to number the modules</b> - this is done automatically based on the order that they are arranged.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[^<>]+$/',
					'error'		=> __('Please specify a name for your module, up to a maximum of 150 characters, just no angled brackets (&lt; or &gt;). Your trainees will be able to see this module title.', 'wp_courseware')
				)	
			),				
			
		'parent_course_id' => array(
				'label' 	=> __('Associated Course', 'wp_courseware'),
				'type'  	=> 'select',
				'required'  => true,
				'cssclass'	=> 'wpcw_associated_course',
				'desc'  	=> __('The associated training course that this module belongs to.', 'wp_courseware'),
				'data'		=> WPCW_courses_getCourseList(__('-- Select a Training Course --', 'wp_courseware'))	
			),	

		'module_desc' => array(
				'label' 	=> __('Module Description', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => true,
				'cssclass'	=> 'wpcw_module_desc',
				'desc'  	=> __('The description of this module. Your trainees will be able to see this module description.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the description of your module to 5000 characters.', 'wp_courseware')
				)	 	
			),		
	);
		
	
	$form = new RecordsForm(
		$formDetails,			// List of form elements
		$wpcwdb->modules, 		// Table for main details
		'module_id' 			// Primary key column name
	);	
	
	$form->customFormErrorMsg = __('Sorry, but unfortunately there were some errors saving the module details. Please fix the errors and try again.', 'wp_courseware');
	$form->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
	
	// Useful place to go
	$directionMsg = '<br/></br>' . sprintf(__('Do you want to return to the <a href="%s">course summary page</a>?', 'wp_courseware'),
		admin_url('admin.php?page=WPCW_wp_courseware')
	);	
	
	// Override success messages
	$form->msg_record_created = __('Module details successfully created.', 'wp_courseware') . $directionMsg;
	$form->msg_record_updated = __('Module details successfully updated.', 'wp_courseware') . $directionMsg;

	$form->setPrimaryKeyValue($moduleID);	
	$form->setSaveButtonLabel(__('Save ALL Details', 'wp_courseware'));
		
	
	// See if we have a course ID to pre-set.
	if ($adding && $courseID = WPCW_arrays_getValue($_GET, 'course_id')) {
		$form->loadDefaults(array(
			'parent_course_id' => $courseID			
		));
	}
	
	// Call to re-order modules once they've been created
	$form->afterSaveFunction = 'WPCW_actions_modules_afterModuleSaved_formHook';
	
	$form->show();
	
	$page->showPageMiddle('20%');
	
	// Editing a module?
	if ($moduleDetails) 	
	{
		// ### Include a link to delete the module
		$page->openPane('wpcw-deletion-module', __('Delete Module?', 'wp_courseware'));
		
		printf('<a href="%s&action=delete_module&module_id=%d" class="wpcw_delete_item" title="%s">%s</a>',
			admin_url('admin.php?page=WPCW_wp_courseware'),
			$moduleID,
			__("Are you sure you want to delete the this module?\n\nThis CANNOT be undone!", 'wp_courseware'),			 
			__('Delete this Module', 'wp_courseware')
		);	
		
		printf('<p>%s</p>', __('Units will <b>not</b> be deleted, they will <b>just be disassociated</b> from this module.', 'wp_courseware'));
		
		$page->closePane();
		
		
		// #### Show a list of all sub-units 
		$page->openPane('wpcw-units-module', __('Units in this Module', 'wp_courseware'));
		
		$unitList = WPCW_units_getListOfUnits($moduleID);
		if ($unitList)
		{
			printf('<ul class="wpcw_unit_list">');
			foreach ($unitList as $unitID => $unitObj)
			{
				printf('<li>%s %d - %s</li>',
					__('Unit', 'wp_courseware'),
					$unitObj->unit_meta->unit_number,
					$unitObj->post_title
				);
			}
			printf('</ul>');
		}
		
		else {
			printf('<p>%s</p>', __('There are currently no units in this module.', 'wp_courseware'));
		}
	}
	
	$page->showPageFooter();
}



/**
 * Function that allows a quiz to be created or edited.
 */
function WPCW_showPage_ModifyQuiz() 
{
	$page = new PageBuilder(true);
	
	$moduleDetails 	= false;
	$moduleID 		= false;
	$adding			= false;

	$quizID 		= false;
	
	// Check POST and GET
	if (isset($_GET['quiz_id'])) {
		$quizID = $_GET['quiz_id'] + 0;
	} 
	else if (isset($_POST['quiz_id'])) {
		$quizID = $_POST['quiz_id'] + 0;
	}
	
	// Trying to edit a course	
	if ($quizDetails = WPCW_quizzes_getQuizDetails($quizID)) 
	{
		// Abort if quiz not found.
		if (!$quizDetails)
		{
			$page->showPageHeader(__('Edit Quiz/Survey', 'wp_courseware'), '70%', WPCW_icon_getPageIconURL());
			$page->showMessage(__('Sorry, but that quiz/survey could not be found.', 'wp_courseware'), true);
			$page->showPageFooter();
			return;
		}
		
		// Editing a quiz, and it was found
		else {
			$page->showPageHeader(__('Edit Quiz/Survey', 'wp_courseware'), '70%', WPCW_icon_getPageIconURL());
		}
	}
	
	// Adding module
	else {
		$page->showPageHeader(__('Add Quiz/Survey', 'wp_courseware'), '70%', WPCW_icon_getPageIconURL());
		
		$adding = true;
	}	
	
	global $wpcwdb;
	
	$formDetails = array(
		'quiz_title' => array(
				'label' 	=> __('Quiz Title', 'wp_courseware'),
				'type'  	=> 'text',
				'required'  => true,
				'cssclass'	=> 'wpcw_quiz_title',
				'desc'  	=> __('The title of your quiz or survey. Your trainees will be able to see this quiz title.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[^<>]+$/',
					'error'		=> __('Please specify a name for your quiz or survey, up to a maximum of 150 characters, just no angled brackets (&lt; or &gt;).', 'wp_courseware')
				)	
			),		
			
		'quiz_desc' => array(
				'label' 	=> __('Quiz/Survey Description', 'wp_courseware'),
				'type'  	=> 'textarea',
				'required'  => false,
				'cssclass'	=> 'wpcw_quiz_desc',
				'rows'		=> 2,
				'desc'  	=> __('(Optional) The description of this quiz. Your trainees won\'t see this description. It\'s just for your reference.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 5000,
					'minlen'	=> 1,
					'error'		=> __('Please limit the description of your quiz to 5000 characters.', 'wp_courseware')
				)	 	
			),		

		'quiz_type' => array(
				'label' 	=> __('Quiz Type', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'cssclass'	=> 'wpcw_quiz_type wpcw_quiz_type_hide_pass',
				'data'		=> array(
					'survey'		=> __('<b>Survey Mode</b> - No correct answers, just collect information.', 'wp_courseware'),
					'quiz_block'	=> __('<b>Quiz Mode - Blocking</b> - require trainee to correctly questions before proceeding. Trainee must achieve <b>minimum pass mark</b> to progress to the next unit.', 'wp_courseware'),
					'quiz_noblock'	=> __('<b>Quiz Mode - Non-blocking</b> - require trainee to answer a number of questions before proceeding, but allow them to progress to the next unit <b>regardless of their pass mark</b>.', 'wp_courseware'),
				)	
			),
			
		'quiz_pass_mark' => array(
				'label' 	=> __('Pass Mark', 'wp_courseware'),
				'type'  	=> 'select',
				'required'  => true,
				'cssclass'	=> 'wpcw_quiz_block_only wpcw_quiz_only',
				'data'		=> WPCW_quizzes_getPercentageList(__('-- Select a pass mark --', 'wp_courseware')),
				'desc'  	=> __('The minimum pass mark that your trainees need to achieve to progress on to the next unit.', 'wp_courseware'),
			),

		'quiz_show_answers' => array(
				'label' 	=> __('Show Answers?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'  => true,
				'cssclass'	=> 'wpcw_quiz_show_answers wpcw_quiz_only',
				'data'		=> array(
					'show_answers' 	=> __('<b>Show Answers</b> - Show the trainee the correct answers before they progress.', 'wp_courseware'),
					'no_answers' 	=> __('<b>No Answers</b> - Don\'t show the trainee any answers before they progress.', 'wp_courseware'),
				),
				'desc'  	=> __('Do you wish your trainee to see the correct answers before proceeding to the next unit?', 'wp_courseware'),
			),
		
	);
		
	
	$form = new RecordsForm(
		$formDetails,			// List of form elements
		$wpcwdb->quiz, 			// Table for main details
		'quiz_id' 				// Primary key column name
	);	
	
	$form->customFormErrorMsg = __('Sorry, but unfortunately there were some errors saving the quiz details. Please fix the errors and try again.', 'wp_courseware');
	$form->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
	
	// Got to summary of quizzes
	$directionMsg = '<br/></br>' . sprintf(__('Do you want to return to the <a href="%s">quiz summary page</a>?', 'wp_courseware'),
		admin_url('admin.php?page=WPCW_showPage_QuizSummary')
	);	
	
	// Override success messages
	$form->msg_record_created = __('Quiz details successfully created.', 'wp_courseware') . $directionMsg;
	$form->msg_record_updated = __('Quiz details successfully updated.', 'wp_courseware') . $directionMsg;

	$form->setPrimaryKeyValue($quizID);	
	$form->setSaveButtonLabel(__('Save Quiz Settings', 'wp_courseware'));
	
	// Do default checking based on quiz type.
	$form->filterBeforeSaveFunction = 'WPCW_actions_quizzes_beforeQuizSaved';
		
	
	// See if we have a minimum pass to preset?
	if ($adding) {
		$form->loadDefaults(array(
			'quiz_pass_mark' 	=> 50,
			'quiz_show_answers'	=> 'no_answers'				
		));
	}
		
	$page->openPane('wpcw_quiz_details', __('Quiz Details', 'wp_courseware'));		
	$form->show();
	
	
	// Try to see if we've got an ID having saved the form from a first add
	// or we're editing the form
	if ($form->primaryKeyValue > 0)
	{
		$quizID = $form->primaryKeyValue;
		
		$page->openPane('wpcw_quiz_details_questions', __('Quiz Questions', 'wp_courseware'));		
		WPCW_showPage_ModifyQuiz_showQuizEntryForms($quizID, $page);		
	}
	
	$page->showPageFooter();
}


/**
 * Gradebook View - show the grade details for the users of the system. 
 */
function WPCW_showPage_GradeBook()
{
	$page = new PageBuilder(false);
	
	$courseDetails = false;
	$courseID = false;
	
	// Trying to view a specific course	
	$courseDetails = false;
	if (isset($_GET['course_id'])) 
	{
		$courseID 		= $_GET['course_id'] + 0;
		$courseDetails 	= WPCW_courses_getCourseDetails($courseID);
	}
	
	// Abort if course not found.
	if (!$courseDetails)
	{		
		$page->showPageHeader(__('GradeBook', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
		$page->showMessage(__('Sorry, but that course could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}
	
	// Show title of this course
	$page->showPageHeader(__('GradeBook', 'wp_courseware') . ': ' . $courseDetails->course_title, '75%', WPCW_icon_getPageIconURL());
	
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Need a list of all quizzes for this course, excluding surveys.
	$quizzesForCourse = WPCW_quizzes_getAllQuizzesForCourse($courseDetails->course_id);
	
	
	// Handle situation when there are no quizzes.
	if (!$quizzesForCourse) {
		$page->showMessage(__('There are no quizzes for this course, therefore no grade information to show.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}
	
	// Create a simple list of IDs to use in SQL queries
	$quizIDList = array();
	foreach ($quizzesForCourse as $singleQuiz)  {
		$quizIDList[] = $singleQuiz->quiz_id;
	}
	
	// Convert list of IDs into an SQL list
	$quizIDListForSQL = '(' . implode(',', $quizIDList) . ')';
	
	// Do we want certificates?
	$usingCertificates = ('use_certs' == $courseDetails->course_opt_use_certificate);
	
	
	// #### Handle checking if we're sending out any emails to users with their final grades
	// Called here so that any changes are reflected in the table using the code below.
	if ('email_grades' == WPCW_arrays_getValue($_GET, 'action')) {
		WPCW_showPage_GradeBook_handleFinalGradesEmail($courseDetails, $page);
	}
	
	// TODO POSTPONED - Handle hiding specific data of quiz columns if there is too much visually.
		
	// Get the requested page number
	$paging_pageWanted = WPCW_arrays_getValue($_GET, 'pagenum') + 0;
	if ($paging_pageWanted == 0) {
		$paging_pageWanted = 1;
	}
	
	// Need a count of how many there are to mark anyway, hence doing calculation.
	// Using COUNT DISTINCT so that we get a total of the different user IDs.
	// If we use GROUP BY, we end up with several rows of results.
	$userCount_toMark = $wpdb->get_var("
		SELECT COUNT(DISTINCT upq.user_id) AS user_count 
		FROM $wpcwdb->user_progress_quiz upq		
			LEFT JOIN $wpdb->users u ON u.ID = upq.user_id											
		WHERE upq.quiz_id IN $quizIDListForSQL
		  AND upq.quiz_needs_marking > 0
		  AND u.ID IS NOT NULL
		  AND quiz_is_latest = 'latest'
		");
	
	// Count - all users for this course
	$userCount_all = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) AS user_count 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		", $courseDetails->course_id));	
	
	// Count - users who have completed the course.	
	$userCount_completed = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) AS user_count 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		  AND uc.course_progress = 100
		", $courseDetails->course_id));
	
	// Count - all users that need their final grade.
	$userCount_needGrade = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*) AS user_count 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		  AND uc.course_progress = 100
		  AND uc.course_final_grade_sent != 'sent'
		", $courseDetails->course_id));

	
	// SQL Code used by filters below
	$coreSQL_allUsers = $wpdb->prepare("
			SELECT * 
			FROM $wpcwdb->user_courses uc									
				LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
			WHERE uc.course_id = %d
			  AND u.ID IS NOT NULL			
			", $courseDetails->course_id);
	
	
	// The currently selected filter to determine what quizzes to show.
	$currentFilter = WPCW_arrays_getValue($_GET, 'filter');
	switch ($currentFilter)
	{
		case 'to_mark':
			// Chooses all progress where there are questions that need grading.
			// Then group by user, so that we don't show the same user twice.
			// Not added join for certificates, since they can't be complete
			// if they've got stuff to be marked.
			$coreSQL = "
				SELECT * 
				FROM $wpcwdb->user_progress_quiz upq									
					LEFT JOIN $wpdb->users u ON u.ID = upq.user_id					
					LEFT JOIN $wpcwdb->user_courses uc ON uc.user_id = upq.user_id
				WHERE upq.quiz_id IN $quizIDListForSQL
				  AND upq.quiz_needs_marking > 0
				  AND u.ID IS NOT NULL
				  AND quiz_is_latest = 'latest'
				GROUP BY u.ID								  
				";  
			
				// No need to re-calculate, just re-use the number.
				$paging_totalCount = $userCount_toMark;
			break;
			
			
		// Completed the course
		case 'completed':
				// Same SQL as all users, but just filtering those with a progress of 100.
				$coreSQL = $coreSQL_allUsers ." 
					AND uc.course_progress = 100
				";
			
				// The total number of results to show - used for paging
				$paging_totalCount = $userCount_completed;
			break;
			
		// Completed the course
		case 'eligible_for_final_grade':
				// Same SQL as all users, but just filtering those with a progress of 100 AND
				// needing a final grade due to flag in course_progress.
				$coreSQL = $coreSQL_allUsers ." 
					AND uc.course_progress = 100 
					AND course_final_grade_sent != 'sent'
				";
			
				// The total number of results to show - used for paging
				$paging_totalCount = $userCount_needGrade;
			break;
			
		// Default to all users, regardless of what progress they've made
		default:
				$currentFilter = 'all';
							
				// Select all users that exist for this course
				$coreSQL = $coreSQL_allUsers;

				// The total number of results to show - used for paging
				$paging_totalCount = $userCount_all;
			break;
	}
	
	
	// Generate page URL
	$summaryPageURL = admin_url('admin.php?page=WPCW_showPage_GradeBook&course_id=' . $courseDetails->course_id);
	
	$paging_resultsPerPage  = 50; 	
	$paging_recordStart 	= (($paging_pageWanted-1) * $paging_resultsPerPage) + 1;
	$paging_recordEnd 		= ($paging_pageWanted * $paging_resultsPerPage);
	$paging_pageCount 		= ceil($paging_totalCount/$paging_resultsPerPage);	
	$paging_sqlStart		= $paging_recordStart - 1;
		
	// Use the main SQL from above, but limit it and order by user's name. 
	$SQL = "$coreSQL
			ORDER BY display_name ASC
			LIMIT $paging_sqlStart, $paging_resultsPerPage";
			
	// Generate paging code
	$baseURL = WPCW_urls_getURLWithParams($summaryPageURL, 'pagenum')."&pagenum=";
	$paging = WPCW_tables_showPagination($baseURL, $paging_pageWanted, $paging_pageCount, $paging_totalCount, $paging_recordStart, $paging_recordEnd);

		
	$tbl = new TableBuilder();
	$tbl->attributes = array(
		'id' 	=> 'wpcw_tbl_quiz_gradebook',
		'class'	=> 'widefat wpcw_tbl'
	);
			
	$tblCol = new TableColumn(__('Learner Details', 'wp_courseware'), 'learner_details');
	$tblCol->cellClass = "wpcw_learner_details";
	$tbl->addColumn($tblCol);
			
	// ### Add the quiz data
	if ($quizzesForCourse)
	{
		// Show the overall progress for the course.
		$tblCol = new TableColumn(__('Overall Progress', 'wp_courseware'), 'course_progress');
		$tblCol->headerClass = "wpcw_center";
		$tblCol->cellClass = "wpcw_grade_course_progress wpcw_center";
		$tbl->addColumn($tblCol);
		
		
		// ### Create heading for cumulative data.
		$tblCol = new TableColumn(__('Cumulative Grade', 'wp_courseware'), 'quiz_cumulative');
		$tblCol->headerClass = "wpcw_center";
		$tblCol->cellClass = "wpcw_grade_summary wpcw_center";
		$tbl->addColumn($tblCol);
		
		// ### Create heading for cumulative data.
		$tblCol = new TableColumn(__('Grade Sent?', 'wp_courseware'), 'grade_sent');
		$tblCol->headerClass = "wpcw_center";
		$tblCol->cellClass = "wpcw_grade_summary wpcw_center";
		$tbl->addColumn($tblCol);
		
		// ### Create heading for cumulative data.
		if ($usingCertificates)
		{
			$tblCol = new TableColumn(__('Certificate Available?', 'wp_courseware'), 'certificate_available');
			$tblCol->headerClass = "wpcw_center";
			$tblCol->cellClass = "wpcw_grade_summary wpcw_center";
			$tbl->addColumn($tblCol);
		}
		
		
		// ### Add main quiz scores
		foreach ($quizzesForCourse as $singleQuiz)
		{
			$tblCol = new TableColumn($singleQuiz->quiz_title, 'quiz_' . $singleQuiz->quiz_id);
			$tblCol->cellClass = "wpcw_center wpcw_quiz_grade";
			$tblCol->headerClass = "wpcw_center wpcw_quiz_grade";
			$tbl->addColumn($tblCol);
		}			
	}
	
	$urlForQuizResultDetails = admin_url('users.php?page=WPCW_showPage_UserProgess_quizAnswers');
			
	
	$userList = $wpdb->get_results($SQL);
	if (!$userList)
	{
		switch ($currentFilter)
		{
			case 'to_mark':
				$msg = __('There are currently no quizzes that need a manual grade.', 'wp_courseware');				
				break;
				
			case 'eligible_for_final_grade':
				$msg = __('There are currently no users that are eligible to receive their final grade.', 'wp_courseware');				
				break;
				
			case 'completed':
				$msg = __('There are currently no users that have completed the course.', 'wp_courseware');				
				break;
				
			default:
				$msg = __('There are currently no learners allocated to this course.', 'wp_courseware');
				break;
		}
		
		// Create spanning item with message - number of quizzes + fixed columns.
		$rowDataObj = new RowDataSimple('wpcw_no_users wpcw_center', $msg, count($quizIDList) + 5);
		$tbl->addRowObj($rowDataObj);
	}
	
	// We've got some users to show.
	else {
		
		// ### Format main row data and show it.
		$odd = false;
		foreach ($userList as $singleUser)
		{
			$data = array();
			
			// Basic Details with avatar
			$data['learner_details'] = sprintf('
				%s
				<span class="wpcw_col_cell_name">%s</span>
				<span class="wpcw_col_cell_username">%s</span>
				<span class="wpcw_col_cell_email"><a href="mailto:%s" target="_blank">%s</a></span></span>
			', 
				get_avatar($singleUser->ID, 48),
				$singleUser->display_name, 
				$singleUser->user_login, 
				$singleUser->user_email, $singleUser->user_email
			);	
	
			// Get the user's progress for the quizzes.
			if ($quizzesForCourse)
			{
				$quizResults = WPCW_quizzes_getQuizResultsForUser($singleUser->ID, $quizIDListForSQL);
				
				// Track cumulative data
				$quizScoresSoFar = 0;
				$quizScoresSoFar_count = 0;
				
				
				// ### Now render results for each quiz
				foreach ($quizIDList as $aQuizID)
				{
					// Got progress data, process the result
					if (isset($quizResults[$aQuizID])) 
					{
						// Extract results and unserialise the data array.
						$theResults = $quizResults[$aQuizID];
						$theResults->quiz_data = maybe_unserialize($theResults->quiz_data);
						
						
						$quizDetailURL = sprintf('%s&user_id=%d&quiz_id=%d&unit_id=%d', $urlForQuizResultDetails, $singleUser->ID, $theResults->quiz_id, $theResults->unit_id);
						
						// We've got something that needs grading. So render link to where the quiz can be graded.
						if ($theResults->quiz_needs_marking > 0)
						{
							$data['quiz_' . $aQuizID] = sprintf('<span class="wpcw_grade_needs_grading"><a href="%s">%s</span>', $quizDetailURL, __('Manual Grade Required', 'wp_courseware'));
						}
						
						// No quizzes need marking, so show the scores as usual.
						else 
						{
							// Use grade for cumulative grade
							$score = number_format($quizResults[$aQuizID]->quiz_grade, 1);
							$quizScoresSoFar += $score;
							$quizScoresSoFar_count++;
													
							// Render score and link to the full test data.
							$data['quiz_' . $aQuizID] = sprintf('<span class="wpcw_grade_valid"><a href="%s">%s%%</span>', $quizDetailURL, $score);
						}
					} 
					
					// No progress data - quiz not completed yet
					else {
						$data['quiz_' . $aQuizID] = '<span class="wpcw_grade_not_taken">' . __('Not Taken', 'wp_courseware') . '</span>';
					}
				}	
				
				
				// #### Show the cumulative quiz results.
				$data['quiz_cumulative'] = '-';
				if ($quizScoresSoFar_count > 0)
				{
					$data['quiz_cumulative'] = 	'<span class="wpcw_grade_valid">' . number_format(($quizScoresSoFar / $quizScoresSoFar_count), 1) . '%</span>';
				}				
			}
			
			// ####ÊUser Progress
			$data['course_progress'] = WPCW_stats_convertPercentageToBar($singleUser->course_progress);
			
			// #### Grade Sent?
			$data['grade_sent'] = ('sent' == $singleUser->course_final_grade_sent ? __('Yes', 'wp_courseware') : '-');
			
			
			// #### Certificate - Show if there's a certificate that can be downloaded.
			if ($usingCertificates && $certDetails = WPCW_certificate_getCertificateDetails($singleUser->ID, $courseDetails->course_id, false))
			{
				$data['certificate_available'] = sprintf('<a href="%s" title="%s">%s</a>',					 
					WPCW_certificate_generateLink($certDetails->cert_access_key), 
					__('Download the certificate for this user.', 'wp_courseware'),
					__('Yes', 'wp_courseware')
				);
			} 
			else {
				$data['certificate_available'] = '-';
			}
			
			// Odd/Even row colouring.
			$odd = !$odd;
			$tbl->addRow($data, ($odd ? 'alternate' : ''));
		}// single user
	} // Check we have some users.
			
	// Here are the action buttons for Gradebook.
	printf('<div class="wpcw_button_group">');
	
		// Button to generate a CSV of the gradebook. 
		printf('<a href="%s" class="button-primary">%s</a>&nbsp;&nbsp;', 
			admin_url('?wpcw_export=gradebook_csv&course_id=' . $courseDetails->course_id),
			__('Export Gradebook (CSV)', 'wp_courseware')
		);
		
		printf('<a href="%s" class="button-primary">%s</a>&nbsp;&nbsp;', 
			admin_url('admin.php?page=WPCW_showPage_GradeBook&action=email_grades&filter=eligible_for_final_grade&course_id=' . $courseDetails->course_id),
			__('Email Final Grades', 'wp_courseware')
		);
		
		// URL that shows the eligible users who are next to get the email for the final grade.
		$eligibleURL = sprintf(admin_url('admin.php?page=WPCW_showPage_GradeBook&course_id=%d&filter=eligible_for_final_grade'), $courseDetails->course_id);

		// Create information about how people are chosen to send grades to.
		printf('<div id="wpcw_button_group_info_gradebook" class="wpcw_button_group_info">%s</div>',
			sprintf(__('Grades will only be emailed to students who have <b>completed the course</b> and who have <b>not yet received</b> their final grade. 
			   You can see the students who are <a href="%s">eligible to receive the final grade email</a> here.', 'wp_courseware'), $eligibleURL)
		);
	
	printf('</div>');		
	
	
	echo $paging;
	
	// Show the filtering to selectively show different quizzes
	echo WPCW_plugin_table_showFilters(array(
		'all' 						=> sprintf(__('All (%d)', 								'wp_courseware'), $userCount_all),
		'completed' 				=> sprintf(__('Completed (%d)', 						'wp_courseware'), $userCount_completed),
		'eligible_for_final_grade' 	=> sprintf(__('Eligible for Final Grade Email (%d)', 	'wp_courseware'), $userCount_needGrade),
		'to_mark' 					=> sprintf(__('Just Quizzes that Need Marking (%d)', 	'wp_courseware'), $userCount_toMark),
	), 
	WPCW_urls_getURLWithParams($summaryPageURL, 'filter')."&filter=", $currentFilter);
	
	// Finally show table		
	echo $tbl->toString();		
	echo $paging;		
	
	
	$page->showPageFooter();
}


/**
 * Handle sending out emails to users when they have completed the course and we're sending them their final grade.
 * 
 * @param Object $courseDetails The details of the course that we're sending details out for.
 * @param PageBuilder $page The page that's rendering the page structure.
 */
function WPCW_showPage_GradeBook_handleFinalGradesEmail($courseDetails, $page)
{
	// This could take a long time, hence setting time limit to unlimited.
	set_time_limit(0);
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// Count - all users that need their final grade.
	$usersNeedGrades = $wpdb->get_results($wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->user_courses uc									
		LEFT JOIN $wpdb->users u ON u.ID = uc.user_id
		WHERE uc.course_id = %d
		  AND u.ID IS NOT NULL
		  AND uc.course_progress = 100
		  AND uc.course_final_grade_sent != 'sent'
		", $courseDetails->course_id));
	
	// Abort if there's nothing to do, showing a useful error message to the user.
	if (empty($usersNeedGrades))
	{	
		$page->showMessage(
			__('There are currently no users that are eligible to receive their final grade.', 'wp_courseware') . ' ' . 
			__('No emails have been sent.', 'wp_courseware'), 
			true);
		return;
	}
	
	$totalUserCount = count($usersNeedGrades);
	
	//WPCW_debug_showArray($courseDetails);
	
	
	// ### Email Template - Construct the from part of the email
	$headers = false; 
	if ($courseDetails->course_from_email) {
		$headers = sprintf('From: %s <%s>' . "\r\n", $courseDetails->course_from_name, $courseDetails->course_from_email);
	}
			
	
	// Start the status pane to wrap the updates.
	printf('<div id="wpcw_gradebook_email_progress">');
	
		// Little summary of how many users there are.
		printf('<h3>%s <b>%d %s</b>...</h3>', 
			__('Sending final grade emails to', 'wp_courseware'), $totalUserCount, _n('user', 'users', $totalUserCount, 'wp_courseware')
		);
		
		// Get all the quizzes for this course
		$quizIDList 		= array();
		$quizIDListForSQL 	= false;
		$quizzesForCourse 	= WPCW_quizzes_getAllQuizzesForCourse($courseDetails->course_id);
		
		// Create a simple list of IDs to use in SQL queries
		if ($quizzesForCourse)
		{		
			foreach ($quizzesForCourse as $singleQuiz)  {
				$quizIDList[$singleQuiz->quiz_id] = $singleQuiz;
			}
		
			// Convert list of IDs into an SQL list
			$quizIDListForSQL = '(' . implode(',', array_keys($quizIDList)) . ')'; 
		}
				
		
		// Run through each user, and generate their details.
		$userCount = 1;
		foreach ($usersNeedGrades as $aSingleUser)
		{
			printf('<p>%s (%s) - <b>%d%% %s</b></p>', 
				$aSingleUser->display_name, $aSingleUser->user_email,
				number_format(($userCount / $totalUserCount) * 100, 1),
				__('complete', 'wp_courseware')
			);
						
			// Create the template for the subject of the email.
			$template  = $courseDetails->email_complete_course_grade_summary_subject;
			$messageSubject = WPCW_email_replaceTags($template, $courseDetails, $aSingleUser);
			
			
			// Generate the data for all of the quizzes, and add it to the email.
			$quizGradeMessage = "\n";
			
			// Only add quiz summary if we have one!
			if (!empty($quizIDList))
			{
				// Get quiz results for this user
				$quizResults = WPCW_quizzes_getQuizResultsForUser($aSingleUser->ID, $quizIDListForSQL);
				
				// Track cumulative data 
				$quizScoresSoFar = 0;
				$quizScoresSoFar_count = 0;
				
				// ### Now render results for each quiz
				foreach ($quizIDList as $aQuizID => $singleQuiz)
				{
					// Got progress data, process the result
					if (isset($quizResults[$aQuizID])) 
					{
						// Extract results and unserialise the data array.
						$theResults = $quizResults[$aQuizID];
						$theResults->quiz_data = maybe_unserialize($theResults->quiz_data);
						
						// We've got something that needs grading.
						if ($theResults->quiz_needs_marking == 0)
						{
							// Calculate score, and use for cumulative.
							$score = number_format($theResults->quiz_grade);
							$quizScoresSoFar += $score;		
							$quizScoresSoFar_count++;
														
							// Add to string with the quiz name and each grade.
							$quizGradeMessage .= sprintf("%s #%d - %s\n%s: %s%%\n\n",
								__('Quiz', 'wp_courseware'), $quizScoresSoFar_count,
								$singleQuiz->quiz_title,
								__('Grade', 'wp_courseware'), $score
							);
							
						}
					} // end of quiz result check. 
				}	
			} // end of check for quizzes for course
			
			// Calculate the cumulative grade
			$cumulativeGrade = ($quizScoresSoFar_count > 0 ? number_format(($quizScoresSoFar / $quizScoresSoFar_count), 1) . '%' : __('n/a', 'wp_courseware'));
			
			// Substitute all of our data into the email.
			$template = $courseDetails->email_complete_course_grade_summary_body;
			$messageBody = WPCW_email_replaceTags($template, $courseDetails, $aSingleUser);
			$messageBody = str_ireplace('{QUIZ_SUMMARY}', trim($quizGradeMessage), $messageBody);
			$messageBody = str_ireplace('{CUMULATIVE_GRADE}', $cumulativeGrade, $messageBody);
						
			
			// Set up the target email address
			$targetEmail = $aSingleUser->user_email;
						
			// Send the actual email
			if (!wp_mail($targetEmail, $messageSubject, $messageBody, $headers)) {
				error_log('WPCW_email_sendEmail() - email did not send.');
			}
			
			
			// Update the user record to mark as being sent
		    $wpdb->query($wpdb->prepare("
		    	UPDATE $wpcwdb->user_courses
		    	   SET course_final_grade_sent = 'sent'
		    	WHERE user_id = %d
		    	  AND course_id = %d
		    ", $aSingleUser->ID, $courseDetails->course_id));
			
			flush();
			$userCount++;
		}	
		
		// Tell the user we're complete.
		printf('<h3>%s</h3>', __('All done.', 'wp_courseware'));
	
	printf('</div>');
}



/**
 * Generate a list of filters for a table, that ultimately is used to trigger an SQL filter on the view
 * of items in a table.
 * 
 * @param Array $filterList The list of items to use in the filter.
 * @param String $baseURL The string to use at the start of the URL to ensure it works correctly.
 * @param String $activeItem The key that matches the item that's currently selected.
 * 
 * @return String The HTML to render the filter.
 */
function WPCW_plugin_table_showFilters($filterList, $baseURL, $activeItem)
{
	$html = '<div class="subsubsub wpcw_table_filter">';
	foreach ($filterList as $filterKey => $filterLabel)
	{
		$html .= sprintf('<a href="%s%s" class="%s">%s</a>', 
			$baseURL, $filterKey,
			($activeItem == $filterKey ? 'wpcw_table_filter_active' : ''),  
			$filterLabel
		);
	}
	
	return $html . '</div>';
}


/**
 * Handle saving questions to the database.
 * @param Integer $quizID The quiz for which the questions apply to.
 * @param Object $page The associated page object for showing messages. 
 */
function WPCW_showPage_ModifyQuiz_showQuizEntryForms_processSave($quizID, $page)
{
	// No updates have been requested, so exit.
	if (!isset($_POST['survey_updated'])) {
		return;
	}
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();	
	
	$questionsToSave    = array();
	$questionsToSave_New = array();

	
	// Check $_POST data for the 
	foreach ($_POST as $key => $value)
	{
		// #### 1 - Check if we're deleting a question?
		// Not interested in new questions that have been added and then deleted. Just the 
		// ones that were added to the database first.
		if (preg_match('/^delete_wpcw_quiz_details_([0-9]+)$/', $key, $matches))
		{
			$SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->quiz_qs
				WHERE question_id = %d
			", $matches[1]);
			
			$wpdb->query($SQL);
		}
		
		
		// #### 2 - See if we have a question to check for.
		else if (preg_match('/^question_question_(new_question_)?([0-9]+)$/', $key, $matches))
		{
			// Got the ID of the question, now get answers and correct answer.
			$questionID = $matches[2];
			
			// Store the extra string if we're adding a new question.
			$newQuestionPrefix = $matches[1];
			 
			$fieldName_Answers 		= 'question_answer_' . 				$newQuestionPrefix . $questionID;
			$fieldName_Correct 		= 'question_answer_sel_' . 			$newQuestionPrefix . $questionID;
			$fieldName_Type			= 'question_type_' . 				$newQuestionPrefix . $questionID;
			$fieldName_Order		= 'question_order_' . 				$newQuestionPrefix . $questionID;		
			$fieldName_AnswerType 	= 'question_answer_type_' . 		$newQuestionPrefix . $questionID;
			$fieldName_AnswerHint 	= 'question_answer_hint_' . 		$newQuestionPrefix . $questionID;
			
			
				
			
			// Order should be a number
			$questionOrder = 0;
			if (isset($_POST[$fieldName_Order])) {
				$questionOrder = $_POST[$fieldName_Order] + 0;
			} 
			
			// Default types
			$qAns 			= false;
			$qAnsCor 		= false;
			$qAnsType   	= false; // Just used for open question types. 
			$qAnsFileTypes 	= false; // Just used for upload file types.
			
			// Get the hint - Just used for open and upload types. Allow HTML.
			$qAnsHint = trim(WPCW_arrays_getValue($_POST, $fieldName_AnswerHint));
			
			// What type of question do we have?
			$questionType = WPCW_arrays_getValue($_POST, $fieldName_Type);
			switch ($questionType)
			{
				case 'multi':
						$qAns = WPCW_quiz_MultipleChoice::editSave_extractAnswerList($fieldName_Answers);
						$qAnsCor = WPCW_quiz_MultipleChoice::editSave_extractCorrectAnswer($qAns, $fieldName_Correct);
						
						// Provide the UI with at least once slot for an answer.
						if (!$qAns) {
							$qAns = array('', '');
						}
					break;
					
				case 'open':					
						// See if there's a question type that's been sent back to the server.
						$answerTypes = WPCW_quiz_OpenEntry::getValidAnswerTypes();
						$thisAnswerType = WPCW_arrays_getValue($_POST, $fieldName_AnswerType);
						
						// Validate the answer type is in the list. Don't create a default so that user must choose.
						if (isset($answerTypes[$thisAnswerType])) {
							$qAnsType = $thisAnswerType;
						} 
												
						// There's no correct answer for an open question.
						$qAnsCor = false; 
					break;
					
				case 'upload':
						$fieldName_FileType 	= 'question_answer_file_types_' . 	$newQuestionPrefix . $questionID;
					
						// Check new file extension types, parsing them.
						$qAnsFileTypesRaw = WPCW_files_cleanFileExtensionList(WPCW_arrays_getValue($_POST, $fieldName_FileType));
						$qAnsFileTypes = implode(',', $qAnsFileTypesRaw);
					break;
					
				case 'truefalse':
						$qAnsCor = WPCW_quiz_TrueFalse::editSave_extractCorrectAnswer($fieldName_Correct);
					break;
					
					
				// Not expecting anything here... so not handling the error case.
				default:					
					break;
			}
						
			// ### 4 - Save new question data as a list ready for saving to the database.
			
			// New question - so no question ID as yet
			if ($newQuestionPrefix) 
			{
				$questionsToSave_New[] = array(
					'question_question' 		=> stripslashes($value),  // Clean up each answer if slashes used for escape characters.
					'question_answers'			=> ($qAns ? implode("\n", $qAns) : $qAns), // Should be valid or false by this point
					'question_correct_answer' 	=> $qAnsCor,
					'parent_quiz_id'			=> $quizID,
					'question_type'				=> $questionType,
					'question_order'			=> $questionOrder,
					'question_answer_type'		=> $qAnsType,
					'question_answer_hint'		=> stripslashes($qAnsHint),
					'question_answer_file_types' => $qAnsFileTypes,
				
				);
			}
			
			// Existing question - so keep question ID
			else 
			{
				$questionsToSave[$questionID] = array(
					'question_id'				=> $questionID,
					'question_question' 		=> stripslashes($value), // Clean up each answer if slashes used for escape characters.
					'question_answers'			=> ($qAns ? implode("\n", $qAns) : $qAns), // Should be valid or false by this point
					'question_correct_answer' 	=> $qAnsCor,
					'parent_quiz_id'			=> $quizID,
					'question_type'				=> $questionType,
					'question_order'			=> $questionOrder,
					'question_answer_type'		=> $qAnsType,
					'question_answer_hint'		=> stripslashes($qAnsHint),
					'question_answer_file_types' => $qAnsFileTypes,
				);
			}
			
		} // end if question found.
	}
		
	// #### 5 - Check we have existing questions to save
	if (count($questionsToSave))
	{
		// Now save all data back to the database.
		foreach ($questionsToSave as $questionID => $questionDetails)
		{		 
			$wpdb->query(arrayToSQLUpdate($wpcwdb->quiz_qs, $questionDetails, 'question_id'));
		}
	}
	
	// #### 6 - Save the new questions we have
	if (count($questionsToSave_New))
	{
		// Now save all data back to the database.
		foreach ($questionsToSave_New as $questionDetails)
		{		 
			$wpdb->query(arrayToSQLInsert($wpcwdb->quiz_qs, $questionDetails));
		}
	}
	
	// Show an error if questions are missing details.
	$page->showMessage(__('Questions were successfully updated.', 'wp_courseware'));
}



/**
 * Show the forms where the quiz answers can be editied.
 * 
 * @param Integer $quizID the ID of the quiz to be edited.
 * @param Object $page The associated page object for showing messages.
 */
function WPCW_showPage_ModifyQuiz_showQuizEntryForms($quizID, $page)
{
	// Handle the saving of quiz questions
	WPCW_showPage_ModifyQuiz_showQuizEntryForms_processSave($quizID, $page);
	
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// Work out if we need correct answers or not. And what the pass mark is.
	$quizDetails = WPCW_quizzes_getQuizDetails($quizID, true);
	$needCorrectAnswers  = ('survey' != $quizDetails->quiz_type);
	 	
	
	// Got URL in action to ensure we go to top of page to see the success message.
	printf('<form method="post" id="wpcw_question_update_form" action="%s&quiz_id=%d">', admin_url('admin.php?page=WPCW_showPage_ModifyQuiz'), $quizID);
		
	// Show the existing quiz questions as a series of forms.
	$quizItems = WPCW_quizzes_getListOfQuestions($quizID);
	
	
	// Show the number of correct answers the user must get in order to pass.
	if ('quiz_block' == $quizDetails->quiz_type) 
	{
		$totalQs = count($quizItems);
		$passQs  = ceil(($quizDetails->quiz_pass_mark / 100) * $totalQs);
		
		printf('<div class="wpcw_msg wpcw_msg_info">');
		printf(__('The trainee will be required to correctly answer at least <b>%d of the %d</b> following questions (<b>at least %d%%</b>) to progress.', 'wp_courseware'),
			$passQs, $totalQs, $quizDetails->quiz_pass_mark
		);
		printf('</div>');
	}
	
	// Got a  quiz, and trainer is requiring to show answers. Tell them we can't show answers
	// as this quiz contains open-ended questions that need grading.
	if ($needCorrectAnswers && 'show_answers' == $quizDetails->quiz_show_answers && WPCW_quizzes_containsQuestionsNeedingManualGrading($quizItems) )
	{
		printf('<div class="wpcw_msg wpcw_msg_error">');
		
			printf(
			__('This quiz contains questions that need <b>manual grading</b>, and you\'ve selected \'<b>Show Answers</b>\' when the user completes this quiz. ', 'wp_courseware') . '<br/><br/>' .
			__('Since answers cannot be shown to the user because they are not known at that stage, <b>answers cannot be shown</b>. To hide this message, select \'<b>No Answers</b>\' above.')								
			);
		printf('</div>');
	}
	
		
	$errorCount = 0;
	global $errorCount;
		
	// Wrapper for questions
	printf('<ol class="wpcw_dragable_question_holder">');

	if ($quizItems)
	{
		// Render edit form for each of the quizzes that already exist
		foreach ($quizItems as $quizItem)
		{
			switch ($quizItem->question_type)
			{
				case 'multi':
					$quizObj = new WPCW_quiz_MultipleChoice($quizItem);
					break;
					
				case 'truefalse':
					$quizObj = new WPCW_quiz_TrueFalse($quizItem);					
					break;
					
				case 'open':
					$quizObj = new WPCW_quiz_OpenEntry($quizItem);
					break;
					
				case 'upload':
					$quizObj = new WPCW_quiz_FileUpload($quizItem);
					break;
					
				default:
					die(__('Unknown quiz type: ', 'wp_courseware') . $quizItem->question_type);
					break;
			}
			
			$quizObj->showErrors = true;
			$quizObj->needCorrectAnswers = $needCorrectAnswers;
			
			// Keep track of errors
			if ($quizObj && $quizObj->gotError) {
				$errorCount++;
			}

			echo $quizObj->editForm_toString();
		}
	}
		
	printf('</ol>');
	
	// Do any of the questions have residual errors? Tell the user.
	if ($errorCount > 0) 
	{
		$page->showMessage(sprintf(__('%d of the questions below have errors. Please make corrections and then save the changes.', 'wp_courseware'), $errorCount), true);
	}
	
	
	$page->showPageMiddle('35%');

	
	// Show the menu for saving and adding new items.
	WPCW_showPage_ModifyQuiz_FloatMenu($page);
	
	// Flag to indicate that questions have been updated.
	printf('<input type="hidden" name="survey_updated" value="survey_updated" />');
	
	printf('</form><a name="new_question"></a>');
	
	// The empty forms for adding a new question		
	$quizItemDummy = new stdClass();
	$quizItemDummy->question_question 		= '';
	$quizItemDummy->question_answers 		= "\n\n\n";
	$quizItemDummy->question_correct_answer = false;
	$quizItemDummy->question_order 			= 0;
	$quizItemDummy->question_answer_type 	= false;
	$quizItemDummy->question_answer_hint 	= false;
	$quizItemDummy->question_answer_file_types 	= 'doc, pdf, jpg, png, jpeg, gif';
	
	
	$quizFormsToCreate = array (
		'new_multi' 	=> 'WPCW_quiz_MultipleChoice',
		'new_tf' 		=> 'WPCW_quiz_TrueFalse',
		'new_open' 		=> 'WPCW_quiz_OpenEntry',
		'new_upload' 	=> 'WPCW_quiz_FileUpload',
	);
	
	// Create the dummy quiz objects
	foreach ($quizFormsToCreate as $dummyid => $objClass)
	{
		// Set placeholder class
		$quizItemDummy->question_id  = $dummyid;
		
		// Create new object and set it up with defaults
		$quizObj = new $objClass($quizItemDummy);
		
		$quizObj->cssClasses = 'wpcw_question_template';
		$quizObj->showErrors = false;
		$quizObj->needCorrectAnswers = $needCorrectAnswers;
		
		echo $quizObj->editForm_toString();
	}

}


/**
 * Creates the floating menu for adding quiz items.
 */
function WPCW_showPage_ModifyQuiz_FloatMenu($page)
{
	?>
	<div class="wpcw_floating_menu" id="wpcw_add_quiz_menu">
		
		<div class="wpcw_add_quiz_block">
			<div class="wpcw_add_quiz_title"><?php _e('Add Quiz/Survey Field', 'wp_courseware'); ?></div>
			<div class="wpcw_add_quiz_options">
				<a href="#new_question" class="button-secondary" id="wpcw_add_question_multi"><?php _e('Add Multiple Choice', 'wp_courseware'); ?></a><br/>
				<a href="#new_question" class="button-secondary" id="wpcw_add_question_truefalse"><?php _e('Add True/False', 'wp_courseware'); ?></a><br/>
				<a href="#new_question" class="button-secondary" id="wpcw_add_question_open"><?php _e('Add Open Ended Question', 'wp_courseware'); ?></a><br/>
				<a href="#new_question" class="button-secondary" id="wpcw_add_question_upload"><?php _e('Add File Upload Question', 'wp_courseware'); ?></a><br/>
			</div>
		</div>
		
		<?php
			// Keep track of new questions so that they all get a new ID. 
			printf('<div id="wpcw_question_template_count" class="wpcw_question_template">0</div>'); 
		?>
		
		<div class="wpcw_add_quiz_save">
			<input type="submit" class="button-primary" value="Save Questions" />
		</div>
	</div>
	
	
	
	<?php 
}



/**
 * Function that show a summary of the training courses.
 */
function WPCW_showPage_QuizSummary() 
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Get the requested page number
	$paging_pageWanted = WPCW_arrays_getValue($_GET, 'pagenum') + 0;
	if ($paging_pageWanted == 0) {
		$paging_pageWanted = 1;
	}
	
	// Title for page with page number
	$titlePage = false;
	if ($paging_pageWanted > 1) {
		$titlePage = sprintf(' - %s %s', __('Page', 'wp_courseware'), $paging_pageWanted);
	}
	
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Quiz &amp; Survey Summary', 'wp_courseware').$titlePage, '75%', WPCW_icon_getPageIconURL());
	
	
	// Handle the quiz deletion before showing remaining quizzes...
	WPCW_quizzes_handleQuizDeletion($page);	
	

	// Was a search string specified? Or a specific item?
	$searchString = WPCW_arrays_getValue($_GET, 's');
	
	// Create WHERE string based search - Title or Description of Quiz
	$stringWHERE = false;
	if ($searchString) {
		$stringWHERE = sprintf(" WHERE quiz_title LIKE '%%%s%%' OR quiz_desc LIKE '%%%s%%' ", 
			$wpdb->escape($searchString),
			$wpdb->escape($searchString)
		);		
	}
	
	$summaryPageURL = admin_url('admin.php?page=WPCW_showPage_QuizSummary');
	
	// Show the form for searching						
	?>			
	<form id="wpcw_quizzes_search_box" method="get" action="<?php echo $summaryPageURL; ?>">
	<p class="search-box">
		<label class="screen-reader-text" for="wpcw_quizzes_search_input"><?php _e('Search Quizzes', 'wp_courseware'); ?></label>
		<input id="wpcw_quizzes_search_input" type="text" value="<?php echo $searchString ?>" name="s"/>
		<input class="button" type="submit" value="<?php _e('Search Quizzes', 'wp_courseware'); ?>"/>
		
		<input type="hidden" name="page" value="WPCW_showPage_QuizSummary" />
	</p>
	</form>
	<br/><br/>
	<?php 	
	
	$SQL_PAGING = "
			SELECT COUNT(*) as quiz_count 
			FROM $wpcwdb->quiz			
			$stringWHERE
			ORDER BY quiz_title ASC 
		";
	
	$paging_resultsPerPage  = 50;
	$paging_totalCount		= $wpdb->get_var($SQL_PAGING);
	$paging_recordStart 	= (($paging_pageWanted-1) * $paging_resultsPerPage) + 1;
	$paging_recordEnd 		= ($paging_pageWanted * $paging_resultsPerPage);
	$paging_pageCount 		= ceil($paging_totalCount/$paging_resultsPerPage);	
	$paging_sqlStart		= $paging_recordStart - 1;

	// Show search message - that a search has been tried.
	if ($searchString) 
	{
		printf('<div class="wpcw_search_count">%s "%s" (%s %s) (<a href="%s">%s</a>)</div>',
			__('Search results for', 'wp_courseware'), 
			htmlentities($searchString), 
			$paging_totalCount,
			_n('result', 'results', $paging_totalCount, 'wp_courseware'),  
			$summaryPageURL,
			__('reset', 'wp_courseware')
		);
	}	
		
	// Do main query
	$SQL = "SELECT * 
			FROM $wpcwdb->quiz			
			$stringWHERE
			ORDER BY quiz_title ASC
			LIMIT $paging_sqlStart, $paging_resultsPerPage
			";
			
	// Generate paging code
	$baseURL = WPCW_urls_getURLWithParams($summaryPageURL, 'pagenum')."&pagenum=";
	$paging = WPCW_tables_showPagination($baseURL, $paging_pageWanted, $paging_pageCount, $paging_totalCount, $paging_recordStart, $paging_recordEnd);
			
	
	$quizzes = $wpdb->get_results($SQL);
	if ($quizzes)  
	{		
		$tbl = new TableBuilder();
		$tbl->attributes = array(
			'id' 	=> 'wpcw_tbl_quiz_summary',
			'class'	=> 'widefat wpcw_tbl'
		);
		
		$tblCol = new TableColumn(__('ID', 'wp_courseware'), 'quiz_id');		
		$tblCol->cellClass = "quiz_id";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Quiz Title', 'wp_courseware'), 'quiz_title');
		$tblCol->cellClass = "quiz_title";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Description', 'wp_courseware'), 'quiz_desc');
		$tblCol->cellClass = "quiz_desc";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Associated Unit', 'wp_courseware'), 'associated_unit');
		$tblCol->cellClass = "associated_unit";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Quiz Type', 'wp_courseware'), 'quiz_type');
		$tblCol->cellClass = "quiz_type";
		$tbl->addColumn($tblCol);
				
		$tblCol = new TableColumn(__('Show Answers', 'wp_courseware'), 'quiz_show_answers');
		$tblCol->cellClass = "quiz_type wpcw_center";
		$tbl->addColumn($tblCol);		
		
		
		$tblCol = new TableColumn(__('Total Questions', 'wp_courseware'), 'total_questions');
		$tblCol->cellClass = "total_questions wpcw_center";
		$tbl->addColumn($tblCol);
						
		$tblCol = new TableColumn(__('Actions', 'wp_courseware'), 'actions');
		$tblCol->cellClass = "actions";
		$tbl->addColumn($tblCol);
		
		// Stores course details in a mini cache to save lots of MySQL lookups.
		$miniCourseDetailCache = array();
		
		// Format row data and show it.
		$odd = false;
		foreach ($quizzes as $quiz)
		{
			$data = array();
			
			// URLs
			$editURL   = admin_url('admin.php?page=WPCW_showPage_ModifyQuiz&quiz_id=' . $quiz->quiz_id);
			
			// Maintain paging where possible.
			$deleteURL = $baseURL . '&action=delete&quiz_id=' . $quiz->quiz_id;			
			
			
			// Basic Details
			$data['quiz_id']  	= $quiz->quiz_id;					
			$data['quiz_desc']  = $quiz->quiz_desc;
			$data['quiz_title']  	= sprintf('<a href="%s">%s</a>', $editURL, $quiz->quiz_title);
			
			// Associated Unit
			if ($quiz->parent_unit_id > 0 && $unitDetails = get_post($quiz->parent_unit_id))
			{
				$data['associated_unit'] = sprintf('<b>%s</b>: <a href="%s" target="_blank" title="%s \'%s\'...">%s</a>',
					__('Unit', 'wp_courseware'), 
					get_permalink($unitDetails->ID),
					__('View ', 'wp_courseware'), 
					$unitDetails->post_title,
					$unitDetails->post_title
				);
				
				// Also add associated course
				if (isset($miniCourseDetailCache[$quiz->parent_course_id])) {
					$courseDetails = $miniCourseDetailCache[$quiz->parent_course_id];
				}
				
				else {
					// Save course details to cache (as likely to use it again).
					$courseDetails = $miniCourseDetailCache[$quiz->parent_course_id] = WPCW_courses_getCourseDetails($quiz->parent_course_id); 
				}
				 
				$data['associated_unit'] .= sprintf('<br/><b>%s:</b> <a href="admin.php?page=WPCW_showPage_ModifyCourse&course_id=%d" title="%s \'%s\'...">%s</a>',
					__('Course', 'wp_courseware'),
					$courseDetails->course_id,
					__('Edit ', 'wp_courseware'), 
					$courseDetails->course_title,
					$courseDetails->course_title
				);
			} 
			// No associated unit yet
			else {
				$data['associated_unit'] = 'n/a';
			}
			
			// Showing Answers?
			$data['quiz_show_answers'] = ('show_answers' == $quiz->quiz_show_answers ? __('Yes', 'wp_courseware') : '-');
			
			// Type of quiz
			$data['quiz_type'] = WPCW_quizzes_getQuizTypeName($quiz->quiz_type);
			if ('quiz_block' == $quiz->quiz_type) 
			{
				$data['quiz_type'] .= '<span class="wpcw_quiz_pass_info">' . sprintf(__('Min. Pass Mark of %d%%', 'wp_courseware'), $quiz->quiz_pass_mark) . '</span>';
			}
			
			// Total number of questions
			$totalQuestions = $wpdb->get_var($wpdb->prepare("
					SELECT COUNT(*) as total_questions 
					FROM $wpcwdb->quiz_qs
					WHERE parent_quiz_id = %d
			", $quiz->quiz_id));
			
			$data['total_questions'] = $totalQuestions + 0;
			
									
			// Actions
			$data['actions']	= '<ul class="wpcw_action_link_list">';
			$data['actions']	.= sprintf('<li><a href="%s" class="button-primary">%s</a></li>', 	$editURL, 	__('Edit', 'wp_courseware'));
			
			$data['actions']	.= sprintf('<li><a href="%s" class="button-secondary wpcw_action_link_delete_quiz wpcw_action_link_delete" rel="%s">%s</a></li>', 	
					$deleteURL,
					__('Are you sure you wish to delete this quiz?', 'wp_courseware'), 	
					__('Delete', 'wp_courseware'));
											
			$data['actions']	.= '</ul>';
			
			// Odd/Even row colouring.
			$odd = !$odd;
			$tbl->addRow($data, ($odd ? 'alternate' : ''));
		}
				
		// Finally show table
		echo $paging;
		echo $tbl->toString();		
		echo $paging;		
	}
	
	else {
		printf('<p>%s</p>', __('There are currently no quizzes to show. Why not create one?', 'wp_courseware'));
	}
	
	$page->showPageFooter();
}




/**
 * Shows the settings page for the plugin.
 */
function WPCW_showPage_Settings()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Training Courses - Settings', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	// Check for update flag
	if (isset($_POST['update']) && $_POST['update'] == 'tables_force_upgrade')
	{
		$page->showMessage(__('Upgrading WP Courseware Tables...', 'wp_courseware'));
		flush();		

		$installed_ver  = get_option(WPCW_DATABASE_KEY) + 0;
		
		WPCW_database_upgradeTables($installed_ver, true, true); 
		$page->showMessage(sprintf(__('%s tables have successfully been upgraded.', 'wp_courseware'), 'WP Courseware') );
	}
	
	
	
	$settingsFields = array(
		'section_access_key' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Licence Key Settings', 'wp_courseware')),
			),			
			
		'licence_key' => array(
				'label' 	=> __('Licence Key', 'wp_courseware'),
				'type'  	=> 'text',
				'desc'  	=> __('Your licence key for the WP Courseware plugin.', 'wp_courseware'), 
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 32,
					'minlen'	=> 32,
					'regexp'	=> '/^[A-Za-z0-9]+$/',
					'error'		=> __('Please enter your 32 character licence key, which contains only letters and numbers.', 'wp_courseware'),
				)	
			), 		
	
			
		'section_default_css' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Style &amp; Design Settings', 'wp_courseware')),
			),
			
			
		'use_default_css' => array(
				'label' 	=> __('Use Default CSS?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'show_css' 	=> sprintf('<b>%s</b> - %s', __('Yes', 'wp_courseware'), __('Use default stylesheet for the frontend of the website.', 'wp_courseware')),
					'hide_css' 	=> sprintf('<b>%s</b> - %s', __('No', 'wp_courseware'), __('Don\'t use the default stylesheet for the frontend of the website (you\'ll write your own CSS)', 'wp_courseware')),
				),
				'desc'  	=> __('If you want to style your training course material yourself, you can disable the default stylesheet. If in doubt, select <b>Yes</b>.', 'wp_courseware'),
			),	
			
		'section_link' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Powered By Link', 'wp_courseware')),
			),			
			
		'show_powered_by' => array(
				'label' 	=> __('Show Powered By Link?', 'wp_courseware'),
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'show_link' 	=> sprintf('<b>%s</b> - %s', __('Yes', 'wp_courseware'), __('Show the <em>\'Powered By WP Courseware\'</em> link.', 'wp_courseware')),
					'hide_link' 	=> sprintf('<b>%s</b> - %s', __('No', 'wp_courseware'), __('Don\'t show any powered-by links.', 'wp_courseware')),
				),
				'desc'  	=> __("Do you want to show a 'Powered By WP Courseware' link at the bottom of course outlines?", 'wp_courseware'),
			),

		'clickbank_id' => array(
				'label' 	=> __('Your Clickbank ID', 'wp_courseware'),
				'type'  	=> 'text',
				'desc'  	=> __("(Optional) Earn some money by providing your Your Clickbank Affiliate ID, which will turn the <b>Powered By WP Courseware</b> into an affiliate link that earns you a percentage of every sale!", 'wp_courseware'), 
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[A-Za-z0-9\-_]+$/',
					'error'		=> __('Please enter your Clickbank Affiliate ID, which contains only letters, numbers, hypens or underscores.', 'wp_courseware'),
				)	
			),
		);
		
	
	// Remove licence key for child multi-sites
	if (!WPCW_plugin_hasAdminRights()) 
	{
		unset($settingsFields['section_access_key']);
		unset($settingsFields['licence_key']);	
	}
				
	$settings = new SettingsForm($settingsFields, WPCW_DATABASE_SETTINGS_KEY, 'wpcw_form_settings_general');
	$settings->setSaveButtonLabel(__('Save ALL Settings', 'wp_courseware'));
	
	// Update messages for translation
	$settings->msg_settingsSaved   	= __('Settings successfully saved.', 'wp_courseware');
	$settings->msg_settingsProblem 	= __('There was a problem saving the settings.', 'wp_courseware'); 	
	$settings->customFormErrorMsg = __('Sorry, but unfortunately there were some errors saving the course details. Please fix the errors and try again.', 'wp_courseware');
	$settings->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
	
	// Form event handlers - processes the saved settings in some way 
	$settings->afterSaveFunction = 'WPCW_showPage_Settings_afterSave';
		
	$settings->show();	
	
	
	// Create little form to force upgrading tables if something went wrong during update.
	echo WPCW_forms_createBreakHTML(__("Upgrade Tables", 'wp_courseware'), false, true, 'wpcw_upgrade_tables');
	?>	
	<p><?php _e("If you're getting any errors with WP Courseware relating to database tables when you've updated, you can force an upgrade of the database tables using the button below.", 'wp_courseware'); ?></p>
	<?php
	
	$form = new FormBuilder('tables_force_upgrade');
	$form->setSubmitLabel(__('Force Table Upgrade', 'wp_courseware'));	
	echo $form->toString();

	
	
	// RHS Support Information
	$page->showPageMiddle('23%');	
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);	
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	$page->showPageFooter();
}


/**
 * Function called after settings are saved.
 * 
 * @param String $formValuesFiltered The data values actually saved to the database after filtering.
 * @param String $originalFormValues The original data values before filtering.
 * @param Object $formObj The form object thats doing the saving.
 */
function WPCW_showPage_Settings_afterSave($formValuesFiltered, $originalFormValues, $formObj)
{
	// Can't update licence key unless admin for site.
	if (!WPCW_plugin_hasAdminRights()) {
		return false;
	}
	
	// Update the licence key for the plugin, in case it's changed.
	global $updater_wpcw;
	$updater_wpcw->setAccessKey($formValuesFiltered['licence_key'], true);
}



/**
 * Shows the settings page for the plugin, shown just for the network page.
 */
function WPCW_showPage_Settings_Network()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('WP Courseware - Settings', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	
	$settingsFields = array(
		'section_access_key' 	=> array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Licence Key Settings', 'wp_courseware'), false, true),
			),			
			
		'licence_key' => array(
				'label' 	=> __('Licence Key', 'wp_courseware'),
				'type'  	=> 'text',
				'desc'  	=> __('Your licence key for the WP Courseware plugin.', 'wp_courseware'), 
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 32,
					'minlen'	=> 32,
					'regexp'	=> '/^[A-Za-z0-9]+$/',
					'error'		=> __('Please enter your 32 character licence key, which contains only letters and numbers.', 'wp_courseware'),
				)	
			), 		
		);
		
				
	$settings = new SettingsForm($settingsFields, WPCW_DATABASE_SETTINGS_KEY, 'wpcw_form_settings_general');
	
	// Set strings and messages
	$settings->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
	$settings->setSaveButtonLabel('Save ALL Settings', 'wp_courseware');
	
	// Form event handlers - processes the saved settings in some way 
	$settings->afterSaveFunction = 'WPCW_showPage_Settings_afterSave';
		
	$settings->show();	
	
	
	// RHS Support Information
	$page->showPageMiddle('23%');	
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);	
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	$page->showPageFooter();
}

/**
 * Convert page/post to a course unit 
 */
function WPCW_showPage_ConvertPage()
{
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Convert Page/Post to Course Unit', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	// Future Feature - Check user can edit other people's pages - use edit_others_pages or custom capability.
	if (!current_user_can('manage_options')) {
		$page->showMessage(__('Sorry, but you are not allowed to edit this page/post.', 'wp_courseware'), true);
		$page->showPageFooter();
		return false;
	}
	
	// Check that post ID is valid
	$postID = WPCW_arrays_getValue($_GET, 'postid') + 0;
	$convertPost = get_post($postID);
	if (!$convertPost) {
		$page->showMessage(__('Sorry, but the specified page/post does not appear to exist.', 'wp_courseware'), true);
		$page->showPageFooter();
		return false;
	}
	
	// Check that post isn't already a course unit before trying change. 
	// This is where the conversion takes place.	
	if ('course_unit' != $convertPost->post_type)
	{
		// Confirm we want to do the conversion
		if (!isset($_GET['confirm']))
		{
			$message = sprintf(__('Are you sure you wish to convert the <em>%s</em> to a course unit?', 'wp_courseware'), $convertPost->post_type);
			$message .= '<br/><br/>';
			
			// Yes Button
			$message .= sprintf('<a href="%s&postid=%d&confirm=yes" class="button-primary">%s</a>', 
				admin_url('admin.php?page=WPCW_showPage_ConvertPage'), 
				$postID,
				__('Yes, convert it', 'wp_courseware')
			);
			
			// Cancel
			$message .= sprintf('&nbsp;&nbsp;<a href="%s&postid=%d&confirm=no" class="button-secondary">%s</a>', 
				admin_url('admin.php?page=WPCW_showPage_ConvertPage'), 
				$postID,
				__('No, don\'t convert it', 'wp_courseware')
			);
			
			
			$page->showMessage($message);
			$page->showPageFooter();
			return false;
		}
		
		
		// Handle the conversion confirmation
		else 
		{
			// Confirmed conversion
			if ($_GET['confirm'] == 'yes')
			{
				$postDetails 				= array();
  				$postDetails['ID'] 			= $postID;
  				$postDetails['post_type'] 	= 'course_unit';
  				
  				// Update the post into the database
  				wp_update_post($postDetails);
			}
			
			// Cancelled conversion
			if ($_GET['confirm'] != 'yes') 
			{
				$page->showMessage(__('Conversion to a course unit cancelled.', 'wp_courseware'), false);
				$page->showPageFooter();
				return false;
			}
		}
  		
	}
	
	// Check conversion happened
	$convertedPost = get_post($postID);
	if ('course_unit' == $convertedPost->post_type)
	{
		$page->showMessage(sprintf(__('The page/post was successfully converted to a course unit. You can <a href="%s">now edit the course unit</a>.', 'wp_courseware'),
			admin_url(sprintf('post.php?post=%d&action=edit', $postID))
		));
	}
	
	else {
		$page->showMessage(__('Unfortunately, there was an error trying to convert the page/post to a course unit. Perhaps you could try again?', 'wp_courseware'), true);
	}
	
	$page->showPageFooter();
}


/**
 * Shows the page to do with importing/exporting training courses.
 */
function WPCW_showPage_ImportExport()
{
	switch (WPCW_arrays_getValue($_GET, 'show'))
	{
		case 'import':
			WPCW_showPage_ImportExport_import();
			break;
			
		case 'import_users':
			WPCW_showPage_ImportExport_importUsers();
			break;
			
		default:
			WPCW_showPage_ImportExport_export();
			break;
	}
}

/**
 * Shows the menu where the user can select the import or export page.
 * @param String $currentPage The currently selected page.
 */
function WPCW_showPage_ImportExport_menu($currentPage)
{
	printf('<div id="wpcw_menu_import_export">');
	
	switch ($currentPage)
	{
		case 'import':
			printf('<span><a href="%s">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Export Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><b>%s</b></span>', __('Import Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import_users">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Users', 'wp_courseware'));
			break;
			
		case 'import_users':
			printf('<span><a href="%s">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Export Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><b>%s</b></span>', __('Import Users', 'wp_courseware'));
			break;
			
		default:
			printf('<span><b>%s</b></span>', __('Export Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Course', 'wp_courseware'));
			printf('&nbsp;|&nbsp;');
			printf('<span><a href="%s&show=import_users">%s</a></span>', admin_url('admin.php?page=WPCW_showPage_ImportExport'), __('Import Users', 'wp_courseware'));
			break;
	}	

	
	printf('</div>');
}


/**
 * Show the export course page.
 */
function WPCW_showPage_ImportExport_export()
{	
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Export Training Course', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	// Show form of courses that can be exported.
	$form = new FormBuilder('wpcw_export');
	$form->setSubmitLabel(__('Export Course', 'wp_courseware'));
	
	// Course selection
	$formElem = new FormElement('export_course_id', __('Course to Export', 'wp_courseware'), true);
	$formElem->setTypeAsComboBox(WPCW_courses_getCourseList(__('--- Select a course to export ---', 'wp_courseware')));
	$form->addFormElement($formElem);
	
	// Options for what to export
	$formElem = new FormElement('what_to_export', __('Course to Export', 'wp_courseware'), true);
	$formElem->setTypeAsRadioButtons(array(
		'whole_course'		=> __('<b>All</b> - The whole course, modules and units.', 'wp_courseware'),
		'just_course'		=> __('<b>Just the Course</b> - Just the course title, description and settings (no units or modules).', 'wp_courseware'),	
		'course_modules'	=> __('<b>Course and Modules</b> - Just the course settings and module settings (no units).', 'wp_courseware'),
	));
	$form->addFormElement($formElem);
	
	$form->setDefaultValues(array(
		'what_to_export' => 'whole_course'
	));
	
	
	
	
	if ($form->formSubmitted())
	{
		// Do the full export
		if ($form->formValid()) {
			// If data is valid, export will be handled by export class.  
		}
		
		// Show errors
		else  {			
			$page->showListOfErrors($form->getListOfErrors(), __('Sorry, but unfortunately there were some errors. Please fix the errors and try again.', 'wp_courseware'));
		}
	}
	
	
	// Show selection menu for import/export to save pages
	WPCW_showPage_ImportExport_menu('export');	
	
	printf('<p class="wpcw_doc_quick">');
	_e('When you export a course, you\'ll get an <b>XML file</b>, which you can then <b>import into another WordPress website</b> that\'s running <b>WP Courseware</b>.<br/> 
	    When you export the course units with a course, just the <b>HTML to render images and video</b> will be copied, but the <b>actual images and video files will not be exported</b>.', 'wp_courseware');
	printf('</p>');
	
	echo $form->toString();
	
	$page->showPageFooter();
}


/**
 * Show the import course page.
 */
function WPCW_showPage_ImportExport_import()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Import Training Course', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());	
	
		
	
	// Show selection menu for import/export to save pages	
	WPCW_showPage_ImportExport_menu('import');
	
	
	// Show form to import some XML
	$form = new FormBuilder('wpcw_import');
	$form->setSubmitLabel(__('Import Course', 'wp_courseware'));
	
	// Course upload for XML file
	$formElem = new FormElement('import_course_xml', __('Course Import XML File', 'wp_courseware'), true);
	$formElem->setTypeAsUploadFile();
	$form->addFormElement($formElem);
	
	
	if ($form->formSubmitted())
	{
		// Do the full export
		if ($form->formValid()) 
		{
			// Handle the importing/uploading
			WPCW_courses_importCourseFromFile($page);
		}
		
		// Show errors
		else  {
			$page->showListOfErrors($form->getListOfErrors(), __('Unfortunately, there were some errors trying to import the CSV file.', 'wp_courseware'));
		}
	}
	
	// Workout maximum upload size
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
	
	printf('<p class="wpcw_doc_quick">');
	printf(__('You can import any export file created by <b>WP Courseware</b> using the form below.', 'wp_courseware') . ' ' . __('The <b>maximum upload file size</b> for your server is <b>%d MB</b>.', 'wp_courseware'), $upload_mb);
	printf('</p>');
	
	echo $form->toString();
	
	$page->showPageFooter();
}




/**
 * Show the import course page.
 */
function WPCW_showPage_ImportExport_importUsers()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Import Users from CSV File', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());	
	
		
	// Show selection menu for import/export to save pages	
	WPCW_showPage_ImportExport_menu('import_users');
	
	
	// Show form to import some XML
	$form = new FormBuilder('wpcw_import_users');
	$form->setSubmitLabel(__('Import Users', 'wp_courseware'));
	
	// Course upload for XML file
	$formElem = new FormElement('import_course_csv', __('User Import CSV File', 'wp_courseware'), true);
	$formElem->setTypeAsUploadFile();
	$form->addFormElement($formElem);
	
	
	if ($form->formSubmitted())
	{
		// Do the full export
		if ($form->formValid()) 
		{
			// Handle the importing/uploading
			WPCW_users_importUsersFromFile($page);
		}
		
		// Show errors
		else  {
			$page->showListOfErrors($form->getListOfErrors(), __('Unfortunately, there were some errors trying to import the XML file.', 'wp_courseware'));
		}
	}
	
	// Workout maximum upload size
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);
	
	printf('<p class="wpcw_doc_quick">');
	printf(__('You can import a CSV file of users using the form below.', 'wp_courseware') . ' ' . __('The <b>maximum upload file size</b> for your server is <b>%d MB</b>.', 'wp_courseware'), $upload_mb);
	printf('</p>');	
	
	echo $form->toString();
	
	
	printf('<br/><br/><div class="wpcw_docs_wrapper">');
		printf('<b>%s</b>', __('Some tips for importing users via a CSV file:', 'wp_courseware'));
		printf('<ul>');
			printf('<li>' . __('If a user email address already exists, then just the courses are updated for that user.', 'wp_courseware'));
			printf('<li>' . __('User names are generated from the first and last name information. If a user name already exists, then a unique username is generated.', 'wp_courseware'));
			printf('<li>' . __('To add a user to many courses, just separate those course IDs with a comma in the <code>courses_to_add_to</code> column.', 'wp_courseware'));
			printf('<li>' . __('If a user is created, any courses set to be automatically assigned will be done first, and then the courses added in the <code>courses_to_add_to</code> column.', 'wp_courseware'));
			printf('<li>' . __('You can download an <a href="%s">example CSV file here</a>.', 'wp_courseware') . '</li>', 								admin_url('?wpcw_export=csv_import_user_sample'));	
			printf('<li>' . __('The IDs for the training courses can be found on the <a href="%s">course summary page</a>.', 'wp_courseware'). '</li>', admin_url('admin.php?page=WPCW_wp_courseware'));
		printf('</ul>');
	printf('</div>');
	
	$page->showPageFooter();
}


/**
 * Handle the quiz deletion from the summary page.
 * @param PageBuilder $page The page rendering object.
 */
function WPCW_quizzes_handleQuizDeletion($page)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	// Check that the quiz exists and deletion has been requested
	if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['quiz_id']))
	{
		$quizID = $_GET['quiz_id'];
		$quizDetails = WPCW_quizzes_getQuizDetails($quizID, false);
		
		// Only do deletion if quiz details are valid.
		if ($quizDetails)
		{
			// Delete quiz questions
			$wpdb->query($wpdb->prepare("
				DELETE FROM $wpcwdb->quiz_qs 
				WHERE parent_quiz_id = %d
			", $quizDetails->quiz_id));
			
			// Delete user progress
			$wpdb->query($wpdb->prepare("
				DELETE FROM $wpcwdb->user_progress_quiz 
				WHERE quiz_id = %d
			", $quizDetails->quiz_id));
			
			// Finally delete quiz itself
			$wpdb->query($wpdb->prepare("
				DELETE FROM $wpcwdb->quiz 
				WHERE quiz_id = %d
			", $quizDetails->quiz_id));
			
			
			$page->showMessage(sprintf(__('The quiz \'%s\' was successfully deleted.', 'wp_courseware'), $quizDetails->quiz_title));
			
		} // end of if $quizDetails
		
	} // end of check for deletion action
}


/**
 * Function called before a module is being saved.
 * 
 * @param Array $originalFormValues The raw form values.
 * @param Object $thisObject The reference to the form object doing the saving. 
 */ 
function WPCW_actions_quizzes_beforeQuizSaved($originalFormValues, $thisObject)
{
	// Ensure if survey is selected, that no answers are set up.
	if ('survey' == $originalFormValues['quiz_type']) {	
		$originalFormValues['quiz_show_answers'] = 'no_answers';
	}
	
	return $originalFormValues;
}


/**
 * Get a list of pages, with heirarchy, set as ID => Page Title in an array.
 * @return Array The page list as an array.
 */
function WPCW_pages_getPageList()
{
	$args= array(
		'echo' => 0 
	);
 
	// Find all values and options, and return as an array of IDs to Page Title with indents.
	if (preg_match_all('/<option(.+?)value="(.+?)">(.+?)<\/option>/i', wp_dropdown_pages($args), $matches)) 
	{
		$blank = array('' => __('---- No Page Selected ----', 'wp_courseware'));
		
		return array_merge($blank, array_combine ($matches[2], $matches[3]));
	}
	return false;
}


/**
 * Handles the upload and import of the course file.
 * @param Object $page The page object to show messages.
 */
function WPCW_courses_importCourseFromFile($page)
{
	if (isset($_FILES['import_course_xml']['name']))
	{
		// See what type of file we're tring to upload
		$type = strtolower($_FILES['import_course_xml']['type']);
		$fileTypes = array(
			'text/xml',
			'application/xml',
		);
		
		if (!in_array($type, $fileTypes)) {
			$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t XML.', 'wp_courseware'), true);
			return false;
		}		
		
		// Filetype is fine, carry on
		$errornum = $_FILES['import_course_xml']['error'] + 0;
		$tempfile = $_FILES['import_course_xml']['tmp_name'];
		
		
		// File uploaded successfully?				
		if ($errornum == 0)
		{
			// Try the import, return error/success here
			$importResults = WPCW_Import::importTrainingCourseFromXML($tempfile);
			if ($importResults['errors']) 
			{
				$page->showListOfErrors($importResults['errors'], __('Unfortunately, there were some errors trying to import the XML file.', 'wp_courseware'));
			} 
			
			// All worked - so show a link to the newly created course here.
			else 
			{
				$message = __('The course was successfully imported.', 'wp_courseware') . '<br/><br/>'; 
				$message .= sprintf(__('You can now <a href="%s">edit the Course Settings</a> or <a href="%s">edit the Unit &amp; Module Ordering</a>.', 'wp_courseware'), 
					admin_url('admin.php?page=WPCW_showPage_ModifyCourse&course_id='.$importResults['course_id']),
					admin_url('admin.php?page=WPCW_showPage_CourseOrdering&course_id='.$importResults['course_id'])
				);
			
				
				$page->showMessage($message);
			}
		}
		// Error occured, so report a meaningful error
		else
		{
			switch ($errornum)
			{				
				case UPLOAD_ERR_FORM_SIZE:
				case UPLOAD_ERR_INI_SIZE:
					$page->showMessage(__("Unfortunately the file you've uploaded is too large for the system.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
					$page->showMessage(__("For some reason, the file you've uploaded didn't transfer correctly to the server. Please try again.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_NO_TMP_DIR:
				case UPLOAD_ERR_CANT_WRITE:
					$page->showMessage(__("There appears to be an issue with your server, as the import file could not be stored in the temporary directory.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_EXTENSION:
					$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t XML.', 'wp_courseware'), true);
					break;
			}
		}
	} 
}


/**
 * Handles the upload and import of the user CSV file.
 * @param Object $page The page object to show messages.
 */
function WPCW_users_importUsersFromFile($page)
{
	set_time_limit(0);
	$page->showMessage(__('Import started...', 'wp_courseware'));
	flush();
	
	if (isset($_FILES['import_course_csv']['name']))
	{
		// See what type of file we're tring to upload
		$type = strtolower($_FILES['import_course_csv']['type']);
		$fileTypes = array(
			'text/csv', 
			'text/plain', 
			'application/csv', 
			'text/comma-separated-values', 
			'application/excel', 
			'application/vnd.ms-excel', 
			'application/vnd.msexcel', 
			'text/anytext', 
			'application/octet-stream', 
			'application/txt'
		);
		
		if (!in_array($type, $fileTypes)) {
			$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t a CSV file.', 'wp_courseware'), true);
			return false;
		}		
		
		// Filetype is fine, carry on
		$errornum = $_FILES['import_course_csv']['error'] + 0;
		$tempfile = $_FILES['import_course_csv']['tmp_name'];
		
		
		// File uploaded successfully?				
		if ($errornum == 0)
		{
			// Try the import, return error/success here
			if (($csvHandle = fopen($tempfile, "r")) !== FALSE)
			{
				$assocData = array();
				$rowCounter = 0;
				
				// Extract the user details from the CSV file into an array for importing.
				while (($rowData = fgetcsv($csvHandle, 0, ",")) !== FALSE) 
				{
					if (0 === $rowCounter) {
						$headerRecord = $rowData;
					} else {
						foreach( $rowData as $key => $value) {
							$assocData[$rowCounter - 1][$headerRecord[$key]] = $value;  
						}
						$assocData[$rowCounter - 1]['row_num'] = $rowCounter + 1;
					}
					$rowCounter++;
				}
				
				// Check we have users to process before continuing.
				if (count($assocData) < 1) {
					$page->showMessage(__('No data was found in the CSV file, so there is nothing to do.', 'wp_courseware'), true);
					return;
				}
				
				
				// Get a list of all courses that we can add a user too.
				$courseList = WPCW_courses_getCourseList(false);
				
				// Statistics for update.
				$count_newUser = 0;
				$count_skippedButUpdated = 0;
				$count_aborted = 0;
				
				// By now, $assocData contains a list of user details in an array. 
				// So now we try to insert all of these users into the system, and validate them all.
				$skippedList = array();
				foreach ($assocData as $userRowData)
				{
					// #### 1 - See if we have a username that we can use. If not, abort.
					$firstName = trim($userRowData['first_name']);
					$lastName  = trim($userRowData['last_name']);
					
					$userNameToCreate = $firstName.$lastName;
					if (!$userNameToCreate)
					{
						$skippedList[] = array(
							'id' 		=> $userRowData,
							'row_num'	=> $userRowData['row_num'],
							'aborted'	=> true,
							'reason' 	=> __('Cannot create a user with no name.', 'wp_courseware')
						);
						$count_aborted++;
						continue;
					} // username check		
					

					// // #### 2 - Email address of user already exists.
					if ($userID = email_exists($userRowData['email_address']))
					{
						$skippedList[] = array(
							'id' 		=> $userRowData,
							'row_num'	=> $userRowData['row_num'],
							'aborted'	=> false,
							'reason' 	=> __('Email address already exists.', 'wp_courseware')
						);				

						$count_skippedButUpdated++;
					} 
					
					
					// #### 3 - User does not exist, so creating
					else 
					{
						
						// #### 3A - Try and create a unique Username
						$userlogin = $userNameToCreate;
					    while (username_exists($userlogin)) 
					    {
					    	$userlogin = $userNameToCreate . rand(10, 999);
					    }
						
					    
					    // #### 3B - Create a new password
					    $newPassword = wp_generate_password(15);
						
						// #### 3C - Try to create the new user
					   	$userDetailsToAdd = array(
					   		'user_login'	=> $userlogin,
					    	'user_email'	=> $userRowData['email_address'],
						    'first_name'	=> $firstName,
						    'last_name'		=> $lastName,
						    'display_name'	=> trim($firstName . ' ' . $lastName),
					   		'user_pass'		=> $newPassword,
					    );
						
					    // #### 3D - Check for error when creating
					    $result = wp_insert_user($userDetailsToAdd);
					    if (is_wp_error($result))
					    {				    	
					    	$skippedList[] = array(
								'id' 		=> $userRowData,
								'row_num'	=> $userRowData['row_num'],
					    		'aborted'	=> true,
								'reason' 	=> $result->get_error_message()
							);
							$count_aborted++;
							continue;
					    }
					    								
						// #### 3E - User now exists at this point, copy ID
						// to user ID variable.
						$userID = $result;
						
						// #### 3F - Notify user of their new password.
						wp_new_user_notification($userID, $newPassword);
						flush();
						
						$count_newUser++;						
					}
					
					// #### 4 - Break list of courses into an array, and then add that user to those courses
					$coursesToAdd = explode(',', $userRowData['courses_to_add_to']);
					if ($coursesToAdd && count($coursesToAdd) > 0) {					
						WPCW_courses_syncUserAccess($userID, $coursesToAdd); 
					}
					
				}
				
				// Summary import.
				$page->showMessage(__('Import complete!', 'wp_courseware') . ' ' . sprintf(__('%d users were registered, %d users were updated, and %d user entries could not be processed.', 'wp_courseware'), 
					$count_newUser, $count_skippedButUpdated, $count_aborted)
				);
				
				
				// Show any skipped users
				if (!empty($skippedList)) 
				{
					printf('<div id="wpcw_user_import_skipped">');					
						printf('<b>' . __('The following %d users were not imported:', 'wp_courseware') . '</b>', count($skippedList));
						
						printf('<table class="widefat">');
							printf('<thead>');
								printf('<tr>');
									printf('<th>%s</th>', __('Line #', 'wp_courseware'));
									printf('<th>%s</th>', __('User Email Address', 'wp_courseware'));
									printf('<th>%s</th>', __('Reason why not imported', 'wp_courseware'));
									printf('<th>%s</th>', __('Updated Anyway?', 'wp_courseware'));
								printf('</tr>');
							printf('</thead>');
							
							$odd = false;
							foreach ($skippedList as $skipItem)
							{
								printf('<tr class="%s %s">', ($odd ? 'alternate' : ''), ($skipItem['aborted'] ? 'wpcw_error' : 'wpcw_ok'));
								printf('<td>%s</td>', $skipItem['row_num']);
								printf('<td>%s</td>', $skipItem['id']['email_address']);
								printf('<td>%s</td>', $skipItem['reason']);
								printf('<td>%s</td>', ($skipItem['aborted'] ? __('No, Aborted', 'wp_courseware') : __('Yes', 'wp_courseware')));
								printf('</tr>');
								
								$odd = !$odd;
							}
						
						printf('</table>');
										
					printf('</div>');
				}
				
				
				// All done
				fclose($csvHandle);
			}
			else {
				$page->showMessage(__('Unfortunately, the temporary CSV file could not be opened for processing.', 'wp_courseware'), true);
				return;
			}
			
		}
		// Error occured, so report a meaningful error
		else
		{
			switch ($errornum)
			{				
				case UPLOAD_ERR_FORM_SIZE:
				case UPLOAD_ERR_INI_SIZE:
					$page->showMessage(__("Unfortunately the file you've uploaded is too large for the system.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_PARTIAL:
				case UPLOAD_ERR_NO_FILE:
					$page->showMessage(__("For some reason, the file you've uploaded didn't transfer correctly to the server. Please try again.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_NO_TMP_DIR:
				case UPLOAD_ERR_CANT_WRITE:
					$page->showMessage(__("There appears to be an issue with your server, as the import file could not be stored in the temporary directory.", 'wp_courseware'), true);
					break;
					
				case UPLOAD_ERR_EXTENSION:
					$page->showMessage(__('Unfortunately, you tried to upload a file that isn\'t a CSV file.', 'wp_courseware'), true);
					break;
			}
		} 
	} // end of if (isset($_FILES['import_course_csv']['name'])) 
}


/** 
 * Page where the site owner can choose which courses a user is allowed to access.
 */
function WPCW_showPage_UserCourseAccess()
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Update User Course Access Permissions', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	
	// Check passed user ID is valid
	$userID = WPCW_arrays_getValue($_GET, 'user_id');
	$userDetails = get_userdata($userID); 
	if (!$userDetails) 
	{
		$page->showMessage(__('Sorry, but that user could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return false;		
	}

	printf(__('<p>Here you can change which courses the user <b>%s</b> (Username: <b>%s</b>) can access.</p>', 'wp_courseware'), $userDetails->data->display_name, $userDetails->data->user_login);
		
	
	// Check to see if anything has been submitted?
	if (isset($_POST['wpcw_course_user_access'])) 
	{
		$subUserID = WPCW_arrays_getValue($_POST, 'user_id')+0;
		$userSubDetails = get_userdata($subUserID); 
		
		// Check that user ID is valid, and that it matches user we're editing.
		if (!$userSubDetails || $subUserID != $userID) {
			$page->showMessage(__('Sorry, but that user could not be found. The changes were not saved.', 'wp_courseware'), true);
		}
		
		// Continue, as things appear to be fine
		else 
		{
			// Get list of courses that user is allowed to access from the submitted values.
			$courseAccessIDs = array();
			foreach ($_POST as $key => $value)
			{
				// Check for course ID selection
				if (preg_match('/^wpcw_course_(\d+)$/', $key, $matches)) {
					$courseAccessIDs[] = $matches[1];					
				}				
			}
			
			// Sync courses that the user is allowed to access
			WPCW_courses_syncUserAccess($subUserID, $courseAccessIDs, 'sync');

			// Final success message	
			$message = sprintf(__('The courses for user <em>%s</em> have now been updated.', 'wp_courseware'), $userDetails->data->display_name);			
			$page->showMessage($message, false);
		}
	}
	

	
	$SQL = "SELECT * 
			FROM $wpcwdb->courses
			ORDER BY course_title ASC 
			";
	
	$courses = $wpdb->get_results($SQL);
	if ($courses)  
	{
		$tbl = new TableBuilder();
		$tbl->attributes = array(
			'id' 	=> 'wpcw_tbl_course_access_summary',
			'class'	=> 'widefat wpcw_tbl'
		);
		
		$tblCol = new TableColumn(__('Allowed Access', 'wp_courseware'), 'allowed_access');		
		$tblCol->cellClass = "allowed_access";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Course Title', 'wp_courseware'), 'course_title');
		$tblCol->cellClass = "course_title";
		$tbl->addColumn($tblCol);
		
		$tblCol = new TableColumn(__('Description', 'wp_courseware'), 'course_desc');
		$tblCol->cellClass = "course_desc";
		$tbl->addColumn($tblCol);
		
		// Format row data and show it.
		$odd = false;
		foreach ($courses as $course)
		{
			$data = array();
			
			// Basic details					
			$data['course_desc']  	= $course->course_desc;
			
			$editURL = admin_url('admin.php?page=WPCW_showPage_ModifyCourse&course_id=' . $course->course_id);
			$data['course_title']  	= sprintf('<a href="%s">%s</a>', $editURL, $course->course_title);
						
			// Checkbox if enabled or not
			$userAccess = WPCW_courses_canUserAccessCourse($course->course_id, $userID);
			$checkedHTML = ($userAccess ? 'checked="checked"' : '');
			 
			$data['allowed_access'] = sprintf('<input type="checkbox" name="wpcw_course_%d" %s/>', $course->course_id, $checkedHTML);
			
			// Odd/Even row colouring.
			$odd = !$odd;
			$tbl->addRow($data, ($odd ? 'alternate' : ''));
		}
		
		// Create a form so user can update access.
		?>
		<form action="<?php str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
			<?php 
			
			// Finally show table
			echo $tbl->toString();		
			
			?>
			<input type="hidden" name="user_id" value="<?php echo $userID; ?>"> 
			<input type="submit" class="button-primary" name="wpcw_course_user_access" value="<?php _e('Save Changes', 'wp_courseware'); ?>" />
		</form>
		<?php 
	}
	
	else {
		printf('<p>%s</p>', __('There are currently no courses to show. Why not create one?', 'wp_courseware'));
	}
	
	
	
	$page->showPageFooter();
}


/**
 * Shows a detailed summary of the user progress.
 */
function WPCW_showPage_UserProgess()
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Detailed User Progress Report', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	
	// Check passed user ID is valid
	$userID = WPCW_arrays_getValue($_GET, 'user_id');
	$userDetails = get_userdata($userID); 
	if (!$userDetails) 
	{
		$page->showMessage(__('Sorry, but that user could not be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return false;		
	}

	printf(__('<p>Here you can see how well <b>%s</b> (Username: <b>%s</b>) is doing with your training courses.</p>', 'wp_courseware'), $userDetails->data->display_name, $userDetails->data->user_login);
		

	// #### 1 - Show a list of all training courses, and then list the units associated with that course.	
	$SQL = "SELECT * 
			FROM $wpcwdb->courses
			ORDER BY course_title ASC 
			";
	
	$courseCount = 0;
	
	$courses = $wpdb->get_results($SQL);
	if ($courses)  
	{
		foreach ($courses as $course)
		{
			$up = new UserProgress($course->course_id, $userID);
			
			// Skip if user is not allowed to access the training course.
			if (!WPCW_courses_canUserAccessCourse($course->course_id, $userID)) {
				continue;
			}
			
			printf('<h3 class="wpcw_tbl_progress_course">%s</h3>', $course->course_title);
			printf('<table class="widefat wpcw_tbl wpcw_tbl_progress">');
			
			printf('<thead>');
				printf('<th>%s</th>', 															__('Unit', 'wp_courseware'));
				printf('<th class="wpcw_center">%s</th>', 								__('Completed', 'wp_courseware'));
				printf('<th class="wpcw_center wpcw_tbl_progress_quiz_name">%s</th>', 	__('Quiz Name', 'wp_courseware'));
				printf('<th class="wpcw_center">%s</th>', 								__('Quiz Status', 'wp_courseware'));
				printf('<th class="wpcw_center">%s</th>', 								__('Actions', 'wp_courseware'));
			printf('</thead><tbody>');			
			
			
			// #### 2 - Fetch all associated modules 
			$modules = WPCW_courses_getModuleDetailsList($course->course_id);
			if ($modules)
			{
				foreach ($modules as $module)
				{
					// #### 3 - Render Modules as a heading.
					printf('<tr class="wpcw_tbl_progress_module">');
						printf('<td colspan="3">%s %d - %s</td>',
							__('Module', 'wp_courseware'),
							$module->module_number,
							$module->module_title
						);
						 
						// Blanks for Quiz Name and Actions.
						printf('<td>&nbsp;</td>');
						printf('<td>&nbsp;</td>');
					printf('</tr>');
					
					// #### 4. - Render the units for this module
					$units = WPCW_units_getListOfUnits($module->module_id);
					if ($units) 
					{						
						foreach ($units as $unit)
						{
							$showDetailLink = false;
							
							printf('<tr class="wpcw_tbl_progress_unit">');
							
							printf('<td class="wpcw_tbl_progress_unit_name">%s %d - %s</td>',
								__('Unit', 'wp_courseware'),
								$unit->unit_meta->unit_number,
								$unit->post_title
							);
							
							// Has the unit been completed yet?
							printf('<td class="wpcw_tbl_progress_completed">%s</td>', $up->isUnitCompleted($unit->ID) ? __('Completed', 'wp_courseware') : '');
							
							// See if there's a quiz for this unit?
							$quizDetails = WPCW_quizzes_getAssociatedQuizForUnit($unit->ID);
							
							// Render the quiz details.
							if ($quizDetails) 
							{
								// Title of quiz
								printf('<td class="wpcw_tbl_progress_quiz_name">%s</td>', $quizDetails->quiz_title);								
								
								// No correct answers, so mark as complete.
								if ('survey' == $quizDetails->quiz_type) 
								{
									$quizResults = WPCW_quizzes_getUserResultsForQuiz($userID, $unit->ID, $quizDetails->quiz_id);
									
									if ($quizResults)
									{
										printf('<td class="wpcw_tbl_progress_completed">%s</td>', __('Completed', 'wp_courseware'));
																			
										// Showing a link to view details
										$showDetailLink = true;
										printf('<td><a href="%s&user_id=%d&quiz_id=%d&unit_id=%d" class="button-secondary">%s</a></td>',	
											admin_url('users.php?page=WPCW_showPage_UserProgess_quizAnswers'),
											$userID, $quizDetails->quiz_id, $unit->ID,
											__('View Survey Details', 'wp_courseware')
										);
									}
									
									// Survey not taken yet
									else {
										printf('<td class="wpcw_center">%s</td>', __('Pending', 'wp_courseware'));
									}
								}
								
								// Quiz - show correct answers.
								else 
								{
									$quizResults = WPCW_quizzes_getUserResultsForQuiz($userID, $unit->ID, $quizDetails->quiz_id);
									
									// Show the admin how many questions were right.
									if ($quizResults) 
									{
										printf('<td class="wpcw_tbl_progress_completed">%d%%</td>', number_format($quizResults->quiz_grade, 1));
										
										// Showing a link to view details
										$showDetailLink = true;			
										
										printf('<td><a href="%s&user_id=%d&quiz_id=%d&unit_id=%d" class="button-secondary">%s</a></td>',	
											admin_url('users.php?page=WPCW_showPage_UserProgess_quizAnswers'),
											$userID, $quizDetails->quiz_id, $unit->ID,
											__('View Quiz Details', 'wp_courseware')
										);
										
									} // end of if  printf('<td class="wpcw_tbl_progress_completed">%s</td>'
									
									
									// Quiz not taken yet
									else {
										printf('<td class="wpcw_center">%s</td>', __('Pending', 'wp_courseware'));
									}
									
								} // end of if survey
							} // end of if $quizDetails
							
							
							// No quiz for this unit
							else {					
								printf('<td class="wpcw_center">-</td>');
								printf('<td class="wpcw_center">-</td>');
							}
							
							// Quiz detail link
							if (!$showDetailLink) {
								printf('<td>&nbsp;</td>');
							}
							
							printf('</tr>');
						}
						
					}
					
				}
			}
			
			printf('</tbody></table>');
			
			// Track number of courses user can actually access
			$courseCount++;
		}
		
		// Course is not allowed to access any courses. So show a meaningful message.
		if ($courseCount == 0) {
			$page->showMessage(sprintf(__('User <b>%s</b> is not currently allowed to access any training courses.', 'wp_courseware'), $userDetails->data->display_name), true);
		}
		
	}
	
	else {
		printf('<p>%s</p>', __('There are currently no courses to show. Why not create one?', 'wp_courseware'));
	}
		
	$page->showPageFooter();
}



/**
 * Shows a detailed summary of the user's quiz or survey answers.
 */
function WPCW_showPage_UserProgess_quizAnswers()
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
	
	$page = new PageBuilder(false);
	$page->showPageHeader(__('Detailed User Quiz/Survey Results', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	$userID = WPCW_arrays_getValue($_GET, 'user_id') + 0;
	$unitID = WPCW_arrays_getValue($_GET, 'unit_id') + 0;
	$quizID = WPCW_arrays_getValue($_GET, 'quiz_id') + 0;
	
	
	
	// Create a link back to the detailed user progress, and back to all users.
	printf('<div class="wpcw_button_group">');
	
	// Link back to all user summary
	printf('<a href="%s" class="button-secondary">%s</a>&nbsp;&nbsp;', 
		admin_url('users.php'),
		__('&laquo; Return to User Summary', 'wp_courseware')
	);
		
	if ($userDetails = get_userdata($userID))
	{
		// Link back to user's personal summary
		printf('<a href="%s&user_id=%d" class="button-secondary">%s</a>&nbsp;&nbsp;', 
			admin_url('users.php?page=WPCW_showPage_UserProgess'),
			$userDetails->ID,
			sprintf(__('&laquo; Return to <b>%s\'s</b> Progress Report', 'wp_courseware'), $userDetails->display_name)
		);		
	}
	
			
	
	// Try to get the full detailed results.
	$results = WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizID);
	
	// No results, so abort.
	if (!$results) 	
	{
		// Close the button wrapper for above early
		printf('</div>'); // .wpcw_button_group
		
		$page->showMessage(__('Sorry, but no results could be found.', 'wp_courseware'), true);
		$page->showPageFooter();
		return;
	}
		
	// Could potentially have an issue where the quiz has been deleted
	// but the data exists.. small chance though.
	$quizDetails = WPCW_quizzes_getQuizDetails($quizID, true);
	
	// Extra button - return to gradebook
	printf('<a href="%s&course_id=%d" class="button-secondary">%s</a>&nbsp;&nbsp;', 
		admin_url('admin.php?page=WPCW_showPage_GradeBook'), $quizDetails->parent_course_id,
		__("&laquo; Return to Gradebook", 'wp_courseware')
	);
	
	printf('</div>'); // .wpcw_button_group
	
	
	// #### 1 - Handle grades being updated
	$results = WPCW_showPage_UserProgess_quizAnswers_handingGrading($quizDetails, $results, $page, $userID, $unitID);
		
	// #### 2A - Check if next action for user has been triggered by the admin. 
	$results = WPCW_showPage_UserProgess_quizAnswers_whatsNext_savePreferences($quizDetails, $results, $page, $userID, $unitID);
		
	// #### 2B - Handle telling admin what's next	
	WPCW_showPage_UserProgess_quizAnswers_whatsNext($quizDetails, $results, $page, $userID, $unitID);
	

	//Ê#### 3 - Handle sending emails if something has changed.
	if (isset($results->sendOutEmails) && $results->sendOutEmails)
	{
		$extraDetail = (isset($results->extraEmailDetail) ? $results->extraEmailDetail : '');
		
		// Need to call the action anyway, but any functions hanging off this
		// should check if the admin wants users to have notifications or not.
		do_action('wpcw_quiz_graded', $userID, $quizDetails, number_format($results->quiz_grade, 1), $extraDetail);
		
		$courseDetails = WPCW_courses_getCourseDetails($quizDetails->parent_course_id);
		if ($courseDetails->email_quiz_grade_option == 'send_email')
		{
			$page->showMessage(__('The user has been sent an email with their grade for this course.', 'wp_courseware'));
		}
	}


	// #### 4 - Table 1 - Overview
	printf('<h3>%s</h3>', __('Quiz/Survey Overview', 'wp_courseware'));
	
	$tbl = new TableBuilder();
	$tbl->attributes = array(
		'id' 	=> 'wpcw_tbl_progress_quiz_info',
		'class'	=> 'widefat wpcw_tbl'
	);
		
	$tblCol = new TableColumn(false, 'quiz_label');
	$tblCol->cellClass = 'wpcw_tbl_label';		
	$tbl->addColumn($tblCol);
	
	$tblCol = new TableColumn(false, 'quiz_detail');		
	$tbl->addColumn($tblCol);
	
	// These are the base details for the quiz to show.
	$summaryData = array(
		__('Quiz Title', 'wp_courseware')					=> $quizDetails->quiz_title,
		__('Quiz Description', 'wp_courseware')				=> $quizDetails->quiz_desc,
		__('Quiz Type', 'wp_courseware')					=> WPCW_quizzes_getQuizTypeName($quizDetails->quiz_type),
		__('No. of Questions', 'wp_courseware') 			=> $results->quiz_question_total,
		 
		__('Completed Date', 'wp_courseware') 				=>
				__('About', 'wp_courseware') . ' ' . human_time_diff($results->quiz_completed_date_ts) . ' ' . __('ago', 'wp_courseware') . 
				'<br/><small>(' . date('D jS M Y \a\t H:i:s', $results->quiz_completed_date_ts) . ')</small>'
	);
	
	
	// Quiz details relating to score, etc.
	if ('survey' != $quizDetails->quiz_type)
	{	
		$summaryData[__('Pass Mark', 'wp_courseware')]		= $quizDetails->quiz_pass_mark . '%';				
		
		// Still got items to grade
		if ($results->quiz_needs_marking > 0)
		{
			$summaryData[__('No. of Question to Grade', 'wp_courseware')] = '<span class="wpcw_status_info wpcw_icon_pending">' .$results->quiz_needs_marking . '</span>';
			$summaryData[__('Overall Grade', 'wp_courseware')]	= '<span class="wpcw_status_info wpcw_icon_pending">' . __('Awaiting Final Grading', 'wp_courseware') . '</span>';
		}
		else
		{
			$summaryData[__('No. of Question to Grade', 'wp_courseware')] = '-';
			
			// Show if PASSED or FAILED with the overall grade.
			$gradeData = false;
			if ($results->quiz_grade >= $quizDetails->quiz_pass_mark) 
			{
				$gradeData = sprintf('<span class="wpcw_tbl_progress_quiz_overall wpcw_question_yesno_status wpcw_question_yes">%s%% %s</span>', number_format($results->quiz_grade, 1), __('Passed', 'wp_courseware'));
			}
			else {
				$gradeData = sprintf('<span class="wpcw_tbl_progress_quiz_overall wpcw_question_yesno_status wpcw_question_no">%s%% %s</span>', number_format($results->quiz_grade, 1), __('Failed', 'wp_courseware'));
			}
			
			$summaryData[__('Overall Grade', 'wp_courseware')]	= $gradeData;
		}
	}
	
	
	foreach ($summaryData as $label => $data)
	{
		$tbl->addRow(array(
			'quiz_label' => $label . ':',
			'quiz_detail' => $data,
		)); 
	}
	
	echo $tbl->toString();
	

	// ### 4 - Form Code - to allow instructor to send data back to 
	printf('<form method="POST" id="wpcw_tbl_progress_quiz_grading_form">');
	printf('<input type="hidden" name="grade_answers_submitted" value="true">');  
	
	// ### 5 - Table 2 - Each Specific Quiz
	$questionNumber = 0;
	if ($results->quiz_data && count($results->quiz_data) > 0)
	{
		foreach ($results->quiz_data as $questionID => $answer)
		{
			$data = $answer;			
			
			// Get the question type
			if (isset($quizDetails->questions[$questionID]))
			{
				// Store as object for easy reference.
				$quObj = $quizDetails->questions[$questionID];
				
				// Render the question as a table.
				printf('<h3>%s #%d - %s</h3>', __('Question', 'wp_courseware'), ++$questionNumber, $quObj->question_question);

				$tbl = new TableBuilder();
				$tbl->attributes = array(
					'id' 	=> 'wpcw_tbl_progress_quiz_info',
					'class'	=> 'widefat wpcw_tbl wpcw_tbl_progress_quiz_answers_'. $quObj->question_type // Add question type to table class, for good measure!
				);
					
				$tblCol = new TableColumn(false, 'quiz_label');
				$tblCol->cellClass = 'wpcw_tbl_label';		
				$tbl->addColumn($tblCol);
				
				$tblCol = new TableColumn(false, 'quiz_detail');		
				$tbl->addColumn($tblCol);
				
				$theirAnswer = false;
				switch ($quObj->question_type)
				{
					case 'truefalse':
					case 'multi':
						$theirAnswer = $answer['their_answer'];
					break;
					
					// File Upload - create a download link
					case 'upload':
						$theirAnswer = sprintf('<a href="%s%s" target="_blank" class="button-primary">%s .%s %s (%s)</a>', 
							WP_CONTENT_URL, $answer['their_answer'],
							__('Open', 'wp_courseware'),
							pathinfo($answer['their_answer'], PATHINFO_EXTENSION),
							__('File', 'wp_courseware'), 
							WPCW_files_getFileSize_human($answer['their_answer'])								
						);
					break;
					
					// Open Ended - Wrap in span tags, to cap the size of the field, and format new lines.
					case 'open': 
						$theirAnswer = '<span class="wpcw_q_answer_open_wrap"><textarea readonly>'. $data['their_answer'] .'</textarea></span>'; 
					break;
				} // end of $theirAnswer check
				
				
				$summaryData = array(
					// Quiz Type - Work out the label for the quiz type
					__('Type', 'wp_courseware')	=> array(
							'data' 		=> WPCW_quizzes_getQuestionTypeName($quObj->question_type), 
							'cssclass' 	=> ''
					),
					
					__('Their Answer', 'wp_courseware')	=> array(
							'data' 		=> $theirAnswer, 
							'cssclass' 	=> ''
					),
				);
				
				
				// Just for quizzes - show answers/grade
				if ('survey' != $quizDetails->quiz_type)
				{
					switch ($quObj->question_type)
					{
						case 'truefalse':
						case 'multi':
							// The right answer...
							$summaryData[__('Correct Answer', 'wp_courseware')] = array(
								'data' 		=> $answer['correct'],
								'cssclass' 	=> ''
							); 
							
							// Did they get it right?
							$getItRight = sprintf('<span class="wpcw_question_yesno_status wpcw_question_%s">%s</span>', $answer['got_right'], 
								('yes' == $answer['got_right'] ? __('Yes', 'wp_courseware') : __('No', 'wp_courseware'))
							);
								
							$summaryData[__('Did they get it right?', 'wp_courseware')] = array(
								'data' 		=> $getItRight,
								'cssclass'	=> ''
							);
						break;
						
						case 'upload':
						case 'open':
								$gradeHTML = false;
								$theirGrade = WPCW_arrays_getValue($answer, 'their_grade');
							
								// Not graded - show select box.
								if ($theirGrade == 0) 
								{
									$cssClass = 'wpcw_grade_needs_grading';
								}
								
								// Graded - Show click-to-edit link
								else 
								{
									$cssClass = 'wpcw_grade_already_graded';									
									$gradeHTML = sprintf('<span class="wpcw_grade_view">%d%% <a href="#">(%s)</a></span>', $theirGrade, __('Click to edit', 'wp_courseware'));
								}
								
								// Not graded yet, allow admin to grade the quiz, or change
								// the grading later if they want to.							
								$gradeHTML .= WPCW_forms_createDropdown(
									'grade_quiz_' . $quObj->question_id, 
									WPCW_quizzes_getPercentageList(__('-- Select a grade --', 'wp_courseware')),
									$theirGrade,
									false,
									'wpcw_tbl_progress_quiz_answers_grade'
								);
								
								
								$summaryData[__('Their Grade', 'wp_courseware')] = array(
									'data' 		=> $gradeHTML,
									'cssclass'	=> $cssClass
								);								
							break;
					}
				} // Check of showing the right answer.		
					
				
				foreach ($summaryData as $label => $data)
				{
					$tbl->addRow(array(
						'quiz_label' => $label . ':',
						'quiz_detail' => $data['data'],
					), $data['cssclass']); 
				}
				
				echo $tbl->toString();

			} // end if (isset($quizDetails->questions[$questionID]))
		} // foreach ($results->quiz_data as $questionID => $answer)
	}
	
	
	printf('</form>');
	
	// Shows a bar that pops up, allowing the user to easily save all grades that have changed.
	?>
	<div id="wpcw_sticky_bar" style="display: none">
		<div id="wpcw_sticky_bar_inner">
			<a href="#" id="wpcw_tbl_progress_quiz_grading_updated" class="button-primary"><?php _e('Save Changes to Grades', 'wp_courseware'); ?></a>
			<span id="wpcw_sticky_bar_status" title="<?php _e('Grades have been changed. Ready to save changes?', 'wp_courseware'); ?>"></span>
		</div>
	</div>
	<br/><br/><br/><br/>
	<?php 
		
	$page->showPageFooter();
}


/**
 * Handles the grading of the quiz questions.
 */
function WPCW_showPage_UserProgess_quizAnswers_handingGrading($quizDetails, $results, $page, $userID, $unitID)
{
	if (isset($_POST['grade_answers_submitted']) && 'true' == $_POST['grade_answers_submitted'])
	{
		$listOfQuestionsToMark = $results->quiz_needs_marking_list;
		
		// Switch array so values become keys.		
		if (!empty($listOfQuestionsToMark)) {
			$listOfQuestionsToMark = array_flip($listOfQuestionsToMark);
		} 
		// Ensure we always have a valid array
		else {
			$listOfQuestionsToMark = array();
		}
		
		
		// Check $_POST keys for the graded results.
		foreach ($_POST as $key => $value)
		{
			// Check that we have a question ID and a matching grade for the quiz. Only want grades that are greater than 0.
			if (preg_match('/^grade_quiz_([0-9]+)$/', $key, $keyMatches) && preg_match('/^[0-9]+$/', $value) && $value > 0)
			{
				$questionID = $keyMatches[1];
				
				// Remove from list to be marked, if found
				unset($listOfQuestionsToMark[$questionID]);

				// Add the grade information to the quiz
				if (isset($results->quiz_data[$questionID])) 
				{
					$results->quiz_data[$questionID]['their_grade'] = $value;
				}				
			}
		}
		
		// Update the database with the list of questions to mark, plus the updated quiz grading information.
		// Return to a simple list again, hence using array flip (ID => index) becomes (index => ID) 
		$results->quiz_needs_marking_list = array_flip($listOfQuestionsToMark); 
		
		// Update the results in the database.
		WPCW_quizzes_updateQuizResults($results);
		
		// Success message
		$page->showMessage(__('Grades have been successfully updated for this user.', 'wp_courseware'));
		
		
		// Refresh the results - now that we've made changes
		$results = WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizDetails->quiz_id);
		
		// All items are marked, so email user, and tell admin that user has been notified.
		if ($results->quiz_needs_marking == 0)
		{
			// Send out email only if not a blocking test, or blocking and passed.
			if ('quiz_block' == $quizDetails->quiz_type && $results->quiz_grade < $quizDetails->quiz_pass_mark) {
				$results->sendOutEmails = false;
			} else {
				$results->sendOutEmails = true;
			}
			
			// Check if the user has passed or not to indicate what to do next.
			if ($results->quiz_grade >= $quizDetails->quiz_pass_mark) 
			{
				// Just a little note to mark as complete
				$results->extraEmailDetail = __('You have passed the quiz.', 'wp_courseware');
				
				printf('<div id="message" class="wpcw_msg wpcw_msg_success">%s</span></div>', 
					__('The user has <b>PASSED</b> this quiz, and the associated unit has been marked as complete.', 'wp_courseware')
				);
				
				WPCW_units_saveUserProgress_Complete($userID, $unitID);
				
				// Unit complete, check if course/module is complete too.
				do_action('wpcw_user_completed_unit', $userID, $unitID, WPCW_units_getAssociatedParentData($unitID));
			} 
		}
	}

	return $results;
}


/**
 * Function that shows details to the admin telling them what to do next.
 */
function WPCW_showPage_UserProgess_quizAnswers_whatsNext($quizDetails, $results, $page, $userID, $unitID)
{
	// Tell admin still questions that need marking
	if ($results->quiz_needs_marking > 0)
	{
		printf('<div id="message" class="wpcw_msg wpcw_msg_info"><span class="wpcw_icon_pending"><b>%s</b></span></div>', 
			__('This quiz has questions that need grading.', 'wp_courseware')
		);
	}
	else  
	{
		// Show the form only if the quiz is blocking and they've failed. 
		if ('quiz_block' == $quizDetails->quiz_type && $results->quiz_grade < $quizDetails->quiz_pass_mark)
		{		
			// Show admin which method was selected.
			if ($results->quiz_next_step_type)
			{
				switch ($results->quiz_next_step_type)
				{
					case 'progress_anyway':
						printf('<div id="message" class="wpcw_msg wpcw_msg_info">%s</span></div>', 
								__('You have allowed the user to <b>progress anyway</b>, despite failing the quiz.', 'wp_courseware')
							);
						break;
						
					case 'retake_quiz':
						printf('<div id="message" class="wpcw_msg wpcw_msg_info">%s</span></div>', 
								__('You have requested that the user <b>re-takes the quiz</b>.', 'wp_courseware')
							);
						break;
				}
			}
			
			// Next step has not been specified, allow the admin to choose one.
			else 
			{
				printf('<div class="wpcw_user_progress_failed"><form method="POST">');
				
				printf('<div id="message" class="wpcw_msg wpcw_msg_error">%s</span></div>', 
					__('Since this is a <b>blocking quiz</b>, and the user has <b>failed</b>, what would you like to do?', 'wp_courseware')
				);
				
				printf('
					<div class="wpcw_user_progress_failed_next_action">
						<label><input type="radio" name="wpcw_user_continue_action" class="wpcw_next_action_progress_anyway" value="progress_anyway" checked="checked" /> <span><b>%s</b> %s</span></label><br/>
						<label><input type="radio" name="wpcw_user_continue_action" class="wpcw_next_action_retake_quiz" value="retake_quiz" /> <span><b>%s</b> %s</span></label>
					</div>
					
					<div class="wpcw_user_progress_failed_reason" style="display: none;">
						<label><b>%s</b></label><br/>
						<textarea name="wpcw_user_progress_failed_reason"></textarea><br/>
						<small>%s</small>
					</div>
					
					<div class="wpcw_user_progress_failed_btns">
						<input type="submit" name="failed_quiz_next_action" value="%s" class="button-primary" />
					</div>
				', 
					__('Allow the user to continue anyway.', 'wp_courseware'),
					__(' (User is emailed saying they can continue)', 'wp_courseware'),
					__('Require the user to re-take the quiz.', 'wp_courseware'),
					__(' (User is emailed saying they need to re-take the quiz)', 'wp_courseware'),
					__('Custom Message:', 'wp_courseware'),
					__('Custom message for the user that\'s sent to the user when asking them to retake the quiz.', 'wp_courseware'),
					__('Save Preference', 'wp_courseware')
				);
				
				printf('</form></div>'); 
			}
		}
	}
}



/**
 * Handles saving what the admin wants to do for the user next.
 */
function WPCW_showPage_UserProgess_quizAnswers_whatsNext_savePreferences($quizDetails, $results, $page, $userID, $unitID)
{
	// Admin wants to save the next action to this progress.
	if (isset($_POST['failed_quiz_next_action']) && $_POST['failed_quiz_next_action'])
	{
		global $wpdb, $wpcwdb;
		$wpdb->show_errors();
		
		$userNextAction = WPCW_arrays_getValue($_POST, 'wpcw_user_continue_action');
		$userRetakeMsg = filter_var(WPCW_arrays_getValue($_POST, 'wpcw_user_progress_failed_reason'), FILTER_SANITIZE_STRING);
		
		// Check action is valid. Abort if not
		if (!in_array($userNextAction, array('retake_quiz', 'progress_anyway'))) {
			return $results;
		}
		
		// Sort out the SQL statement for what to update
		switch ($userNextAction)
		{
			// User needs to retake the course.
			case 'retake_quiz':
				break;
				
			// User is allowed to progress
			case 'progress_anyway':
					$userRetakeMsg = false;
					
					// Mark the unit as completed.
					WPCW_units_saveUserProgress_Complete($userID, $unitID);
					
					// Unit complete, check if course/module is complete too.
					do_action('wpcw_user_completed_unit', $userID, $unitID, WPCW_units_getAssociatedParentData($unitID));
				break;
		}
		
		// Update the progress item
		$SQL = $wpdb->prepare("
		    	UPDATE $wpcwdb->user_progress_quiz
		    	  SET quiz_next_step_type = '%s', 
		    	      quiz_next_step_msg = %s  
		    	WHERE user_id = %d 
		    	  AND unit_id = %d 
		    	  AND quiz_id = %d
		    	ORDER BY quiz_attempt_id DESC
		    	LIMIT 1
	   		", 
				$userNextAction, $userRetakeMsg,
				$userID, $unitID, $quizDetails->quiz_id
			);
			 
		$wpdb->query($SQL);		
		
		// Need to update the results object for use later.
		$results = WPCW_quizzes_getUserResultsForQuiz($userID, $unitID, $quizDetails->quiz_id);
							
		
		switch ($userNextAction)
		{
			// User needs to retake the course.
			case 'retake_quiz':
					$results->extraEmailDetail = __('Since you didn\'t pass the quiz, the instructor has asked that you re-take this quiz.', 'wp_courseware');
					if ($userRetakeMsg) { 
						$results->extraEmailDetail .= "\n\n" . 	$userRetakeMsg;
					}
				break;
				
			// User is allowed to progress
			case 'progress_anyway':
					$results->extraEmailDetail = __('Although you didn\'t pass the quiz, the instructor is allowing you to continue on to the next unit.', WPCW_showPage_UserProgess_quizAnswers_handingGrading);
					
					// Mark the unit as completed.
					WPCW_units_saveUserProgress_Complete($userID, $unitID);
					
					// Unit complete, check if course/module is complete too.
					do_action('wpcw_user_completed_unit', $userID, $unitID, WPCW_units_getAssociatedParentData($unitID));
				break;
		}
		
    	// Tell code to send out emails
		$results->sendOutEmails = true;		
	}
	
	return $results;
}


/**
 * Handle any deletion if any has been requested.
 * @param Object $page The reference to the object showing the page content
 */
function WPCW_handler_processDeletion($page)
{
	// Check for deletion command
	if (!isset($_GET['action'])) {
		return;
	}
	$action = WPCW_arrays_getValue($_GET, 'action');
	
	
	switch ($action)
	{
		// ### Deleting a module		
		case 'delete_module':
			$module_id = WPCW_arrays_getValue($_GET, 'module_id');
			$moduleDetails = WPCW_modules_getModuleDetails($module_id);
			if ($moduleDetails)
			{
				// Actually delete the module from the system
				WPCW_modules_deleteModule($moduleDetails);
				
				$page->showMessage(sprintf(__('Successfully deleted module "<em>%s</em>".', 'wp_courseware'), $moduleDetails->module_title));	
			}
			break;
			
		// ### Deleting a course
		case 'delete_course':
			$course_id = WPCW_arrays_getValue($_GET, 'course_id');
			$courseDetails = WPCW_courses_getCourseDetails($course_id);
			if ($courseDetails)
			{
				// Actually delete the course from the system
				WPCW_modules_deleteCourse($courseDetails);
				
				$page->showMessage(sprintf(__('Successfully deleted training course "<em>%s</em>".', 'wp_courseware'), $courseDetails->course_title));	
			}
			break;
			break;
		
	}
}




/**
 * Delete a course, its modules and disassociating of the units that it contains.
 * 
 * @param Object $courseDetails The details of the course to delete.
 */
function WPCW_modules_deleteCourse($courseDetails)
{
	if (!$courseDetails) {
		return;
	}
	
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Remove association with all units for this unit
   	$unitList = $wpdb->get_col($wpdb->prepare("
    	SELECT unit_id
    	FROM $wpcwdb->units_meta
    	WHERE parent_course_id = %d
    ", $courseDetails->course_id));
    
    if ($unitList)
    {
    	foreach ($unitList as $unitID)
    	{
    		// Update database with new association and ordering.
			$SQL = $wpdb->prepare("
				UPDATE $wpcwdb->units_meta
				   SET unit_order = 0, parent_module_id = 0, parent_course_id = 0, unit_number = 0
				WHERE unit_id = %d
			", $unitID);
			
			$wpdb->query($SQL);
			
			// Update post meta to remove associated module detail
			update_post_meta($unitID, 'wpcw_associated_module', 0);
			
			// Remove progress for this unit, since unit is now unassociated.
			$SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->user_progress
				WHERE unit_id = %d
			", $unitID);	

			$wpdb->query($SQL);
    	}
    }
    
    // Module deletion here.
    $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->modules
				WHERE parent_course_id = %d
			", $courseDetails->course_id));
    
   	// Certificate deletion for this course
    $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->certificates
				WHERE cert_course_id = %d
			", $courseDetails->course_id));
    
    // Perform course deletion here.
    $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->courses
				WHERE course_id = %d
			", $courseDetails->course_id));
    
   	// Course progress summary for each user needs to be removed.
    $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->user_courses
				WHERE course_id = %d
			", $courseDetails->course_id));
    
    // Trigger event that course has been deleted
	do_action('wpcw_course_deleted', $courseDetails);	
}





/**
 * Delete a module and disassociating of the units that it contains.
 * 
 * @param Object $moduleDetails The details of the module to delete.
 */
function WPCW_modules_deleteModule($moduleDetails)
{
	if (!$moduleDetails) {
		return;
	}
	
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    // Remove association with all units for this module
    $unitList = WPCW_units_getListOfUnits($moduleDetails->module_id);
    if ($unitList)
    {
		// Unassociate units from this module
		$SQL = $wpdb->prepare("
			UPDATE $wpcwdb->units_meta
			   SET unit_order = 0, parent_module_id = 0, parent_course_id = 0, unit_number = 0
			WHERE parent_module_id = %d
		", $moduleDetails->module_id);
    	
    	// Update database with new association and ordering.
    	foreach ($unitList as $unitID => $unitObj)
    	{			
			$wpdb->query($SQL);
			
			// Update post meta to remove associated module detail
			update_post_meta($unitID, 'wpcw_associated_module', 0);
			
			// Remove progress for this unit, since unit is now unassociated.
			$SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->user_progress
				WHERE unit_id = %d
			", $unitID);	

			$wpdb->query($SQL);
    	}
    }
    
    // Perform module deletion here.
    $wpdb->query($SQL = $wpdb->prepare("
				DELETE FROM $wpcwdb->modules
				WHERE module_id = %d
			", $moduleDetails->module_id));

    // Modules have changed for this course, update numbering
    do_action('wpcw_modules_modified', $moduleDetails->parent_course_id);
        
    // Course has been updated, update the progress details
    $courseDetails = WPCW_courses_getCourseDetails($moduleDetails->parent_course_id);
    if ($courseDetails) {
    	do_action('wpcw_course_details_updated', $courseDetails);
    }    
    
    // Trigger event that module has been deleted
	do_action('wpcw_module_deleted', $moduleDetails);
}


/**
 * Safe method to find a subitem on the menu and remove it.
 * @param $submenuName The name of the submenu to search.
 * @param $menuItemID The id of the menu item to be removed.
 */
function WPCW_menu_removeSubmenuItem($submenuName, $menuItemID)
{
	global $submenu;	

	// Not found
	if (!isset($submenu[$submenuName])) {
		return false;
	}

	// Search each item of the submenu
	foreach ($submenu[$submenuName] as $index => $details)
	{
		// Found a matching subitem title
		if ($details[2] == $menuItemID) {
			unset($submenu[$submenuName][$index]);			
			
			// No need to continue searching
			return;
		}
	}
}




/**
 * Method called whenever a post is saved, which will check that any course units 
 * save their meta data. 
 * 
 * @param Integer $post_id The ID of the post being saved.
 */
function WPCW_units_saveUnitPostMetaData($post_id, $post)
{	 
	// Check we have a course unit, not any other type (including revisions).
    if ('course_unit' != $post->post_type) {
        return;
    }
    
	// Check user is allowed to edit the post.    
    if ( !current_user_can( 'edit_post', $post_id)) {
        return;
    }
    
    global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// See if there's an entry in the courseware table
	$SQL = $wpdb->prepare("
		SELECT * 
		FROM $wpcwdb->units_meta 
		WHERE unit_id = %d
	", $post_id);
	
	// Ensure there's a blank entry in the database for this post.
	if (!$wpdb->get_row($SQL))
	{
		$SQL = $wpdb->prepare("
			INSERT INTO $wpcwdb->units_meta (unit_id, parent_module_id) 
			VALUES (%d, 0)
		", $post_id);
		
		$wpdb->query($SQL);		
	}
}


/**
 * Update the course unit summary columns to shows the related modules and courses.
 * @param Array $column_headers The list of columns to show (before showing them).
 * @return Array The actual list of columns to show.
 */
function WPCW_units_manageColumns($column_headers)
{    
	// Copy date column
	$oldDate = $column_headers['date']; 
	unset($column_headers['date']);
	
    // Add new columns
    $column_headers['wpcw_col_module_and_course'] 	= __('Associated Module &amp; Course', 'wp_courseware');
    $column_headers['wpcw_col_quiz'] 				= __('Associated Quiz', 'wp_courseware');
    
    // Put date at the end
    $column_headers['date'] = $oldDate;
    
    return $column_headers;
}

 
/**
 * Creates the column columns of data.
 * 
 * @param String $column_name The name of the column we're changing.
 * @param Integer $post_id The ID of the post we're rendering.
 * 
 * @return String The formatted HTML code for the table.
 */
function WPCW_units_addCustomColumnContent($column_name, $post_id)
{
	switch ($column_name)
	{
		// Associated quiz link
		case 'wpcw_col_quiz':
			if ($quizDetails = WPCW_quizzes_getAssociatedQuizForUnit($post_id))
			{
				printf('<a href="%s&quiz_id=%d">%s</a>', admin_url('admin.php?page=WPCW_showPage_ModifyQuiz'), $quizDetails->quiz_id, $quizDetails->quiz_title );
			} 
			// No quiz
			else {
				echo '-';
			}
			break;
		
		case 'wpcw_col_module_and_course':				
			$parentObj = WPCW_units_getAssociatedParentData($post_id);
	
			if (!$parentObj) {
				_e('n/a', 'wp_courseware');
			}
			// Got parent items, render away
			else {
				printf('<span class="wpcw_col_cell_module"><b>%s %d</b> -  %s</span>
						<span class="wpcw_col_cell_course"><b>%s:</b> %s</span>',
					__('Module', 'wp_courseware'), 
					$parentObj->module_number,
					$parentObj->module_title,
					__('Course', 'wp_courseware'),
					$parentObj->course_title
				);
			}
		break; // wpcw_col_module_and_course
	}
}


/**
 * Function called when a post is being deleted by WordPress. Want to check
 * if this relates to a unit, and if so, remove it from our tables.
 * 
 * @param Integer $post_id The ID of the post being deleted.
 */
function WPCW_units_deleteUnitHandler($post_id)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	// See if we've got data on this unit in the meta table
	$SQL = $wpdb->prepare("SELECT * FROM $wpcwdb->units_meta WHERE unit_id = %d", $post_id);
	if ($unitDetails = $wpdb->get_row($SQL))
	{
		// Right, it's one of our units, so need to delete the meta data
		$SQL = $wpdb->prepare("DELETE FROM $wpcwdb->units_meta WHERE unit_id = %d", $post_id);
		$wpdb->query($SQL);
		
		// Delete it from the user progress too
		$SQL = $wpdb->prepare("DELETE FROM $wpcwdb->user_progress WHERE unit_id = %d", $post_id);
		$wpdb->query($SQL);
		
		// Associated with a course?
		$parentData = WPCW_units_getAssociatedParentData($post_id);
		if ($unitDetails->parent_course_id > 0) {		
			// Need to update the course unit count and progresses
			do_action('wpcw_course_details_updated', $unitDetails->parent_course_id);
		}		
		
		// Quiz - Unconnect it from the quiz that it's associated with.
		$SQL = $wpdb->prepare("UPDATE $wpcwdb->quiz SET parent_unit_id = 0, parent_course_id = 0 WHERE parent_unit_id = %d", $post_id);
		$wpdb->query($SQL);
		
		// Quiz Progress - Unconnect it from this quiz. 
		$SQL = $wpdb->prepare("UPDATE $wpcwdb->user_progress_quiz SET unit_id = 0 WHERE unit_id = %d", $post_id);
		$wpdb->query($SQL);
	}	
}


/**
 * Update the user summary columns to show our custom fields, and hide cluttering ones.
 * @param Array $column_headers The list of columns to show (before showing them).
 * @return Array The actual list of columns to show.
 */
function WPCW_users_manageColumns($column_headers)
{
	// Remove list of posts
    unset($column_headers['posts']);
    
    // Remove name and email address (so that we can combine it)
   	unset($column_headers['name']);
   	unset($column_headers['email']);
	unset($column_headers['role']);
    
    // Add new name column
    $column_headers['wpcw_col_user_details'] = __('Details', 'wp_courseware');
    
    // Training Course Allocations
    $column_headers['wpcw_col_training_courses'] 		= __('Training Course Progress', 'wp_courseware');
    $column_headers['wpcw_col_training_courses_access'] = __('Actions', 'wp_courseware');
    
    
    return $column_headers;
}





/**
 * Creates the column columns of data.
 * 
 * @param String $colContent The content of the column.
 * @param String $column_name The name of the column we're changing.
 * @param Integer $user_id The ID of the user we're rendering.
 * 
 * @return String The formatted HTML code for the table.
 */
function WPCW_users_addCustomColumnContent($colContent, $column_name, $user_id) 
{
	
	switch ($column_name)
	{
		// #### Basically condense user details.
		case 'wpcw_col_user_details': 		
	    	// Format nice details of name, email and role to save space.
	    	$userDetails = get_userdata($user_id);
	    	
	    	// Ensure role is valid and it exists.
	    	$roleName = false;
	    	if (!empty($userDetails->roles)) {
	    		$roleName = $userDetails->roles[0];
	    	}
	    	
			$colContent = sprintf('<span class="wpcw_col_cell_name">%s</span>', $userDetails->data->display_name);
			$colContent .= sprintf('<span class="wpcw_col_cell_email"><a href="mailto:%s" target="_blank">%s</a></span>', $userDetails->data->user_email, $userDetails->data->user_email);
			$colContent .= sprintf('<span class="wpcw_col_cell_role">%s</span>', ucwords($roleName));
	    break;
	    
	    
    
	    // ####ÊThe training course statuses.
	    case 'wpcw_col_training_courses':
	    	// Got some associated courses, so render progress.
	    	$courseData = WPCW_users_getUserCourseList($user_id);
	    	if ($courseData)
	    	{
	    		foreach ($courseData as $courseDataItem) {
	    			$colContent .= WPCW_stats_convertPercentageToBar($courseDataItem->course_progress, $courseDataItem->course_title);
	    		}
	    	} 
	    	
	    	// No courses
	    	else {
	    		$colContent = __('No associated courses', 'wp_courseware');
	    	}
	    break;
	    
	    
	    // #### Links to change user access for courses.
	    case 'wpcw_col_training_courses_access':
	    	$colContent = sprintf('<span><a href="%s&user_id=%d" class="button-primary">%s</a></span>',
	    		admin_url('users.php?page=WPCW_showPage_UserProgess'), 
	    		$user_id,
	    		__('View Detailed Progress', 'wp_courseware')
	    	);
	    	
	    	// View the full progress of the user.
	    	$colContent .= sprintf('<span><a href="%s&user_id=%d" class="button-secondary">%s</a></span>',
	    		admin_url('users.php?page=WPCW_showPage_UserCourseAccess'), 
	    		$user_id,
	    		__('Update Course Access Permissions', 'wp_courseware')
	    	);
	    break;
    }
    
    
    //
    
    
    return $colContent;
}


/**
 * Create a break bar for the forms as a tab, with a save button too.
 * 
 * @return String The HTML for the section break.
 */
function WPCW_forms_createBreakHTML_tab() 
{
	$html = false;	
	$html .= '<div class="wpcw_form_break_tab"></div>';
	return $html;
}


/**
 * Create a break bar for the forms, with a save button too.
 * @param String $title The title for the section.
 * @param String $buttonText The text for the button on the break section.
 * @param String $extraCSSClass Any extra CSS for styling the break.
 * 
 * @return String The HTML for the section break.
 */
function WPCW_forms_createBreakHTML($title, $buttonText = false, $hideButton = false, $extraCSSClass = false) 
{
	if (!$hideButton) {
		$buttonText = __('Save ALL Settings', 'wp_courseware');
	}
	
	$btnHTML = false;
	if ($buttonText && !$hideButton) {
		$btnHTML = sprintf('<input type="submit" value="%s" name="Submit" class="button-primary">', $buttonText);
	}
	
	return sprintf('
		<div class="wpcw_form_break %s">			
			%s
			<h3>%s</h3>
			<div class="wpcw_cleared">&nbsp;</div>
		</div>
	', 
	$extraCSSClass, 
	$btnHTML, 
	$title);
}


/**
 * Shows the conversion metabox to convert the post type
 */
function WPCW_units_showConversionMetaBox()
{
	add_meta_box( 
        'wpcw_units_convert_post',
        __( 'Convert Post to Course Unit', 'wp_courseware' ),
        'WPCW_units_showConversionMetaBox_Inner',
        'post',
        'side',
        'low'        
    );
    add_meta_box(
        'wpcw_units_convert_post',
        __( 'Convert Page to Course Unit', 'wp_courseware' ), 
        'WPCW_units_showConversionMetaBox_Inner',
        'page',
        'side',
        'low'
    );
}


/**
 * Constructs the inner form to convert the post type to a course unit.
 */
function WPCW_units_showConversionMetaBox_Inner()
{
	global $post;
	$conversionURL = admin_url('admin.php?page=WPCW_showPage_ConvertPage&postid=' . $post->ID);
	
	?><p>	
	<?php printf(__('Click to <a href="%s">convert this <b>%s</b> to a Course Unit</a>.', 'wp_courseware'), $conversionURL, get_post_type($post->ID)); ?>
	
	</p><?php	
}

/**
 * Generate an array of pass marks for a select box.
 * @param String $addBlank If specified, add a blank entry to the top of the list
 * @return Array A list of pass marks.
 */
function WPCW_quizzes_getPercentageList($addBlank = false)
{
	$list = array();
	
	if ($addBlank) {
		$list[] = $addBlank;
	}
		
	for ($i = 100; $i > 0; $i--) {
		$list[$i] = $i . '%';
	}
	
	return $list;
}


/**
 * Return the number of quizzes that are pending grading.
 * @return Integer The total number of quizzes that need grading.
 */
function WPCW_quizzes_getPendingGradingCount_all()
{
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
 
    return $wpdb->get_var("
		SELECT COUNT(*)
		FROM $wpcwdb->user_progress_quiz
		WHERE quiz_needs_marking > 0
		  AND quiz_is_latest = 'latest'
	");    
}



/**
 * Gets a list of all blocking courses for the specified course ID.
 * @param Integer $courseID The ID of the course to search.
 * @return Array A list of blocking quizzes for the specified course ID (or false if there are none).
 */
function WPCW_quizzes_getAllBlockingQuizzesForCourse($courseID)
{
	global $wpdb, $wpcwdb;
    $wpdb->show_errors();
    
    $SQL = $wpdb->prepare("
    	SELECT * 
    	FROM $wpcwdb->quiz 
    	WHERE parent_course_id = %d 
    	  AND quiz_type = 'quiz_block'
   	", $courseID);
    	
    
    return $wpdb->get_results($SQL);
}

/**
 * Get the quiz results data for the specified user and list of quizzes.
 * 
 * @param Integer $userID The ID of the user to get the progress data for.
 * @param String $quizIDListForSQL The SQL that contains an SQL list of quiz IDs.
 * @return Array A list of the quiz progress for the specified user.
 */
function WPCW_quizzes_getQuizResultsForUser($userID, $quizIDListForSQL)
{
	global $wpdb, $wpcwdb;
	$wpdb->show_errors();
	
	$SQL = $wpdb->prepare("
		SELECT * FROM $wpcwdb->user_progress_quiz
		WHERE quiz_id IN $quizIDListForSQL
		  AND user_id = %d
	", $userID);
	
	$quizResults = $wpdb->get_results($SQL);
	$quizData = array();
	
	if ($quizResults)
	{
		// Convert list into quid_id => object
		foreach ($quizResults as $aResult) {
			$quizData[$aResult->quiz_id] = $aResult;
		}
	}
	
	return $quizData;
}



/**
 * Translates a question type into its proper name.
 * 
 * @param String $questionType The type of the quiz question.
 * @return String The question type as a label.
 */
function WPCW_quizzes_getQuestionTypeName($questionType)
{
	$questionTypeStr = __('n/a', 'wp_courseware');
	switch ($questionType)
	{
		case 'truefalse':
				$questionTypeStr = __('True/False', 'wp_courseware');
			break;
		
		case 'multi':
				$questionTypeStr = __('Multiple Choice', 'wp_courseware');
			break;
	
		case 'upload':
				$questionTypeStr = __('File Upload', 'wp_courseware');
			break;
			
		case 'open':
				$questionTypeStr = __('Open Ended', 'wp_courseware');
			break;
	}
	return $questionTypeStr;
}


/**
 * Determine if any of the specified list of questions require manual grading.
 * 
 * @param Array $quizItems The items to check
 * @return Boolean True if the items need manual grading, false otherwise.
 */
function WPCW_quizzes_containsQuestionsNeedingManualGrading($quizItems)
{
	if (!$quizItems) {
		return false;
	}
	
	foreach ($quizItems as $quizItem)
	{
		// Open or upload questions
		if ('open' == $quizItem->question_type || 'upload' == $quizItem->question_type) {
			return true;	
		}			
	}
	
	return false;
}


/**
 * Get a list of all quizzes for a training course, in the order that they are used.
 * 
 * @param Integer $courseID The ID of the course to get the quizzes for.
 * 
 * @return Array A list of the quizzes in order.
 */
function WPCW_quizzes_getAllQuizzesForCourse($courseID)
{
	global $wpcwdb, $wpdb;
	$wpdb->show_errors();
		
	return $wpdb->get_results($wpdb->prepare("
    	SELECT * 
    	FROM $wpcwdb->quiz q
    		LEFT JOIN $wpcwdb->units_meta um ON um.unit_id = q.parent_unit_id
    	WHERE q.parent_course_id = %d
    	  AND quiz_type != 'survey' 
    	ORDER BY unit_order
   	", $courseID));
}

/**
 * Show the section that deals with pagination.
 * 
 * @param String $baseURL The URL to use that starts of the paging.
 * @param Integer $pageNumber The current page.
 * @param Integer $pageCount The number of pages.
 * @param Integer $dataCount The number of data rows.
 * @param Integer $recordStart The current record number.
 * @param Integer $recordEnd The ending record number.
 * @param String $leftControls The HTML for controls shown on the left.
 */
function WPCW_tables_showPagination($baseURL, $pageNumber, $pageCount, $dataCount, $recordStart, $recordEnd, $leftControls = false)
{
	$html = '<div class="tablenav wpcw_tbl_paging">';
	$html .= $leftControls; 
	
	
	$html .= '<div class="wpbs_paging tablenav-pages">';
	$html .= sprintf('<span class="displaying-num">Displaying %s &ndash; %s of %s</span>',
				$recordStart,
				($dataCount < $recordEnd ? $dataCount : $recordEnd), // ensure that the upper number of the record matches how many are left.
				$dataCount
			); 

	// Got more than 1 page?				
	if ($pageCount > 1) 
	{
		if ($pageNumber > 1) 
		{
			$html .= sprintf('<a href="%s%s" class="prev page-numbers">&laquo;</a>'."\n",
						$baseURL,
						$pageNumber-1						
					);
		}
		
		$pageList = array();
						
		// Always have first and last page linked
		$pageList[] = 1;
		$pageList[] = $pageCount;
		
		// Have 3 pages either side of page we're on
		if ($pageNumber-3 > 1) {
			$pageList[] = $pageNumber-3;
		}
		
		if ($pageNumber-2 > 1) {
			$pageList[] = $pageNumber-2;
		}
		if ($pageNumber-1 > 1) {
			$pageList[] = $pageNumber-1;
		}
		if ($pageNumber+1 < $pageCount) {
			$pageList[] = $pageNumber+1;
		}
		if ($pageNumber+2 < $pageCount) {
			$pageList[] = $pageNumber+2;
		}
		if ($pageNumber+3 < $pageCount) {
			$pageList[] = $pageNumber+3;
		}				

		// Plus we want the current page
		if ($pageNumber != $pageCount && $pageNumber != 1) {
			$pageList[] = $pageNumber;
		}
		
		// Sort pages in order and then render them
		sort($pageList);
		$previous = 0;
		foreach ($pageList as $pageLink)
		{
			// Add dots if a large gap between numbers
			if ($previous > 0 && ($pageLink - $previous) > 1) {
				$html .= '<span class="page-numbers dots">...</span>';
			}
			
			$html .= sprintf('<a href="%s%s" class="page-numbers %s">%s</a>',
				$baseURL,
				$pageLink,
				($pageNumber == $pageLink ? 'current' : ''),
				$pageLink
				);

			// Want to check what the previous one is
			$previous = $pageLink;
		}
		
		// Got pages left at the end
		if ($pageCount > $pageNumber) {
			$html .= sprintf('<a href="%s%s" class="next page-numbers">&raquo;</a>',
						$baseURL, 
						$pageNumber+1
						);
		}
	
	} // end of it pageCount > 1
	$html .= '</div>'; // end of tablenav-pages
	
	
	return $html . '</div>'; // end of tablenav
}


/**
 * Get the URL for the desired page, preserving any parameters.
 * @param String $pageBase The based page to fetch.
 * @param Mixed $ignoreFields The array or string of parameters not to include.
 * @return String The newly formed URL. 
 */
function WPCW_urls_getURLWithParams($pageBase, $ignoreFields = false)
{
	// Parameters to extract from URL to keep in the URL.
	$params = array (
		's' 		=> false, 
		'pagenum' 	=> false, 
		'filter'	=> false
	);
	
	// Got fields we don't want in the URL? Handle both a string and
	// arrays
	if ($ignoreFields) 
	{
		if (is_array($ignoreFields)) {	
			foreach ($ignoreFields as $field) {
				unset($params[$field]);
			}
		} else {
			unset($params[$ignoreFields]);
		}
	}	
	 
	foreach ($params as $paramName => $notused)
	{
		$value = WPCW_arrays_getValue($_GET, $paramName);
		if ($value) {
			$pageBase .=  '&' . $paramName . '=' . $value;
		}
	}
	
	return $pageBase;
}



/**
 * Show the page where the user can set up the certificate settings. 
 */
function WPCW_showPage_Certificates()
{
	$page = new PageBuilder(true);
	$page->showPageHeader(__('Training Courses - Certificate Settings', 'wp_courseware'), '75%', WPCW_icon_getPageIconURL());
	
	 
	$settingsFields = array(
		'section_certificates_defaults' => array(
				'type'	  	=> 'break',
				'html'	   	=> WPCW_forms_createBreakHTML(__('Certificate Settings', 'wp_courseware')),
			),			
			
		'cert_signature_type' => array(
				'label' 	=> __('Signature Type', 'wp_courseware'),
				'type'  	=> 'radio',
				'cssclass'	=> 'wpcw_cert_signature_type',
				'required'	=> 'true',
				'data'		=> array(
					'text' 		=> sprintf('<b>%s</b> - %s', __('Text', 'wp_courseware'), __('Just use text for the signature.', 'wp_courseware')),
					'image' 	=> sprintf('<b>%s</b> - %s', __('Image File', 'wp_courseware'), __('Use an image for the signature.', 'wp_courseware')),
				),
			),	

		'cert_sig_text' => array(
				'label' 	=> __('Name to use for signature', 'wp_courseware'),
				'type'  	=> 'text',
				'cssclass'	=> 'wpcw_cert_signature_type_text',
				'desc'  	=> __('The name to use for the signature area.', 'wp_courseware'),
				'validate'	 	=> array(
					'type'		=> 'string',
					'maxlen'	=> 150,
					'minlen'	=> 1,
					'regexp'	=> '/^[^<>]+$/',
					'error'		=> __('Please enter the name to use for the signature area.', 'wp_courseware'),
				)	
			),
			
		'cert_sig_image_url' => array(
				'label' 	=> __('Your Signature Image', 'wp_courseware'),
				'cssclass'	=> 'wpcw_image_upload_field wpcw_cert_signature_type_image',
				'type'  	=> 'text',
				'desc'  	=> '&bull;&nbsp;' . __('The URL of your signature image. Using a transparent image is recommended.', 'wp_courseware') .  
							   	'<br/>&bull;&nbsp;' . sprintf(__('The image must be <b>%d pixels wide, and %d pixels high</b> to render correctly. ', 'wp_courseware'), WPCW_CERTIFICATE_SIGNATURE_WIDTH_PX*2, WPCW_CERTIFICATE_SIGNATURE_HEIGHT_PX*2),
				'validate'	 	=> array(
					'type'		=> 'url',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please enter the URL of your signature image.', 'wp_courseware'),
				),
				'extrahtml'		=> sprintf('<input id="cert_sig_image_url_btn" class="wpcw_image_upload button-secondary" type="button" value="%s" />', __('Upload Image', 'wp_courseware')),
			),			
				
		'cert_logo_enabled' => array(
				'label' 	=> __('Show your logo?', 'wp_courseware'),
				'cssclass'	=> 'wpcw_cert_logo_enabled',
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'cert_logo' 	=> sprintf('<b>%s</b> - %s', __('Yes', 'wp_courseware'), __('Use your logo on the certificate.', 'wp_courseware')),
					'no_cert_logo' 	=> sprintf('<b>%s</b> - %s', __('No', 'wp_courseware'), __('Don\'t show a logo on the certificate.', 'wp_courseware')),
				),
			),	

		'cert_logo_url' => array(
				'label' 	=> __('Your Logo Image', 'wp_courseware'),
				'type'  	=> 'text',
				'cssclass'	=> 'wpcw_cert_logo_url wpcw_image_upload_field',
				'desc'  	=> '&bull;&nbsp;' . __('The URL of your logo image. Using a transparent image is recommended.', 'wp_courseware') .  
							   	'<br/>&bull;&nbsp;' . sprintf(__('The image must be <b>%d pixels wide, and %d pixels</b> high to render correctly. ', 'wp_courseware'), WPCW_CERTIFICATE_LOGO_WIDTH_PX*2, WPCW_CERTIFICATE_LOGO_HEIGHT_PX*2),
				'validate'	 	=> array(
					'type'		=> 'url',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please enter the URL of your logo image.', 'wp_courseware'),
				),
				'extrahtml'		=> sprintf('<input  id="cert_logo_url_btn" class="wpcw_image_upload button-secondary" type="button" value="%s" />', __('Upload Image', 'wp_courseware')),	
			),	

		'cert_background_type' => array(
				'label' 	=> __('Certificate Background', 'wp_courseware'),
				'cssclass'	=> 'wpcw_cert_background_type',
				'type'  	=> 'radio',
				'required'	=> 'true',
				'data'		=> array(
					'use_default' 	=> sprintf('<b>%s</b> - %s', __('Built-in', 'wp_courseware'), __('Use the built-in certificate background.', 'wp_courseware')),
					'use_custom' 	=> sprintf('<b>%s</b> - %s', __('Custom', 'wp_courseware'), __('Use your own certificate background.', 'wp_courseware')),
				),
			),	

		'cert_background_custom_url' => array(
				'label' 	=> __('Custom Background Image', 'wp_courseware'),
				'type'  	=> 'text',
				'cssclass'	=> 'wpcw_cert_background_custom_url wpcw_image_upload_field',
				'desc'  	=> '&bull;&nbsp;' . __('The URL of your background image.', 'wp_courseware') .  
							   	'<br/>&bull;&nbsp;' . sprintf(__('The background image must be <b>%d pixels wide, and %d pixels</b> high at <b>72 dpi</b> to render correctly. ', 'wp_courseware'), WPCW_CERTIFICATE_BG_WIDTH_PX, WPCW_CERTIFICATE_BG_HEIGHT_PX),
				'validate'	 	=> array(
					'type'		=> 'url',
					'maxlen'	=> 300,
					'minlen'	=> 1,
					'error'		=> __('Please enter the URL of your certificate background image.', 'wp_courseware'),
				),
				'extrahtml'		=> sprintf('<input  id="cert_background_custom_url_btn" class="wpcw_image_upload button-secondary" type="button" value="%s" />', __('Upload Image', 'wp_courseware')),	
			),	
		);
		
	
	$settings = new SettingsForm($settingsFields, WPCW_DATABASE_SETTINGS_KEY, 'wpcw_form_settings_certificates');
	$settings->setSaveButtonLabel(__('Save ALL Settings', 'wp_courseware'));
	
	$settings->msg_settingsSaved   	= __('Settings successfully saved.', 'wp_courseware');
	$settings->msg_settingsProblem 	= __('There was a problem saving the settings.', 'wp_courseware');
	$settings->setAllTranslationStrings(WPCW_forms_getTranslationStrings());
			
	$settings->show();	

	
	
	// RHS Support Information
	$page->showPageMiddle('23%');	
		
	// Create a box where the admin can preview the certificates to see what they look like.
	$page->openPane('wpcw-certificates-preview', __('Preview Certificate', 'wp_courseware'));
	printf('<p>%s</p>', __('After saving the settings, you can preview the certificate using the button below. The preview opens in a new window.', 'wp_courseware'));
	printf('<div class="wpcw_btn_centre"><a href="%scertificate_gen.php?certificate=preview" target="_blank" class="button-primary">%s</a></div>', WPCW_plugin_getPluginPath(), __('Preview Certificate', 'wp_courseware'));	
	
	$page->closePane();
	
	
	
	WPCW_docs_showSupportInfo($page);
	WPCW_docs_showSupportInfo_News($page);	
	WPCW_docs_showSupportInfo_Affiliate($page);
	
	
	$page->showPageFooter();
}


/**
 * Translation strings to use with each form.
 * @return Array The translated strings.
 */
function WPCW_forms_getTranslationStrings()
{
	return array(
			"Please fill in the required '%s' field." 	=> __("Please fill in the required '%s' field.", 'wp_courseware'),
			"There's a problem with value for '%s'." 	=> __("There's a problem with value for '%s'.", 'wp_courseware'),
			'required' 									=> __('required', 'wp_courseware')
	);
}

/**
 * Create a dropdown box using the list of values provided and select a value if $selected is specified.
 * @param $name String The name of the drop down box.
 * @param $values String  The values to use for the drop down box.
 * @param $selected String  If specified, the value of the drop down box to mark as selected.
 * @param $cssid String The CSS ID of the drop down list.
 * @param $cssclass String The CSS class for the drop down list.
 * @return String The HTML for the select box.
 */
function WPCW_forms_createDropdown($name, $values, $selected, $cssid = false, $cssclass = false)
{
	if (!$values) {
		return false;
	}
	
	$selectedhtml = 'selected="selected" ';
	
	// CSS Attributes
	$css_attrib = false;
	if ($cssid) {
		$css_attrib = "id=\"$cssid\" ";
	}
	if ($cssclass) {
		$css_attrib .= "class=\"$cssclass\" ";
	}
	
	$html = sprintf('<select name="%s" %s>', $name, $css_attrib);	
	
	foreach ($values as $key => $label)
	{
		$html .= sprintf('<option value="%s" %s>%s&nbsp;&nbsp;</option>', $key, ($key == $selected ? $selectedhtml : ''), $label);
	}
		
	return $html . '</select>';
}



?>