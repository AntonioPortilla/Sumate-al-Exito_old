<div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar">
	<p class="op-micro-copy"><?php _e('Use this option if you want anyone trying to exit your page via the browser close buttons to be shown a message and redirected to a URL of your choosing',OP_SN) ?></p>
	<label for="<?php echo $fieldid ?>url" class="form-title"><?php _e('Redirect to URL') ?></label>
	<p class="op-micro-copy"><?php _e('Enter the URL that your users browser should be redirected to on exit.') ?></p>
    <input type="text" name="<?php echo $fieldname ?>[url]" id="<?php echo $fieldid ?>url" value="<?php op_page_attr_e($section_name,'url') ?>" />

    <label for="<?php echo $fieldid ?>message" class="form-title"><?php _e('Redirect Browser Message',OP_SN) ?></label>
    <p class="op-micro-copy"><?php _e('Enter the message to be shown to the user in a browser pop when the user tries to exit. This would normally be a message advising if they want to close their browser or be redirected',OP_SN) ?></p>
    <textarea id="<?php echo $fieldid ?>message" name="<?php echo $fieldname ?>[message]"><?php echo str_replace('\&quot;', '&quot;', (op_page_attr($section_name,'message'))); ?></textarea>
</div>