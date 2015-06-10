<div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar cf">
	<?php if($error = $this->error('op_sections_' . OptimizePress_Sl_Api::OPTION_API_KEY_PARAM)): ?>
    <span class="error"><?php echo $error ?></span>
    <?php endif ?>
    <label for="op_favicon" class="form-title"><?php _e('Enter API Key',OP_SN) ?></label>
    <p class="op-micro-copy"><?php _e('Login to the OptimizePress members area to find your API Keys inside the "Licensing" section',OP_SN) ?></p>
    <input type="text" name="op[sections][<?php echo OptimizePress_Sl_Api::OPTION_API_KEY_PARAM; ?>]" id="op_api_key" value="<?php echo op_sl_get_key(); ?>" />
    <div class="clear"></div>
</div>