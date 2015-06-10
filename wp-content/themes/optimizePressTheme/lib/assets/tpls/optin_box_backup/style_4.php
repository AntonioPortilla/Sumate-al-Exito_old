<div class="optin-box-4">
	<?php op_get_var_e($content,'headline','','<h2>%1$s</h2>') ?>
	<div class="optin-box-content">
		<?php op_get_var_e($content,'paragraph','') ?>
		<?php echo $form_open.$hidden_str ?>
	       	<?php op_get_var_e($fields,'email_field');
			echo implode('',$extra_fields) ?>
			<button type="submit">
				<img src="<?php echo OP_ASSETS_URL ?>images/optin_box/button-gradient.png" alt="button-gradient" width="281" height="33" />
				<span><?php echo $submit_text ?></span>
			</button>
		</form>
		<?php op_get_var_e($content,'privacy','','<p class="privacy"><img src="'.OP_ASSETS_URL.'images/optin_box/privacy.png" alt="privacy" width="16" height="15" /> %1$s</p>') ?>
	</div>
</div>