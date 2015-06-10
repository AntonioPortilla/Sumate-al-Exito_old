<link rel="stylesheet" href="https://service.v2contact.com/chat/css">
<script src="https://service.v2contact.com/chat/api-source"></script>
<?php
global $post;
$class = (defined('OP_LIVEEDITOR') ? ' op-live-editor' : '');
?><!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6<?php echo $class ?>" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7<?php echo $class ?>" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8<?php echo $class ?>" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html<?php echo $class==''?'':' class="'.$class.'"'; ?> <?php language_attributes(); ?>> <!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php 
	op_set_seo_title();
?>
<?php
if ( is_singular() && get_option( 'thread_comments' ) )
	wp_enqueue_script('comment-reply', false, array(OP_SN.'-noconflict-js'), OP_VERSION);
wp_head();
?>
</head>
<body <?php body_class(); ?>>
	<div class="container main-content">
		<?php
		op_page_header();
		$GLOBALS['op_feature_area']->load_feature();
		op_page_feature_title();
		echo $GLOBALS['op_content_layout'];
		op_page_footer();
		?>
	</div><!-- container -->
<?php op_footer() ?>
<script>
	jQuery('#navigation-alongside li>a').css('border-radius','6px');
	
	var bloque = jQuery('.bloque_video_landing');
	bloque.find('div.fixed-width').find('div').eq(0).removeClass();
	bloque.find('div').eq(0).removeClass();	
	/*************************************/
	/*************************************/



	var mql = window.matchMedia("screen and (max-width: 959px)")
	if (mql.matches){ // if media query matches
		var ruta_confirmacion 	= '<?php echo home_url(); ?>/confirmacion/',
			ruta_gracias		= '<?php echo home_url(); ?>/gracias/',
			ruta_actual			= window.location.href;

		if (ruta_confirmacion == ruta_actual) {
			jQuery('.bloque-mobile').css('display','none');
	 		jQuery('.bloque_video_landing').css('display','block');
	 		jQuery('div.footer').css('display','none');
	 		//alert('eureka!!!');
		}
		if (ruta_gracias == ruta_actual) {
			jQuery('.bloque-mobile').css('display','none');
	 		jQuery('.bloque_video_landing').css('display','block');
	 		//alert('GRACIAS MUCHAS GRACIAS');
		}	
	 
	}
	else{
	 
	}



</script>
</body>
</html>