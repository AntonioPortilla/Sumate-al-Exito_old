
	<?php if ( is_active_sidebar( 'sub-footer-sidebar' ) ) : ?>
		<div class="sub-footer cf">
			<div class="content-width">
            	<?php dynamic_sidebar( 'sub-footer-sidebar' ); ?>
            </div>
		</div>
        <?php endif; ?>
	<div class="footer">
		<div class="content-width">
			<div class="footer-content cf">
				<div class="footer-left">
					<?php 
					$copy = op_default_option('copyright_notice');
					
					if(!empty($copy)){
						echo '<p>'.$copy.'<br /></p>';
					}
					op_mod('promotion')->display();
					?>
				</div>
				<?php has_nav_menu('footer') && wp_nav_menu( array( 'theme_location' => 'footer', 'depth' => 1 ) ); ?>
			</div>
		</div>
	</div>
</div>
<?php op_footer() ?>
	<script src="<?php echo home_url(); ?>/wp-content/themes/optimizePressTheme/themes/3/js/jquery.js"></script>
    <script src="<?php echo home_url(); ?>/wp-content/themes/optimizePressTheme/themes/3/js/foundation.min.js"></script>
    <script>
      $(document).foundation();
    </script>
</body>
</html>