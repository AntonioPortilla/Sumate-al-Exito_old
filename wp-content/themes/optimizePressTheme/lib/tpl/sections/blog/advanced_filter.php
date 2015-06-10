<div class="op-bsw-grey-panel-content op-bsw-grey-panel-no-sidebar cf">
	<p class="op-micro-copy"><?php _e('Check the filters that you want disabled on pages created with page builder. If some external plugin is hooked on "the_content" here is the place to disable it (for pages created with page builder).', OP_SN); ?></p>
	<label class="form-title"><?php _e('Disabled filters:', OP_SN); ?></label>
	<?php
		global $wp_filter;
		$wpOriginalFilters = array(
			'capital_P_dangit', 'do_shortcode', 'wptexturize', 'convert_smilies', 'convert_chars', 'wpautop', 'shortcode_unautop', 'prepend_attachment',
			'fixptag', 'run_shortcode', 'autoembed'
		);
		foreach ($wp_filter['the_content'] as $priority) {
			foreach ($priority as $name => $item) {
				if (is_string($item['function'])) {
					$label = $item['function'];
				} else {
					$label = $item['function'][1];
				}
				if (in_array($label, $wpOriginalFilters)) {
					continue;
					// $disabled = ' disabled="disabled"';
				} else {
					$disabled = '';
				}
	?>
			<label for="op_sections_advanced_filter_<?php echo $label; ?>" class="form-title"><?php op_checkbox_field('op[sections][advanced_filter][' . $label . ']', '1', checked(op_default_option('advanced_filter', $label), '1', false), $disabled); ?>&nbsp;<?php echo $label; ?></label>
	<?php
			}
		}
	?>
</div>