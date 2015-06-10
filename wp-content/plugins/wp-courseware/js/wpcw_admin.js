var $j = jQuery.noConflict();
$j(function()
{	
	// Draggable modules
	$j('.wpcw_dragable_modules').sortable({
		placeholder: "wpcw_dragable_modules_placeholder",	// Class for placeholder for CSS
		forcePlaceholderSize: true,							// Forces placeholder to be right size
		cursor: 'pointer',									// Sets useful UI cursor
		stop: function(event, ui) { showUnitsChanged(); }	// UI change because ordering has started
	}).disableSelection();
	
	// Draggable units
	$j('.wpcw_dragable_modules ol, #wpcw_unassigned_units ol').sortable({
		placeholder: "wpcw_dragable_units_placeholder",		// Class for placeholder for CSS
		forcePlaceholderSize: true,							// Forces placeholder to be right size
		cursor: 'pointer',									// Sets useful UI cursor
		connectWith: ".wpcw_dragable_units_connected",		// Links the units		
		stop: function(event, ui) { showUnitsChanged(); }	// UI change because ordering has started
	}).disableSelection();	
	
	// Draggable quizzes
	$j('.wpcw_dragable_quiz_holder ol, #wpcw_unassigned_quizzes ol').sortable({
		placeholder: "wpcw_dragable_quizzes_placeholder",	// Class for placeholder for CSS
		forcePlaceholderSize: true,							// Forces placeholder to be right size
		cursor: 'pointer',									// Sets useful UI cursor
		connectWith: ".wpcw_dragable_quizzes_connected",	// Links the units		
		stop: function(event, ui) { showUnitsChanged(); },	// UI change because ordering has started
		receive: function(event, ui) {
	        if ($j(this).hasClass('wpcw_one_only') &&		// Ensure only units restrict to 1 
	        	$j(this).children().length > 1) {			// See if more than 1.
	            $j(ui.sender).sortable('cancel');
	        }
	    }
	});
	
	// Draggable quiz questions for ordering.
	$j('.wpcw_dragable_question_holder').sortable({
		placeholder: "wpcw_dragable_questions_placeholder",		// Class for placeholder for CSS
		forcePlaceholderSize: true,								// Forces placeholder to be right size
		cursor: 'move',											// Sets useful UI cursor
		handle: '.wpcw_move_icon', 								// Move handle is move icon.
		stop: function(event, ui) { reorderQuizQuestions(); }	// Re-order the questions using the hidden field.
	});	
	
	
	// Delete Confirmation
	$j('.wpcw_delete_item').click(function()
	{
		// Use title to grab the message, to handle multi-language support.
		if (confirm($j(this).attr('title')) == true) {
			return true;
		}
		return false;
	});
	
	// Remove an answer from a quiz
	$j('#wpcw_quiz_details_questions').on('click', '.wpcw_question_remove', function(e)
	{
		e.preventDefault();
		
		// Get parent for later and remove this current row
		var parentTable = $j(this).closest('li').attr('id');
		$j(this).closest('tr').remove();
		
		reorderQuestionAnswers(parentTable);
	});	
	
	// Add a question to a quiz
	$j('#wpcw_quiz_details_questions').on('click', '.wpcw_question_add', function(e)
	{
		e.preventDefault();
		
		// Get this row, clone it
		var row = $j(this).closest('tr');
		var newAnswer = row.clone();
		
		// Empty row, ensure correct answer is not checked
		newAnswer.find('td input[type="text"]').val('');
		newAnswer.find('.wpcw_quiz_details_tick_correct input[type="radio"]').attr('checked', false);
		
		// Add new row after existing
		newAnswer.insertAfter(row);
				
		reorderQuestionAnswers($j(this).closest('li').attr('id'));
	});
	
	
	// Re-order the answers for a question
	function reorderQuestionAnswers(tableid)
	{
		var count = 1;
		$j('#'+ tableid + ' .wpcw_quiz_row_answer').each(function(row) 
		{
			// Renumber the answer
			var qNum = $j(this).find('th span').text(count);
			
			// Renumber radio value
			$j(this).find('.wpcw_quiz_details_tick_correct input[type="radio"]').val(count);
			
			// Renumber the input fields to use the right name 
			var ansField = $j(this).find('td input[type="text"]'); 
			var newName = ansField.attr('name').replace(/\[\d+\]/g, '[' + count + ']');
			ansField.attr('name', newName);
			
			count++;
		});
	}
	
	
	// Check each question, and update hidden field to be the new order
	function reorderQuizQuestions()
	{
		var count = 1;		
		$j('.wpcw_dragable_question_holder .wpcw_question_hidden_order').each(function () 
		{
		    $j(this).val(count++);
		});
	}
	
	
	
	
	// Checks a list of units for quizes for sending back to server to
	// update ordering.
	function checkListOfUnitsForQuizzes(dataList, unitList)
	{
		// Now iterate over unit list to work out order of quizzes
		if (unitList.length > 0)
		{
			// Iterate over quizes to see if units have any quizzes.
			$j.each(unitList, function(index, unitid)
			{
				var unitQuizzes = $j('#' + unitid + ' .wpcw_dragable_quizzes_connected').sortable('toArray');
				if (unitQuizzes.length > 0) {
					dataList[unitid] = unitQuizzes;
				}
			});
		}
	}
	
	// Show or hide form aspects based on type.
	function toggleView_quiz_type() 
	{
		var quizType = $j('.wpcw_quiz_type_hide_pass:checked').val();
		$j('.wpcw_quiz_only').closest('tr').toggle(quizType != 'survey');
		$j('.wpcw_quiz_block_only').closest('tr').toggle(quizType == 'quiz_block');		
	}
	
	// Has the quiz type field been clicked?
	$j('.wpcw_quiz_type_hide_pass').click(function(){
		toggleView_quiz_type();
	});
	
	
		
	// Save Reordered Units
	$j('#wpcw_dragable_modules_save').click(function()
	{
		// Get a list of all modules and their current order.
        var moduleList = $j('.wpcw_dragable_modules').sortable('toArray');
        
        // Units and quizzes that are unassigned
        var unassignedUnits = $j('#wpcw_unassigned_units ol').sortable('toArray');        
        var unassignedQuizzes = $j('#wpcw_unassigned_quizzes ol').sortable('toArray');
                
        // Initialise AJAX message
        var data = {
        		action: 		'wpcw_handle_unit_ordering_saving',
        		moduleList: 	moduleList,
        		unassunits:		unassignedUnits,
        		unassquizzes:	unassignedQuizzes,
        		order_nonce:	wpcw_be_ajax.order_nonce
        	};        
        
        // Check unassignedUnits for quiz changes.
        checkListOfUnitsForQuizzes(data, unassignedUnits);        
        
        // Handle module saving
        if (moduleList.length > 0) 
        {
        	// Iterate over each module
        	$j.each(moduleList, function(index, moduleid) 
        	{
        		// Now iterate over moduleList of units to work out what order they are in.
        		var moduleListUnits = $j('#' + moduleid + ' .wpcw_dragable_units_connected').sortable('toArray');
        		
        		// Create sublist of module ID => list of units in their respective orders.
        		data[moduleid] = moduleListUnits;
        		
        		checkListOfUnitsForQuizzes(data, moduleListUnits);        		
        	});
        }
                
        $j('#wpcw_sticky_bar_status').text('Saving changes...');
        $j('#wpcw_sticky_bar').attr('class', 'saving');
        
    	jQuery.post(ajaxurl, data, function(response) {
    		$j('#wpcw_sticky_bar_status').text('New ordering saved successfully.');
    		$j('#wpcw_sticky_bar').attr('class', 'done').delay(1000).slideUp('slow');
    	});
    	
    	return false;
    });    
	
	// Show Stickybar - Units Changed
	function showUnitsChanged()
	{
		if ($j('#wpcw_sticky_bar').is(":visible")) {
			return;
		} 		
		
		var textOrig = $j('#wpcw_sticky_bar_status').attr('title');
		$j('#wpcw_sticky_bar_status').text(textOrig);
		$j('#wpcw_sticky_bar').attr('class', 'ready').slideDown('slow');
	}
	
	// Show Stickybar - Quiz Grade Changed
	function showQuizGradesChanged()
	{
		if ($j('#wpcw_sticky_bar').is(":visible")) {
			return;
		} 		
		
		var textOrig = $j('#wpcw_sticky_bar_status').attr('title');
		$j('#wpcw_sticky_bar_status').text(textOrig);
		$j('#wpcw_sticky_bar').attr('class', 'ready').slideDown('slow');
	}
	
	
	
	// Add a new question - multi
	$j('#wpcw_add_question_multi').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_multi', "_new_multi");
	});
	
	// Add a new question - true/false
	$j('#wpcw_add_question_truefalse').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_tf', "_new_tf");
	});
	
	// Add a new question - open ended
	$j('#wpcw_add_question_open').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_open', "_new_open");
	});
	
	// Add a new question - upload
	$j('#wpcw_add_question_upload').click(function(e) {
		cloneQuizForm('#wpcw_quiz_details_new_upload', "_new_upload");
	});
	
	
	// Generic function that does the template quiz clone.
	function cloneQuizForm(templateQuizForm, strToReplace)
	{
		// Get the ID of the new question, and update the count.
		var newQCount = parseInt($j('#wpcw_question_template_count').text());
		$j('#wpcw_question_template_count').text(++newQCount);
		
		// Duplicate the template form for a new question, renaming everything to use a custom ID
		var newForm = $j(templateQuizForm).clone().outerHTML().replace(new RegExp(strToReplace, 'g'), "_new_question_" + newQCount);
		
		$j('.wpcw_dragable_question_holder').append($j(newForm).fadeIn());
		reorderQuizQuestions();
	}
	
	
	
	
	
	
	// Delete a question with confirmation message.
	$j('.wpcw_dragable_question_holder').on('click', '.wpcw_delete_icon', function(e)
	{		
		e.preventDefault();
		if (confirm($j(this).attr('rel'))) 
		{		
			var parent = $j(this).closest('li');
			
			// Add hidden field to form to mark this item for deletion. 
			$j('#wpcw_question_update_form').append('<input type="hidden" name="delete_' + parent.attr('id') + '" value="true" />');
			
			// Hide item from view.
			parent.fadeOut('slow', function() { $j(this).remove(); });
		}
	});
	
	// Delete a quiz with confirmation message.
	$j('.wpcw_action_link_delete_quiz').click(function(e) 
	{		
		return confirm($j(this).attr('rel')); 
	});
	
	
	// Get the outer HTML for an element
	jQuery.fn.outerHTML = function(s) {
	    return s
	        ? this.before(s).remove()
	        : jQuery("<p>").append(this.eq(0).clone()).html();
	};
	
	// Floating menu
	var name = ".wpcw_floating_menu";
	if ($j(name) && $j(name).css("top"))
	{
		menuYloc = parseInt($j(name).css("top").substring(0,$j(name).css("top").indexOf("px")));  
		$j(window).scroll(function () {  
	        var offset = menuYloc+$j(document).scrollTop()+"px";  
	        $j(name).animate({top:offset},{duration:500,queue:false});  
	    });   
	}
	
	// Uploading an image dialog
	// Based on http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
	var file_frame;
	var elemFieldToUpdate;
	
	$j('.wpcw_image_upload').live('click', function(event, eName)
	{
	    event.preventDefault();
	    elemFieldToUpdate = $j(this).attr('id').replace('_btn', '');
	 
	    // If the media frame already exists, reopen it.
	    if (file_frame) {
	      file_frame.open();
	      return;
	    } 
	 
	    // Create the media frame.
	    file_frame = wp.media.frames.file_frame = wp.media(
	    {
	    	title: jQuery(this).data('uploader_title'),
	    	button: {
	    		text: jQuery(this).data('uploader_button_text')
	    	},
	    	multiple: false  
	    });
	 
	    // When an image is selected, run a callback.
	    file_frame.on( 'select', function() 
	    {
	    	// We set multiple to false so only get one image from the uploader
	    	attachment = file_frame.state().get('selection').first().toJSON();
	 
	    	// Update the field with the new image.
	    	$j('#' + elemFieldToUpdate).val(attachment.url);
	    });
	 
	    // Finally, open the modal
	    file_frame.open();
	});
	
	
	// Show or hide certificate aspects based on type.
	function toggleView_certs_type() 
	{
		var certType = $j('.wpcw_cert_signature_type:checked').val();
		$j('.wpcw_cert_signature_type_text').closest('tr').toggle(certType == 'text');
		$j('.wpcw_cert_signature_type_image').closest('tr').toggle(certType != 'text');		
	}
	
	function toggleView_certs_logo() 
	{
		var certLogo = $j('.wpcw_cert_logo_enabled:checked').val();
		$j('.wpcw_cert_logo_url').closest('tr').toggle(certLogo == 'cert_logo');		
	}
	
	function toggleView_certs_bg_img() 
	{
		var bgCustom = $j('.wpcw_cert_background_type:checked').val();
		$j('.wpcw_cert_background_custom_url').closest('tr').toggle(bgCustom == 'use_custom');		
	}
	
	// Has the certificate type been changed?
	$j('.wpcw_cert_signature_type').click(function(){
		toggleView_certs_type();
	});
	
	// Has the certificate logo been enabled?
	$j('.wpcw_cert_logo_enabled').click(function(){
		toggleView_certs_logo();
	});
	
	// Has the custom certificate bg been enabled?
	$j('.wpcw_cert_background_type').click(function(){
		toggleView_certs_bg_img();
	});
	
	// Course Edit - Tabs
	$j('#wpcw_courses_tabs .wpcw_tab').click(function(e)
	{
		// Remove all active tabs.
		$j('#wpcw_courses_tabs .wpcw_tab').removeClass('wpcw_tab_active');
		$j('.wpcw_tab_wrapper .form-table').removeClass('wpcw_tab_content_active');
		
		// Make the selected tab active
		$j(this).addClass('wpcw_tab_active');
		$j('.form-table#' + $j(this).attr('data-tab')).addClass('wpcw_tab_content_active');
		
		e.preventDefault();
	});
	
	
	// Quiz Questions - Instructor has changed grades
	$j('.wpcw_tbl_progress_quiz_answers_grade').change(function(e) {
		showQuizGradesChanged();
	});
	
	// Save button clicked on grading popup bar.
	$j('#wpcw_tbl_progress_quiz_grading_updated').click(function(e)
	{
		e.preventDefault();
		$j('#wpcw_tbl_progress_quiz_grading_form').submit();
	});
	
	// Grade for open-ended questions - click to edit
	$j('.wpcw_grade_already_graded a').click(function(e)
	{
		e.preventDefault();
		$j(this).closest('.wpcw_grade_view').hide();
		$j(this).closest('td').find('.wpcw_tbl_progress_quiz_answers_grade').show();
	});
	
	// Allow admin to choose when a quiz can be re-graded.
	$j('.wpcw_user_progress_failed_next_action label').click(function(e)
	{
		$j('.wpcw_user_progress_failed_reason').toggle($j(this).find('.wpcw_next_action_retake_quiz').is(':checked'));
	});
	
	
	
	// On load
	toggleView_quiz_type(); 
	toggleView_certs_type(); 
	toggleView_certs_logo();
	toggleView_certs_bg_img();
	
	// Show first tab (if there are any)
	$j('.wpcw_tab_wrapper a.wpcw_tab:first-child').trigger('click');
});