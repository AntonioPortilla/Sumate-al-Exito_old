<div class="optin-box-7">
	<?php op_get_var_e($content,'headline','','<h2>%1$s</h2>') ?>
    <div class="optin-box-content">
    	<?php op_get_var_e($content,'paragraph','') ?>
        <?php echo $form_open.$hidden_str ?>
        	<div class="text-boxes">
				<?php 
				op_get_var_e($fields,'name_field');
                op_get_var_e($fields,'email_field');
                echo implode('',$extra_fields)
                ?>
            </div>
            <button type="submit"><span><?php echo $submit_text ?></span></button>
        </form>
        <?php op_get_var_e($content,'privacy','','<p class="privacy"><img src="'.OP_ASSETS_URL.'images/optin_box/privacy.png" alt="privacy" width="16" height="15" /> %1$s</p>') ?>
    </div>
</div>