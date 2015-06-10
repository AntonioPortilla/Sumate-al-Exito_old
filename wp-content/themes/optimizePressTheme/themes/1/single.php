<?php
global $post;
get_header() ?>

            <?php while ( have_posts() ) : the_post();
			$img = '';
            if(is_singular() && has_post_thumbnail($post->ID)){
		    	$img = '<div class="post-image">'.get_the_post_thumbnail($post->ID,'post-thumbnail').'</div>';
            }
			?>
			<div id="post-<?php the_ID() ?>" <?php post_class('main-content-area'.($img==''?' no-post-image':'')) ?>>
	            <?php op_mod('advertising')->display(array('advertising', 'post_page', 'top')) ?>

				<div class="latest-post cf">
					<div class="cf post-meta-container">
        				<?php ('post' == get_post_type()) && op_post_meta() ?>
                        <?php op_mod('sharing')->display('sharing') ?>
					</div>
					<?php echo $img ?>
					<h1 class="the-title"><?php the_title(); ?></h1>
                    <div class="single-post-content cf">
						<?php the_content(); ?>
                        <?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', OP_SN ) . '</span>', 'after' => '</div>' ) ); ?>
                    </div>
				</div> <!-- end .latest-post -->

				<?php op_mod('advertising')->display(array('advertising', 'post_page', 'bottom')) ?>

                <?php op_mod('related_posts')->display('related_posts',array('before'=>'<div class="related-posts cf"><h3 class="section-header"><span>'.__('RELATED POSTS',OP_SN).'</span></h3>','after'=>'</div>','ulclass'=>'cf')) ?>
				<?php comments_template( '', true ); ?>
            </div>
           	<?php endwhile ?>
<?php op_sidebar() ?>

<?php get_footer() ?>