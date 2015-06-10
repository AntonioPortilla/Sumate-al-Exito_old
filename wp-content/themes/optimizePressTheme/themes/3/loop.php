				<!--<div class="hola-post-antiguo older-post-list cf">-->
                    <?php /* Start the Loop */ ?>
					
	                    <?php 
						global $post;
						while ( have_posts() ) : the_post();
						$class = ' no-post-thumbnail'; $image = '';
						if(has_post_thumbnail()){
							$image = '<a href="'.get_permalink().'" title="'.sprintf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ).'" rel="bookmark" class="post-image">'.get_the_post_thumbnail( $post->ID ).'</a>';
							$class = '';
						}
						 ?>
							<li class="repeticua">
		                    	<?php echo $image ?>
								<h4>
									<div class="ver_ahora">Ver Ahora</div>
									<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', OP_SN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
								</h4>
		                    	<?php //op_post_meta() ?>
		                    	<div class="contenido-publicacion">
		                        	<?php the_excerpt(); ?>
		                        </div>
								
							</li> <!-- end .older-post -->
						<?php endwhile ?>
					
					<?php op_pagination() ?>
				<!-- </div> -->