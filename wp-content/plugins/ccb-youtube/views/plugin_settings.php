<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e('Videos - Plugin settings', 'cbc_video');?></h2>
	<form method="post" action="">
		<?php wp_nonce_field('cbc-save-plugin-settings', 'cbc_wp_nonce');?>
		<table class="form-table cbc-player-settings-options">
			<tbody>
				<!-- Import type -->
				<tr><td colspan="2"><h3><?php _e('Post settings', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3></td></tr>
				<tr valign="top">
					<th scope="row"><label for="post_type_post"><?php _e('Import as regular post type (aka post)', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" name="post_type_post" value="1" id="post_type_post"<?php cbc_check( $options['post_type_post'] );?> />
						<span class="description">
						<?php _e('Videos will be imported as <strong>regular posts</strong> instead of custom post type video. Posts having attached videos will display having the same player options as video post types.', 'cbc_video');?>
						</span>
					</td>
				</tr>				
				<tr valign="top">
					<th scope="row"><label for="archives"><?php _e('Embed videos in archive pages', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" name="archives" value="1" id="archives"<?php cbc_check( $options['archives'] );?> />
						<span class="description">
							<?php _e('When checked, videos will be visible on all pages displaying lists of video posts.', 'cbc_video');?>
						</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="use_microdata"><?php _e('Include microdata on video pages', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" name="use_microdata" value="1" id="homepage"<?php cbc_check( $options['use_microdata'] );?> />
						<span class="description">
							<?php _e('When checked, all pages displaying videos will also include microdata for SEO purposes ( more on <a href="http://schema.org" target="_blank">http://schema.org</a> ).', 'cbc_video');?>
						</span>
					</td>
				</tr>
				
				<!-- Visibility -->
				<tr><td colspan="2"><h3><?php _e('Video post type options', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3></td></tr>
				<tr valign="top">
					<th scope="row"><label for="public"><?php _e('Video post type is public', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" name="public" value="1" id="public"<?php cbc_check( $options['public'] );?> />
						<span class="description">
						<?php if( !$options['public'] ):?>
							<span style="color:red;"><?php _e('Videos cannot be displayed in front-end. You can only incorporate them in playlists or display them in regular posts using shortcodes.', 'cbc_video');?></span>
						<?php else:?>
						<?php _e('Videos will display in front-end as post type video are and can also be incorporated in playlists or displayed in regular posts.', 'cbc_video');?>
						<?php endif;?>
						</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="homepage"><?php _e('Include videos post type on homepage', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" name="homepage" value="1" id="homepage"<?php cbc_check( $options['homepage'] );?> />
						<span class="description">
							<?php _e('When checked, if your homepage displays a list of regular posts, videos will be included among them.', 'cbc_video');?>
						</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="main_rss"><?php _e('Include videos post type in main RSS feed', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" name="main_rss" value="1" id="main_rss"<?php cbc_check( $options['main_rss'] );?> />
						<span class="description">
							<?php _e('When checked, custom post type will be included in your main RSS feed.', 'cbc_video');?>
						</span>
					</td>
				</tr>				
				
				
				<!-- Rewrite settings -->
				<tr><td colspan="2"><h3><?php _e('Video post type rewrite (pretty links)', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3></td></tr>
				<tr valign="top">
					<th scope="row"><label for="post_slug"><?php _e('Post slug', 'cbc_video')?>:</label></th>
					<td>
						<input type="text" id="post_slug" name="post_slug" value="<?php echo $options['post_slug'];?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="taxonomy_slug"><?php _e('Taxonomy slug', 'cbc_video')?> :</label></th>
					<td>
						<input type="text" id="taxonomy_slug" name="taxonomy_slug" value="<?php echo $options['taxonomy_slug'];?>" />
					</td>
				</tr>
								
				<!-- Import settings -->
				<tr><td colspan="2"><h3><?php _e('Content settings', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3></td></tr>
				<tr valign="top">
					<th scope="row"><label for="import_categories"><?php _e('Import categories', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" id="import_categories" name="import_categories"<?php cbc_check($options['import_categories']);?> />
						<span class="description"><?php _e('Categories retrieved from YouTube will be automatically created and videos assigned to them accordingly.', 'cbc_video');?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="import_date"><?php _e('Import date', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="import_date" id="import_date"<?php cbc_check($options['import_date']);?> />
						<span class="description"><?php _e("Imports will have YouTube's publishing date.", 'cbc_video');?></span>
					</td>
				</tr>	
				
				<tr valign="top">
					<th scope="row"><label for="featured_image"><?php _e('Set featured image', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="featured_image" id="featured_image"<?php cbc_check($options['featured_image']);?> />
						<span class="description"><?php _e("YouTube video thumbnail will be set as post featured image.", 'cbc_video');?></span>
						<p style="color:red"><?php _e('If you choose to import thumbnails as featured images please lower the number<br /> of videos imported under <strong>Automatic import</strong> option to 10 or 20 videos.', 'cbc_video');?></p>
					</td>
				</tr>
								
				<tr valign="top">
					<th scope="row"><label for="import_title"><?php _e('Import titles', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" id="import_title" name="import_title"<?php cbc_check($options['import_title']);?> />
						<span class="description"><?php _e('Automatically import video titles from feeds as post title.', 'cbc_video');?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="import_description"><?php _e('Import descriptions as', 'cbc_video')?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' => array(
									'content' 			=> __('post content', 'cbc_video'),
									'excerpt' 			=> __('post excerpt', 'cbc_video'),
									'content_excerpt' 	=> __('post content and excerpt', 'cbc_video'),
									'none'				=> __('do not import', 'cbc_video')
								),
								'name' => 'import_description',
								'selected' => $options['import_description']								
							);
							cbc_select($args);
						?>
						<p class="description"><?php _e('Import video description from feeds as post description, excerpt or none.', 'cbc_video')?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="remove_after_text"><?php _e('Remove text from descriptions found after', 'cbc_video')?>:</label></th>
					<td>
						<input type="text" name="remove_after_text" value="<?php echo $options['remove_after_text'];?>" id="remove_after_text" size="70" />
						<p class="description">
							<?php _e('If text above is found in description, all text following it (including the one entered above) will be removed from post content.', 'cbc_video');?><br />
							<?php _e('<strong>Please note</strong> that the plugin will search for the entire string entered here, not parts of it. An exact match must be found to perform the action.', 'cbc_video');?>
						</p>
					</td>
				</tr>				
				
				<tr valign="top">
					<th scope="row"><label for="prevent_autoembed"><?php _e('Prevent auto embed on video content', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="prevent_autoembed" id="prevent_autoembed"<?php cbc_check($options['prevent_autoembed']);?> />
						<span class="description">
							<?php _e('If content retrieved from YouTube has links to other videos, checking this option will prevent auto embedding of videos in your post content.', 'cbc_video');?>
						</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="make_clickable"><?php _e("Make URL's in video content clickable", 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" name="make_clickable" id="make_clickable"<?php cbc_check($options['make_clickable']);?> />
						<span class="description">
							<?php _e("Automatically make all valid URL's from content retrieved from YouTube clickable.", 'cbc_video');?>
						</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="import_status"><?php _e('Import status', 'cbc_video')?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' => array(
									'publish' 	=> __('Published', 'cbc_video'),
									'draft' 	=> __('Draft', 'cbc_video'),
									'pending'	=> __('Pending', 'cbc_video')
								),
								'name' 		=> 'import_status',
								'selected' 	=> $options['import_status']
							);
							cbc_select($args);
						?>
						<p class="description"><?php _e('Imported videos will have this status.', 'cbc_video');?></p>
					</td>
				</tr>
				
				<!-- Manual Import settings -->
				<tr><td colspan="2"><h3><?php _e('Bulk Import settings', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3></td></tr>
				<tr valign="top">
					<th scope="row"><label for="import_frequency"><?php _e('Automatic import', 'cbc_video')?>:</label></th>
					<td>
						<?php _e('Import ', 'cbc_video');?>
						<?php 
							$args = array(
								'options' 	=> cbc_automatic_update_batches(),
								'name'		=> 'import_quantity',
								'selected'	=> $options['import_quantity']
							);
							cbc_select( $args );
						?>
						<?php _e('every', 'cbc_video');?>
						<?php 
							$args = array(
								'options' => cbc_automatic_update_timing(),
								'name' 		=> 'import_frequency',
								'selected' 	=> $options['import_frequency']
							);
							cbc_select( $args );
						?>
						<p class="description"><?php _e('How often should YouTube be queried for playlist updates.', 'cbc_video');?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="automatic_import_uses"><?php _e('Automatic import runs by', 'cbc_video')?>:</label></th>
					<td>
						<input type="radio" name="automatic_import_uses" id="wp_cron" value="wp_cron"<?php cbc_check( ('wp_cron' == $options['automatic_import_uses']) );?> />
						<label for="wp_cron"><?php _e('WordPress internal CRON JOB system. Videos will be imported at the given interval everytime a user visits your website.', 'cbc_video');?></label><br />
						<input type="radio" name="automatic_import_uses" id="server_cron" value="server_cron"<?php cbc_check( ('server_cron' == $options['automatic_import_uses']) );?> />
						<label for="server_cron"><?php printf( __('My own SERVER CRON job (you need to set up a cron job on your server to open address: <strong>%s</strong> ). ', 'cbc_video'), cbc_get_server_cron_address());?></label>						
						<p class="description"><?php _e('If you select to make automatic imports by SERVER CRON JOB, the same delay as the one set under Automatic import will apply. <br />The difference is that if your website has low traffic, imports will still be made as oposed to WP cron job.<br /> SERVER CRON JOB frequency should be set having the same time delay as the one entered under Automatic Import.', 'cbc_video');?></p>
					</td>
				</tr>
				
				<tr>	
					<th scope="row"><label for="manual_import_per_page"><?php _e('Manual import results per page', 'cbc_video')?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' 	=> cbc_automatic_update_batches(),
								'name'		=> 'manual_import_per_page',
								'selected'	=> $options['manual_import_per_page']
							);
							cbc_select( $args );
						?>
						<p class="description"><?php _e('How many results to display per page on manual import.', 'cbc_video');?></p>
					</td>
				</tr>
				
				<tr>
					<td colspan="2">
						<h3><?php _e('Player settings', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3>
						<p class="description"><?php _e('General YouTube player settings. These settings will be applied to any new video by default and can be changed individually for every imported video.', 'cbc_video');?></p>
					</td>
				</tr>
				
				<tr>
					<th><label for="cbc_aspect_ratio"><?php _e('Player size', 'cbc_video');?>:</label></th>
					<td>
						<label for="cbc_aspect_ratio"><?php _e('Aspect ratio', 'cbc_video');?>:</label>
						<?php 
							$args = array(
								'options' 	=> array(
									'4x3' 	=> '4x3',
									'16x9' 	=> '16x9'
								),
								'name' 		=> 'aspect_ratio',
								'id'		=> 'cbc_aspect_ratio',
								'class'		=> 'cbc_aspect_ratio',
								'selected' 	=> $player_opt['aspect_ratio']
							);
							cbc_select( $args );
						?>
						<label for="cbc_width"><?php _e('Width', 'cbc_video');?>:</label>
						<input type="text" name="width" id="cbc_width" class="cbc_width" value="<?php echo $player_opt['width'];?>" size="2" />px
						| <?php _e('Height', 'cbc_video');?> : <span class="cbc_height" id="cbc_calc_height"><?php echo cbc_player_height( $player_opt['aspect_ratio'], $player_opt['width'] );?></span>px
					</td>
				</tr>
				
				<tr>
					<th><label for="cbc_video_position"><?php _e('Show video in custom post','cbc_video');?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' => array(
									'above-content' => __('Above post content', 'cbc_video'),
									'below-content' => __('Below post content', 'cbc_video')
								),
								'name' 		=> 'video_position',
								'id'		=> 'cbc_video_position',
								'selected' 	=> $player_opt['video_position']
							);
							cbc_select($args);
						?>
					</td>
				</tr>
				
				<tr>
					<th><label for="cbc_volume"><?php _e('Volume', 'cbc_video');?></label>:</th>
					<td>
						<input type="text" name="volume" id="cbc_volume" value="<?php echo $player_opt['volume'];?>" size="1" maxlength="3" />
						<label for="cbc_volume"><span class="description">( <?php _e('number between 0 (mute) and 100 (max)', 'cbc_video');?> )</span></label>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="autoplay"><?php _e('Autoplay', 'cbc_video')?>:</label></th>
					<td><input type="checkbox" value="1" id="autoplay" name="autoplay"<?php cbc_check( (bool )$player_opt['autoplay'] );?> /></td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="cbc_controls"><?php _e('Show player controls', 'cbc_video')?>:</label></th>
					<td><input type="checkbox" value="1" id="cbc_controls" class="cbc_controls" name="controls"<?php cbc_check( (bool)$player_opt['controls'] );?> /></td>
				</tr>
				
				<tr valign="top" class="controls_dependant"<?php cbc_hide((bool)$player_opt['controls']);?>>
					<th scope="row"><label for="fs"><?php _e('Allow fullscreen', 'cbc_video')?>:</label></th>
					<td><input type="checkbox" name="fs" id="fs" value="1"<?php cbc_check( (bool)$player_opt['fs'] );?> /></td>
				</tr>
				
				<tr valign="top" class="controls_dependant"<?php cbc_hide((bool)$player_opt['controls']);?>>
					<th scope="row"><label for="autohide"><?php _e('Autohide controls', 'cbc_video')?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' => array(
									'0' => __('Always show controls', 'cbc_video'),
									'1' => __('Hide controls on load and when playing', 'cbc_video'),
									'2' => __('Fade out progress bar when playing', 'cbc_video')	
								),
								'name' => 'autohide',
								'selected' => $player_opt['autohide']
							);
							cbc_select($args);
						?>
					</td>
				</tr>
				
				<tr valign="top" class="controls_dependant"<?php cbc_hide((bool)$player_opt['controls']);?>>
					<th scope="row"><label for="theme"><?php _e('Player theme', 'cbc_video')?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' => array(
									'dark' => __('Dark', 'cbc_video'),
									'light'=> __('Light', 'cbc_video')
								),
								'name' => 'theme',
								'selected' => $player_opt['theme']
							);
							cbc_select($args);
						?>
					</td>
				</tr>
				
				<tr valign="top" class="controls_dependant"<?php cbc_hide((bool)$player_opt['controls']);?>>
					<th scope="row"><label for="color"><?php _e('Player color', 'cbc_video')?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' => array(
									'red' => __('Red', 'cbc_video'),
									'white'=> __('White', 'cbc_video')
								),
								'name' => 'color',
								'selected' => $player_opt['color']
							);
							cbc_select($args);
						?>
					</td>
				</tr>
				
				<tr valign="top" class="controls_dependant"<?php cbc_hide((bool)$player_opt['controls']);?>>
					<th scope="row"><label for="modestbranding"><?php _e('No YouTube logo on controls bar', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" id="modestbranding" name="modestbranding"<?php cbc_check( (bool)$player_opt['modestbranding'] );?> />
						<span class="description"><?php _e('Setting the color parameter to white will cause this option to be ignored.', 'cbc_video');?></span>
					</td>					
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="iv_load_policy"><?php _e('Annotations', 'cbc_video')?>:</label></th>
					<td>
						<?php 
							$args = array(
								'options' => array(
									'1' => __('Show annotations by default', 'cbc_video'),
									'3'=> __('Hide annotations', 'cbc_video')
								),
								'name' => 'iv_load_policy',
								'selected' => $player_opt['iv_load_policy']
							);
							cbc_select($args);
						?>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="rel"><?php _e('Show related videos', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" id="rel" name="rel"<?php cbc_check( (bool)$player_opt['rel'] );?> />
						<label for="rel"><span class="description"><?php _e('when checked, after video ends player will display related videos', 'cbc_video');?></span></label>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="showinfo"><?php _e('Show video title by default', 'cbc_video')?>:</label></th>
					<td><input type="checkbox" value="1" id="showinfo" name="showinfo"<?php cbc_check( (bool )$player_opt['showinfo']);?> /></td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="disablekb"><?php _e('Disable keyboard player controls', 'cbc_video')?>:</label></th>
					<td>
						<input type="checkbox" value="1" id="disablekb" name="disablekb"<?php cbc_check( (bool)$player_opt['disablekb'] );?> />
						<span class="description"><?php _e('Works only when player has focus.', 'cbc_video');?></span>
						<p class="description"><?php _e('Controls:<br> - spacebar : play/pause,<br> - arrow left : jump back 10% in current video,<br> - arrow-right: jump ahead 10% in current video,<br> - arrow up - volume up,<br> - arrow down - volume down.', 'cbc_video');?></p>
					</td>
				</tr>
				
				<tr>
					<td colspan="2">
						<h3><?php _e('YouTube API key', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3>
						<p class="description"><?php _e('Not needed in most cases but if you experience any YouTube API errors, might help if entered.', 'cbc_video');?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="youtube_api_key"><?php _e('Enter API key', 'cbc_video')?>:</label></th>
					<td>
						<input type="text" name="youtube_api_key" id="youtube_api_key" value="<?php echo $youtube_api_key;?>" size="60" />
						<p class="description">
							<?php if( !cbc_get_yt_api_key('validity') ):?>
							<span style="color:red;"><?php _e('YouTube API key is invalid. All requests will be made without using an API key. Please check the Google Console for the correct API key.', 'cbc_video');?></span><br />
							<?php endif;?>
							<?php _e('To get your YouTube API key, visit this address:', 'cbc_video');?> <a href="https://code.google.com/apis/console" target="_blank">https://code.google.com/apis/console</a>.<br />
							<?php _e('After signing in, visit <strong>Services</strong> and enable <strong>YouTube Data API</strong>.', 'cbc_video');?><br />
							<?php _e('To get your API key, visit API Access and copy an API key from the screen and enter it above.', 'cbc_video');?><br />
							<?php  printf( __('For more detailed informations please see <a href="%s" target="_blank">this tutorial</a>.', 'cbc_video') , 'http://www.constantinb.com/youtube-video-post-for-wordpress-how-to-get-your-youtube-api-key/' ); ?>
						</p>						
					</td>
				</tr>
				
				<tr>
					<td colspan="2">
						<h3><?php _e('Purchase code', 'cbc_video');?> <?php submit_button(__('save settings', 'cbc_video'), 'secondary', 'submit', false, array('style'=>'margin-left:30px;'));?></h3>
						<p class="description"><?php _e('Envato purchase code will enable automatic updates.', 'cbc_video');?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="envato_purchase_code"><?php _e('Enter purchase code', 'cbc_video')?>:</label></th>
					<td>
						<input type="text" name="envato_purchase_code" id="envato_purchase_code" value="<?php echo $envato_licence;?>" size="60" />
						<p class="description"><?php _e('You can find your purchase code by accessing your Envato marketplace Downloads page <br />and clicking on Licence Certificate link that can be found under your plugin purchase.', 'cbc_video');?></p>						
					</td>
				</tr>
				
				
			</tbody>
		</table>
		<?php submit_button(__('Save settings', 'cbc_video'));?>
	</form>
</div>