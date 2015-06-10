<?php
if (!empty($color)){
	?>
	<style>
		#<?php echo $id; ?>.step-graphic-style-<?php echo $style; ?> span{
			background-color: <?php echo $color; ?>;
		}
		#<?php echo $id; ?>.step-graphic-style-<?php echo $style; ?> p.heading{
			color: <?php echo $color; ?>;
		}
	</style>
	<?php
}
?>

<ul id="<?php echo $id; ?>" class="step-graphic-style-<?php echo $style; ?>">
	<?php
	foreach($steps as $step){
		?>
		<li>
			<?php echo (!empty($step['text']) ? '<span><h1>'.$step['text'].'</h1></span>' : ''); ?>
			<div>
				<p class="heading"><?php echo strip_tags($step['headline']); ?></p>
				<p><?php echo strip_tags($step['information']); ?></p>
			</div>
		</li>
		<?php
	}
	?>
</ul>