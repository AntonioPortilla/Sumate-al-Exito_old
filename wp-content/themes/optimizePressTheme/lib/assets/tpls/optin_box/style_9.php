<?php include 'style.inc.php'; ?>

<div id="<?php echo $id; ?>" class="optin-box optin-box-10"<?php echo $style_str; ?>>
	<div class="optin-box-content">
		<?php echo $form_open.$hidden_str ?>
		<label><?php _e('Email',OP_SN) ?></label>
		<div class="text-boxes">
		    <?php op_get_var_e($fields,'email_field'); ?>
		</div>
		<?php wp_nonce_field('op_optin', 'op_optin_nonce'); ?>
		<?php echo $submit_button ?>
		</form>
	</div>
	<?php op_get_var_e($content,'privacy','','<p class="privacy"><img src="'.OP_ASSETS_URL.'images/optin_box/privacy.png" alt="privacy" width="16" height="15" /> %1$s</p>') ?>
</div>