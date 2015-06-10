<?php echo $this->load_tpl('page_builder/header');?>

<h2><?php _e('Name Your Page',OP_SN) ?></h2>
<p><?php _e('Enter the title for your page here - this will be used in the Wordpress interface',OP_SN) ?></p>
<input type="text" name="op[page][name]" id="op_page_name" value="<?php echo op_attr($page_title) ?>" />

<h2><?php _e('Page URL/Permalink',OP_SN) ?></h2>
<p><?php printf(__('Customize your page permalink below. Please ensure your permalinks are set to %1$s in your %2$s.',OP_SN),'/%postname%/','<a href="options-permalink.php" target="_blank">'.__('Wordpress Permalinks Settings',OP_SN).'</a>') ?>
<?php
if($error = $this->error('page_name')){
	echo '<br><br><span class="error">'.$error.'</span>';
} elseif($permalinks_disabled){
	echo '<br><br><span class="error">'.__('You must enable permalinks in order for this to work.',OP_SN).'</span>';
}
?></p>

<input type="text" name="op[page][slug]" id="op_page_slug" value="<?php echo op_attr($page_name) ?>" />
<div id="op_ajax_checker">
    <a href="#check" class="check-availability"><?php _e('Check availability', OP_SN) ?></a>
    <a href="#cancel" class="op-hidden check-availability-cancel"><?php _e('Cancel', OP_SN) ?></a>
    <img class="op-bsw-waiting" src="images/wpspin_light.gif" alt="" style="position: relative; top: -5px;" />
    <!--<span class="success op-hidden"><?php _e('Valid Page URL',OP_SN) ?></span>
    <span class="error op-hidden"><?php _e('Page URL already in use',OP_SN) ?></span>-->
    <span class="success op-hidden" style="position: relative; top: -10px; margin-left: 5px; color: green; font-weight: bold;">&#x2713;&nbsp; <?php _e('Available', OP_SN); ?></span>
    <span class="error op-hidden" style="position: relative; top: -10px; margin-left: 5px; color: red; font-weight: bold;">&#x2717;&nbsp; <?php _e('Unavailable', OP_SN); ?></span>
</div>

<div class="cf"></div>

<h2><?php _e('Upload a Page Thumbnail (optional)',OP_SN) ?></h2>
<p><?php _e('Upload a thumbnail for your page (Thumbnails should be 300x170 pixels)',OP_SN) ?></p>
<?php if (!isset($page_thumbnail_preset)) $page_thumbnail_preset = ''; ?>
<?php if (!isset($page_thumbnail)) $page_thumbnail = ''; ?>
<?php op_thumb_gallery('op[page][thumbnail_preset]', $page_thumbnail_preset, 'page_thumbs') ?>
<?php op_upload_field('op[page][thumbnail]',$page_thumbnail) ?>

<h2><?php _e('Select Page Presets',OP_SN) ?></h2>
<p><?php _e('Use the options below to create a blank page or use one of your pre-defined presets or content templates',OP_SN) ?></p>
<div id="preset-option">
<?php echo $this->load_tpl('generic/img_radio_selector',array('previews'=>$preset_options,'classextra'=>'preset-type-select')); ?>
</div>
<?php if(isset($presets)): ?>
<div id="preset-option-preset" class="preset-option op-hidden">
	<h2><?php _e('Select a page preset',OP_SN) ?></h2>
    <p><?php _e('If you have previously created a page and saved the PageBuilder settings as a preset you can load that preset here.',OP_SN) ?></p>
    <?php op_show_warning(__('Using a saved Preset will load a complete set of options for your Page including all PageBuilder settings, and override any current settings for your page. When you click next on this page you will be loaded straight into the LiveEditor to edit your page',OP_SN),true) ?>
    <?php echo $presets ?>
</div>
<?php endif; ?>
<div id="preset-option-content_layout" class="preset-option op-hidden">
	<h2><?php _e('Select a Pre-Made Content Template',OP_SN) ?></h2>
    <p><?php _e('Select the layout you want from below and when your page is loaded in the LiveEditor the layout and content will be ready for you to customize.',OP_SN) ?></p>
    <?php op_show_warning(__('Please note you will still need to add your header in the PageBuilder process and you are free to tweak any of the settings (we do not recommend changing the template settings unless you&rsquo;re familiar with these options)',OP_SN),true) ?>
    <br /><div class="op-hidden" id="upload_new_layout_container">
	    <a href="#load" id="view_layouts"><?php _e('View Uploaded Templates',OP_SN) ?></a>
        <iframe src="<?php menu_page_url(OP_SN.'-page-builder') ?>&amp;section=content_upload" width="700" height="400"></iframe>
    </div>
    <div id="content_layout_container">
	    <a href="#upload" id="upload_new_layout"><?php _e('Upload Content Template',OP_SN) ?></a>
        <div id="content_layout_container_list">
		<?php
		if(isset($content_layouts)):
        echo $content_layouts;
		endif;
        ?>
    	</div>
        <?php echo (defined('OP_PAGEBUILDER_ID')?op_tpl('live_editor/layouts/keep_options'):'') ?>
    </div>
</div>
<?php echo $this->load_tpl('page_builder/footer'); ?>