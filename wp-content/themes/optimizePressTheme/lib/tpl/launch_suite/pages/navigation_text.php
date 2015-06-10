<?php $conf = op_get_var($config,'navigation') ?><label class="form-title"><?php _e('Navigation Link Text',OP_SN) ?></label>
<p class="op-micro-copy"><?php _e('Enter the text for the link which appear in your launch navigation when this page is active (linked)',OP_SN) ?></p>
<input type="text" class="active-link-text" name="op[funnel_pages]<?php echo $field_name ?>[navigation][active_link_text]<?php echo $field_ext ?>" id="op_funnel_pages_<?php echo str_replace(array('[', ']'), '', $field_name)?>_navigation_active_link_text_<?php echo str_replace(array('[', ']'), '', $field_ext)?>" value="<?php echo stripslashes(op_attr(op_get_var($conf,'active_link_text'))); ?>" />

<label class="form-title"><?php _e('Coming Soon Navigation Link Text',OP_SN) ?></label>
<p class="op-micro-copy"><?php _e('Enter the text for the link which appear in your launch navigation when this page is inactive (coming soon)',OP_SN) ?></p>
<input type="text" name="op[funnel_pages]<?php echo $field_name ?>[navigation][inactive_link_text]<?php echo $field_ext ?>" value="<?php echo stripslashes(op_attr(op_get_var($conf,'inactive_link_text'))); ?>" />