<?php $conf = op_get_var($config,'page_setup') ?>
<div class="gateway_on">
    <label class="form-title"><img src="<?php echo OP_IMG; ?>page-new.png" class="icon" /> <?php _e('Landing Page / Entry Page',OP_SN) ?></label>
    <p class="op-micro-copy"><?php _e('Select the landing page for this stage of your launch funnel. The user will be redirected here',OP_SN) ?></p>
    <select name="op[funnel_pages]<?php echo $field_name ?>[page_setup][landing_page]<?php echo $field_ext ?>" class="landing_page">
    <?php echo $landing_select ?>
    </select>
    <?php //echo $add_page_link ?>
</div>
<p class="op-bsw-notify cf gateway_off"><span><?php _e('To redirect to a landing page you must turn on the Gateway Key. This will activate redirection in your funnel and enable the landing page options',OP_SN) ?></span></p>
<label class="form-title launch-funnel-title"><img src="<?php echo OP_IMG; ?>blackboard.png" class="icon" />  <?php _e('Value Page / Funnel Page',OP_SN) ?></label>
<p class="op-micro-copy"><?php _e('This page contains your launch content, video or training to add value as part of the launch process',OP_SN) ?></p>
<select name="op[funnel_pages]<?php echo $field_name ?>[page_setup][value_page]<?php echo $field_ext ?>" class="value_page">
<?php echo $value_select ?>
</select>
<?php //echo $add_page_link ?>

<label class="form-title"><img src="<?php echo OP_IMG; ?>page-disabled.png" class="icon" />  <?php _e('Page URL',OP_SN) ?></label>
<p class="op-micro-copy gateway_on"><?php _e('Use this link if you want to send visitors to the landing page unless they have already had access',OP_SN) ?></p>
<p class="op-micro-copy gateway_off"><?php _e('Use this link to send visitors to your landing page unless they have already had access or the Gateway key is turned off',OP_SN) ?></p>
<input type="text" name="op[funnel_pages]<?php echo $field_name ?>[page_setup][page_url]<?php echo $field_ext ?>" class="value_page_url" />
<div class="gateway_on">
    <label class="form-title"><img src="<?php echo OP_IMG; ?>page-access.png" class="icon" />  <?php _e('Page Access URL',OP_SN) ?></label>
    <p class="op-micro-copy"><?php _e('If you want to give instant access to your content use this link (this will cookie your visitors) - use this in your autoresponder sequences.',OP_SN) ?></p>
    <input type="text" name="op[funnel_pages]<?php echo $field_name ?>[page_setup][page_access_url]<?php echo $field_ext ?>" class="value_page_access_url" />
</div>