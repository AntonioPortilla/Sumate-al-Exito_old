<?php get_header() ?>
		<?php
		$class = op_default_attr('column_layout','option')
		?>
		<div class="main-content content-width cf <?php echo $class ?>" style="display:none;">
	    	<div class="main-content-area-container cf">
                <div class="sidebar-bg"></div>

                <div class="main-content-area">
                    <?php op_mod('advertising')->display(array('advertising', 'home_page', 'top')) ?>
                    <?php
                    if( have_posts() ):
                        if(is_paged()):
                            op_theme_file('loop');
                        else:
                    $postslist = get_posts( array( 'numberposts' => 1 ) );
                    foreach ($postslist as $post) :  setup_postdata($post);
                    $has_img = has_post_thumbnail(); ?>
                    <div class="title-home latest-post<?php echo $has_img ? '':' no-post-image' ?>">
                        <div class="cf post-meta-container">
                        <?php op_post_meta() ?>
                        <p class="post-meta date-extra"><?php the_time('F j Y') ?></p>
                        </div>
                        
                        <?php if($has_img): ?>
                        <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" class="post-image"><?php the_post_thumbnail( ); ?></a>
                        <?php endif ?>                        
                        
                        <div class="contenido-post">
                        <h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                        <?php the_excerpt() ?></div>
                        <script>
                            jQuery('.contenido-post p').eq(0).hide();
                        </script> 
                    </div>
                    <?php endforeach; ?>
                    <div class="clear"></div>
    
                    <?php 
                    $postslist = new WP_Query( array( 'posts_per_page' => 4, 'tag' => 'featured') );
                    if($postslist->have_posts()):?>
                    <div class="featured-posts cf">
                        <h3 class="section-header"><span><?php _e('FEATURED POSTS',OP_SN) ?></span></h3>
                        <div class="post-content-container">
                            <?php foreach ($postslist->posts as $post) :  setup_postdata($post);
                            $has_img = has_post_thumbnail(); ?>
                            <div class="post-content cf<?php echo $has_img ? '':' no-post-image' ?>">
                                <?php if($has_img): ?>
                                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" class="post-image"><?php the_post_thumbnail( 'featured-post' ); ?></a>
                                <?php endif ?>
                                <h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                                <?php the_excerpt() ?>
                                <?php op_post_meta() ?>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <?php endif ?>
                    
                    
                    <?php op_mod('advertising')->display(array('advertising', 'home_page', 'middle')) ?>
                    
                    
                    <div class="older-post-list cf"><?php
                    $postslist = new WP_Query( array( 'posts_per_page' => (get_option('posts_per_page')-1), 'offset' => 1 ) );
                    if ($postslist->have_posts()) :
                    foreach ($postslist->posts as $post) :  setup_postdata($post); ?>
                        <?php 
                        $class = ' no-post-thumbnail'; $image = '';
                        if(has_post_thumbnail()){
                            $class = '';
                        }
                        ?>
                        <div class="title-home older-post<?php echo $class ?>">
                        <?php op_post_meta() ?>
                        <?php //op_post_meta() ?>

                        <?php if(has_post_thumbnail()): ?>
                        <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" class="post-image"><?php the_post_thumbnail( ); ?></a>
                        <?php endif ?> 
                        <?php echo $image ?>  
                                              
                        <h4 class="titulo_posts"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h4>
                            
                        <div class="contenido-post2">
                        <?php the_excerpt() ?>
                        </div>
                        
                        
                        </div>
                    <?php endforeach; ?>
                    <?php endif;?>
                    <?php op_pagination() ?>
                    </div> <!-- end .older-post-list -->
                    <?php endif; endif ?>
                    <?php op_mod('advertising')->display(array('advertising', 'home_page', 'bottom')) ?>
                </div> <!-- end .main-content-area -->
                <?php op_sidebar() ?>

                <script>
                    //jQuery('.contenido-post2 p').eq(0).hide();
                    jQuery('.contenido-post2 p').hide();
                    jQuery('.contenido-post2 p.leer').show();
                    jQuery('p.leer').css('display','block');
                </script> 

                <div class="clear"></div>
            </div>
        </div>

    <div id="publicaciones_sae"> <!-- style="display:none;" -->
        <div class="row">
          <div class="large-12 columns title-publicacion">
            <h1>Entrevistas de Negocios</h1>
            <h3>Ideas de negocios con emprendedores y especialistas en diversos sectores empresariales </h3>
          </div>
        </div>  

        
        <div class="row">
            <div class="bloque_posts">
                
            <ul class="small-block-grid-3">
                <?php 
                    //hoy 26
                    op_mod('advertising')->display(array('advertising', 'home_page', 'top')) ?>
                    <?php
                    if( have_posts() ):
                        if(is_paged()):
                            op_theme_file('loop');
                        else:


                
                $postslist = get_posts( array( 'numberposts' => 1 ) );
                    foreach ($postslist as $post) :  setup_postdata($post);
                    $has_img = has_post_thumbnail(); ?>
                    
                       <li>
                        <?php if($has_img): ?>
                        <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" class="post-image"><?php the_post_thumbnail( ); ?></a>
                        <?php endif ?>                       
                        <h2>
                            <div class="ver_ahora">Ver Ahora</div>
                            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
                        </h2>
                        <div class="contenido-publicacion">
                        <?php the_excerpt() ?>                         
                        </div>                           
                       </li>                       
                        
                    <?php endforeach; ?>



                    
                <?php       
                $postslist = new WP_Query( array( 'posts_per_page' => (get_option('posts_per_page')-1), 'offset' => 1 ) );
                if ($postslist->have_posts()) :
                foreach ($postslist->posts as $post) :  setup_postdata($post); ?>
                    <?php 
                    $class = ' no-post-thumbnail'; $image = '';
                    if(has_post_thumbnail()){
                        $class = '';
                    }
                    ?>                   
                
                    <li>
                        <?php if(has_post_thumbnail()): ?>
                        <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" class="post-image"><?php the_post_thumbnail( ); ?></a>
                        <?php endif ?> 
                        <?php echo $image ?>  
                                              
                        <h4>
                            <div class="ver_ahora">Ver Ahora</div>
                            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
                        </h4>
                            
                        <div class="contenido-publicacion">
                        <?php the_excerpt() ?>
                        </div>
                    </li>            
            <?php endforeach; ?>
                <?php endif;?>


                

                <?php
                //hoy 26 
                    $postslist = new WP_Query( array( 'posts_per_page' => 4, 'tag' => 'featured') );
                    if($postslist->have_posts()):?>
                    <div class="antiguas featured-posts cf">
                        <h3 class="section-header"><span><?php _e('FEATURED POSTS',OP_SN) ?></span></h3>
                        <div class="post-content-container">
                            <?php foreach ($postslist->posts as $post) :  setup_postdata($post);
                            $has_img = has_post_thumbnail(); ?>
                            <div class="post-content cf<?php echo $has_img ? '':' no-post-image' ?>">
                                <?php if($has_img): ?>
                                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" class="post-image"><?php the_post_thumbnail( 'featured-post' ); ?></a>
                                <?php endif ?>
                                <h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                                <?php the_excerpt() ?>
                                <?php op_post_meta() ?>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <?php endif ?>
                    
                    
                    <?php op_mod('advertising')->display(array('advertising', 'home_page', 'middle')) ?>






                <?php op_pagination() ?>


            </ul>


            <?php endif; endif ?>
                    <?php op_mod('advertising')->display(array('advertising', 'home_page', 'bottom')) ?>
                    </div> <!-- end bloque-list -->
                <?php //op_sidebar() ?>



        </div>

        
    </div>

<?php get_footer() ?>