var op_asset_settings = (function($){
	return {
		help_vids: {
			step_1: {
				url: 'http://op2-inapp.s3.amazonaws.com/elements-order-box.mp4',
				width: '600',
				height: '341'
			},
			step_2: {
				url: 'http://op2-inapp.s3.amazonaws.com/elements-order-box.mp4',
				width: '600',
				height: '341'
			},
			step_3: {
				url: 'http://op2-inapp.s3.amazonaws.com/elements-order-box.mp4',
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
				title_1: {
					title: 'title',
					type: 'image-selector',
					folder: 'titles_1',
					showOn: {field:'step_1.style',value:'1'},
					attr: 'title'
				},
				header_1: {
					title: 'header',
					type: 'image-selector',
					folder: 'headers_1',
					showOn: {field:'step_1.style',value:'1'},
					attr: 'header'
				},
				title_2: {
					title: 'title',
					type: 'image-selector',
					folder: 'titles_2',
					showOn: {field:'step_1.style',value:'2'},
					attr: 'title'
				},
				title_3: {
					title: 'title',
					showOn: {field:'step_1.style',value:'3'},
					attr: 'title'
				},
				content: {
					title: 'content',
					type: 'wysiwyg',
					addClass: 'op-hidden-in-edit'
				}
			},
			step_3: {
				microcopy: {
					text: 'order_box_advanced1',
					type: 'microcopy'
				},
				microcopy2: {
					text: 'advanced_warning_2',
					type: 'microcopy',
					addClass: 'warning'
				},
				font: {
					title: 'order_box_font_settings',
					type: 'font'
				},
				width: {
					title: 'width',
					type: 'input',
					default_value: ''
				},
				microcopy_align: {
					text: 'width_needed_for_alignment_to_work',
					type: 'microcopy'
				},
				alignment: {
					title: 'alignment',
					type: 'select',
					values: {'left': 'left', 'center': 'center', 'right': 'right'},
					default_value: 'center'
				}
			}
		},
		insert_steps: {2:{next:'advanced_options'},3:true},
		customSettings: function(attrs,steps){
			attrs = attrs.attrs || {};
			var style = attrs.style || '1';
			OP_AB.set_font_settings('font',attrs,'op_assets_core_order_box_font');
			OP_AB.set_selector_value('op_assets_core_order_box_style_container',style);
			if(style == 1){
				OP_AB.set_selector_value('op_assets_core_order_box_title_1_container',attrs.title || '');
				OP_AB.set_selector_value('op_assets_core_order_box_header_1_container',attrs.header || '');
			} else if(style == 2){
				OP_AB.set_selector_value('op_assets_core_order_box_title_2_container',attrs.title || '');
			} else if(style == 3){
				$('#op_assets_core_order_box_title_3').val(attrs.title || '');
			}

			tinyMCE.activeEditor.setContent(attrs.content);
			$('#op_assets_core_order_box_width').val(OP_AB.unautop(attrs.width || ''));
			$('#op_assets_core_order_box_alignment').find('option').each(function(){
				if ($(this).val()==attrs.alignment) $(this).attr('selected', 'selected');
			});
		}
	};
}(opjq));