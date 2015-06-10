<div class="wrap">
	<div class="icon32" id="icon-themes"><br></div>
	<h2><?php _e('Compatibility', 'cbc_video');?></h2>
	
	<?php if( !cbc_check_theme_support() ):?>
	<div  id="message" class="error">
		<p>
			<strong><?php _e("Seems like your theme isn't compatible with the plugin.", 'cbc_video');?></strong>
			<a class="button" href="http://www.constantinb.com/youtube-video-post-for-wordpress-theme-compatibility-tutorial/"><?php _e('See how to make it compatible!', 'cbc_video');?></a>
		</p>
	</div>
	<?php else:?>
	<div  id="message" class="updated">
		<p>
			<strong><?php _e("Congratulations, your current theme is compatible by default with the plugin.", 'cbc_video');?></strong>			
		</p>
	</div>
	<?php endif;?>
	
	<h3><?php _e('Default compatible WordPress themes', 'cbc_video');?></h3>
	<p>
		<?php _e('If any of the themes below is installed and active, you have the option to import YouTube videos directly as posts compatible with the theme.', 'cbc_video');?>
	</p>
	<ol>
		<?php foreach($themes as $theme):?>
		<li>
			<?php 
				$class = 'not-installed';
				if( isset( $theme['installed'] ) && $theme['installed'] ){
					$class = 'cbc-installed';
				}
				if( isset($theme['active']) && $theme['active'] ){
					$class = 'cbc-active';
				}				
			?>
			<?php printf('<a href="%1$s" target="_blank" title="%2$s" class="%3$s">%2$s</a>', $theme['url'], $theme['theme_name'], $class);?>
		</li>
		<?php endforeach;?>
	</ol>
	
	<p>
		<?php _e("If your theme isn't listed above, the next thing to try is to <strong>import videos as regular post type</strong>. To do this, just visit page plugin page Settings and check the option <strong>Import as regular post type (aka post)</strong>.", 'cbc_video');?><br />
		<?php _e('This will enable you to import YouTube videos as regular posts that have the same player settings as the custom post type and will follow the rules you set in Settings page.', 'cbc_video');?>
	</p>
	
	<p>
		<?php printf(__("If importing as regular post type doesn't do it for you (for example your WP theme has video capabilities and you want to import videos as posts compatible with your theme), just %sfollow the tutorial to make your WP theme compatible with the plugin%s.", 'cbc_video'), '<a href="http://www.constantinb.com/youtube-video-post-for-wordpress-theme-compatibility-tutorial/" target="_blank">', '</a>');?>
	</p>
	
	<h3><?php _e('Default compatible WordPress plugins', 'cbc_video');?></h3>
	<p>
		<?php printf( __('Currently, only %sYoast Video SEO plugin%s is supported by default.', 'cbc_video'), '<a href="https://yoast.com/wordpress/plugins/video-seo/" target="_blank">', '</a>');?>
	</p>
	
	<h3><?php _e('Docs and tutorials', 'cbc_video');?></h3>
	<ul>
		<li><a href="http://www.constantinb.com/documentation-wp-youtube-video-import/" target="_blank"><?php _e('How to use the plugin', 'cbc_video');?></a></li>
		<li><a href="http://www.constantinb.com/youtube-video-post-for-wordpress-how-to-get-your-youtube-api-key/" target="_blank"><?php _e('How to get your YouTube API key', 'cbc_video')?></a></li>
		<li><a href="http://www.constantinb.com/youtube-video-post-for-wordpress-theme-compatibility-tutorial/" target="_blank"><?php _e('How to make a non-supported theme compatible with the plugin', 'cbc_video');?></a></li>		
	</ul>
</div>	