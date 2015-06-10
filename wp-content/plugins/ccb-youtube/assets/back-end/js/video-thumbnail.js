/**
 * 
 */
;(function($){
	$(document).ready(function(){
		
		$('#cbc-import-video-thumbnail').live('click', function(e){
			e.preventDefault();
			
			var data = {
				'action' 	: 'cbc_import_video_thumbnail',
				'id'		: CBC_POST_DATA.post_id
			};
			
			$.ajax({
				type 	: 'post',
				url 	: ajaxurl,
				data	: data,
				success	: function( response ){
					WPSetThumbnailHTML( response.data );
				}
			});	
			
		});
		
	});
})(jQuery);