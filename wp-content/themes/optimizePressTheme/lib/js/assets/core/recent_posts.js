var op_asset_settings = (function($){
	return {
		help_vids: {
			step_1: {
				url: 'http://op2-inapp.s3.amazonaws.com/elements-recent-posts.mp4',
				width: '600',
				height: '341'
			},
			step_2: {
				url: 'http://op2-inapp.s3.amazonaws.com/elements-recent-posts.mp4',
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
				posts_num: {
					title: 'posts_num',
					type: 'select',
					values: {'1': '1', '2': '2', '3': '3', '4': '4', '5': '5', '6': '6', '7': '7', '8': '8', '9': '9', '10': '10'},
					default_value: '5'
				}
			}
		},
		insert_steps: {2:true},
		customInsert: function(attrs){
			var str = '',
				style = attrs.style,
				posts_num = attrs.posts_num;

			str = '[recent_posts style="' + style + '" posts_num="' + posts_num + '"][/recent_posts]';
			OP_AB.insert_content(str);
			$.fancybox.close();
		},
		customSettings: function(attrs,steps){
			attrs = attrs.attrs;
			var style = attrs.style || 1,
				posts_num = attrs.posts_num || 5;

			//Set the style
			OP_AB.set_selector_value('op_assets_core_recent_posts_style_container',style);

			//Set number of posts
			$('#op_assets_core_recent_posts_posts_num').find('option').each(function(){
				if ($(this).val()==attrs.posts_num) $(this).attr('selected', 'selected');
			});
		}
	};
}(opjq));