	<p class="description">
		<?php _e('Import videos from YouTube.', 'cbc_video');?><br />
		<?php _e('Enter your search criteria and submit. All found videos will be displayed and you can selectively import videos into WordPress.', 'cbc_video');?>
	</p>
	<form method="get" action="" id="cbc_load_feed_form">
		<?php wp_nonce_field('cbc-video-import', 'cbc_search_nonce');?>
		<input type="hidden" name="post_type" value="<?php echo $this->post_type;?>" />
		<input type="hidden" name="page" value="cbc_import" />
		<input type="hidden" name="cbc_source" value="youtube" />
		<table>
			<tr class="cbc_feed">
				<td valign="top">
					<label for="cbc_feed"><?php _e('Feed type', 'cbc_video');?> :</label>
				</td>
				<td>
					<select name="cbc_feed" id="cbc_feed">
						<option value="user" title="<?php _e('YouTube user ID', 'cbc_video');?>"><?php _e('User feed', 'cbc_video');?></option>
						<option value="playlist" title="<?php _e('YouTube playlist ID', 'cbc_video');?>"><?php _e('Playlist feed', 'cbc_video');?></option>
						<option value="query" title="<?php _e('Search query', 'cbc_video');?>" selected="selected"><?php _e('Search query feed', 'cbc_video');?></option>
					</select>
					<span class="description"><?php _e('Select the type of feed you want to load.', 'cbc_video');?></span>									
				</td>
			</tr>
			
			<tr class="cbc_results">
				<td valign="top"><label for="cbc_results"><?php _e('Number of videos to retrieve', 'cbc_video');?> :</label></td>
				<td>
					<input type="text" name="cbc_results" id="cbc_results" value="50" size="2" />
					<span class="description"><?php _e('Enter any number of results you want to retrieve. Results will be displayed paginated.', 'cbc_video');?></span>	
				</td>
			</tr>
			
			<tr class="cbc_duration">
				<td valign="top"><label for="cbc_duration"><?php _e('Video duration', 'cbc_video');?> :</label></td>
				<td>
					<select name="cbc_duration" id="cbc_duration">
						<option value=""><?php _e('Any', 'cbc_video');?></option>
						<option value="short"><?php _e('Short (under 4min.)', 'cbc_video');?></option>
						<option value="medium"><?php _e('Medium (between 4 and 20min.)', 'cbc_video');?></option>
						<option value="long"><?php _e('Long (over 20min.)', 'cbc_video');?></option>
					</select>		
				</td>
			</tr>
			
			<tr class="cbc_query">
				<td valign="top">
					<label for="cbc_query"><?php _e('Search by', 'cbc_video');?>:</label>
				</td>
				<td>
					<input type="text" name="cbc_query" id="cbc_query" value="" />
					<span class="description"><?php _e('Enter playlist ID, user ID or search query according to Feed Type selection.', 'cbc_video');?></span>
				</td>
			</tr>
			
			<tr class="cbc_order">
				<td valign="top"><label for="cbc_order"><?php _e('Order by', 'cbc_video');?> :</label></td>
				<td>
					<select name="cbc_order" id="cbc_order">
						<option value="published"><?php _e('Date of publishing', 'cbc_video');?></option>
						<option value="viewCount"><?php _e('Number of views', 'cbc_video');?></option>
						
						<option value="position"><?php _e('Position in playlist', 'cbc_video');?></option>
						<option value="commentCount"><?php _e('Number of comments', 'cbc_video');?></option>
						<option value="duration"><?php _e('Duration (longest to shortest)', 'cbc_video');?></option>
						<option value="reversedPosition"><?php _e('Reversed position in playlist', 'cbc_video');?></option>
						<option value="title"><?php _e('Video title', 'cbc_video');?></option>
						
						<option value="relevance" disabled="disabled"><?php _e('Search relevance', 'cbc_video');?></option>
						<option value="rating" disabled="disabled"><?php _e('Rating', 'cbc_video');?></option>					
					</select>
				</td>
			</tr>
			
			<?php
				$theme_support =  cbc_check_theme_support();
				if( $theme_support ):
			?>
			<tr>
				<td valign="top">
					<label for="cbc_theme_import"><?php printf( __('Import as posts <br />compatible with <strong>%s</strong>?', 'cbc_video'), $theme_support['theme_name']);?></label>
				</td>
				<td>
					<input type="checkbox" name="cbc_theme_import" id="cbc_theme_import" value="1" />
					<span class="description">
						<?php printf( __('If you choose to import as %s posts, all videos will be imported as post type <strong>%s</strong> and will be visible in your blog categories.', 'cbc_video'), $theme_support['theme_name'], $theme_support['post_type']);?>
					</span>
				</td>
			</tr>
			<?php 
				endif
			?>
			
			<!-- 
			<tr>
				<td valign="top"><label for=""></label></td>
				<td></td>
			</tr>
			-->			
		</table>
		<?php submit_button( __('Load feed', 'cbc_video'));?>
	</form>