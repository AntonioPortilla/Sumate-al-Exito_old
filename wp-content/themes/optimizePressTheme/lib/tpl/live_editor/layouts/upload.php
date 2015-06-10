<?php echo op_tpl('admin_header') ?> 
<label><?php _e('Upload a content template in .zip format',OP_SN) ?></label>
<?php 
if(isset($error))
	echo '<p class="error">'.$error.'</p>';
?>
<p class="install-help"><?php _e('If you have a content template in a .zip format, you may install it by uploading it here.',OP_SN) ?></p>
<form method="post" enctype="multipart/form-data" action="<?php echo menu_page_url(OP_SN.'-page-builder',false) ?>&amp;section=content_upload">
	<?php wp_nonce_field( 'op_content_layout_upload' ) ?>
    <label class="screen-reader-text" for="pluginzip"><?php _e('Content template zip file',OP_SN) ?></label>
	<input type="file" id="pluginzip" name="pluginzip" />
    <div class="cf"></div>
	<br /><input type="submit" class="button" value="<?php _e('Install Now',OP_SN) ?>" />
</form>
<?php echo op_tpl('admin_footer') ?> 