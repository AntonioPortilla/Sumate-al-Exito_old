<div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar">
	<label for="<?php echo $fieldid ?>show_on" class="form-title"><?php _e('When to Show Lightbox',OP_SN) ?></label>
	<p class="op-micro-copy"><?php _e('Choose when to show the Lightbox Popup on your page',OP_SN) ?></p>
    <select name="<?php echo $fieldname ?>[show_on]" id="<?php echo $fieldid ?>show_on">
    <?php
	$val = op_page_attr($section_name,'show_on');
	$opts = array('load'=>__('Show on load',OP_SN), 'exit'=>__('Show on exit',OP_SN));
	foreach($opts as $name => $title){
		echo '<option value="'.$name.'"'.($val == $name ? ' selected="selected"' : '').'>'.$title.'</option>';
	}
	?>
    </select>
    
    <div class="op-type-switcher-container">
        <label for="<?php echo $fieldid ?>type" class="form-title"><?php _e('Lightbox Pop Content',OP_SN) ?></label>
        <p class="op-micro-copy"><?php _e('Use the options below to customize the content of your Lightbox Popup
',OP_SN) ?></p>
        <select name="<?php echo $fieldname ?>[type]" id="<?php echo $fieldid ?>type" class="op-type-switcher">
        <?php
        $val = op_page_attr($section_name,'type');
        $opts = array('optin'=>__('Opt-in Form',OP_SN), 'html'=>__('HTML Content',OP_SN));
        foreach($opts as $name => $title){
            echo '<option value="'.$name.'"'.($val == $name ? ' selected="selected"' : '').'>'.$title.'</option>';
        }
        ?>
        </select><br />
        <div class="op-type op-type-optin"><?php
        op_mod('signup_form')->display_settings(array($section_name,'optin_form'),array('disable'=>'color_scheme|on_off_switch'));
		?></div>
        <div class="op-type op-type-html"><br /><?php
        op_mod('content_fields')->display_settings(array($section_name,'html_content'),array(
							'fields' => array(
								'content' => array(
									'name' => __('HTML Content', OP_SN),
									'type' => 'textarea',
									'help' => __('Enter HTML content to show in your lightbox.', OP_SN),
								)
							)));
		?></div>
    </div>
    <br />
    <div id="popdom-promote-box">
    	<p><?php printf(__('For more advanced Popup designs, split testing and many more features, we recommend Popup Domination. <a href="%s" target="_blank">Click here</a>',OP_SN),'http://gurucb.popdom.hop.clickbank.net/') ?></p>
    </div>
    
</div>