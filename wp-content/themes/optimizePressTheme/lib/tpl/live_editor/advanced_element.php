<div id="op-le-advanced">
    <h1><?php _e('Advanced element options',OP_SN) ?></h1>
    <div class="op-lightbox-content">
    	<label><?php _e('Code before element', OP_SN);?></label>
    	<p class="op-micro-copy"><?php _e('Enter shortcode or similar which will be rendered before the element', OP_SN);?></p>
    	<textarea name="op_advanced_code_before" id="op_advanced_code_before"></textarea>
    	
    	<label><?php _e('Code after element', OP_SN);?></label>
    	<p class="op-micro-copy"><?php _e('Enter shortcode or similar which will be rendered after the element', OP_SN);?></p>
    	<textarea name="op_advanced_code_after" id="op_advanced_code_after"></textarea>
    	
    	<label><?php _e('Hide element for mobile phones?', OP_SN);?></label>
        <input type="checkbox" name="op_hide_phones" />
        
        <label><?php _e('Hide element for tablets?', OP_SN);?></label>
        <input type="checkbox" name="op_hide_tablets" />
    	
        <label><?php _e('Element class', OP_SN);?></label>
        <input type="text" name="op_advanced_class" id="op_advanced_class" />
        
        <label><?php _e('Delayed fade-in', OP_SN);?></label>
        <p class="op-micro-copy"><?php _e('Enter number of seconds after which the element will fade in', OP_SN);?></p>
        <input type="text" name="op_advanced_fadein" id="op_advanced_fadein" />
    </div>
    <div class="op-insert-button cf">
            <button type="button" id="op-le-advanced-update" class="editor-button"><?php _e('Update',OP_SN) ?></button>
    </div>
</div>