<div class="optin-modal-container">
	<?php include 'style.inc.php'; ?>
    
	<a href="#" class="optin-modal-link"><?php echo $trigger_button; ?></a>
	
	<div id="<?php echo $id; ?>" class="optin-box optin-box-14 optin-modal"<?php echo $style_str; ?>>
	<?php
		$headline = op_get_var($content,'headline','','<h2>%1$s</h2>');
		echo !empty($headline) ? $headline : '';
	
		$paragraph = op_get_var($content,'paragraph','');
		echo !empty($paragraph) ? '<p class="description">'.strip_tags($paragraph).'</p>' : '';
	
		echo $form_open.$hidden_str;
		op_get_var_e($fields,'name_field');
		op_get_var_e($fields,'email_field');
		echo implode('',$extra_fields);
		?>
		<a href="#" class="css-button-block css-button style-1">
		    <span class="text"><?php echo $button_content; ?></span>
		    <div class="shine"></div>
		    <div class="hover"></div>
		    <div class="active"></div>
		</a>
	</form>
	<?php op_get_var_e($content,'privacy','','<p class="privacy">%1$s</p>') ?>
	<a class="<?php echo $close_class; ?>"><img src="<?php echo OP_ASSETS_URL; ?>images/optin_modal/close.png" /></a>
</div>