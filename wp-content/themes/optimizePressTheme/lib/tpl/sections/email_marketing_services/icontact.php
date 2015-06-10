<div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar cf">        
    <label for="op_sections_email_marketing_services_icontact_username" class="form-title"><?php _e('iContact Username', OP_SN); ?></label>
    <?php op_text_field('op[sections][email_marketing_services][icontact_username]', op_get_option('icontact_username')); ?>
    <label for="op_sections_email_marketing_services_icontact_password" class="form-title"><?php _e('API password', OP_SN); ?></label>
    <?php op_text_field('op[sections][email_marketing_services][icontact_password]', op_get_option('icontact_password')); ?>
    <p class="op-micro-copy"><a href="https://app.icontact.com/icp/core/externallogin?sAppId=<?php echo OP_ICONTACT_APP_ID; ?>" target="_blank"><?php _e('Get API password', OP_SN); ?></a></p>
    <p class="op-note"><em><?php _e('Note: You need to allow external program to access your account and generate password for the API access.', OP_SN); ?></em></p>
</div>