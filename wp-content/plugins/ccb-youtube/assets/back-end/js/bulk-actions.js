/**
 * 
 */
;(function($){
	$(document).ready(function(){
		
		var actions = cbc_bulk_actions.actions,
			select_top = $('.tablenav.top select[name=action]'),
			select_bottom = $('.tablenav.bottom select[name=action2]');
		
		$.each(actions, function(value, text){
			$(select_top).append('<option value="' + value + '">' + text + '</option>');
			$(select_bottom).append('<option value="' + value + '">' + text + '</option>');
		});
		
		var messageTop = $('<span></span>', {'class':'cbc-message'}).insertAfter( $('#doaction') ),
			messageBottom = $('<span></span>', {'class':'cbc-message'}).insertAfter( $('#doaction2') );
		
		$(select_top).change(function(){
			$(messageTop).empty();
			$(messageBottom).empty();
		});
		
		$(select_bottom).change(function(){
			$(messageTop).empty();
			$(messageBottom).empty();
		});
		
		var is_loading = false;
		
		$('#posts-filter').submit(function(e){
			
			var val_top = $(select_top).val(),
				val_bottom = $(select_bottom).val();
			
			if( is_loading ){
				e.preventDefault();
				$(messageTop).html( cbc_bulk_actions.wait_longer );
				$(messageBottom).html( cbc_bulk_actions.wait_longer );
				return;
			}
			
			if( 'cbc_thumbnail' == val_top || 'cbc_thumbnail' == val_bottom ){
				e.preventDefault();
				
				is_loading = true;
				
				$('#doaction').addClass('loading');
				$('#doaction2').addClass('loading');
				
				$(messageTop).html( cbc_bulk_actions.wait );
				$(messageBottom).html( cbc_bulk_actions.wait );
				
				if( 'cbc_thumbnail' != val_top ){
					$(select_top).val('cbc_thumbnail');
				}
				
				$.ajax({
					url		: ajaxurl,
					type	: 'POST',
					data	: $(this).serialize(),
					success	: function( response ){
						$('#doaction').removeClass('loading');
						$('#doaction2').removeClass('loading');
						
						is_loading = false;
						
						if( '' == response || !response.data ){
							$(messageTop).html( cbc_bulk_actions.maybe_error );
							$(messageBottom).html( cbc_bulk_actions.maybe_error );
							return;
						}
						
						$(messageTop).html( response.data );
						$(messageBottom).html( response.data );
						
					}
				});
			}
		})
	});
})(jQuery);