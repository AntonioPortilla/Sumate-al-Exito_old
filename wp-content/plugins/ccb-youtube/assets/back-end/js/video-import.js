/**
 * Video import form functionality
 * @version 1.0
 */
;(function($){
	$(document).ready(function(){
		
		// search criteria form functionality
		$('#cbc_feed').change(function(){
			var val = $(this).val(),
				ordVal = $('#cbc_order').val();
			
			$('label[for=cbc_query]').html($(this).find('option:selected').attr('title')+' :');
						
			switch( val ){
				case 'query':
					$('tr.cbc_duration').show();
					
					var hide = ['position', 'commentCount', 'duration', 'reversedPosition', 'title'],
						show = ['relevance', 'rating'];
					
					$.each( hide, function(i, el){
						$('#cbc_order option[value='+el+']').attr({'disabled':'disabled'}).css('display', 'none');
					})
					$.each( show, function(i, el){
						$('#cbc_order option[value='+el+']').removeAttr('disabled').css('display', '');
					})
					
					var hI = $.inArray( ordVal, hide );					
					if( -1 !== hI ){
						$('#cbc_order option[value='+hide[hI]+']').removeAttr('selected');
					}
					
				break;
				case 'user':
				case 'playlist':	
					$('tr.cbc_duration').hide();
					
					var show = ['position', 'commentCount', 'duration', 'reversedPosition', 'title'],
						hide = ['relevance', 'rating'];
				
					$.each( hide, function(i, el){
						$('#cbc_order option[value='+el+']').attr({'disabled':'disabled'}).css('display', 'none');
					})
					$.each( show, function(i, el){
						$('#cbc_order option[value='+el+']').removeAttr('disabled').css('display', '');
					})
					
					var hI = $.inArray( ordVal, hide );					
					if( -1 !== hI ){
						$('#cbc_order option[value='+hide[hI]+']').removeAttr('selected');
					}
					
				break;
			}			
		}).trigger('change');
		
		$('#cbc_load_feed_form').submit(function(e){
			var s = $('#cbc_query').val();
			if( '' == s ){
				e.preventDefault();
				$('#cbc_query, label[for=cbc_query]').addClass('cbc_error');
			}
		});
		$('#cbc_query').keyup(function(){
			var s = $(this).val();
			if( '' == s ){
				$('#cbc_query, label[for=cbc_query]').addClass('cbc_error');
			}else{
				$('#cbc_query, label[for=cbc_query]').removeClass('cbc_error');
			}	
		})
		
		/**
		 * Feed results table functionality
		 */		
		// rename table action from action (which conflicts with ajax) to action_top
		$('.ajax-submit .tablenav.top .actions select[name=action]').attr({'name' : 'action_top'});		
		// form submit on search results
		var submitted = false;
		$('.ajax-submit').submit(function(e){
			e.preventDefault();
			if( submitted ){
				$('.cbc-ajax-response')
					.html(cbc_importMessages.wait);
				return;
			}
			
			var dataString 	= $(this).serialize();
			submitted = true;
			
			$('.cbc-ajax-response')
				.removeClass('success error')
				.addClass('loading')
				.html(cbc_importMessages.loading);
			
			$.ajax({
				type 	: 'post',
				url 	: ajaxurl,
				data	: dataString,
				dataType: 'json',
				success	: function(response){
					if( response.success ){
						$('.cbc-ajax-response')
							.removeClass('loading error')
							.addClass('success')
							.html( response.success );
					}else if( response.error ){
						$('.cbc-ajax-response')
							.removeClass('loading success')
							.addClass('error')
							.html( response.error );
					}										
					submitted = false;
				},
				error: function(response){
					$('.cbc-ajax-response')
						.removeClass('loading success')
						.addClass('error')
						.html( cbc_importMessages.server_error + '<div id="cbc_server_error_output" style="display:none;">'+ response.responseText +'</div>' );
					
					$('#cbc_import_error').click(function(e){
						e.preventDefault();
						$('#cbc_server_error_output').toggle();
					});
					
					submitted = false;
				}
			});			
		});		
	})
})(jQuery);