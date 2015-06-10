/**
 * Add new video step 1 functionality
 */

;(function($){
	$(document).ready(function(){
		
		// toggle explanation
		$('#cbc_explain').click(function(e){
			e.preventDefault();
			$('#cbc_explain_output').toggle();
		})		
	})
})(jQuery);