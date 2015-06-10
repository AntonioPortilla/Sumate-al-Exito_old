/**
 * Modal window for videos list functionality
 */
;(function($){
	$(document).ready(function(){
		
		// check all functionality
		var chkbxs = $('#cb-select-all-1, #cb-select-all-2, .ccb-video-list-select-all');		
		$(chkbxs).click(function(){
			if( $(this).is(':checked') ){
				$('.cbc-video-checkboxes').attr('checked', 'checked').trigger('change');
				$(chkbxs).attr('checked', 'checked');
			}else{
				$('.cbc-video-checkboxes').removeAttr('checked').trigger('change');
				$(chkbxs).removeAttr('checked');
			}
		});
		
		// some elements
		var playlistItemsContainer 	= window.parent.jQuery('#ccb-list-items'),
			m						= window.parent.CBC_SHORTCODE_MODAL,
			inputField				= $( window.parent.jQuery('#cbc-playlist-items') ).find('input[name=cbc_selected_items]'),
			in_playlist				= $.grep( $(inputField).val().split('|'), function(val){ return '' != val });
			
		
		// check boxes on load
		if(in_playlist.length > 0){
			$.each( in_playlist, function(i, post_id){
				$('#cbc-video-'+post_id).attr('checked', 'checked');
			});
		}
		
		// checkboxes functionality
		$('.cbc-video-checkboxes').change( function(){
			var post_id = $(this).val();			
			if( $(this).is(':checked') ){				
				if( in_playlist.length == 0 ){
					$(playlistItemsContainer).empty();
				}				
				if( -1 == $.inArray( post_id, in_playlist ) ){				
					in_playlist = $.merge(in_playlist, [post_id]);				
					var c = $('<div />', {
						'class'	: 'playlist_item',
						'id' 	: 'playlist_item_'+post_id,
						'html' 	: $('#title'+post_id).html() + ' <span class="duration">[' + $('#duration'+post_id).html() + ']</span>'
					}).appendTo( playlistItemsContainer );
					
					$('<a />', {
						'id' 	: 'cbc-del-'+post_id,
						'class' : 'cbc-del-item',
						'html' 	: m.deleteItem,
						'href' 	: '#',
						'click' : function(e){
							e.preventDefault();
							$('#cbc-video-'+post_id).removeAttr('checked');
							$(c).remove();
							in_playlist = $.grep( in_playlist, function(value, i){
								return post_id != value;
							});							
							if( in_playlist.length == 0 ){
								$(playlistItemsContainer).empty().html( '<em>'+m.no_videos+'</em>' );				
							}							
							$(inputField).val( in_playlist.join('|') );
						}
					}).prependTo(c);					
				}				
			}else{
				in_playlist = $.grep( in_playlist, function(value, i){
					if( post_id == value ){
						$(playlistItemsContainer).find('div#playlist_item_'+post_id).remove();
					}					
					return post_id != value;
				})
			}			
			if( in_playlist.length == 0 ){
				$(playlistItemsContainer).empty().html( '<em>'+m.no_videos+'</em>' );				
			}			
			$(inputField).val( in_playlist.join('|') );
		});
		
		
		// single shortcode
		var form = $('#cbc-video-list-form'),
			attsContainer = $('#cbc-shortcode-atts'),
			divId = false;
		
		$('.cbc-show-form').click(function(e){
			e.preventDefault();
			var post_id = $(this).attr('id').replace('cbc-embed-', '');
			divId = 'single-video-settings-'+post_id;
			
			$(form).hide();
			$(attsContainer).html( $('#'+divId).html() );
			$('#'+divId).empty();
		})
		
		$('.cbc-cancel-shortcode').live( 'click', function(e){
			e.preventDefault();
			
			var post_id = $(this).attr('id').replace('cancel', ''),
			divId = 'single-video-settings-'+post_id;;
			
			$('#'+divId).html( $(attsContainer).html() );
			$(attsContainer).empty();
			$(form).show();
		})
		
		$('.cbc-insert-shortcode').live('click', function(e){
			e.preventDefault();
			var post_id = $(this).attr('id').replace('shortcode', ''),
				divId	= 'single-video-settings-'+post_id,
				fields 	= $('#'+divId).find('input, select');
			
			var volume = $('#cbc_volume'+post_id).val(),
				width = $('#cbc_width'+post_id).val(),
				aspect = $('#cbc_aspect_ratio'+post_id).val(),
				autoplay = $('#cbc_autoplay'+post_id).is(':checked') ? 1 : 0,
				controls = $('#cbc_controls'+post_id).is(':checked') ? 1 : 0;
			
			var shortcode = '[cbc_video id="'+post_id+'" volume="'+volume+'" width="'+width+'" aspect_ratio="'+aspect+'" autoplay="'+autoplay+'" controls="'+controls+'"]';
			
			window.parent.send_to_editor(shortcode);
			window.parent.jQuery(window.parent.window.CCBVideo_DIALOG_WIN).dialog('close');
			
		})
		
	});	
})(jQuery);
