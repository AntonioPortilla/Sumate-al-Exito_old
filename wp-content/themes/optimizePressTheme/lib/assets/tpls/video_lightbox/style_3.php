<?php
    if (!empty($inlinecontent)) $inlinecontent = ($data['type']=='embed' ? $inlinecontent : '<span>'.$inlinecontent.'</span>');
?>

<a href="<?php echo $url; ?>"  class="video-lightbox-style-2" title="<?php echo __('',OP_SN); ?>" rel="prettyPhoto" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>" data-video="<?php echo $videoUrl; ?>" data-video1="<?php echo $videoUrl1; ?>" data-video2="<?php echo $videoUrl2; ?>" data-autobuffer="<?php echo $auto_buffer; ?>" data-hide-controls="<?php echo $hide_controls; ?>" data-autoplay="<?php echo $auto_play; ?>">
    <div class="preview-container">
    	<?php 
    		$ruta = $placeholder;
    		
    		$imagen 	= explode('/', $ruta);
    		$imagen 	= array_pop($imagen);
    		$nombre 	= substr($imagen, 0, -4);
    		$reemplazar = str_replace('-', ' ', $nombre);
    		//var_dump($reemplazar);
    	?>
            <img src="<?php echo $placeholder; ?>" class="scale-with-grid" alt="<?php echo $reemplazar; ?>" title="<?php echo $reemplazar; ?>">
            <div class="circle"><div class="play"></div></div>
            <?php echo $inlinecontent; ?>
    </div>
</a>