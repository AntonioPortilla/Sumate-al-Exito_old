<div class="optin-modal-container">
	<?php include 'style.inc.php'; ?>
    
	<a href="#" class="optin-modal-link"><?php echo $trigger_button; ?></a>
	
	<div id="<?php echo $id; ?>" class="optin-box optin-box-9 optin-modal"<?php echo $style_str; ?>>
		<div class="optin-box-content">
		<?php echo $form_open.$hidden_str ?>
			<div class="text-boxes">
			<?php op_get_var_e($fields,'email_field'); ?>
			</div>
			<a href="#" class="css-button-block css-button style-1">
			    <span class="text"><?php echo $button_content; ?></span>
			    <div class="shine"></div>
			    <div class="hover"></div>
			    <div class="active"></div>
			</a>
		</form>
		<?php op_get_var_e($content,'privacy','','<p class="privacy"><img src="'.OP_ASSETS_URL.'images/optin_box/privacy.png" alt="privacy" width="16" height="15" /> %1$s</p>') ?>
		<a class="<?php echo $close_class; ?>"><img src="<?php echo OP_ASSETS_URL; ?>images/optin_modal/close.png" /></a>
	    </div>
	</div>
</div>