<div class="cbc-yt-playlist default"<?php cbc_output_width();?>>
	<div class="cbc-player"<?php cbc_output_player_size();?> <?php cbc_output_player_data();?>></div>
	<div class="cbc-playlist-wrap">
		<div class="cbc-playlist">
			<?php foreach( $videos as $cbc_video ): ?>
			<div class="cbc-playlist-item">
				<a href="<?php cbc_video_post_permalink();?>"<?php cbc_output_video_data();?>>
					<?php cbc_output_thumbnail();?>
					<?php cbc_output_title();?>
				</a>
			</div>
			<?php endforeach;?>
		</div>
		<a href="#" class="playlist-visibility collapse"></a>
	</div>	
</div>