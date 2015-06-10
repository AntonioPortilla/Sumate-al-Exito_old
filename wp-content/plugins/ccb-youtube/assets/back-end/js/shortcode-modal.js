/**
 * TinyMce playlist shortcode insert 
 */
var CCBVideo_DIALOG_WIN = false;
;(function($){
	$(document).ready(function(){
		$('#cbc-insert-playlist-shortcode').live('click', function(e){
			e.preventDefault();
			var videos 		= $('#cbc-playlist-items').find('input[name=cbc_selected_items]').val();
			if( '' == videos ){
				return;
			}
			
			var videos_array = $.grep( videos.split('|'), function(val){ return '' != val }),
				shortcode 	= '[cbc_playlist videos="'+( videos_array.join(',') )+'"]';;
			
			send_to_editor(shortcode);
			$(CCBVideo_DIALOG_WIN).dialog('close');
		});
		
		$('#cbc-shortcode-2-post').click(function(e){
			e.preventDefault();
			if( CCBVideo_DIALOG_WIN ){
				CCBVideo_DIALOG_WIN.dialog('open');
			}
		});
		
		// dialog window
		$('body').append('<div id="CCBVideo_Modal_Window"></div>');
		var url = 'edit.php?post_type=video&page=cbc_videos';
		
		var dialog = $('#CCBVideo_Modal_Window').dialog({
			'autoOpen'		: false,
			'width'			: '90%',
			'height'		: 750,
			'maxWidth'		: '90%',
			'maxHeight'		: 750,
			'minWidth'		: '90%',
			'minHeight'		: 750,
			'modal'			: true,
			'dialogClass'	: 'wp-dialog',
			'title'			: '',
			'resizable'		: true,
			'open'			:function(ui){
				$(ui.target)
					.css({'overflow':'hidden'})
					.append(
						'<div class="wrap"><div id="cbc-playlist-items">'+
							'<div class="inside">'+
								'<input type="hidden" name="cbc_selected_items"  value="" />'+
								'<h2>'+CBC_SHORTCODE_MODAL.playlist_title+' <a href="#" id="cbc-insert-playlist-shortcode" class="add-new-h2">'+CBC_SHORTCODE_MODAL.insert_playlist+'</a></h2>'+
								'<div id="ccb-list-items">'+
									'<em>'+CBC_SHORTCODE_MODAL.no_videos+'</em>'+
								'</div>'+
							'</div>'+	
						'</div>'+
						'<div id="cbc-display-videos">'+
							'<iframe src="'+url+'" frameborder="0" width="100%" height="100%"></iframe>'+
						'</div></div>'
					);
				
			},
			'close':function(ui){
				$(ui.target).empty();
			}
		})		
		CCBVideo_DIALOG_WIN = dialog;		
	});
})(jQuery);