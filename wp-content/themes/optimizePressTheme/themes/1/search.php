<?php get_header(); ?>

            <div class="main-content-area">
            <?php if ( have_posts() ) : ?>
                <h2 class="the-title"><?php printf( __( 'Search Results for: &ldquo;%s&rdquo;', OP_SN ), '<span>' . get_search_query() . '</span>' ); ?></h2>
                <?php
                op_mod('advertising')->display(array('advertising', 'pages', 'top'));
                op_theme_file('loop'); ?>
            <?php else: ?>
                <h2 class="the-title"><?php _e( 'Nothing Found', OP_SN ); ?></h2>
                <?php op_mod('advertising')->display(array('advertising', 'pages', 'top')) ?>
                <p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', OP_SN ); ?></p>
                <?php op_search_form() ?>
            <?php endif ?>
                <?php op_mod('advertising')->display(array('advertising', 'pages', 'bottom')) ?>
            </div>
            <?php op_sidebar() ?>

<?php get_footer(); ?>