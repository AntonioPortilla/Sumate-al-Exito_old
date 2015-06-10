<div id="content_layout_keep_options" class="cf">
<?php
$options = array(
	'header_layout' => __('Keep Existing Header',OP_SN),
	'feature_area' => __('Keep Existing Feature Area',OP_SN),
	'footer_area' => __('Keep Existing Footer',OP_SN),
	'content' => __('Keep Existing Content',OP_SN),
	'scripts' => __('Keep Existing Other Scripts',OP_SN),
	'typography' => __('Keep Existing Typography',OP_SN),
	'color_scheme' => __('Keep Existing Colour Scheme',OP_SN),
);
foreach($options as $name => $title){
	echo '
	<div class="checkbox-row">
		<div class="checkbox-container">
			<input type="checkbox" name="keep_options[]" id="keep_options_'.$name.'" value="'.$name.'" />
			<label for="keep_options_'.$name.'">'.$title.'</label>
		</div>
	</div>';
}
?>
</div>