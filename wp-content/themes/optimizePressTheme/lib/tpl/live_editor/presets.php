<form id="le-presets-dialog">
	<h1><?php _e('Presets',OP_SN) ?></h1>
    <div class="op-lightbox-content">
    	<div class="op-actual-lightbox-content cf op-type-switcher-container">
        	<select name="preset_type" id="preset_type" class="op-type-switcher">
            	<option value="new"><?php _e('Create New',OP_SN) ?></option>
            	<option value="overwrite"><?php _e('Overwrite',OP_SN) ?></option>
            </select>
            <p class="op-micro-copy"><?php _e('Please note this will also save the current page.',OP_SN) ?></p>
            <div class="op-type op-type-new">
            	<label for="preset_new"><?php _e('Title:',OP_SN) ?></label>
                <input type="text" name="preset_new" value="" id="preset_new" />
            </div>
            <div class="op-type op-type-overwrite">
            	<?php echo $preset_select ?>
            </div>
        </div>
    </div>
    <div class="op-insert-button cf">
    	<button type="submit" class="editor-button"><span><?php _e('Save',OP_SN) ?></span></button>
    </div>
</form>