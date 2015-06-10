<div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar cf">
<?php if (op_get_option('gotowebinar_api_key') === false) : ?>
	<label for="op_sections_email_marketing_services_gotowebinar_api_key" class="form-title"><?php _e('GoToWebinar API key', OP_SN); ?></label>
    <p class="op-micro-copy"><?php _e('Copy GoToWebinar API key here.', OP_SN); ?></p>
    <?php op_text_field('op[sections][email_marketing_services][gotowebinar_api_key]', op_get_option('gotowebinar_api_key')); ?>
<?php else : ?>
    <label for="op_sections_email_marketing_services_gotowebinar_access_token" class="form-title"><?php _e('GoToWebinar API connection', OP_SN); ?></label>
    <?php if (op_get_option('gotowebinar_access_token') === false || op_get_option('gotowebinar_organizer_key') === false): ?>
    <p class="op-micro-copy">
    	<?php _e('GoToWebinar is disconnected.', OP_SN); ?> <a href="<?php echo admin_url('admin.php?action=' . OP_GOTOWEBINAR_AUTH_URL); ?>&authorize=1"><?php _e('Connect', OP_SN); ?></a>
    	<?php _e('or', OP_SN); ?> <a href="<?php echo admin_url('admin.php?action=' . OP_GOTOWEBINAR_AUTH_URL); ?>&clean=1"><?php _e('Clean API key', OP_SN); ?></a>
    </p>
	<?php else: ?>
	<p class="op-micro-copy"><?php _e('GoToWebinar is connected.', OP_SN); ?> <a href="<?php echo admin_url('admin.php?action=' . OP_GOTOWEBINAR_AUTH_URL); ?>&disconnect=1"><?php _e('Disconnect', OP_SN); ?></a></p>
	<?php endif; ?>
<?php endif; ?>
</div>