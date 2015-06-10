var op_asset_settings = (function($){
	return {
		help_vids: {
			step_1: {
				url: 'http://op2-inapp.s3.amazonaws.com/elements-headline.mp4',
				width: '600',
				height: '341'
			},
			step_2: {
				url: 'http://op2-inapp.s3.amazonaws.com/elements-headline.mp4',
				width: '600',
				height: '341'
			}
		},
		attributes: {
			step_1: {
				style: {
					type: 'style-selector',
					folder: 'previews',
					addClass: 'op-disable-selected'
				}
			},
			step_2: {
				content: {
					title: 'text',
					type: 'wysiwyg',
					default_value: 'Your Header',
					format: 'br'
				},
				font: {
					title: 'font_settings',
					type: 'font'
				},
				align: {
					title: 'alignment',
					type: 'radio',
					values: {'left':'left','center':'center','right':'right'},
					default_value: 'center'
				},
				line_height: {
					title: 'line_height',
					suffix: ''
				},
				highlight: {
					title: 'highlight',
					type: 'color'
				},
				top_margin: {
					title: 'top_margin',
					help: 'text_block_top_margin_help',
					suffix: ''
				},
				bottom_margin: {
					title: 'bottom_margin',
					help: 'text_block_bottom_margin_help',
					suffix: ''
				}
			}
		},
		insert_steps: {2:true}
	}
}(opjq));