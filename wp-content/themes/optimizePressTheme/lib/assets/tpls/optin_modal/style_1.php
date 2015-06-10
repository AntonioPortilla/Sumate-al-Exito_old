<div class="optin-modal-container">
    <?php include 'style.inc.php'; ?>
    
    <a href="#" class="optin-modal-link"><?php echo $link_title; ?></a>
    
    <div id="<?php echo $id; ?>" class="optin-modal optin-modal-style-1"<?php echo $style_str; ?>>
            <?php
                $headline = op_get_var($content,'headline','','<h2>%1$s</h2>');
                echo !empty($headline) ? $headline : '';
            ?>
            <form<?php echo $form_attrs; ?>>
                    <?php echo $hidden_str ?>
                    <label><span>Step 1</span>Enter Email Address</label>
                    <?php op_get_var_e($fields,'email_field'); ?>
                    <label><span>Step 2</span>Click The Button</label>
                    <?php echo $submit_button; ?>
            </form>
            <?php op_get_var_e($content,'privacy','','<p class="privacy"><span>Privacy Policy: </span>%1$s</p>') ?>
            <a class="<?php echo $close_class; ?>"><img src="<?php echo OP_ASSETS_URL; ?>images/optin_modal/close.png" /></a>
    </div>
</div>