;(function($){
    var loaded = false, farbtastic, current_picker, font_picker_html, cur_upload = null, disable_set_val = false;
    $(document).ready(function(){
        //$('.op-type-switcher:visible').live('change',function(){
        $('body').on('change', '.op-type-switcher:visible', function(){
            $(this).closest('.op-type-switcher-container').find('.op-type:first').hide().siblings('.op-type').hide().end().end().find('.op-type-'+$(this).val()).show().find('.op-bsw-grey-panel-content:not(:visible)').show().end().find('.op-type-switcher:visible').trigger('change');
        }).trigger('change');

        //$('.op-notify').live('click', function(e){
        $('body').on('click', '.op-notify', function(e){
            var $target = $(e.target);
            var date;
            var expires;
            var days = 2; //number of days before cookie expires

            if (!$target.is('a')) {
                $(this).fadeThenSlideToggle();
                if ($target.hasClass('op-notify-close') && $(this).hasClass('js-remember-choice')) {
                    //Write the notification into the cookie, so it can stay permanently hidden.
                    date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toGMTString();
                    document.cookie = $(this).attr('id') + "=" + 'notification_hidden' + expires + "; path=/";
                }
                e.preventDefault();
            }
        });

        $('.default-val-link').click(function(e){
            var hash = $(this).attr('href').split('#')[1];
            $('#'+hash).val($('#'+hash+'_default').val());
            e.preventDefault();
        });

        $(document).mousedown( function(e) {
            $('#op-color-picker-1').hide().css({'top':'auto','left':'auto'});
            if($(e.target).closest('.select-font').length == 0){
                $('.font-dropdown').hide();
            }
            if($(e.target).closest('.op-asset-dropdown-list').length == 0){
                $('.op-asset-dropdown-list:not(.op-disable-selected .op-asset-dropdown-list)').hide();
            }
        });

        $('.img-radio-selector.menu-position :radio').change(function(){
            var $t = $(this), func = 'hide', $panel_content = $t.closest('.op-bsw-grey-panel-content');
            if(typeof op_menu_link_colors != 'undefined' && typeof op_menu_link_colors[$t.val()] != 'undefined'){
                func = 'show';
            }
            $panel_content.find('.layout-settings').hide();
            $panel_content.find('#layout-settings-' + $t.val()).show();
        });

        $('a.fancybox').fancybox({
            beforeShow: function(){
                //This is necessary in order to hide the parent fancybox scrollbars and close button
                $('html').css({
                    overflow: 'hidden',
                    height: '100%'
                });
                $(window.parent.document).find('.fancybox-close').css({ display: 'none' });
            },
            afterClose: function(){
                //This is necessary in order to hide the parent fancybox scrollbars and close button
                $('html').css({
                    overflow: 'auto',
                    height: 'auto'
                });
                $(window.parent.document).find('.fancybox-close').css({ display: 'block' });
            }
        });
        init_footer_columns();
        init_hidden_panels();
        init_radio_selectors();
        init_color_schemes();
        init_tabs();
        init_multirow();
        init_blogenabler();
        init_layout_options();
        init_color_pickers();
        init_slider_pickers();
        init_font_pickers();
        init_upload_fields();
        init_selectors();
        init_help_vids();
        if($('form.op-bsw-settings,div.op-bsw-wizard').length > 0){
            $('textarea.wp-editor-area').each(function(){
                // For TinyMCE 3 value must be set before addControl, and for TinyMCE 4 after addEditor.
                if (tinymce.majorVersion > 3) {
                    tinymce.execCommand("mceAddEditor", true, $(this).attr('id'));
                    $(this).val(op_wpautop($(this).val()));
                } else {
                    $(this).val(op_wpautop($(this).val()));
                    tinymce.execCommand("mceAddControl", true, $(this).attr('id'));
                }
            });
        }

        loaded = true;

        $('.op-js-item-layout-delete').click(function(){
            var $this = $(this);
            $.ajax({
                type: 'POST',
                url: OptimizePress.ajaxurl,
                data: {'action': OptimizePress.SN+'-content-layout-delete', 'layout': $this.attr('data-id'), 'nonce': $this.attr('data-nonce')},
                success: function(response){
                    if (typeof response.success === 'boolean' && response.success === true) {
                        $this.parent().remove();
                    } else {
                        alert('Error occured!');
                    }
                },
                dataType: 'json'
            });
            return false;
        });

        // Dirty fix. HTML first selection of the tinymce is messed up, this ensures that we always end up with VISUAL
        if (typeof tinyMCE === 'object' && tinyMCE.majorVersion && tinyMCE.majorVersion > 3) {
            $(window).on('beforeunload', function () {
                $('.switch-tmce').trigger('click');
            });
        }

    });
    function init_help_vids(){
        $('a.op-help-vid').each(function(){
            var info = $(this).next(),
                width = parseInt(info.find('.help_vid_width').val(),10),
                height = parseInt(info.find('.help_vid_height').val(),10);
            $(this).fancybox({
                width: width,
                height: height
            });
        });
    };
    function init_footer_columns(){
        var full_width = 900, margin = 10;
        if(typeof op_footer_prefs != 'undefined'){
            full_width = op_footer_prefs.full_width || full_width;
            margin = op_footer_prefs.column_margin || margin;
        }
        $('.img-radio-selector.footer-columns :radio').change(function(){
            var $t = $(this), v = $t.val(), cont = $t.closest('.op-bsw-grey-panel-content').find('.column-container');
            if($t.is(':checked')){
                if(v > 1){
                    var width = Math.floor((full_width-((v-1)*margin))),
                        val = Math.floor(width/v);
                    cont.show();
                    cont = cont.find('.column-editor').find('div').hide().end();
                    v++;
                    for(var i=1;i<v;i++){
                        var el = cont.find('div.width-'+i).show().find(':input'),
                            ev = el.val();
                        if(ev == '' || loaded){
                            el.val(val);
                        }
                    }
                } else {
                    cont.css('display','none');
                }
            }
        });
    };
    function init_font_pickers(){
        var selectors = $('.font-selector');
        if(selectors.length > 0){
            var html = '<ul><li><a href="#">Theme Default</a></li></ul>', img, font;
            selectors.filter(':first').find('optgroup').each(function(){
                html += '<p>'+$(this).attr('label')+'</p><ul>';
                $(this).find('option').each(function(){
                    font = $(this).val();
                    img = font.replace(/\s+/g, '-').toLowerCase()+'.jpg';
                    html += '<li><a href="#"><img src="'+OptimizePress.imgurl+'fonts/'+img+'" alt="'+font+'" /></a></li>';
                });
                html += '</ul>';
            });
            html = '<div class="select-font"><a href="#" class="selected-font">Theme Default</a><div class="font-dropdown">'+html+'</div></div>';

            //$('.selected-font').live('click',function(e){
            $('body').on('click', '.selected-font', function(e){
                $(this).closest('.select-font').find('.font-dropdown').toggle();
                e.preventDefault();
            });

            //$('.select-font li a').live('click',function(e){
            $('body').on('click', '.select-font li a', function(e){
                var $t = $(this), img = $t.find('img'), val = '';
                if(img.length > 0){
                    val = img.attr('alt');
                }
                $t.closest('.select-font').find('.font-dropdown').hide().end().siblings('.font-selector').val(val).end().find('.selected-font').html($t.html());
                e.preventDefault();
            });

            selectors.each(function(){
                $(this).hide().after(html).siblings('.select-font').find('li a img[alt="'+$(this).val()+'"]').parent().trigger('click');
            }).change(function(){
                var $t = $(this), v = $t.val(), el = $t.siblings('.select-font');
                if(v == ''){
                    el = el.find('li a:first');
                } else {
                    el = el.find('li a img[alt="'+v+'"]');
                }
                el.trigger('click');
            });
        }

        //$('a[href$="#reset"]').live('click',function(e){
        $('body').on('click', 'a[href$="#reset"]', function(e){

            /**
             * .focus().blur() is a fix for weird behaviour that happens when
             * one color input is focused and reset next to
             * another one is clicked
             */
            $(this)
                .parent().find(':input')
                    .focus()
                    .blur()
                    .val('')
                    .trigger('change')
                .end().find('.op-asset-dropdown-list a:first')
                    .trigger('click');

            e.preventDefault();

        });
    };

    function init_selectors(){
        $('select.style-selector').next().find('a.selected-item').click(function(e){
            $(this).next().toggle();
            e.preventDefault();
        }).next().find('li a').click(function(e){
            var $t = $(this);
            e.preventDefault();
            disable_set_val = true;
            var el = $(this).closest('.op-asset-dropdown').find('li.selected').removeClass('selected').end().find('a.selected-item').html($t.html()).next().hide().end().end().prev().val($t.find('img').attr('alt'));
            $t.parent().addClass('selected');
            if(loaded){
                el.trigger('change');
            }
            disable_set_val = false;
        }).end().end().end().end().change(function(){
            if(disable_set_val === false){
                $(this).next().find('li img[alt="'+$(this).val()+'"]').trigger('click');
            }
        }).trigger('change');
    };

    /**
     * Initializes jQuery UI slider elements
     * @return {void}
     */
    function init_slider_pickers() {
        $('.slider-item').each(function(i, el) {
            var owner = $(el).closest('.submit-button-container').attr('id');
            $(el).slider({
                disabled: ($(el).attr('data-disabled') == 'true' ? true : false),
                min: parseInt($(el).attr('data-min')),
                max: parseInt($(el).attr('data-max')),
                value: parseInt($(el).attr('data-value')),
                stop: function (event, ui) {
                    var id;
                    if (typeof ui.handle != 'undefined') {
                        id = $(ui.handle).parent().attr('id');
                    } else {
                        id = ui.id;
                    }
                    $.event.trigger({type: 'update_button_preview', value: ui.value, id: id, owner: owner, element_type: 'slider', element: ui});
                },
                slide: function (event, ui) {

                    var id;
                    var $output;
                    var dataUnit = '';

                    if (typeof ui.handle != 'undefined') {
                        id = $(ui.handle).parent().attr('id');
                    } else {
                        id = ui.id;
                    }

                    if (owner) {
                        $output = $('#' + owner + ' #output_' + id);
                    } else {
                        $output = $('#output_' + id);
                    }

                    dataUnit = $output.attr('data-unit') || '';

                    if ($output.length > 0) {
                        $output.html('' + ui.value + dataUnit);
                    }

                    if (owner) {
                        $('#' + owner + ' #input_' + id).val(ui.value);
                    } else {
                        $('#input_' + id).val(ui.value);
                    }

                }
            });
        });
    }
    function init_color_pickers(){
        if($('#op-color-picker-1').length == 0){
            $('body').append('<div id="op-color-picker-1" />');
        }
        var el = $('#op-color-picker-1');
        var $t2 = $;
        farbtastic = $.farbtastic('#op-color-picker-1',pick_color);
        $('.color-picker-container :input').focus(function(){
            $t2 = $(this);
            $(this).data('cp_link').trigger('click');
        }).blur(function(){
            el.hide();
        }).change(function(){
            var $t = $(this),
                c = get_color($t.val());
            if (!$t) $t = $t2;
            $t.data('cp_link').css('background-color',(c == '#' ? 'transparent' : c));
            c = (c === '#') ? '' : c;
            if (current_picker && current_picker[1] && $(current_picker[1]).is(':visible')) {
                pick_color(c);
            }
            if(el.is(':visible') && c != '#'){
                farbtastic.setColor(c);
            }
        }).each(function(){
            var atag = $(this).siblings('a.pick-color');
            atag.data('input',$(this));
            $(this).data('cp_link',atag).data('cp_link').css('background-color',get_color($(this).val()));
        });
        $('a.pick-color').click(function(e){
            current_picker = [$(this),$(this).data('input')];
            pick_color(current_picker[1].val());
            el.position({
                of: current_picker[0],
                my: "left top",
                at: "left bottom"
            }).show();
            e.preventDefault();
        });
    };
    function pick_color(color){
        farbtastic.setColor(color);
        current_picker[1].val(color);
        current_picker[0].css('background-color',color);
        $.event.trigger({type: 'update_button_preview', id: current_picker[1].attr('id'), owner: current_picker[1].closest('.submit-button-container').attr('id'), value: color});
    };
    function get_color(val){
        return '#'+val.replace(/[^a-fA-F0-9]/, '');
    };
    function init_color_schemes(){
        if(typeof op_color_schemes !== 'undefined'){
            $('.section-color_scheme .color-schemes .img-radio-item input[type="radio"]').change(function(){
                var scheme = op_color_schemes[$(this).val()].colors || {};
                $.each(scheme,function(key,val){
                    var el = $('#op_sections_color_scheme_field_'+key);
                    if((el.val() != '' && loaded) || el.val() == ''){
                        el.val(val).trigger('change');
                    }
                });
                //$('.section-color_scheme .color-options :input').trigger('change');
            }).filter(':checked').trigger('change');
        }
    };
    function init_layout_options(){
        var container = $('.column-layout .column-container'),
            editor = $('.column-layout .column-editor');
        $('.column-layout :radio').change(function(){
            var layout = {}, v = $(this).val();
            if(typeof op_column_widths.widths[v] !== 'undefined'){
                container.show();
                layout = op_column_widths.widths[v];
                editor.find('div').hide();
                var last_el = null, el, func = 'prepend', el2, input;
                $.each(layout,function(i,v){
                    el = editor.find('div.width-'+i);
                    if(el.length == 0){
                        if(last_el !== null){
                            el2.after(width_field(i,v.title));
                        } else {
                            editor.prepend(width_field(i,v.title));
                        }
                    }
                    last_el = i;
                    el = editor.find('div.width-'+i);
                    el2 = el;
                    el.show();
                    input = el.find(':input');
                    if(input.val() == '' || loaded){
                        input.val(v.width);
                    }
                });
            } else {
                container.hide();
            }
        }).filter(':checked').trigger('change');
    };
    function width_field(classname,title){
        var fieldid = 'op_sections_column_layout_widths_'+classname;
        return '<p class="width-'+classname+'">'+
                '<label for="'+fieldid+'">'+title+'</label>'+
                '<input type="text" name="op[sections][column_layout][widths]['+classname+']" id="'+fieldid+'" />'+
            '</p>';
    };
    function init_blogenabler(){
        $('.op-bsw-blog-enabler').iButton({
            change: function(elem){
                var waiting = elem.closest('p').find('.op-bsw-waiting');
                waiting.fadeIn('fast');
                $.post(
                    OptimizePress.ajaxurl,
                    {
                        'action': OptimizePress.SN+'-enable-blog',
                        'enabled':(elem.is(':checked') ? 'Y' : 'N'),
                        '_wpnonce':$('#_wpnonce').val()
                    },
                    function(resp){
                        waiting.fadeOut('fast');
                        if(typeof resp.error !== 'undefined'){
                            alert(resp.errror);
                        }
                    },
                    'json'
                );
            }
        });
    };
    function init_multirow(){
        //$('.op-multirow .file-preview a[href$="#remove"]').live('click',function(e){
        $('body').on('click', '.op-multirow .file-preview a[href$="#remove"]', function(e){
            var $t = $(this), parent = $t.parent(), el = parent.find('.op-removefile');
            if(el.length == 0){
                el = $t.closest('.op-type').find('.op-removefile');
            }
            el.val('Y');
            parent.hide();
            e.preventDefault();
        });
        //$('.op-multirow .add-new-row').live('click',function(e){
        $('body').on('click', '.op-multirow .add-new-row', function(e){
            var container = $(this).closest('.op-multirow'),
                ul = container.find('.op-multirow-list'),
                lis = ul.find('> li'),
                maxli = container.find('.op-max-entries'),
                add_row = false;
            if(maxli.length > 0 && lis.length < maxli.val()){
                add_row = true;
            }
            if(maxli.length == 0){
                add_row = true;
            }
            if(add_row){
                lis.filter(':first').clone().find('.op-multirow-remove').remove().end().find(':input').val('').end().appendTo(ul).find('.op-type-switcher').trigger('change');
                ul.find('> li:last .file-preview a[href$="#remove"]').trigger('click');
            }
            e.preventDefault();
        });
        //$('.op-multirow .op-multirow-controls a').live('click',function(e){
        $('body').on('click', '.op-multirow .op-multirow-controls a', function(e){
            var hash = $(this).attr('href').split('#')[1],
                lis = $(this).closest('.op-multirow-list').find('> li'),
                li = $(this).closest('.op-multirow-list > li'),
                idx = lis.index(li);
            switch(hash){
                case 'move-up':
                    if(idx > 0){
                        move_item(li,'prev');
                    }
                    break;
                case 'move-down':
                    if(idx < lis.length-1){
                        move_item(li,'next');
                    }
                    break;
                case 'remove':
                    if(lis.length > 1){
                        li.remove();
                    } else {
                        li.find(':input').val('').trigger('change').end().find('.op-multirow-remove').remove();
                    }
                    break;
            }
            e.preventDefault();
        });
    };
    function init_tabs(){
        $('ul.op-bsw-grey-panel-tabs').op_tabs();
    };
    function init_radio_selectors(){
        //$('.img-radio-selector input[type="radio"]').change(function(){
        $('body').on('change', '.img-radio-selector input[type="radio"]', function () {
            if ($(this).val() === 'alongside') {
                $('.op-header-layout-alongside').show();
                $('.op-header-layout-below').hide();
            } else if ($(this).val() === 'below') {
                $('.op-header-layout-alongside').hide();
                $('.op-header-layout-below').show();
            }
            $(this).closest('.img-radio-selector').parent().find('.img-radio-selected').removeClass('img-radio-selected');
            $(this).closest('.img-radio-item').addClass('img-radio-selected');
        });
        $('body').on('click', '.img-radio-label', function () {
            $(this).closest('.img-radio-item').find(':radio').attr('checked',true).trigger('change');
        });
        $('.img-radio-selector.menu-position .img-radio-selected .img-radio-label').trigger('click');
    };
    function init_hidden_panels(){
        $('.panel-controlx:not(.op-bsw-blog-enabler):not(.op-disable-ibutton-load)').iButton({
            change: function(elem){
                var parent = elem.closest('.op-bsw-grey-panel'),
                    panel = parent.find('.op-bsw-grey-panel-content:first'),
                    link_el = parent.find('.show-hide-panel a:first'),
                    visible = panel.is(':visible'),
                    value = elem.is(':checked');
                !visible && value === true && link_el.trigger('click');
                visible && value === false && link_el.trigger('click');
            }
        });
        //$('.op-bsw-grey-panel-header').find('h3 a').live('click',function(e){
        $('body').on('click', '.op-bsw-grey-panel-header h3 a', function(e){
            e.preventDefault();
            $(this).closest('div').find('.show-hide-panel a').trigger('click');
        });

        $('body').on('click', '.show-hide-panel a', function(e){
            e.preventDefault();
            var $t = $(this),
                func1 = 'addClass',
                func2 = 'show',
                parent = $t.closest('.op-bsw-grey-panel'),
                panel = parent.find('.op-bsw-grey-panel-content:first');
            if(panel.is(':visible')){
                func1 = 'removeClass';
                func2 = 'hide';
            }
            $t[func1]('op-bsw-visible');
            panel[func2]();
            if(func2 == 'show'){
                parent.find('.op-type-switcher').trigger('change');
            }
            parent.find('.op-bsw-grey-panel-hide')[func2]();
        });
        $('.op-bsw-grey-panel.has-error .show-hide-panel a').trigger('click');
    };
    function move_item(li,type){
        var clone = li.clone(true);
        clone.find(':input').each(function(idx){
            $(this).val(li.find(':input:eq('+idx+')').val());
        });
        li[type]()[(type=='next' ? 'after':'before')](clone);
        li.remove();
    };
    $.fn.fadeThenSlideToggle = function(speed, easing, callback){
        if(this.is(':hidden')){
            return this.slideDown(speed, easing).fadeTo(speed, 1, easing, callback);
        } else {
            return this.fadeTo(speed, 0, easing).slideUp(speed, easing, callback);
        }
    };
    function init_upload_fields(){
        //$('.op-file-uploader a.button').live('click',function(e){
        $('body').on('click', '.op-file-uploader a.button', function(e){
            e.preventDefault();
            cur_upload = [$(this).next()];
            var par = cur_upload[0].parent();
            cur_upload.push(par.find('.file-preview'));
            cur_upload.push(par.find('.op-uploader-path'));
        });
        //$('.op-file-uploader a.remove-file').live('click',function(e){
        $('body').on('click', '.op-file-uploader a.remove-file', function(e){
            e.preventDefault();
            $(this).closest('.content').html('').parent().prev().val('');
            if($.isFunction($.fancybox.update)){
                $.fancybox.update();
            }
        });
    };



    window.op_attach_file = function(){
        if(cur_upload !== null){
            tb_remove();
            var content = cur_upload[1].find('.content').html(''),
                waiting = cur_upload[1].find('.op-show-waiting').fadeIn('fast'),
                args = {
                    action: OptimizePress.SN+'-file-attachment',
                    attach_type: arguments[0]
                };
            if(arguments[0] == 'url'){
                args.media_url = arguments[1];
            } else {
                args.media_item = arguments[1];
                args.media_size = arguments[2];
            }

            $.post(OptimizePress.ajaxurl,args,function(resp){
                waiting.fadeOut('fast',function(){
                    if(cur_upload[2].length > 0){
                        cur_upload[2].val(resp.file);
                    }
                    cur_upload[0].val(resp.url).trigger('change');
                    var tmp_c = $(resp.html),
                        tmp_i = tmp_c.find('img');
                    if(tmp_i.length > 0){
                        tmp_i.load(function(){
                            $(window).trigger('resize');
                        });
                    }
                    content.html(tmp_c).fadeIn('fast');
                });
            },'json');
        }
    };
    window.op_wpautop = function(pee) {
        // pee = unautop(pee);
        var blocklist = 'table|thead|tfoot|tbody|tr|td|th|caption|col|colgroup|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|address|math|p|h[1-6]|fieldset|legend|hr|noscript|menu|samp|header|footer|article|section|hgroup|nav|aside|details|summary';

        if (!pee) {
            return '';
        }

        pee = pee.replace(/(\r\n|\n)([\&nbsp\;|\s])(\r\n|\n)/g,'<p>&nbsp;</p>');

        if ( pee.indexOf('<object') != -1 ) {
            pee = pee.replace(/<object[\s\S]+?<\/object>/g, function(a){
                return a.replace(/[\r\n]+/g, '');
            });
        }

        pee = pee.replace(/<[^<>]+>/g, function(a){
            return a.replace(/[\r\n]+/g, ' ');
        });

        // Protect pre|script tags
        if ( pee.indexOf('<pre') != -1 || pee.indexOf('<script') != -1 ) {
            pee = pee.replace(/<(pre|script)[^>]*>[\s\S]+?<\/\1>/g, function(a) {
                return a.replace(/(\r\n|\n)/g, '<wp_temp_br>');
            });
        }

        pee = pee + '\n\n';
        pee = pee.replace(/<br \/>\s*<br \/>/gi, '\n\n');
        pee = pee.replace(new RegExp('(<(?:'+blocklist+')(?: [^>]*)?>)', 'gi'), '\n$1');
        pee = pee.replace(new RegExp('(</(?:'+blocklist+')>)', 'gi'), '$1\n\n');
        pee = pee.replace(/<hr( [^>]*)?>/gi, '<hr$1>\n\n'); // hr is self closing block element
        pee = pee.replace(/\r\n|\r/g, '\n');
        pee = pee.replace(/\n\s*\n+/g, '\n\n');
        pee = pee.replace(/([\s\S]+?)\n\n/g, '<p>$1</p>\n');
        pee = pee.replace(/<p>\s*?<\/p>/gi, '');
        pee = pee.replace(new RegExp('<p>\\s*(</?(?:'+blocklist+')(?: [^>]*)?>)\\s*</p>', 'gi'), "$1");
        pee = pee.replace(/<p>(<li.+?)<\/p>/gi, '$1');
        pee = pee.replace(/<p>\s*<blockquote([^>]*)>/gi, '<blockquote$1><p>');
        pee = pee.replace(/<\/blockquote>\s*<\/p>/gi, '</p></blockquote>');
        pee = pee.replace(new RegExp('<p>\\s*(</?(?:'+blocklist+')(?: [^>]*)?>)', 'gi'), "$1");
        pee = pee.replace(new RegExp('(</?(?:'+blocklist+')(?: [^>]*)?>)\\s*</p>', 'gi'), "$1");
        pee = pee.replace(/\s*\n/gi, '<br />\n');
        pee = pee.replace(new RegExp('(</?(?:'+blocklist+')[^>]*>)\\s*<br />', 'gi'), "$1");
        pee = pee.replace(/<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)/gi, '$1');
        pee = pee.replace(/(?:<p>|<br ?\/?>)*\s*\[caption([^\[]+)\[\/caption\]\s*(?:<\/p>|<br ?\/?>)*/gi, '[caption$1[/caption]');

        pee = pee.replace(/(<(?:div|th|td|form|fieldset|dd)[^>]*>)(.*?)<\/p>/g, function(a, b, c) {
            if ( c.match(/<p( [^>]*)?>/) )
                return a;

            return b + '<p>' + c + '</p>';
        });

        // put back the line breaks in pre|script
        pee = pee.replace(/<wp_temp_br>/g, '\n');
        return pee;
    };

    window.op_unautop = function(content){
        var blocklist1, blocklist2;

        // Protect pre|script tags
        if ( content.indexOf('<pre') != -1 || content.indexOf('<script') != -1 ) {
            content = content.replace(/<(pre|script)[^>]*>[\s\S]+?<\/\1>/g, function(a) {
                a = a.replace(/<br ?\/?>(\r\n|\n)?/g, '<wp_temp>');
                return a.replace(/<\/?p( [^>]*)?>(\r\n|\n)?/g, '<wp_temp>');
            });
        }
        content = content.replace(/<p>\s*<\/p>/g,'<op_temp>');
        /*
         * Newlines were wrongly parsed and they were duplicated. Here we are preserving this situation (check #newline-fix-2)
         */
        content = content.replace(/<p>\s*<br ?\/?>/g,'<op_temp>');
        // Pretty it up for the source editor
        blocklist1 = 'blockquote|ul|ol|li|table|thead|tbody|tfoot|tr|th|td|div|h[1-6]|p|fieldset';
        content = content.replace(new RegExp('\\s*</('+blocklist1+')>\\s*', 'g'), '</$1>\n');
        content = content.replace(new RegExp('\\s*<((?:'+blocklist1+')(?: [^>]*)?)>', 'g'), '\n<$1>');

        // Mark </p> if it has any attributes.
        content = content.replace(/(<p [^>]+>.*?)<\/p>/g, '$1</p#>');

        // Sepatate <div> containing <p>
        content = content.replace(/<div( [^>]*)?>\s*<p>/gi, '<div$1>\n\n');

        // Remove <p> and <br />
        content = content.replace(/\s*<p>/gi, '');
        content = content.replace(/\s*<\/p>\s*/gi, '\n\n');
        content = content.replace(/\n[\s\u00a0]+\n/g, '\n\n');
        content = content.replace(/\s*<br ?\/?>\s*/gi, '\n');

        // Fix some block element newline issues
        content = content.replace(/\s*<div/g, '\n<div');
        content = content.replace(/<\/div>\s*/g, '</div>\n');
        content = content.replace(/\s*\[caption([^\[]+)\[\/caption\]\s*/gi, '\n\n[caption$1[/caption]\n\n');
        content = content.replace(/caption\]\n\n+\[caption/g, 'caption]\n\n[caption');


        blocklist2 = 'blockquote|ul|ol|li|table|thead|tbody|tfoot|tr|th|td|h[1-6]|pre|fieldset';
        content = content.replace(new RegExp('\\s*<((?:'+blocklist2+')(?: [^>]*)?)\\s*>', 'g'), '\n<$1>');
        content = content.replace(new RegExp('\\s*</('+blocklist2+')>\\s*', 'g'), '</$1>\n');
        content = content.replace(/<li([^>]*)>/g, '\t<li$1>');

        if ( content.indexOf('<hr') != -1 ) {
            content = content.replace(/\s*<hr( [^>]*)?>\s*/g, '\n\n<hr$1>\n\n');
        }

        if ( content.indexOf('<object') != -1 ) {
            content = content.replace(/<object[\s\S]+?<\/object>/g, function(a){
                return a.replace(/[\r\n]+/g, '');
            });
        }

        // Unmark special paragraph closing tags
        content = content.replace(/<\/p#>/g, '</p>\n');
        content = content.replace(/\s*(<p [^>]+>[\s\S]*?<\/p>)/g, '\n$1');

        // Trim whitespace
        content = content.replace(/^\s+/, '');
        content = content.replace(/[\s\u00a0]+$/, '');

        // put back the line breaks in pre|script
        content = content.replace(/<wp_temp>/g, '\n');
        /*
         * #newline-fix-2, we are making simple substitution
         */
        content = content.replace(/<op_temp>/g,'<p>&nbsp;</p>');

        return content;
    };


    $.fn.op_tabs = function(){
        return this.each(function(){
            var selected_class = 'op-bsw-grey-panel-tabs-selected';
            var tabs = $(this).find('li').find('a').click(function(e){
                    var hash = $(this).attr('href').split('#')[1];
                    $(this).parent().parent().find('.'+selected_class).removeClass(selected_class).end().end().addClass(selected_class).closest('.op-bsw-grey-panel-content').find('> .op-bsw-grey-panel-tab-content-container').find('> .op-bsw-grey-panel-tab-content:visible').hide().end().find('> .tab-'+hash).show().find('.op-type-switcher').trigger('change');
                    e.preventDefault();
                }).end(),
                first = (tabs.filter('.has-error').length > 0) ? tabs.filter('.has-error:first') : tabs.filter(':first');
            first.find('a:first').trigger('click');
        });
    };

    /**
     * Converts HEX color value to RGB array/object
     * @param  {string} hex
     * @return {array}
     */
    window.hexToRgb = function(hex) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    };

    /**
     * Generates CSS color string depending on opacity (HEX or RGBA)
     * @param  {string} color
     * @param  {int} opacity
     * @return {string}
     */
    window.generateCssColor = function(color, opacity) {
        if (opacity != 100) {
            color = hexToRgb(color);
            if (color) {
                return 'rgba(' + color.r + ', ' + color.g + ', ' + color.b + ', ' + parseFloat(opacity/100) + ')';
            } else {
                return color;
            }
        } else {
            return color;
        }
    };

    /**
     * Transitioning from OP v2.2.1 to 2.2.2 we added encoding and decoding
     * to most of the elements to enable shortcodes in element fields.
     * Due to this transition, we can't be sure that all strings are properly encoded
     * (elements created in older version, but edited in newer would return
     * malformend URI error upon edit), so this ensures the error doesn't happen.
     * A couple version from 2.2.2 when we can be pretty-much sure users
     * updated their elements we can remove try-catch
     * Also, this ensures that string is always passed into to decodeURIComponent.
     *
     * @param  {string} str
     * @return {string} decoded string or empty string
     */
    window.op_decodeURIComponent = function(str) {
        var decodedStr;

        try {
            decodedStr = decodeURIComponent(str || '');
        } catch(e) {
            decodedStr = str || '';
        }

        return decodedStr;

    }


    /**
     * Base64 / binary data / UTF-8 strings utilities
     * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Base64_encoding_and_decoding
     */

    /* Array of bytes to base64 string decoding */
    window.b64ToUint6 = function (nChr) {

      return nChr > 64 && nChr < 91 ?
          nChr - 65
        : nChr > 96 && nChr < 123 ?
          nChr - 71
        : nChr > 47 && nChr < 58 ?
          nChr + 4
        : nChr === 43 ?
          62
        : nChr === 47 ?
          63
        :
          0;

    }
    window.base64DecToArr = function (sBase64, nBlocksSize) {

        var sB64Enc = sBase64.replace(/[^A-Za-z0-9\+\/]/g, ""),
            nInLen = sB64Enc.length,
            nOutLen = nBlocksSize ? Math.ceil((nInLen * 3 + 1 >> 2) / nBlocksSize) * nBlocksSize : nInLen * 3 + 1 >> 2,
            taBytes = new Uint8Array(nOutLen);

        for (var nMod3, nMod4, nUint24 = 0, nOutIdx = 0, nInIdx = 0; nInIdx < nInLen; nInIdx++) {
            nMod4 = nInIdx & 3;
            nUint24 |= b64ToUint6(sB64Enc.charCodeAt(nInIdx)) << 18 - 6 * nMod4;
            if (nMod4 === 3 || nInLen - nInIdx === 1) {
                for (nMod3 = 0; nMod3 < 3 && nOutIdx < nOutLen; nMod3++, nOutIdx++) {
                    taBytes[nOutIdx] = nUint24 >>> (16 >>> nMod3 & 24) & 255;
                }
                nUint24 = 0;

            }
        }

        return taBytes;
    }


    /* Base64 string to array encoding */
    window.uint6ToB64 = function (nUint6) {

        return nUint6 < 26 ?
            nUint6 + 65 : nUint6 < 52 ?
            nUint6 + 71 : nUint6 < 62 ?
            nUint6 - 4 : nUint6 === 62 ?
            43 : nUint6 === 63 ?
            47 :
            65;

    }
    window.base64EncArr = function (aBytes) {

        var nMod3 = 2,
            sB64Enc = "";

        for (var nLen = aBytes.length, nUint24 = 0, nIdx = 0; nIdx < nLen; nIdx++) {
            nMod3 = nIdx % 3;
            // Zvonko commented this out, it was breaking the resulting endoded string with new lines
            /*if (nIdx > 0 && (nIdx * 4 / 3) % 76 === 0) {
             sB64Enc += "\r\n";
             }*/
            nUint24 |= aBytes[nIdx] << (16 >>> nMod3 & 24);
            if (nMod3 === 2 || aBytes.length - nIdx === 1) {
                sB64Enc += String.fromCharCode(uint6ToB64(nUint24 >>> 18 & 63), uint6ToB64(nUint24 >>> 12 & 63), uint6ToB64(nUint24 >>> 6 & 63), uint6ToB64(nUint24 & 63));
                nUint24 = 0;
            }
        }

        return sB64Enc.substr(0, sB64Enc.length - 2 + nMod3) + (nMod3 === 2 ? '' : nMod3 === 1 ? '=' : '==');

    }

    /* UTF-8 array to DOMString and vice versa */
    window.UTF8ArrToStr = function (aBytes) {

        var sView = "";

        for (var nPart, nLen = aBytes.length, nIdx = 0; nIdx < nLen; nIdx++) {
            nPart = aBytes[nIdx];
            sView += String.fromCharCode(
                nPart > 251 && nPart < 254 && nIdx + 5 < nLen ? /* six bytes */
                /* (nPart - 252 << 32) is not possible in ECMAScript! So...: */
                (nPart - 252) * 1073741824 + (aBytes[++nIdx] - 128 << 24) + (aBytes[++nIdx] - 128 << 18) + (aBytes[++nIdx] - 128 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128 : nPart > 247 && nPart < 252 && nIdx + 4 < nLen ? /* five bytes */
                (nPart - 248 << 24) + (aBytes[++nIdx] - 128 << 18) + (aBytes[++nIdx] - 128 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128 : nPart > 239 && nPart < 248 && nIdx + 3 < nLen ? /* four bytes */
                (nPart - 240 << 18) + (aBytes[++nIdx] - 128 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128 : nPart > 223 && nPart < 240 && nIdx + 2 < nLen ? /* three bytes */
                (nPart - 224 << 12) + (aBytes[++nIdx] - 128 << 6) + aBytes[++nIdx] - 128 : nPart > 191 && nPart < 224 && nIdx + 1 < nLen ? /* two bytes */
                (nPart - 192 << 6) + aBytes[++nIdx] - 128 : /* nPart < 127 ? */ /* one byte */
                nPart
            );
        }

        return sView;

    }
    window.strToUTF8Arr = function (sDOMStr) {

        var aBytes, nChr, nStrLen = sDOMStr.length,
            nArrLen = 0;

        /* mapping... */

        for (var nMapIdx = 0; nMapIdx < nStrLen; nMapIdx++) {
            nChr = sDOMStr.charCodeAt(nMapIdx);
            nArrLen += nChr < 0x80 ? 1 : nChr < 0x800 ? 2 : nChr < 0x10000 ? 3 : nChr < 0x200000 ? 4 : nChr < 0x4000000 ? 5 : 6;
        }

        aBytes = new Uint8Array(nArrLen);

        /* transcription... */

        for (var nIdx = 0, nChrIdx = 0; nIdx < nArrLen; nChrIdx++) {
            nChr = sDOMStr.charCodeAt(nChrIdx);
            if (nChr < 128) {
                /* one byte */
                aBytes[nIdx++] = nChr;
            } else if (nChr < 0x800) {
                /* two bytes */
                aBytes[nIdx++] = 192 + (nChr >>> 6);
                aBytes[nIdx++] = 128 + (nChr & 63);
            } else if (nChr < 0x10000) {
                /* three bytes */
                aBytes[nIdx++] = 224 + (nChr >>> 12);
                aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
                aBytes[nIdx++] = 128 + (nChr & 63);
            } else if (nChr < 0x200000) {
                /* four bytes */
                aBytes[nIdx++] = 240 + (nChr >>> 18);
                aBytes[nIdx++] = 128 + (nChr >>> 12 & 63);
                aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
                aBytes[nIdx++] = 128 + (nChr & 63);
            } else if (nChr < 0x4000000) {
                /* five bytes */
                aBytes[nIdx++] = 248 + (nChr >>> 24);
                aBytes[nIdx++] = 128 + (nChr >>> 18 & 63);
                aBytes[nIdx++] = 128 + (nChr >>> 12 & 63);
                aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
                aBytes[nIdx++] = 128 + (nChr & 63);
            } else /* if (nChr <= 0x7fffffff) */ {
                /* six bytes */
                aBytes[nIdx++] = 252 + /* (nChr >>> 32) is not possible in ECMAScript! So...: */ (nChr / 1073741824);
                aBytes[nIdx++] = 128 + (nChr >>> 24 & 63);
                aBytes[nIdx++] = 128 + (nChr >>> 18 & 63);
                aBytes[nIdx++] = 128 + (nChr >>> 12 & 63);
                aBytes[nIdx++] = 128 + (nChr >>> 6 & 63);
                aBytes[nIdx++] = 128 + (nChr & 63);
            }
        }

        return aBytes;

    }

    /**
     * Helper method for base64 encoding with utf16 support
     */
    window.op_base64encode = function (str) {
        return base64EncArr(strToUTF8Arr(str || ''));
    }

    /**
     * Helper method for base64 decoding with utf16 support
     */
    window.op_base64decode = function (str) {
        return UTF8ArrToStr(base64DecToArr(str));
    }

}(opjq));
