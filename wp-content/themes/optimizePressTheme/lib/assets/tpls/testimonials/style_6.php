<?php include('style.inc.php'); ?>

<blockquote id="<?php echo $id; ?>" class="testimonial testimonial-image-style-5 testimonial-style-serif cf">
	<img alt="" src="<?php echo $image ?>" />
	<?php echo $content ?>
	<cite><strong><?php echo $name ?></strong>,
		<?php if (trim($href) != ''): ?>
			<a href="<?php echo $href ?>" target="_blank"><?php echo $company ?></a>
		<?php else: ?>
			<span class="op-testimonial-company"><?php echo $company ?></span>
		<?php endif; ?>
	</cite>
</blockquote>