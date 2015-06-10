<div class="optin-box-9">
	<div class="optin-box-content">
    	<?php echo $form_open.$hidden_str ?>
        	<div class="text-boxes">
            	<div class="text-box"><?php op_get_var_e($fields,'email_field'); ?></div>
            </div>
            <button type="submit"><span><?php echo $submit_text ?></span></button>
        </form>
        <?php op_get_var_e($content,'privacy','','<p class="privacy"><img src="'.OP_ASSETS_URL.'images/optin_box/privacy.png" alt="privacy" width="16" height="15" /> %1$s</p>') ?>
    </div>
</div>