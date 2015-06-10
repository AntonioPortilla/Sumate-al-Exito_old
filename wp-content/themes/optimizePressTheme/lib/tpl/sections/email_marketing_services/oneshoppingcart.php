<div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar cf">        
    <label for="op_sections_email_marketing_services_oneshoppingcart_enabled" class="form-title"><?php _e('1ShoppingCart.com connection', OP_SN); ?></label>
    <?php if (op_assets_provider_enabled('oneshoppingcart') === false): ?>
    <p class="op-micro-copy"><?php _e('1ShoppingCart is disabled.', OP_SN); ?> <a href="<?php echo admin_url('admin.php?action=' . OP_ONESHOPPINGCART_CONNECT_URL); ?>"><?php _e('Enable', OP_SN); ?></a></p>
	<?php else: ?>
	<p class="op-micro-copy"><?php _e('1ShoppingCart is enabled.', OP_SN); ?> <a href="<?php echo admin_url('admin.php?action=' . OP_ONESHOPPINGCART_CONNECT_URL); ?>&disconnect=1"><?php _e('Disable', OP_SN); ?></a></p>
	<?php endif; ?>
</div>