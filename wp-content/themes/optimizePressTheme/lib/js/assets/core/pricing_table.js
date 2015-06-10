var op_asset_settings = (function($){
    return {
        help_vids: {
            step_1: {
                url: 'http://op2-inapp.s3.amazonaws.com/elements-pricing-table.mp4',
                width: '600',
                height: '341'
            },
            step_2: {
                url: 'http://op2-inapp.s3.amazonaws.com/elements-pricing-table.mp4',
                width: '600',
                height: '341'
            }
        },
        attributes: {
            step_1: {
                style: {
                    type: 'style-selector',
                    folder: '',
                    addClass: 'op-disable-selected'
                }
            },
            step_2: {
                tabs: {
                    title: 'columns_count',
                    type: 'select',
                    valueRange: {start:2,finish:4,text_suffix:'columns'},
                    default_value: '',
                    multirow: {
                        link_prefix: 'column',
                        attributes: {
                            title: {
                                title: 'title',
                                type: 'input',
                                default_value: 'Column Title',
                                events: {
                                    change: function(){
                                        var multi = $(this).closest('.op-multirow'),
                                            cont = multi.parent(),
                                            idx = cont.find('.op-multirow').index(multi);
                                        cont.find('.op-multirow-tabs li a:eq('+idx+')').text($(this).val());
                                    },
                                    keyup: function(){
                                        $(this).trigger('change');
                                    }
                                },
                                trigger_events: 'change'
                            },
                            price: {
                                title: 'price',
                                type: 'input',
                                default_value: '0.00'
                            },
                            pricing_unit: {
                                title: 'pricing_unit',
                                type: 'input',
                                default_value: '$'
                            },
                            pricing_variable: {
                                title: 'pricing_variable',
                                type: 'input',
                                default_value: ''
                            },
                            order_button_text: {
                                title: 'order_button_text',
                                type: 'input'
                            },
                            order_button_url: {
                                title: 'order_button_url',
                                type: 'input'
                            },
                            package_description: {
                                title: 'package_description',
                                type: 'wysiwyg'
                            },
                            most_popular: {
                                title: 'most_popular',
                                type: 'checkbox',
                                events: {
                                    change: function(){
                                        var input = $(this).parent().parent().next();
                                        if ($(this).is(':checked')) input.show(); else input.hide();
                                    }
                                }
                            },
                            most_popular_text: {
                                title: 'most_popular_text',
                                type: 'input'
                            },
                            items: {
                                title: 'package_features',
                                type: 'multirow',
                                multirow: {
                                    remove_row: 'after',
                                    attributes: {
                                        feature_title: {
                                            title: 'feature_title',
                                            type: 'input'
                                        }
                                    }
                                }
                            },
                        },
                        onAdd: function(){
                            //Add event to click handler for button that will remove the op-multirow class
                            //This is necessary as the class breaks the column selector
                            $(this).find('.field-items .new-row').click(function(){
                                $(this).parent().prev().find('div').each(function(){
                                    if ($(this).hasClass('op-multirow')) $(this).removeClass('op-multirow').addClass('op-feature-title-row');
                                });
                            });

                            //Trigger the change event to update the column name in the column selector
                            $(this).find('.op-multirow:last :input[type="text"]').trigger('change');

                            //Hide the most popular text field and set checkbox to unchecked by default
                            $(this).find('.field-most_popular_text').hide().prev().find('.checkbox-container input').prop('checked', false);
                        }
                    }
                }
            }
        },
        insert_steps: {2:true},
        customInsert: function(attrs){
            var str = '', total = attrs.tabs.total, style = (attrs.style || 1);

            for(var i=0;i<total;i++){
                var title = '', price = '', pricing_unit = '', pricing_variable = '', most_popular = 'N', most_popular_text = '',
                    order_button_text = '', order_button_url = '', package_description = '', items = '';

                if(typeof attrs.tabs.rows[i] != 'undefined'){
                    title = encodeURIComponent(attrs.tabs.rows[i].title || '');
                    price = encodeURIComponent(attrs.tabs.rows[i].price || '');
                    pricing_unit = encodeURIComponent(attrs.tabs.rows[i].pricing_unit || '');
                    pricing_variable = encodeURIComponent(attrs.tabs.rows[i].pricing_variable || '');
                    most_popular = attrs.tabs.rows[i].most_popular;
                    most_popular_text = encodeURIComponent(attrs.tabs.rows[i].most_popular_text);
                    order_button_text = encodeURIComponent(attrs.tabs.rows[i].order_button_text || '');
                    order_button_url = encodeURIComponent(attrs.tabs.rows[i].order_button_url || '');
                    package_description = encodeURIComponent(attrs.tabs.rows[i].package_description || '');

                    $.each(attrs.tabs.rows[i].items, function(index, val){
                        items += '<li>' + encodeURIComponent(val.feature_title) +'</li>';
                    });
                }
                str += '[tab style="' + style + '" total="' + total + '" title="'+title.replace( /"/ig,"'")+'" price="' + price + '" pricing_unit="' + pricing_unit + '" pricing_variable="' + pricing_variable + '" most_popular="' + most_popular + '" most_popular_text="' + most_popular_text + '" order_button_text="' + order_button_text + '" order_button_url="' + order_button_url + '" package_description="' + package_description + '" items="' + items + '"][/tab]';
            };
            str = '[pricing_table style="' + style + '"]'+str+'[/pricing_table]';
            OP_AB.insert_content(str);
            $.fancybox.close();
        },
        customSettings: function(attrs,steps){
            var style = attrs.attrs.style, tab = attrs.tab || [], container = steps[1].find('.field-id-op_assets_core_pricing_table_tabs-multirow-container');

            //Set the style
            OP_AB.set_selector_value('op_assets_core_pricing_table_style_container',(style || ''));

            //Set the proper number of columns
            $('#op_assets_core_pricing_table_tabs option[value=' + tab.length + ']').attr('selected', 'selected').parent('select').trigger('change');

            container = container.find('> div');

            //Iterate between the columns and set the proper settings
            $.each(tab,function(i,v){
                var tmp = container.filter(':eq('+i+')');
                var id = tmp.find('input[name$="_title"]')
                                .val(op_decodeURIComponent(v.attrs.title))
                                .trigger('change')
                            .end().find('textarea')
                                .val(OP_AB.unautop(op_decodeURIComponent(v.attrs.content)))
                                .trigger('change')
                                .attr('id');
                var v = v.attrs;

                //Get the elements for the default settings
                var $title = tmp.find('.field-title input');
                var $price = tmp.find('.field-price input');
                var $pricing_unit = tmp.find('.field-pricing_unit input');
                var $pricing_variable = tmp.find('.field-pricing_variable input');
                var $order_button_text = tmp.find('.field-order_button_text input');
                var $order_button_url = tmp.find('.field-order_button_text').next().find('input');
                var $package_description = tmp.find('.field-package_description');
                var $most_popular = tmp.find('.field-most_popular .checkbox-container input');
                var $most_popular_text = tmp.find('.field-most_popular_text input');
                var $items = tmp.find('.field-items').prev();

                //Set the defaults
                $price.val(op_decodeURIComponent(v.price));
                $pricing_unit.val($('<div/>').html(op_decodeURIComponent(v.pricing_unit)).text());
                $pricing_variable.val(op_decodeURIComponent(v.pricing_variable));
                $order_button_text.val(op_decodeURIComponent(v.order_button_text));
                $order_button_url.val(op_decodeURIComponent(v.order_button_url));

                //Set most popular defaults
                $most_popular_text.val(op_decodeURIComponent(v.most_popular_text));
                var isMostPopularChecked = (v.most_popular=='Y' ? true : false);
                if (isMostPopularChecked) $most_popular_text.parent().show();
                $most_popular.prop('checked', isMostPopularChecked);

                //Set WYSIWYG content
                var desc = ($package_description.find('.op-wysiwyg .wp-editor-wrap').attr('id') || '');
                if (desc!='' && desc!='undefined'){
                    desc = desc.replace('wp-', '');
                    desc = desc.replace('-wrap', '');
                    switchEditors.go(desc,'tmce');
                    var ed = tinyMCE.get(desc);
                    if (ed) ed.setContent(op_wpautop(op_decodeURIComponent(v.package_description)),{ no_events: true });
                }

                //Iterate through all the items in the features list and add defaults
                ctr = 1;
                var items = $('<ul>' + v.items + '</ul>').find('li').each(function(){
                    var id = ($items.attr('class') || '');
                    if (desc!='' && desc!='undefined'){
                        id = id.replace(' cf', '');
                        id = id.replace(/multirow-container/ig, '');
                        id = id.replace('items-', 'items');
                        id = id.replace(/ /g, '');
                        var text = op_decodeURIComponent($(this).text());
                        var html = '<div class="op-feature-title-row cf pricing-table-row"><div class="field-row field-input ' + id + '_' + ctr + '_feature_title field-feature_title cf"><label for="' + id + '_' + ctr + '_feature_title">Feature Title</label><input type="text" id="' + id + '_' + ctr + '_feature_title" name="' + id + '_' + ctr + '_feature_title" value="' + text + '"></div><a class="remove-row" href="#"><img alt="Remove Row" src="' + OptimizePress.imgurl + 'remove-row.png"></a></div>';
                        $items.append(html);
                        ctr++;
                    }
                });
            });
        }
    };
})(opjq);