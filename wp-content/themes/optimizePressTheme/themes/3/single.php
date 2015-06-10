<?php
global $post;
get_header();
			$class = op_default_attr('column_layout','option');
			$add_sidebar = true;
			if(defined('OP_SIDEBAR')){
				if(OP_SIDEBAR === FALSE){
					$class = 'no-sidebar';
					$add_sidebar = false;
				} else {
					$class = OP_SIDEBAR;
				}
			}
			?>
			<div class="main-content content-width cf <?php echo $class ?>">
		    	<div class="main-content-area-container cf">
	    	        <?php echo $add_sidebar ? '<div class="sidebar-bg"></div>' : '' ?>

                    <?php while ( have_posts() ) : the_post();
                    $img = '';
                    if(is_singular() && has_post_thumbnail($post->ID)){
                        $img = '<div class="post-image">'.get_the_post_thumbnail($post->ID,'post-thumbnail').'</div>';
                    }
                    ?>
                    <div id="post-<?php the_ID() ?>" <?php post_class('main-content-area'.($img==''?' no-post-image':'')) ?>>
                        <?php op_mod('advertising')->display(array('advertising', 'post_page', 'top')) ?>

                        <div class="body-post latest-post cf">
                        <h1 class="the-title"><?php the_title(); ?></h1>
                            <div class="cf post-meta-container">
                                <?php ('post' == get_post_type()) && op_post_meta() ?>
                                <p class="post-meta date-extra"><?php the_time('F j Y') ?></p>
                            </div>
                            <?php echo $img ?>
							<?php op_mod('sharing')->display('sharing') ?>
                            <div class="contenido-post single-post-content cf">
                                <?php the_content(); ?>
                                <?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', OP_SN ) . '</span>', 'after' => '</div>' ) ); ?>
                            </div>
                        </div> <!-- end .latest-post -->

                        <?php op_mod('advertising')->display(array('advertising', 'post_page', 'bottom')) ?>

                        <?php op_mod('related_posts')->display('related_posts',array('before'=>'<div class="related-posts cf"><h3 class="section-header"><span>'.__('RELATED POSTS',OP_SN).'</span></h3>','after'=>'</div>','ulclass'=>'cf')) ?>
                        <?php comments_template( '', true ); ?>
                    </div>
                    <script>
                        jQuery('.contenido-post iframe').closest('.body-post').find('.post-image').css('display','none');
                        var parrafo = jQuery('.contenido-post iframe').eq(0).closest('p');
                            parrafo.css({
                            'height': '0',
                            'overflow': 'hidden',
                            'padding-bottom': '56.25%',
                            'padding-top': '30px',
                            'position': 'relative'
                            });
                        var video = jQuery('.contenido-post iframe').eq(0);
                            video.css({
                            'height': '100%',
                            'left': '0',
                            'position': 'absolute',
                            'top': '0',
                            'width': '100%'
                            });
                        
                    </script>
                    <?php endwhile ?>
                    <?php op_sidebar() ?>
                </div>
                <div class="clear"></div>
            </div>


<?php get_footer() ?>