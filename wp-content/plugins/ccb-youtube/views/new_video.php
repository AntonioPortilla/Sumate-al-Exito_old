<div class="wrap">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<h2><?php echo $title;?> - <?php _e('step 1', 'cbc_video');?></h2>
	<form method="post" action="" >
		<?php wp_nonce_field('cbc_query_new_video', 'wp_nonce');?>
		
		<p><?php _e('Please enter the video ID you want to search for:', 'cbc_video');?></p>
		<input type="text" name="cbc_video_id" value="" />
		<a href="#" id="cbc_explain"><?php _e('how to get video ID', 'cbc_video');?></a>
		<?php if( $theme_supported = cbc_check_theme_support() ):?>
		<br />
		<input type="checkbox" name="single_theme_import" id="single_theme_import" value="1" />
		<label for="single_theme_import"><?php printf( __('Import as post compatible with theme <strong>%s</strong>', 'cbc_video'), $theme_supported['theme_name'] );?></label>
		<?php endif;?>
		<p class="hidden" id="cbc_explain_output">
			<?php _e('<strong>Step 1</strong> - open any YouTube video page with your favourite browser.', 'cbc_video');?><br />
			<?php _e('<strong>Step 2</strong> - From your browser address bar copy the value from variable v (highlighted in image below).', 'cbc_video');?><br />
			<img vspace="10" src="<?php echo CBC_URL;?>assets/back-end/images/yt-video-id-example.png" /><br />
			<?php _e('<strong>Step 3</strong> - paste the ID into the field above and hit Search video below.', 'cbc_video');?>
		</p>
		
		<input type="hidden" name="cbc_source" value="youtube" />
		<?php submit_button(__('Search video', 'cbc_video'));?>
	</form>
</div>