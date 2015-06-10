<?php include('style.inc.php'); ?>

<blockquote id="<?php echo $id; ?>" class="testimonial testimonial-image-style-1">
	<img alt="" src="<?php echo $image ?>" />
	<cite>
		<strong><?php echo $name ?></strong>,
		<?php if (trim($href) != ''): ?>
			<a href="<?php echo $href ?>" target="_blank"><?php echo $company ?></a>
		<?php else: ?>
			<span class="op-testimonial-company"><?php echo $company ?></span>
		<?php endif; ?>
	</cite>
	<?php echo $content ?>
</blockquote>