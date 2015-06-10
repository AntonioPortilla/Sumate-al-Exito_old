var $j = jQuery.noConflict();
//$j(function()
jQuery(document).ready(function($j) 
{	
	// Marking a unit as completed
	$j('.wpcw_fe_progress_box_mark a').click(function() {
		
		var courseid = $j(this).attr('id');
		var data = {
				action: 		'wpcw_handle_unit_track_progress',
				id:				courseid,
				progress_nonce: wpcw_fe_ajax.progress_nonce
			}; 
		
		$j(this).hide();
		$j(this).parent().find('.wpcw_loader').show();
		
		$j.post(wpcw_fe_ajax.ajaxurl, data, function(response) {
			$j('#wpcw_fe_' + courseid).hide().html(response).fadeIn();
		});
		
		return false;
	});	
	
	
	// Function that's called when setting up the quiz form.
	function quizHandler_setup() 
	{
		var quizForm = $j('.wpcw_fe_quiz_box_wrap form');
		if (quizForm.length > 0)
		{
			// the ID of the quiz
			var quizid = quizForm.attr('id');
			
			// Progress bar details
			var bar = quizForm.find('.wpcw_fe_upload_progress_bar');
			var percent = quizForm.find('.wpcw_fe_upload_progress_percent');
			
			// Configure the AJAX request
			var data = {    	
				action 			: 'wpcw_handle_unit_quiz_response',
				id 				: quizid,
				progress_nonce	: wpcw_fe_ajax.progress_nonce
			};
			
			// Configure form 
			var options = {
				target			: '.wpcw_fe_quiz_box_wrap#wpcw_fe_' + quizid,   	// Target to update on response
				replaceTarget	: true,						// Replace completely, rather than just content.
				url				: wpcw_fe_ajax.ajaxurl,		// PostURL
				data			: data,						// AJAX details
				type 			: 'POST',	

				// Before submission - Handle validation here too.
				beforeSubmit : function(formData, jqForm, options) 
				{
					// Assume all fields are valid until checked, so remove error class.
					$j('.wpcw_fe_quiz_q_single').removeClass('wpcw_fe_quiz_q_error');
					
					
					// formData is an array of objects representing the name and value of each field 
				    // that will be sent to the server. Check that each form entry has a value.
					// If a radio group is in the list, then it will not show here, so check for that
					// separately.
					var missingData = false;
					for (var i=0; i < formData.length; i++) 
					{ 
						// Flag question as having an error if empty						
				        if (!formData[i].value)
				        {				        	
				        	$j('#wpcw_fe_wrap_' + formData[i].name).addClass('wpcw_fe_quiz_q_error');
				        	missingData = true;				        	
				        }
					} // end for
					
					// Check for missing radio button selections.
					$j('#' + quizid + ' .wpcw_fe_quiz_q_multi, #' + quizid + ' .wpcw_fe_quiz_q_truefalse').each(function()
					{
						if ($j(this).find('input:checked').length == 0) {
							$j(this).addClass('wpcw_fe_quiz_q_error');
							missingData = true;
						}
					});

					// Handle reporting the error, as missing some data.
				    if (missingData)   	
				    {
			        	// Create the message area
			        	var quizFormParent = quizForm.closest('.wpcw_fe_quiz_box_wrap');
			        	var msgArea = $j('.wpcw_fe_progress_box_wrap .wpcw_fe_progress_box_error');
			        	if (msgArea.length == 0) 
			        	{
			        		// No error area, so add it with error message.
			        		$j('<div class="wpcw_fe_progress_box_wrap"><div class="wpcw_fe_progress_box wpcw_fe_progress_box_error">' + 
			        				wpcw_fe_ajax.str_quiz_all_fields + 
			        			'</div></div>').insertBefore(quizFormParent);
			        	} 
			        	else {
			        		// Error area already exists, just update it with right error.
			        		msgArea.text(wpcw_fe_ajax.str_quiz_all_fields);
			        	}
			        	
			        	// Scroll to the top of the quiz area to show error message.
				    	$j('html, body').animate({				
					         scrollTop: $j('.wpcw_fe_quiz_box_wrap').offset().top - 100
					     }, 200);
			            
			            return false; 
			        } // Something was missing. 
										
					
					// Hide button, show that progress is happening.
					quizForm.find('.wpcw_fe_quiz_submit input').hide();
			    	quizForm.find('.wpcw_loader').show();
			    	
			    	// Hide any errors
			    	$j('.wpcw_fe_progress_box_wrap .wpcw_fe_progress_box_error').remove();
					
					var percentVal = '0%';
					bar.width(percentVal);
					percent.html(percentVal);
					
					// Only show the progress bar if theres an upload field.
					if (quizForm.find('.wpcw_fe_quiz_q_upload_wrapper').length > 0) {
						bar.parent().show();
					}					
				},
				
				// Handle the upload progress
				uploadProgress : function(event, position, total, percentComplete)
				{					
					var percentVal = percentComplete + '%';
					bar.width(percentVal);
					percent.text(wpcw_fe_ajax.str_uploading + ' ' + percentVal); // Show uploading message
				},

				// Called when the upload has completed.
				success : function() 
				{
					var percentVal = '100%';
					bar.width(percentVal);
					percent.text(wpcw_fe_ajax.str_uploading + ' ' + percentVal); // Show uploading message
					
					// Scroll to the top of the quiz area.
			    	$j('html, body').animate({				
				         scrollTop: $j('.wpcw_fe_quiz_box_wrap').offset().top - 100
				     }, 200);
			    	
			    	// Re-attach ajax
			    	quizHandler_setup();
				}
				
				
			};
			
			// Set up the AJAX form request.
			$j('.wpcw_fe_quiz_box_wrap form').ajaxForm(options);
		}
	}
	quizHandler_setup(); // On load
	
	
    
	
	// Toggle visibility of modules in widget/shortcode
	$j('.wpcw_widget_progress .wpcw_fe_module').click(function()
	{
		var moduleID = $j(this).attr('id');
		
		// Show Mode
		if ($j(this).hasClass('wpcw_fe_module_toggle_hide')) {
			$j(this).find('.wpcw_fe_toggle').text('-');
			$j(this).addClass('wpcw_fe_module_toggle_show').removeClass('wpcw_fe_module_toggle_hide');
			$j(this).closest('.wpcw_widget_progress').find('.' + moduleID).show();
		} 		
		// Hide Mode
		else {
			$j(this).find('.wpcw_fe_toggle').text('+');
			$j(this).addClass('wpcw_fe_module_toggle_hide').removeClass('wpcw_fe_module_toggle_show');
			$j(this).closest('.wpcw_widget_progress').find('.' + moduleID).hide();			
		}
	});
	
	
	// Hide all modules (that need collapsing) on load
	$j('.wpcw_widget_progress .wpcw_fe_module_toggle_hide').each(function() {
		var moduleID = $j(this).attr('id');
		$j(this).closest('.wpcw_widget_progress').find('.' + moduleID).hide();
	});
	
	// Disable events on navigation buttons that are disabled.
	$j('a.fe_btn_navigation_disabled').click(function(e) {
		e.preventDefault();
	});
	
});