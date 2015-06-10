;var op_cur_html;
var cur = [];
(function($){
    var fancy_defaults = {}, win = $(window),
        parent_win = window.dialogArguments || opener || parent || top,
        scroll_top, epicbox,
        child_element = false, cur_child, refresh_item = null,
        sort_default = {
            revert:'invalid',
            scrollSensitivity: '50',
            tolerance: 'pointer'
        },
        editor_switch = false,
        wysiwygs_checked = false;
    var cat_options = [];
    var subcat_options = [];
    var $body;

    //OP is global optimizepress object
    OP = OP || {};
    OP.disable_alert = false;

    /**
     * We register any custom elements needed in live editor
     * Custom elements will work even withouth registering them,
     * they'll just utilize HTMLUnknownElement interface instead of HTMLElement interface
     */
    if (typeof document.registerElement !== 'undefined') {
        document.registerElement('op-row-before');
        document.registerElement('op-row-after');
    }

    $(document).ready(function(){
        $body = $('body');
        bind_content_sliders();
        $('#changeMembershipType').click(function(event){
            $('#pageTypeChange').attr('disabled', false).attr('name', 'op[type]').css('border-color', '#66AFE9').css('box-shadow', '0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 8px rgba(102, 175, 233, 0.6)');
        });
        $('#pageTypeChange').change(function(event){
            $('#le-membership-dialog .editor-button').trigger('click');
        });
        // header and navigation link
        $('#op_header_disable_link').change(function(event){
            if ($(this).is(':checked')) {
                $('#op_header_link').hide();
            } else {
                $('#op_header_link').show();
            }
        }).trigger('change');
        $('#op_category_id').find('option').each(function() {
            var selected_val;
            if ($(this).attr('selected')) {
                selected_val = $(this).val();
            } else {
                selected_val = '';
            }
            cat_options.push({value: $(this).val(), text: $(this).text(), parent: $(this).attr('class'), selected: selected_val});
        });
        $('#op_subcategory_id').find('option').each(function() {
            var selected_val;
            if ($(this).attr('selected')) {
                selected_val = $(this).val();
            } else {
                selected_val = '';
            }
            subcat_options.push({value: $(this).val(), text: $(this).text(), parent: $(this).attr('class'), selected: selected_val});
        });
        if(typeof switchEditors != 'undefined'){
            editor_switch = true;
        }
        /** AUTOSAVE **/
        var autosaveTriggered = false;
        function autosave() {
            autosaveTriggered = true;
            OP.disable_alert = true;
            window.op_dont_show_loading = true;
            save_content();
        }
        if (!autosaveTriggered) {
            setInterval(autosave, OptimizePress.op_autosave_interval * 1000);
        }
        /***********/
        /*******/
        $('#op_product_id').change(function(event, a){
            show_member_fields($('#op_category_id'), $(this).val(), 'category', a);
        }).trigger('change', ["first"]);
        $('#op_category_id').change(function(event, a){
            show_member_fields($('#op_subcategory_id'), $(this).val(), 'subcategory', a);
        }).trigger('change', ["second"]);
        function show_member_fields(el, id, what, clean) {
            el.empty();
            if (what == 'category') {
                el.append(
                    $('<option>').text('').val('')
                );
                $.each(cat_options, function(i) {
                    var option = cat_options[i];
                    if(option.parent === 'parent-' + id) {
                        if (option.selected != '') {
                            el.append(
                                    $('<option>').text(option.text).val(option.value).attr('selected', true)
                                );
                        } else {
                            el.append(
                                $('<option>').text(option.text).val(option.value)
                            );
                        }
                    }
                });
            } else {
                el.append(
                    $('<option>').text('').val('')
                );
                $.each(subcat_options, function(i) {
                    var option = subcat_options[i];
                    if(option.parent === 'parent-' + id) {
                        if (option.selected != '') {
                            el.append(
                                    $('<option>').text(option.text).val(option.value).attr('selected', true)
                                );
                        } else {
                            el.append(
                                $('<option>').text(option.text).val(option.value)
                            );
                        }
                    }
                });
            }
            if (typeof clean === 'undefined') {
                el.val('');
            }
            if (el.selector == '#op_category_id') {
                $('#op_category_id').trigger('change');
            }
            if (el.selector == '#op_category_id1') {
                $('#op_category_id1').trigger('change', clean);
            }
        };
        /*function show_member_fields(el, id, clean) {
            el.find("option").show();
            el.find("option:not(.parent-" + id + ",.default-val)").hide();
            if (typeof clean === 'undefined') {
                el.val('');
            }
            if (el.selector == '#op_category_id') {
                $('#op_category_id').trigger('change', clean);
            }
        };*/
        var preset_options = $('#preset-option-preset,#preset-option-content_layout');
        $('#preset-option :radio').change(function(){
            preset_options.hide();
            if($(this).is(':checked') && (v = $(this).val()) && v != 'blank'){
                $('#preset-option-'+v).show();
            }
        }).filter(':checked').trigger('change');
        /*******/
        epicbox = [$('#epicbox-overlay'),$('#epicbox')];
        epicbox.push($('.epicbox-content',epicbox[1]));
        epicbox.push($('.epicbox-scroll',epicbox[2]));
        epicbox[0].css({ opacity: 0.8 });
        win.bind('beforeunload', function (e) {
            if (OP.disable_alert === false) {
                var message = 'If you leave page, all unsaved changes will be lost.';
                if (typeof e == 'undefined') {
                    e = window.event;
                }
                if (e) {
                    e.returnValue = message;
                }
                return message;
            }
        }).resize(function(){
            scroll_top = $('.fancybox-inner').scrollTop();
            resize_epicbox();
        });

        fancy_defaults = {
            padding: 0,
            autoSize: true,
            wrapCSS: 'fancybox-no-scroll',
            helpers: {
                overlay: {
                    closeClick: false
                }
            },
            keys: false,
            beforeClose: close_wysiwygs,

            afterClose: function(){

                scroll_top = null;
                refresh_item = null;

                /**
                 * We unbind events related to revisions
                 * (they're binded in afterShow, if revisions tab is opened)
                 */
                if (OP._pageRevisionsActive) {
                    OP._pageRevisionsActive = false;
                    $('#op-current-iframe').attr('src', '');
                    $('#op-revisions-iframe').attr('src', '');
                    $(document).off('pageRevisionsFancyboxOpen');
                    $(window).off('resize', OP._repositionRevisionsPopup);
                }

                //This is necessary in order to hide the parent fancybox scrollbars and close button
                $('html').css({
                    overflow: 'auto',
                    height: 'auto'
                });

                /**
                 * Parent fancybox close button was hidden
                 * (because we don't want two close buttons to be visible when fancybox is opened)
                 */
                $(window.parent.document).find('.fancybox-close').css({ display: 'block' });
            },

            beforeShow: function() {

                //This is necessary in order to hide the parent fancybox scrollbars and close button
                $('html').css({
                    overflow: 'hidden',
                    height: '100%'
                });

                /**
                 * Parent fancybox close button was hidden
                 * (because we don't want two close buttons to be visible when fancybox is opened)
                 */
                $(window.parent.document).find('.fancybox-close').css({ display: 'none' });

            },

            afterShow: function () {

                var $fancyBoxOpened;
                var $fancyBoxIframe;

                if(editor_switch && typeof this.content != 'string'){
                    this.content.find('.wp-editor-area').each(function(){
                        var id = $(this).attr('id');

                        // For TinyMCE 3 value must be set before addControl, and for TinyMCE 4 after addEditor.
                        if (tinyMCE.majorVersion > 3) {
                            tinyMCE.execCommand("mceAddEditor", true, id);
                            $(this).val(OP_AB.autop($(this).val()));
                        } else {
                            $(this).val(OP_AB.autop($(this).val()));
                            tinyMCE.execCommand("mceAddControl", true, id);

                        }
                    });
                }

                $('select.op-type-switcher').trigger('change');
                $fancyBoxOpened = $('.fancybox-opened').eq(-1);
                $fancyBoxIframe = $fancyBoxOpened.find('iframe');

                if ($fancyBoxIframe.length > 0) {
                    $fancyBoxIframe.focus();
                } else {
                    $fancyBoxOpened
                        .find('.fancybox-inner')
                            .addClass('op_no_outline')
                            .attr('tabindex', 0)
                            .focus();
                }

                // We do this to resize revisions dialog iframes properly
                if (OP._pageRevisionsActive) {
                    $(document).trigger('pageRevisionsFancyboxOpen');
                }

                //Fancybox loading is now hidden, so we can show default OP loading overlay if needed.
                window.op_dont_show_loading = false;

            },
            onCancel: function () {
                cur = [];
            },
            onUpdate: function () {
                if(scroll_top != null){
                    $('.fancybox-inner').scrollTop(scroll_top);
                }
            }
        };
        // $('#op-le-row-options-update').click(function(e){
        $body.on('click', '#op-le-row-options-update', function(e){
            e.preventDefault();
            var dataStyles = {};
            if ($('input[name="op_full_width_row"]:checked').length > 0) {
                cur[0].addClass('section');
            } else {
                cur[0].removeClass('section');
            }

            /*
             * Row CSS class
             */
            if (cur[0].attr('data-style') && '' !== cur[0].attr('data-style')) {
                var oldDataStyles = JSON.parse(atob(cur[0].attr('data-style')));
                /*
                 * We need to get rid of old class entry
                 */
                if (typeof oldDataStyles.cssClass !== 'undefined') {
                    cur[0].removeClass(oldDataStyles.cssClass);
                }
            }
            if ($('#op_row_css_class').val() !== '') {
                cur[0].addClass($('#op_row_css_class').val());
                dataStyles.cssClass = $('#op_row_css_class').val();
            }

            // bg options
            if ($('#op_row_bg_options').val() && $('#op_row_background').val()) {
                var position = $('#op_row_bg_options').val();
                var image = "url(" + $('#op_row_background').val() + ")";
                dataStyles.backgroundImage = image;
                dataStyles.backgroundPosition = position;
                switch (position) {
                    case 'center':
                        cur[0].css({'background-image': image,
                                    'background-repeat': 'no-repeat',
                                    'background-position': 'center'});
                    break;
                    case 'cover':
                        cur[0].css({'background-image': image,'background-size': 'cover',
                                    'background-repeat': 'no-repeat'});
                    break;
                    case 'tile_horizontal':
                        cur[0].css({'background-image': image, 'background-repeat': 'repeat-x'});
                    break;
                    case 'tile':
                        cur[0].css({'background-repeat' : 'repeat', 'background-image' : image});
                    break;
                }
            } else {
                cur[0].css({'background-image': 'none', 'background-repeat': 'no-repeat'});
            }

            function htmlEntities(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            ///// row code before and after
            var html = '',
            before = '',
            after = '',
            markup = '';
            if ($('#op_row_before').val()) {
                before = '<op-row-before class="op-row-code-before">' + htmlEntities($('#op_row_before').val()) + '</op-row-before>';
            }
            if ($('#op_row_after').val()) {
                after = '<op-row-after class="op-row-code-after">' + htmlEntities($('#op_row_after').val()) + '</op-row-after>';
            }
            cur[0].prev('.op-row-code-before').remove();
            cur[0].next('.op-row-code-after').remove();
            cur[0].before(before);
            cur[0].after(after);

            if ($('#op_row_before').val()) {
                dataStyles.codeBefore = $('#op_row_before').val();
            }
            if ($('#op_row_after').val()) {
                dataStyles.codeAfter = $('#op_row_after').val();
            }
            ///// end row code before and after
            //bgcolor
            if ($('#op_section_row_options_bgcolor_start').val()) {
                dataStyles.backgroundColorStart = $('#op_section_row_options_bgcolor_start').val();
                if ($('#op_section_row_options_bgcolor_end').val()) {
                    // gradient
                    var start_color = $('#op_section_row_options_bgcolor_start').val();
                    var end_color = $('#op_section_row_options_bgcolor_end').val();
                    dataStyles.backgroundColorEnd = $('#op_section_row_options_bgcolor_end').val();
                    cur[0]
                        .css('background', start_color)
                        .css('background', '-webkit-gradient(linear, left top, left bottom, color-stop(0%, ' + start_color + '), color-stop(100%, ' + end_color + '))')
                        .css('background', '-webkit-linear-gradient(top, ' + start_color + ' 0%, ' + end_color + ' 100%)')
                        .css('background', '-moz-linear-gradient(top, ' + start_color + ' 0%, ' + end_color + ' 100%)')
                        .css('background', '-ms-linear-gradient(top, ' + start_color + ' 0%, ' + end_color + ' 100%)')
                        .css('background', '-o-linear-gradient(top, ' + start_color + ' 0%, ' + end_color + ' 100%)')
                        .css('background', 'linear-gradient(to bottom, ' + start_color + ' 0%, ' + end_color + ' 100%)')
                        .css('filter', 'progid:DXImageTransform.Microsoft.gradient( startColorstr=' + start_color + ', endColorstr=' + end_color + ', GradientType=0 )');
                } else {
                    cur[0].css('background-color', $('#op_section_row_options_bgcolor_start').val());
                }
            } else {
                cur[0].css('background-color', '');
            }
            //padding top
            if ($('#op_row_top_padding').val()) {
                cur[0].css('padding-top', $('#op_row_top_padding').val() + 'px');
                dataStyles.paddingTop = $('#op_row_top_padding').val();
            } else {
                cur[0].css('padding-top', '');
            }
            //padding bottom
            if ($('#op_row_bottom_padding').val()) {
                cur[0].css('padding-bottom', $('#op_row_bottom_padding').val() + 'px');
                dataStyles.paddingBottom = $('#op_row_bottom_padding').val();
            } else {
                cur[0].css('padding-bottom', '');
            }

            // // border width
            // if ($('#op_row_border_width').val()) {
            //     cur[0].css('border-top-width', $('#op_row_border_width').val() + 'px');
            //     cur[0].css('border-bottom-width', $('#op_row_border_width').val() + 'px');
            //     dataStyles.borderWidth = $('#op_row_border_width').val();
            // }

            // if ($('#op_section_row_options_borderColor').val()) {
            //     cur[0].css('border-top-color', $('#op_section_row_options_borderColor').val());
            //     cur[0].css('border-bottom-color', $('#op_section_row_options_borderColor').val());
            //     cur[0].css('border-style', 'solid');
            //     dataStyles.borderColor = $('#op_section_row_options_borderColor').val();
            // }

            // border top width
            if ($('#op_row_border_top_width').val()) {
                cur[0].css('border-top-width', $('#op_row_border_top_width').val() + 'px');
                dataStyles.borderTopWidth = $('#op_row_border_top_width').val();
            } else {
                cur[0].css('border-top-width', '0px');
                dataStyles.borderTopWidth = '';
            }

            // border top color
            if ($('#op_section_row_options_borderTopColor').val()) {
                cur[0].css('border-top-color', $('#op_section_row_options_borderTopColor').val());
                cur[0].css('border-top-style', 'solid');
                dataStyles.borderTopColor = $('#op_section_row_options_borderTopColor').val();
            } else {
                cur[0].css('border-top-color', '');
                dataStyles.borderTopColor = '';
            }

            // border bottom width
            if ($('#op_row_border_bottom_width').val()) {
                cur[0].css('border-bottom-width', $('#op_row_border_bottom_width').val() + 'px');
                dataStyles.borderBottomWidth = $('#op_row_border_bottom_width').val();
            } else {
                cur[0].css('border-bottom-width', '0px');
                dataStyles.borderBottomWidth = '';
            }

            // border bottom color
            if ($('#op_section_row_options_borderBottomColor').val()) {
                cur[0].css('border-bottom-color', $('#op_section_row_options_borderBottomColor').val());
                cur[0].css('border-bottom-style', 'solid');
                dataStyles.borderBottomColor = $('#op_section_row_options_borderBottomColor').val();
            } else {
                cur[0].css('border-bottom-color', '');
                dataStyles.borderBottomColor = '';
            }


            /*
             * Row addons
             */
            dataStyles.addon = {};
            $.each($('[id^="op_row_addon_"]'), function(i, item) {
                var $item = $(item);
                var id = $item.attr('id').replace('op_row_addon_', '');
                if ($item.is(':checkbox')) {
                    if ($item.prop('checked')) {
                        dataStyles.addon[id] = true;
                    }
                } else if ($item.hasClass('slider-item')) {
                    dataStyles.addon[id] = $item.find('input').val();
                } else {
                    dataStyles.addon[id] = $item.val();
                }
            });

            var base = btoa(JSON.stringify(dataStyles));
            cur[0].attr('data-style', base);

            // Hook event for addons
            $(window).trigger('op_row_addon_update', cur[0]);

            $.fancybox.close();
        });
        $('.editable-area').each(function(){
            var el = $(this),
                id = el.attr('id');
            init_editable_area(el,id.substr(0,id.length-5));
            custom_item_ids(el);
        });
        init_child_elements();
        // $('#op-le-row-select li a').click(function(e){
        $body.on('click', '#op-le-row-select li a', function(e){
            $('#op-le-row-select li.selected').removeClass('selected');
            $(this).parent().addClass('selected');
            e.preventDefault();
        });
        // $('#op-le-row-select-insert').click(function(e){
        $body.on('click', '#op-le-row-select-insert', function(e){
            e.preventDefault();
            add_new_row($('#op-le-row-select li.selected').find('a:first'));
        });
        // $('#op-le-row-select li a').dblclick(function(e){
        $body.on('dblclick', '#op-le-row-select li a', function(e){
            e.preventDefault();
            add_new_row($(this));
        });
        // split columns
        // $('#op-le-split-column li a').click(function(e){
        $body.on('click', '#op-le-split-column li a', function(e){
            $('#op-le-split-column li.selected').removeClass('selected');
            $(this).parent().addClass('selected');
            e.preventDefault();
        });
        // $('#op-le-split-column-insert').click(function(e){
        $body.on('click', '#op-le-split-column-insert', function(e){
            e.preventDefault();
            split_column($('#op-le-split-column li.selected').find('a:first'));
        });
        $('a.add-new-element').each(function() {
            if ($(this).width() < 120) {
                $(this).find('span').hide();
            }
        });
        // $('#op-le-split-column li a').dblclick(function(e){
        $body.on('dblclick', '#op-le-split-column li a', function(e){
            e.preventDefault();
            split_column($(this));
        });
        // end split columns
        //$('a.feature-settings').live('click',function(e){
        $body.on('click', 'a.feature-settings', function(e){
            e.preventDefault();
            e.stopPropagation();
            var $t = $(this);
            cur = [$t.closest('.op-feature-area'),'replaceWith'];
            $.fancybox.open($.extend({},fancy_defaults,{
                type: 'inline',
                href: $t.attr('href')
            }));
        });

        // $('#op-le-settings-toolbar div.links a').click(function(e){
        $body.on('click', '#op-le-settings-toolbar div.links a', openPopupDialog);

        function openPopupDialog(e){

            var hash = $(this).attr('href').split('#')[1];
            var $currentElement = $('#' + hash);

            e.preventDefault();

            /**
             * Revisions are handled separately
             */
            if (hash && hash === 'op-revisions-dialog') {

                OP._initPageRevisions(fancy_defaults);

            } else {

                $.fancybox($.extend({}, fancy_defaults, {
                    minWidth: $currentElement.width(),
                    href: '#'+ hash
                }));

            }

        }

        $('form.op-feature-area').submit(function(e){
            var form_html;

            e.preventDefault();

            $(this).find('.wp-editor-area').each(function(){
                var id = $(this).attr('id'),
                    content = OP_AB.wysiwyg_content(id);
                $(this).val(content);
            });

            /**
             * Taking care of excessive form html elements (<textarea> & <style>)
             */
            form_html = $(this).find('#op_feature_area_settings_optin_formhtml').val();
            form_html = form_html.replace(/<textarea((.|[\r|\n])*)?<\/\s?textarea>/gi, '');
            form_html = form_html.replace(/<textarea(.*?)>/gi, '');
            form_html = form_html.replace(/<style((.|[\r|\n])*)?<\/\s?style>/gi, '');
            form_html = form_html.replace(/<style(.*?)>/gi, '');
            $(this).find('#op_feature_area_settings_optin_formhtml').val(form_html);

            $.post(OptimizePress.ajaxurl,$(this).serialize(),
                function(resp){
                    if(typeof resp.error != 'undefined'){
                        alert(resp.error);
                    } else {
                        cur[0][cur[1]](resp.output);
                        OP_Feature_Area[resp.option] = resp.js_options;
                        $.fancybox.close();
                    }
                },
                'json'
            );
        });

        // $('#op-le-close-editor').click(function(e){
        $body.on('click', '#op-le-close-editor', function(e){
            e.preventDefault();
            OP.disable_alert = true;
            parent_win.OP.disable_alert = true;
            parent_win.opjq.fancybox.close();
        });

        $body.on('click', '#op-le-save-1', function(e){
            e.preventDefault();
            save_content();
        });

        // $('#op-le-save-2').click(function(e){
        $body.on('click', '#op-le-save-2', function(e){
            e.preventDefault();
            OP.disable_alert = true;
            parent_win.OP.disable_alert = true;
            save_content(parent_win.opjq.fancybox.close);
        });
        $('#le-settings-dialog').submit(function(e){
            e.preventDefault();
            $.fancybox.close();
            save_content();
        });

        // Callback after typography settings have been submitted/saved.
        function typography_saved() {
            window.location.reload();
        }

        $('#le-typography-dialog').submit(function(e){
            e.preventDefault();
            $.fancybox.close();
            OP.disable_alert = true;
            window.op_dont_hide_loading = true;
            op_show_loading();
            save_content(typography_saved);
        });

        //$('.op-feature-area:not(:has(.editable-area))').live('click',function(e){
        $body.on('click', '.op-feature-area:not(:has(.editable-area))', function(e){
            $(this).find('a.feature-settings').trigger('click');

            /*
             * Preventing onbeforeunload event
             */
            if (typeof $(this).attr('data-unload') != 'undefined' && $(this).attr('data-unload') == '0') {
                e.preventDefault();
            }
        });
        //$('.fancybox-inner a').live('click',function(e){
        $body.on('click', '.fancybox-inner a', function(e){
            scroll_top = $('.fancybox-inner').scrollTop();
            $.fancybox.update();
        });
        init_content_layouts();
        init_presets_dialogs();
        if(op_launch_funnel_enabled === true && typeof parent_win.op_launch_suite_update_selects == 'function'){
            parent_win.op_launch_suite_update_selects($('#page_id').val());
        }

        //$('a[href$="#le-settings-dialog"]').trigger('click');

        //Show delayed fade elements
        $("[data-fade]").each(function(){
            var style = $(this).attr('style');
            style = style || '';
            style = style.replace(/display:\s?none;?/gi, '');
            $(this).attr('style', style);
        });

        // show paste button only if we have something in storage
        togglePasteButtons();
    });

    function togglePasteButtons() {
        if (!localStorage.getItem('op_row')) {
            $('.paste-row').hide();
        } else {
            $('.paste-row').show();
        }
    }

    function bind_content_sliders(){
        //Get all the content slider buttons
        var $btn = $('.op-content-slider-button'), $cur_btn;

        //Loop through all buttons
        $btn.each(function(){
            $cur_btn = $(this);
            var $target = $('#' + $(this).data('target')); //Get the target of the current button (the content slider)

            //Unbind any existing click events so we dont duplicate them
            $(this).unbind('click').click(function(e){
                var scrollY = window.pageYOffset;
                $target.show().animate({top:scrollY},400);
                e.preventDefault();
            });

            //Initialize the close button
            $target.find('.hide-the-panda').unbind('click').click(function(e){
                var scrollY = window.pageYOffset;

                $target.animate({top:-(scrollY)},400, function(){
                    $(this).hide();
                });

                e.preventDefault();
            });

            $target.on('click', 'ul.op-image-slider-content li a', function(e){
                var $input = $cur_btn.next('input.op-gallery-value');
                var $preview = $input.next('.file-preview').find('.content');
                var src = $(this).find('img').attr('src');
                var html = '<a class="preview-image" target="_blank" href="' + src + '"><img alt="uploaded-image" src="' + src + '"></a><a class="remove-file button" href="#remove">Remove Image</a>';
                $input.val(src);
                $input.parent().next('.op-file-uploader').find('.file-preview .content').empty().html(html).find('.remove-file').click(function(){
                    $(this).parent().empty().parent('.file-preview').prev('.op-uploader-value').val('');
                });
                /*$preview.empty().html(html).find('.remove-file').click(function(){
                    $preview.empty().parent('.file-preview').prev('.op-gallery-value').val('');
                });*/
                $('#op_page_thumbnail').val(src);
                $target.animate({top:-475},400, function(){
                    $(this).hide();
                });

                e.preventDefault();
            });
        });
    }

    function init_content_layouts(){
        var buttons = $('#le-layouts-dialog .op-insert-button');
        // $('#le-layouts-dialog ul.op-bsw-grey-panel-tabs a').click(function(e){
        $body.on('click', '#le-layouts-dialog ul.op-bsw-grey-panel-tabs a', function(e){
            e.preventDefault();
            if($(this).get_hash() == 'predefined'){
                buttons.show();
            } else {
                buttons.hide();
            }
        });
        // $('#export_layout_category_create_new').click(function(e){
        $body.on('click', '#export_layout_category_create_new', function(e){
            e.preventDefault();
            $('#export_layout_category_select_container:visible').fadeOut('fast',function(){
                $('#export_layout_category_new_container').fadeIn('fast');
            });
        });
        // $('#export_layout_category_select').click(function(e){
        $body.on('click', '#export_layout_category_select', function(e){
            e.preventDefault();
            $('#export_layout_category_new_container:visible').fadeOut('fast',function(){
                $('#export_layout_category_select_container').fadeIn('fast');
            });
        });
        // $('#export_layout_category_new_submit').click(function(e){
        $body.on('click', '#export_layout_category_new_submit', function(e){
            e.preventDefault();
            var waiting = $(this).next().find('img').fadeIn('fast'), name = $(this).prev().val(),
                data = {
                    action: OptimizePress.SN+'-live-editor-create-category',
                    _wpnonce: $('#_wpnonce').val(),
                    category_name: name
                };
            $.post(OptimizePress.ajaxurl,data,function(resp){
                waiting.fadeOut('fast');
                if(typeof resp.error != 'undefined'){
                    alert(resp.error);
                } else {
                    $('#export_layout_category').html(resp.html);
                    $('#export_layout_category_select').trigger('click');
                }
            },'json');
        });
        // membership
        $('#le-membership-dialog').submit(function(e){
            e.preventDefault();
            $.fancybox.close();
            $.fancybox.showLoading();
            var data = {
                action: OptimizePress.SN+'-live-editor-membership',
                _wpnonce: $('#_wpnonce').val(),
                page_id: $('#page_id').val()
            };
            $.extend(data, serialize($(this)));
            save_content();
            $.post(OptimizePress.ajaxurl, data,
                function(resp){
                    $.fancybox.hideLoading();
                    if(typeof resp.error != 'undefined'){
                        alert(resp.error);
                    }
                    if(typeof resp.done != 'undefined'){
                        OP.disable_alert = true;
                        window.location.reload();
                    }
                },
                'json'
            );
        });
        // end membership

        // headers
        $('#le-headers-dialog').submit(function(e){
            e.preventDefault();
            var selected = $(this).find(':radio:checked').val();
            $.fancybox.close();
            //$.fancybox.showLoading();
            var data = {
                action: OptimizePress.SN+'-live-editor-headers',
                _wpnonce: $('#_wpnonce').val(),
                page_id: $('#page_id').val()
            };
            $.extend(data, serialize($(this)));
            save_content();
            $.post(OptimizePress.ajaxurl, data,
                function(resp){
                    $.fancybox.hideLoading();
                    if(typeof resp.error != 'undefined'){
                        alert(resp.error);
                    }
                    if(typeof resp.done != 'undefined'){
                        OP.disable_alert = true;
                        window.location.reload();
                    }
                },
                'json'
            );
        });
        // end headers
        // colours
        $('#le-colours-dialog').submit(function(e){
            e.preventDefault();
            var selected = $(this).find(':radio:checked').val();
            $.fancybox.close();
            //$.fancybox.showLoading();
            var data = {
                action: OptimizePress.SN+'-live-editor-colours',
                _wpnonce: $('#_wpnonce').val(),
                page_id: $('#page_id').val()
            };
            $.extend(data, serialize($(this)));
            save_content();
            $.post(OptimizePress.ajaxurl, data,
                function(resp){
                    $.fancybox.hideLoading();
                    if(typeof resp.error != 'undefined'){
                        alert(resp.error);
                    }
                    if(typeof resp.done != 'undefined'){
                        OP.disable_alert = true;
                        window.location.reload();
                    }
                },
                'json'
            );
        });
        // end colours

        $('#le-layouts-dialog').submit(function(e){
            e.preventDefault();
            var selected = $(this).find(':radio:checked').val();
            $.fancybox.close();
            $.fancybox.showLoading();
            var opts = {
                action: OptimizePress.SN+'-live-editor-get-layout',
                _wpnonce: $('#_wpnonce').val(),
                layout: selected,
                page_id: $('#page_id').val(),
                keep_options: []
            };
            $('#content_layout_keep_options :checkbox:checked').each(function(){
                opts.keep_options.push($(this).val());
            });
            $.post(OptimizePress.ajaxurl,opts,
                function(resp){
                    $.fancybox.hideLoading();
                    if(typeof resp.error != 'undefined'){
                        alert(resp.error);
                    }
                    if(typeof resp.done != 'undefined'){
                        OP.disable_alert = true;
                        window.location.reload();
                    }
                },
                'json'
            );
        });

        // $('#op_export_content').delegate('a.delete-file','click',function(e){
        $body.on('click', '#op_export_content a.delete-file', function(e){
            e.preventDefault();
            var waiting = $(this).parent().prev().fadeIn('fast'),
                data = {
                    action: OptimizePress.SN+'-live-editor-deleted-exported-layout',
                    _wpnonce: $('#_wpnonce').val(),
                    filename: $('#zip_filename').val()
                };
            $.post(OptimizePress.ajaxurl,data,function(resp){
                waiting.fadeOut('fast');
                if(typeof resp.error != 'undefined'){
                    alert(resp.error);
                }
                $('#op_export_content').html('');
            },'json');
        });

        // $('#export_layout_submit').click(function(e){
        $body.on('click', '#export_layout_submit', function(e){
            e.preventDefault();
            $('#op_export_content').html('');
            var waiting = $(this).next().fadeIn('fast'),
                data = {
                    action: OptimizePress.SN+'-live-editor-export-layout',
                    status: $('#op-live-editor-status').val(),
                    _wpnonce: $('#_wpnonce').val(),
                    layout_name: $('#export_layout_name').val(),
                    layout_description: $('#export_layout_description').val(),
                    layout_category: $('#export_layout_category').val(),
                    image: $('#export_layout_image_path').val(),
                    page_id: $('#page_id').val(),
                    op: {},
                    layouts: {}
                };

            $('div.editable-area').each(function(){
                var l = $(this).data('layout');
                data.layouts[l] = get_layout_array($(this));
            });
            if(typeof OP_Feature_Area != 'undefined'){
                data.feature_area = OP_Feature_Area;
            }
            var dialogs = ['typography','settings'];
            for(var i=0,dl=dialogs.length;i<dl;i++){
                $.extend(data.op,serialize($('#le-'+dialogs[i]+'-dialog')).op || {});
            }

            $.post(OptimizePress.ajaxurl,data,
                function(resp){
                    waiting.fadeOut('fast');
                    if(typeof resp.error != 'undefined'){
                        alert(resp.error);
                    }
                    if(typeof resp.output != 'undefined'){
                        $('#op_export_content').html(resp.output);
                    }
                },
                'json'
            );
        });

        $('#op_header_layout_nav_bar_alongside_enabled').change(function(){
            $('#advanced_colors_nav_bar_alongside').toggle($(this).is(':checked'));
        }).trigger('change');

        $('#op_footer_area_enabled').change(function(){
            $('#advanced_colors_footer').toggle($(this).is(':checked'));
        }).trigger('change');

        /**
         * The $('#op_footer_area_enabled').trigger() - line above - triggers iButton change in common.js::init_hidden_panels() and therefore opens footer area upon page load, which is not a desired behaviour.
         * This is the fix that hides the area afterwards, but doesn't change the behaviour attached to initial change event.
         */
        $('#op_footer_area_enabled').parentsUntil('.section-footer_area', '.op-bsw-grey-panel-header').next().hide();

        $('#op_header_layout_nav_bar_below_enabled').change(function(){
            $('#advanced_colors_nav_bar_below').toggle($(this).is(':checked'));
        }).trigger('change');

        $('#op_header_layout_nav_bar_above_enabled').change(function(){
            $('#advanced_colors_nav_bar_above').toggle($(this).is(':checked'));
        }).trigger('change');
    };

    function init_presets_dialogs(){
        // $('#op-save-preset').click(function(e){
        $body.on('click', '#op-save-preset', function(e){
            e.preventDefault();
            $.fancybox.open($.extend({},fancy_defaults,{
                type: 'inline',
                href: '#le-presets-dialog'
            }));
        });
        $('#le-presets-dialog').submit(function(e){
            e.preventDefault();
            var data = {
                action: OptimizePress.SN+'-live-editor-save-preset',
                status: $('#op-live-editor-status').val(),
                _wpnonce: $('#_wpnonce').val(),
                page_id: $('#page_id').val(),
                op: {},
                preset: serialize($('#le-presets-dialog')),
                layouts: {}
            };
            $('div.editable-area').each(function(){
                var l = $(this).data('layout');
                data.layouts[l] = get_layout_array($(this));
            });
            if(typeof OP_Feature_Area != 'undefined'){
                data.feature_area = OP_Feature_Area;
            }
            var dialogs = ['typography','settings'];
            for(var i=0,dl=dialogs.length;i<dl;i++){
                $.extend(data.op,serialize($('#le-'+dialogs[i]+'-dialog')).op || {});
            }
            $.post(OptimizePress.ajaxurl,data,
                function(resp){
                    if(typeof resp.error != 'undefined'){
                        alert(resp.error);
                    } else {
                        if(typeof resp.preset_dropdown != 'undefined'){
                            $('#preset_save').html(resp.preset_dropdown);
                            $('#preset_type').val('overwrite').trigger('change');
                        }
                        alert(OP_AB.translate('saved'));
                        $.fancybox.close();
                    }
                },
                'json'
            );
        });
    };

    /**
     * 0 = #epicbox-overlay
     * 1 = #epicbox
     * 2 = .epicbox-content
     * 3 = .epicbox-scroll
     * 4 = .epicbox-actual-content
     */
    function resize_epicbox(){
        //epicbox[3].css("height",epicbox[3].innerHeight() + "px");
        if (epicbox && epicbox[1] && epicbox[2] && epicbox[3]) {
            epicbox[1].height(epicbox[3].outerHeight());
            epicbox[2].height(epicbox[1].height());
            epicbox[1].css("margin-top","-" +  epicbox[1].innerHeight() / 2 + "px");
        }
    }

    function init_child_elements(){

        //$('.element-container a.add-new-element').live('click',function(e){
        $body.on('click', '.element-container a.add-new-element', function(e){
            var $t = $(this),
                w = parseInt($t.closest('.cols').width(), 10) + 40;
            e.preventDefault();

            // Popup/OverlayOptimizer epicbox must be sized differently, regardless of current column width.
            if ($t.parent().parent().hasClass('op-popup')) {

                // 700 is the default popup width
                w = parseInt($t.parent().parent().data('width'), 10) || 700;

                // To account for padding/margin of the epicbox
                w = w + 40;

                // To make sure the epicbox never goes out of window
                w = w >= $(window).width() ? $(window).width() - 40 : w;

            }

            resize_epicbox();
            epicbox[3].html('');
            epicbox[2].css('background','url(images/wpspin_light.gif) no-repeat center center');
            epicbox[0].add(epicbox[1]).fadeIn();
            epicbox[1].width(w).css('margin-left',-(w/2)+'px');
            if (w < 400) {
                epicbox[1].addClass('epicbox-narrow');
            }
            cur_child = $t.closest('.element').find('textarea.op-le-child-shortcode');
            op_cur_html = epicbox[3];
            $.post(OptimizePress.ajaxurl,
                {
                    action: OptimizePress.SN+'-live-editor-parse',
                    _wpnonce: $('#_wpnonce').val(),
                    shortcode: cur_child.val(),
                    depth: 1,
                    page_id: $('#page_id').val()
                },
                function(resp){
                    if(typeof resp.output != 'undefined'){
                        if (typeof resp.font != 'undefined' && resp.font !== '' && resp.font[0] === 'google') {
                            WebFont.load({google:{families:[resp.font[1] + resp.font[2].properties]}});
                        }

                        //.epicbox-content
                        epicbox[2]
                            .css('background','none')
                            .addClass('op_no_outline')
                            .attr('tabindex', 0)
                            .focus();

                        //.epicbox-scroll
                        epicbox[3].html(resp.output+resp.js);

                        //.epicbox-actual-content
                        epicbox[4] = $('.epicbox-actual-content',epicbox[3]);

                        resize_epicbox();
                        init_child_sortables();
                        init_child_previews();
                    }
                },
                'json'
            );
        });

        //$('.op-element-links a.element-delete',epicbox[3]).live('click',function(e){
        epicbox[3].on('click', '.op-element-links a.element-delete', function(e){
            e.preventDefault();
            confirm('Are you sure you wish to remove this element?') && $(this).closest('.row').remove() && resize_epicbox();
        });

        //$('a.add-new-element',epicbox[3]).live('click',function(e){
        epicbox[3].on('click', 'a.add-new-element', function(e){
            e.preventDefault();
            $('#op_asset_browser_container').addClass('hide-elements-with-child-elements');
            child_element = true;
            var prev = $(this).prev();
            cur = [$(this),'before'];
            refresh_item = null;
            OP_AB.open_dialog();

            /**
             * We want to refresh the visible items list at this step.
             * Otherwise, for example, if you enter feature box and then try to add an element to it,
             * you can end up with an empty list and no message to indicate that no elements matching the search.
             */
            $('#op_assets_filter').val('').trigger('keyup');
        });

        //$('.op-element-links a.element-settings',epicbox[3]).live('click',function(e){
        epicbox[3].on('click', '.op-element-links a.element-settings', function(e){
            e.preventDefault();
            var el = $(this).closest('.row');
            cur = [el,'replaceWith'];
            child_element = true;
            edit_element(el,false);
        });

        //$('.close',epicbox[1]).click(function(e){
        epicbox[1].on('click', '.close', function(e){
            e.preventDefault();
            child_element = false;
            $('#op_asset_browser_container').removeClass('hide-elements-with-child-elements');
            epicbox[0].add(epicbox[1]).fadeOut();
        });

        $('#op_child_elements_form').submit(function(e){

            e.preventDefault();

            // If this is a popup, handle it differently.
            var popupContent = '';
            var popupButton = '';
            var popupElement = false;
            var popupElements = '';
            var out = '';
            var $popupContentTextarea = '';

            if ($(this).find('.op_popup_element_present').length > 0) {
                popupElement = true;
            }

            out = '[op_liveeditor_elements] ';
            if (popupElement) {
                out = '[op_popup_elements]';
                popupElements = '[op_popup_elements]';
            }

            $(this).find('textarea.op-le-child-shortcode').each(function(){

                var thisPopupElement = $(this).val().indexOf('[op_popup_button]') === 0 ? true : false;
                var entireShortcode = '';
                // var thisPopupElement = $(this).val().indexOf('[op_popup_elements]') === 0 ? true : false;

                if (!thisPopupElement) {

                    if (!popupElement) {

                        out += '[op_liveeditor_element data-style="' + ($(this).parent().parent().attr('data-style') || '') + '"]';
                        out +=      $(this).val();
                        out += '[/op_liveeditor_element] ';

                    } else {

                        if ($(this).val().indexOf('[op_popup_elements]') < 0) {
                            popupContent += '[op_popup_content_element data-style="' + ($(this).parent().parent().attr('data-style') || '') + '"]';
                            popupContent +=     $(this).val();
                            popupContent += '[/op_popup_content_element]';
                        } else {
                            $popupContentTextarea = $(this);
                        }

                    }

                } else {

                    popupButton += $(this).val();

                }

            });

            if (!popupElement) {
                out += '[/op_liveeditor_elements] ';
            }

            if (popupElement) {

                popupElements += popupContent;
                popupElements += '[/op_popup_elements]';
                $popupContentTextarea.val(popupElements);

                out += '[op_popup_button]';
                popupButton = popupButton.split(/\[op_popup_button\](.*)\[\/op_popup_button\]/gi);
                popupButton = popupButton[1];

                out += popupButton;
                out += '[/op_popup_button]';
                out += '[op_popup_content]';
                out += popupContent;
                out += '[/op_popup_content]';
                out += '[/op_popup_elements]';

                cur_child.closest('.element-container').find('.op-le-child-shortcode').val(popupElements);
                entireShortcode = cur_child.closest('.element-container').find('.op-le-shortcode').val();
                entireShortcode = entireShortcode.replace(/\[op_popup_content[ d|\]][\s\S]*\[\/op_popup_content\]/gi, '[op_popup_content]'+popupContent+'[/op_popup_content]');
                cur_child.closest('.element-container').find('.op-le-shortcode').val(entireShortcode);
                cur_child.closest('.element-container').find('.op-le-shortcode').text(entireShortcode);

            } else {

                cur_child.val(out);
                refresh_element(cur_child);

            }

            child_element = false;
            $('.close',epicbox[1]).trigger('click');

        });
    };

    function close_wysiwygs(){
        if(editor_switch && typeof this.content != 'string'){
            this.content.find('.wp-editor-area').each(function(){
                var id = $(this).attr('id');
                if(id != 'opassetswysiwyg'){
                    $('#'+id+'-tmce').trigger('click');
                    //var content = OP_AB.wysiwyg_content(id);
                    tinyMCE.execCommand('mceFocus', false, id);
                    if (tinyMCE.majorVersion > 3) {
                        tinyMCE.execCommand('mceRemoveEditor', false, id);
                    } else {
                        tinyMCE.execCommand('mceRemoveControl', false, id);
                    }
                    //$(this).val(content);
                }
            });
        }
    };

    function init_child_sortables(ref){
        var ref = ref || false;
        if(ref){
            epicbox[4].sortable('refresh').disableSelection();
        } else {
            epicbox[4].sortable($.extend({},sort_default,{
                handle:'.op-element-links .element-move',
                items:'div.row',
                update: null
            })).disableSelection();
        }
    };

    // If needed, initialize previews for elements that have live previews instead of static images.
    // This is called upon epicbox open
    function init_child_previews() {
        OP.initCountdownElements();
    }

    function get_full_shortcode(c){
        var textarea = c.find('textarea.op-le-shortcode'),
            sc = textarea.text();
        if (!sc) {
            sc = textarea.val();
        }
        if (!sc) {
            sc = '';
        }
        var reg = new RegExp('#OP_CHILD_ELEMENTS#');
        c.find('textarea.op-le-child-shortcode').each(function(){
            sc = sc.replace(reg,$(this).val());
        });

        return sc;
    };
    function refresh_element(text){
        text.text('');
        var c = text.closest('.element-container'),
            sc = get_full_shortcode(c),
            el = c.find('.element:first'),
            waiting = c.find('.op-waiting'),
            elDataStyle;
        op_cur_html = c;
        el.fadeOut('fast').html('');
        waiting.fadeIn('fast').end().find('.op-show-waiting').fadeIn('fast');
        $.post(OptimizePress.ajaxurl,
            {
                action: OptimizePress.SN+'-live-editor-parse',
                _wpnonce: $('#_wpnonce').val(),
                shortcode: sc,
                depth: 0,
                page_id: $('#page_id').val()
            },
            function(resp){
                if(typeof el != 'undefined' && typeof resp.output != 'undefined'){
                    if (typeof resp.font != 'undefined' && resp.font !== '' && resp.font[0] === 'google') {
                        WebFont.load({google:{families:[resp.font[1] + resp.font[2].properties]}});
                    }

                    if (op_cur_html.attr('data-style')) {
                        elDataStyle = JSON.parse(atob(op_cur_html.attr('data-style')));
                    } else {
                        elDataStyle = {};
                    }
                    elDataStyle.codeBefore = elDataStyle.codeBefore || '';
                    elDataStyle.codeAfter = elDataStyle.codeAfter || '';
                    el.html(elDataStyle.codeBefore + resp.output+resp.js + elDataStyle.codeAfter);

                    var area = c.closest('.editable-area');
                    refresh_sortables(area);
                    if(typeof resp.shortcode != 'undefined'){
                        text.val(resp.shortcode);
                    }
                    waiting.fadeOut('fast',function(){
                        el.fadeIn('fast');
                    });
                    custom_item_ids(area);
                    init_child_previews();
                }
            },
            'json'
        );
    };
    function custom_item_ids(container){
        container.each(function(idx){
            var $t = $(this),
                id = $t.attr('id'),
                pref = 'le_'+(id == 'footer_area' ? 'footer' : (id == 'content_area' ? 'body' : id ))+'_row_',
                rcounter = 1;
            $(this).find('> .row').each(function(){
                var ccounter = 1;
                $(this).attr('id',pref+rcounter).find('> .cols').each(function(){
                    var ecounter = 1;
                    $(this).attr('id',pref+rcounter+'_col_'+ccounter).find('> .element-container:not(.sort-disabled)').each(function(){
                        $(this).attr('id',pref+rcounter+'_col_'+ccounter+'_el_'+ecounter);
                        ecounter++;
                    });
                    ccounter++;
                });
                rcounter++;
            });
        });
    };
    /**
     * NEW FUNCTION
     */
    function get_layout_array(el){
        var data = [],
            nr = 0;
        if(el.length > 0){
            data = [];
        }
        el.find('> .row').each(function(){
            var therow = $(this),
                row = {
                    row_class: therow.attr('class'),
                    row_style: therow.attr('style'),
                    row_data_style: therow.attr('data-style'),
                    children: []
                };
            therow.find('> > .cols:not(.sort-diabled)').each(function(){
                var thecol = $(this),
                    col = {
                        col_class: thecol.attr('class'),
                        type: 'column',
                        children: []
                    },
                    rchild = {
                        type: '',
                        object: {}
                    },
                    schild = {
                        type : '',
                        object : {}
                    };

                thecol.find('> div:not(.sort-disabled, .add-element-container)').each(function() {
                    var cchild = {
                        type: '',
                        object: {}
                    };
                    if ($(this).hasClass('element-container')) {
                        cchild.type = 'element';
                        cchild.object = get_full_shortcode($(this));
                        cchild.element_class = $(this).attr('class');
                        if (!$(this).hasClass('cf')) {
                            $(this).addClass('cf');
                        }
                        cchild.element_data_style = $(this).attr('data-style');
                        col.children.push(cchild);
                    } else if ($(this).hasClass('subcol')) {
                        var thesubcol = $(this),
                        subcol = {
                            subcol_class: $(this).attr('class'),
                            type: 'subcolumn',
                            children: []
                        };
                        thesubcol.find('> div:not(.sort-disabled, .add-element-container)').each(function() {
                            var thesubcolel = $(this),
                                gchild = {
                                    type: '',
                                    object: {}
                                };
                            gchild.type = 'element';
                            gchild.object = get_full_shortcode(thesubcolel);
                            gchild.element_class = $(this).attr('class');
                            gchild.element_data_style = $(this).attr('data-style');
                            subcol.children.push(gchild);
                        });
                        col.children.push(subcol);
                    }
                });
                rchild.type = 'column';
                rchild.object = col;
                row.children.push(rchild);
            });
            data.push(row);
        });

        return data;
    };
    function save_content(callback){
        var data = {
            action: OptimizePress.SN+'-live-editor-save',
            status: $('#op-live-editor-status').val(),
            _wpnonce: $('#_wpnonce').val(),
            page_id: $('#page_id').val(),
            op: {},
            layouts: {}
        };

        op_show_loading();

        $('div.editable-area').each(function(){
            var l = $(this).data('layout');
            data.layouts[l] = get_layout_array($(this));
        });
        if(typeof OP_Feature_Area != 'undefined'){
            data.feature_area = OP_Feature_Area;
        }
        var dialogs = ['typography','settings'];
        for(var i=0,dl=dialogs.length;i<dl;i++){
            $.extend(data.op,serialize($('#le-'+dialogs[i]+'-dialog')).op || {});
        }
        $.post(OptimizePress.ajaxurl,data,
            function(resp){
                op_hide_loading();
                if(typeof resp.error != 'undefined'){
                    alert(resp.error);
                    window.op_dont_hide_loading = false;
                    op_hide_loading();
                } else if($.isFunction(callback)){
                    if (!OP.disable_alert) {
                        alert(OP_AB.translate('saved'));
                    }
                    callback();
                } else {
                    if (!OP.disable_alert) {
                        alert(OP_AB.translate('saved'));
                    }
                    OP.disable_alert = false;
                    window.op_dont_hide_loading = false;
                    op_hide_loading();
                }
            },
            'json'
        );
    };
    function init_editable_area(container,prefix){
        prefix = prefix || '';
        prefix = prefix == '' ? '' : prefix+'-';
        if(container.data('one_col') == 'N'){
            container.append('<div id="'+prefix+'add-new-row" class="cf"><div class="'+prefix+'add-new-row-link"><div class="add-new-row-content"><a href="#op-le-row-select" class="add-new-button"><img src="'+OptimizePress.imgurl+'/live_editor/add_new.png" alt="'+translate('add_new_row')+'" /><span>'+translate('add_new_row')+'</span></a></div></div></div>');
            var el = $('a','#'+prefix+'add-new-row');
            el.fancybox($.extend({},fancy_defaults,{
                type: 'inline',
                href: '#op-le-row-select',
                beforeLoad: function(){
                    cur = [$('#'+prefix+'add-new-row'),'before'];
                }
            }));
            container.on('click', '.add-new-row', function(e){
                e.preventDefault();
                cur = [$(this).closest('.row'),'before'];
                $.fancybox.open($.extend({},fancy_defaults,{
                    type: 'inline',
                    href: '#op-le-row-select'
                }));
            });
            $body.on('mouseenter', '.cols', function(){
                $(this).find('.split-column').fadeIn(100);
            });
            $body.on('mouseleave', '.cols', function(){
                $(this).find('.split-column').fadeOut(100);
            });
            $body.on('click', '.split-column', function(e){
                e.preventDefault();
                var column_type = $(this).attr("href");
                column_type = column_type.substring(1);
                $('#op-le-split-column ul li').each(function(e){
                    $(this).hide();
                });
                switch (column_type) {
                    case 'one-half':
                        $('#op-le-split-column ul li a.split-half').parent().show();
                    break;
                    case 'two-thirds':
                        $('#op-le-split-column ul li a.split-half').parent().show();
                        $('#op-le-split-column ul li a.one-third-first').parent().show();
                        $('#op-le-split-column ul li a.one-third-second').parent().show();
                        $('#op-le-split-column ul li a.one-thirds').parent().show();
                        $('#op-le-split-column ul li a.one-fourth-first').parent().show();
                        $('#op-le-split-column ul li a.one-fourth-second').parent().show();
                    break;
                    case 'two-fourths':
                        $('#op-le-split-column ul li a.split-half').parent().show();
                    break;
                    case 'three-fourths':
                        $('#op-le-split-column ul li a.split-half').parent().show();
                        $('#op-le-split-column ul li a.one-third-first').parent().show();
                        $('#op-le-split-column ul li a.one-third-second').parent().show();
                        $('#op-le-split-column ul li a.one-thirds').parent().show();
                        $('#op-le-split-column ul li a.one-fourth-first').parent().show();
                        $('#op-le-split-column ul li a.one-fourth-second').parent().show();
                        $('#op-le-split-column ul li a.one-fourths').parent().show();
                    break;
                    case 'three-fifths':
                        $('#op-le-split-column ul li a.split-half').parent().show();
                        $('#op-le-split-column ul li a.one-third-first').parent().show();
                        $('#op-le-split-column ul li a.one-third-second').parent().show();
                        $('#op-le-split-column ul li a.one-thirds').parent().show();
                    break;
                    case 'four-fifths':
                        $('#op-le-split-column ul li a.split-half').parent().show();
                        $('#op-le-split-column ul li a.one-third-first').parent().show();
                        $('#op-le-split-column ul li a.one-third-second').parent().show();
                        $('#op-le-split-column ul li a.one-thirds').parent().show();
                        $('#op-le-split-column ul li a.one-fourth-first').parent().show();
                        $('#op-le-split-column ul li a.one-fourth-second').parent().show();
                        $('#op-le-split-column ul li a.one-fourths').parent().show();
                    break;
                }
                cur = [$(this).closest('.column'),'append'];
                $.fancybox.open($.extend({},fancy_defaults,{
                    type: 'inline',
                    href: '#op-le-split-column'
                }));
            });
            // $('.banner').click(function(e) {
            $body.on('click', '.banner', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $.fancybox.open($.extend({},fancy_defaults,{
                    type: 'inline',
                    href: '#le-headers-dialog'
                }));
            });
            // row options click
            //$('.edit-row').live('click', function(e){
            $body.on('click', '.edit-row', function(e){
                e.preventDefault();
                cur = [$(this).closest('.row'),'before'];
                if (cur[0].hasClass('section')) {
                    $('input[name="op_full_width_row"]').prop('checked', true);
                } else {
                    $('input[name="op_full_width_row"]').prop('checked', false);
                }
                cur_style = cur[0].attr('style');
                cur_data_style = cur[0].attr('data-style');

                // clearing old data
                $('#op_section_row_options_bgcolor_start').val('').trigger('change');
                $('#op_section_row_options_bgcolor_end').val('').trigger('change');
                $('#op_row_top_padding').val('');
                $('#op_row_before').val('');
                $('#op_row_after').val('');
                $('#op_row_css_class').val('');
                $('#op_row_bottom_padding').val('');
                $('#op_row_border_width').val('');
                $('#op_row_border_top_width').val('');
                $('#op_row_border_bottom_width').val('');
                $('#op_section_row_options_borderColor').val('').trigger('change');
                $('#op_section_row_options_borderTopColor').val('').trigger('change');
                $('#op_section_row_options_borderBottomColor').val('').trigger('change');

                if (cur_data_style) {

                    /*
                     * Row addons
                     */
                    $.each($('[id^="op_row_addon_"]'), function(i, item) {
                        if ($(item).is(':checkbox')) {
                            $(item).prop('checked', false);
                        } else if ($(item).hasClass('op-uploader-value')) {
                            OP_AB.set_uploader_value($(item).prop('id'), '');
                        } else if ($(item).prop('type') == 'number') {
                            $(item).val($(item).val());
                        } else if ($(item).hasClass('slider-item')) {
                            $(item).find('input').val(0);
                            $(item).slider('value', 0);
                            $(item).prev().find('span')
                                .text('0' + $(item).prev().find('span').data('unit'))
                                .attr('id', 'output_' + $(item).attr('id'));
                        } else {
                            $(item).val('');
                        }
                    });

                    OP_AB.set_uploader_value('op_row_background', '');
                    $(".op_row_bg_options option").each(function(){
                        $(this).attr("selected", false);
                    });
                    var obj = JSON.parse(atob(cur_data_style));
                    for (var key in obj) {
                        switch (key) {
                            case 'cssClass':
                                $('#op_row_css_class').val(obj[key]);
                                break;
                            case 'codeBefore':
                                $('#op_row_before').val(obj[key]);
                                break;
                            case 'codeAfter':
                                $('#op_row_after').val(obj[key]);
                                break;
                            case 'paddingTop':
                                $('#op_row_top_padding').val(obj[key]);
                                break;
                            case 'paddingBottom':
                                $('#op_row_bottom_padding').val(obj[key]);
                                break;
                            case 'backgroundImage':
                                var imgUrl = obj[key].slice(4, -1);
                                OP_AB.set_uploader_value('op_row_background', imgUrl);
                                break;
                            case 'backgroundPosition':
                                $('.op_row_bg_options option[value="'+obj[key]+'"]').attr('selected','selected');
                                break;
                            case 'borderWidth':
                                // We are splitting this option into two fields (border top & border bottom),
                                // so this ensures we don't lose any previously defined styles
                                // $('#op_row_border_width').val(obj[key]);
                                $('#op_row_border_top_width').val(obj[key]);
                                $('#op_row_border_bottom_width').val(obj[key]);
                                break;
                            case 'borderColor':
                                // $('#op_section_row_options_borderColor').val(obj[key]).trigger('change');
                                $('#op_section_row_options_borderTopColor').val(obj[key]).trigger('change');
                                $('#op_section_row_options_borderBottomColor').val(obj[key]).trigger('change');
                                break;
                            case 'borderTopWidth':
                                $('#op_row_border_top_width').val(obj[key]);
                                break;
                            case 'borderTopColor':
                                $('#op_section_row_options_borderTopColor').val(obj[key]).trigger('change');
                                break;
                            case 'borderBottomWidth':
                                $('#op_row_border_bottom_width').val(obj[key]);
                                break;
                            case 'borderBottomColor':
                                $('#op_section_row_options_borderBottomColor').val(obj[key]).trigger('change');
                                break;
                            case 'backgroundColorStart':
                                $('#op_section_row_options_bgcolor_start').val(obj[key]).trigger('change');
                                break;
                            case 'backgroundColorEnd':
                                $('#op_section_row_options_bgcolor_end').val(obj[key]).trigger('change');
                                break;
                            case 'addon':
                                $.each(obj[key], function(i, item) {
                                    var $element = $('#op_row_addon_' + i);
                                    if ($element.is(':checkbox')) {
                                        $element.prop('checked', item);
                                    } else if ($element.hasClass('op-uploader-value')) {
                                        OP_AB.set_uploader_value($element.prop('id'), item);
                                    } else if ($element.hasClass('slider-item')) {
                                        $element.slider('value', item);
                                        $element.prev().find('span').text(item + $element.prev().find('span').data('unit'));
                                    } else {
                                        $element.val(item).trigger('change');
                                    }
                                });
                                break;
                        }
                    }
                } else {

                    /*
                     * Row addons
                     */
                    $.each($('[id^="op_row_addon_"]'), function(i, item) {
                        if ($(item).is(':checkbox')) {
                            $(item).prop('checked', false);
                        } else if ($(item).hasClass('op-uploader-value')) {
                            OP_AB.set_uploader_value($(item).prop('id'), '');
                        } else if ($(item).prop('type') == 'number') {
                            $(item).val($(item).val());
                        } else if ($(item).hasClass('slider-item')) {
                            $(item).find('input').val(0);
                            $(item).slider('value', 0);
                            $(item).prev().find('span')
                                .text('0' + $(item).prev().find('span').data('unit'))
                                .attr('id', 'output_' + $(item).attr('id'));
                        } else {
                            $(item).val('');
                        }
                    });

                    OP_AB.set_uploader_value('op_row_background', '');
                    $(".op_row_bg_options option").each(function(){
                        $(this).attr("selected", false);
                    });
                }

                $.fancybox.open($.extend({},fancy_defaults,{
                    type: 'inline',
                    href: '#op-le-row-options'
                }));
            });
            // show/hide row paste buttons on focus
            $(window).on('focus', function(){
                togglePasteButtons();
            });
            // paste row click
            $body.on('click', '.paste-row', function(e){
                e.preventDefault();
                var rowToPaste = localStorage.getItem('op_row') || '';
                $(this).closest('.row').before(rowToPaste);
                custom_item_ids(container);
                refresh_sortables(container);
                $('.paste-row').show();
                //localStorage.removeItem('op_row');
            });
            // copy row click
            $body.on('click', '.copy-row', function(e){
                e.preventDefault();
                var el = $(this).closest('.row');
                var cloned = el.clone().attr('id', '');
                var i = 0;
                var j = 0;
                el.find('textarea.op-le-shortcode').each(function(){
                    var current = $(cloned.find('textarea.op-le-shortcode'));
                    var replace = $(this).val();
                    $(current[i]).val(replace);
                    $(current[i]).text(replace);
                    i++;
                });
                el.find('textarea.op-le-child-shortcode').each(function(){
                    var current = $(cloned.find('textarea.op-le-child-shortcode'));
                    var replace = $(this).val();
                    $(current[j]).val(replace);
                    $(current[j]).text(replace);
                    j++;
                });
                cloned.find('.element-container, .column').each(function(){
                    $(this).attr('id', '');
                });
                // let's save the row in local storage!
                try {
                    localStorage.setItem('op_row', cloned[0].outerHTML);
                    $('.paste-row').show();
                    alert('Row succesfully copied');
                } catch (ex) {
                    alert('Session Storage not supported or you tried to copy row that is too big');
                }
            });
            // clone row click
            $body.on('click', '.clone-row', function(e){
                var el = $(this).closest('.row');
                var cloned = el.clone().attr('id', '');
                var i = 0;
                var j = 0;
                el.find('textarea.op-le-shortcode').each(function(){
                    var current = $(cloned.find('textarea.op-le-shortcode'));
                    var replace = $(this).val();
                    $(current[i]).val(replace);
                    i++;
                });
                el.find('textarea.op-le-child-shortcode').each(function(){
                    var current = $(cloned.find('textarea.op-le-child-shortcode'));
                    var replace = $(this).val();
                    $(current[j]).val(replace);
                    j++;
                });
                cloned.find('.element-container, .column').each(function(){
                    $(this).attr('id', '');
                });
                el.after(cloned);
                custom_item_ids(container);
                refresh_sortables(container);
            });
            //$('a.delete-row',container).live('click',function(e){
            container.on('click', 'a.delete-row', function(e){
                e.preventDefault();
                if (confirm('Are you sure you wish to remove this row and all its elements?')) {
                    var cur = $(this).closest('.row');
                    cur.prev('.op-row-code-before').remove();
                    cur.next('.op-row-code-after').remove();
                    cur.remove();
                }
            });
        }
        custom_item_ids(container);
        refresh_sortables(container);

        //container.find('a.move-row,a.element-move').live('click',function(e){
        container.on('click', 'a.move-row,a.element-move', function(e){
            e.preventDefault();
        });
        // container.find('.cols > .add-element-container > a.add-new-element').click(function(e){
        container.on('click', '.cols > .add-element-container > a.add-new-element', function(e){
            e.preventDefault();
            child_element = false;
            cur = [$(this).parent().prev(),'before'];
            refresh_item = null;
            OP_AB.open_dialog();

            /**
             * We want to refresh the visible items list at this step.
             * Otherwise, for example, if you enter feature box and then try to add an element to it,
             * you can end up with an empty list and no message to indicate that no elements matching the search.
             */
            $('#op_assets_filter').trigger('keydown');
        });
        //$('.op-element-links a.element-delete',container).live('click',function(e){
        container.on('click', '.op-element-links a.element-delete', function(e){
            e.preventDefault();
            confirm('Are you sure you wish to remove this element?') && $(this).closest('.element-container').remove();
        });
        //$('.op-element-links a.element-settings',container).live('click',function(e){
        container.on('click', '.op-element-links a.element-settings', function(e){
            e.preventDefault();
            var el = $(this).closest('.element-container');
            var child = el.find('> .element a.add-new-element');
            if (child.length) {
                child.trigger('click');
            } else {
                cur = [el,'replaceWith'];
                refresh_item = el.find('textarea.op-le-shortcode');
                edit_element(refresh_item.closest('.element-container'));
            }
        });

        // advanced element options click
        $body.off('click', '.op-element-links a.element-advanced');
        $body.on('click', '.op-element-links a.element-advanced', function(e){
            e.preventDefault();
            cur = [$(this).closest('.element-container')];
            if (cur[0].hasClass('hide-mobile')) {
                $('input[name="op_hide_phones"]').prop('checked', true);
            } else {
                $('input[name="op_hide_phones"]').prop('checked', false);
            }
            if (cur[0].hasClass('hide-tablet')) {
                $('input[name="op_hide_tablets"]').prop('checked', true);
            } else {
                $('input[name="op_hide_tablets"]').prop('checked', false);
            }
            cur_data_style = cur[0].attr('data-style');

            /**
             * Due to oldish bug it is possible that cur_data_style is set to string undefined
             */
            if (cur_data_style && cur_data_style !== 'undefined') {
                // clearing old data
                $('#op_advanced_fadein').val('');
                $('#op_advanced_code_before').val('');
                $('#op_advanced_code_after').val('');
                $('#op_advanced_class').val('');
                var obj = JSON.parse(atob(cur_data_style));
                for (var key in obj) {
                    switch (key) {
                        case 'codeBefore':
                            $('#op_advanced_code_before').val(obj[key]);
                        break;
                        case 'codeAfter':
                            $('#op_advanced_code_after').val(obj[key]);
                        break;
                        case 'fadeIn':
                            $('#op_advanced_fadein').val(obj[key]);
                        break;
                        case 'advancedClass':
                            $('#op_advanced_class').val(obj[key]);
                        break;
                    }
                }
            } else {
                // clearing old data
                $('#op_advanced_fadein').val('');
                $('#op_advanced_code_before').val('');
                $('#op_advanced_code_after').val('');
                $('#op_advanced_class').val('');
            }
            $.fancybox.open($.extend({},fancy_defaults,{
                type: 'inline',
                href: $(this).attr('href')
            }));
        });

        // advanced element option Update button click
        //$('#op-le-advanced-update').click(function(e){
        $body.off('click', '#op-le-advanced-update');
        $body.on('click', '#op-le-advanced-update', function (e) {
            var dataStyles = {};
            var html = '';
            var before = '';
            var after = '';
            var markup = '';
            var childHideClasses = '';
            var childHideMobile = cur[0].hasClass('hide-mobile');
            var childHideTablet = cur[0].hasClass('hide-tablet');

            e.preventDefault();

            if (cur[0].hasClass('row')) {
                cur[0].removeClass().addClass('row element-container cf');
            } else {
                cur[0].removeClass().addClass('element-container cf');
            }

            if ($('#op_advanced_class').val()) {
                cur[0].addClass($('#op_advanced_class').val());
                dataStyles.advancedClass = $('#op_advanced_class').val();
            }

            if ($('input[name="op_hide_phones"]:checked').length > 0) {
                cur[0].addClass('hide-mobile');
                dataStyles.hideMobile = 1;
            } else {
                cur[0].removeClass('hide-mobile');
            }
            if ($('input[name="op_hide_tablets"]:checked').length > 0) {
                cur[0].addClass('hide-tablet');
                dataStyles.hideTablet = 1;
            } else {
                cur[0].removeClass('hide-tablet');
            }
            if ($('#op_advanced_fadein').val()) {
                dataStyles.fadeIn = $('#op_advanced_fadein').val();
            }

            if ($('#op_advanced_code_before').val()) {
                before = $('#op_advanced_code_before').val();
            }
            if ($('#op_advanced_code_after').val()) {
                after = $('#op_advanced_code_after').val();
            }

            cur[0].find(".element, .op-hidden, .op-element-links, .op-waiting").each(function (i, item) {

                var g = $(item);
                var child_data_styles = '';
                var op_le_class;
                var elementContent;
                var value;

                if (g.hasClass('element')) {
                    elementContent = before + $(g[0].innerHTML)[0].outerHTML + after;
                    if (g.find('> .op-popup').length > 0) {
                        markup += '<div class="element">' + elementContent + '';
                    } else {
                        markup += '<div class="element">' + elementContent + '</div>';
                    }
                } else {
                    if (g.hasClass('op-hidden') && !g.hasClass('op-waiting')) {

                        op_le_class = (!g.find(' > textarea').hasClass('op-le-child-shortcode')) ? 'op-le-shortcode' : 'op-le-child-shortcode';

                        value = g.find('> textarea').val();
                        markup += '<div class="op-hidden">';
                            markup += '<textarea name="shortcode[]" class="' + op_le_class + '">';
                                markup += value;
                            markup += '</textarea>';
                        markup += '</div>';

                        if (g.prev().hasClass('op-popup')) {
                            markup += '</div>';
                        }
                    } else {
                        markup += g[0].outerHTML;
                    }
                }
            });

            cur[0].html(markup);

            if ($('#op_advanced_code_before').val()) {
                dataStyles.codeBefore = $('#op_advanced_code_before').val();
            }
            if ($('#op_advanced_code_after').val()) {
                dataStyles.codeAfter = $('#op_advanced_code_after').val();
            }
            var base = btoa(JSON.stringify(dataStyles));
            cur[0].attr('data-style', base);
            $.fancybox.close();
        });

        container.on('click', '.op-element-links a.element-parent-settings', function(e){
            e.preventDefault();
            var el = $(this).closest('.element-container');
            cur = [el,'replaceWith'];
            refresh_item = el.find('textarea.op-le-shortcode');
            edit_element(refresh_item.closest('.element-container'));
        });

        // clone element click
        $body.off('click', '.op-element-links a.element-clone');
        $body.on('click', '.op-element-links a.element-clone', function(e){
            e.preventDefault();
            var el = $(this).closest('.element-container');
            var value = el.find('textarea.op-le-shortcode').val();
            var childValue = el.find('textarea.op-le-child-shortcode').val();
            var cloned = el.clone(true, true).attr('id', '');
            cloned.find('textarea.op-le-shortcode').val(value);
            cloned.find('textarea.op-le-child-shortcode').val(childValue);
            el.after(cloned);
        });
    };

    function edit_element(el,get_full){
        get_full = get_full === false ? false : true;
        var sc = get_full ? get_full_shortcode(el) : el.find('textarea.op-le-child-shortcode').val();

        // Older child elements (created by default in OP < 2.1.5) are not wrapped into element container div,
        // and therefore are not generated with proper shortcode. This is the fix for this issue.
        if (!get_full && el.find('.element > p').length > 0) {
            sc = '[text_block]' + sc + '[/text_block]';
        }

        OP_AB.open_dialog(0);
        $.post(OptimizePress.ajaxurl,
            {
                action: OptimizePress.SN+'-live-editor-params',
                _wpnonce: $('#_wpnonce').val(),
                shortcode: sc
            },
            function(resp){
                if(typeof resp.error != 'undefined'){
                    alert(resp.error);
                } else {
                    if ($('.fancybox-wrap').length > 0) {
                        OP_AB.edit_element(resp);
                    }
                }
            },
            'json'
        );
    };

    function serialize(el){
        if(!el.length){
            return false;
        }
        var data = {},
            lookup = data;
        el.find(':input[type!="checkbox"][type!="radio"][type!="submit"], input:checked').each(function(){
            var name = this.name.replace(/\[([^\]]+)?\]/g,',$1').split(','),
                cap = name.length-1,
                i = 0;
            if(name[0]){
                for(;i<cap;i++)
                    lookup= lookup[name[i]] = lookup[name[i]] || (name[i+1] == '' ? [] : {});
            }
            if(typeof lookup.length != 'undefined')
                lookup.push($(this).val());
            else
                lookup[name[cap]] = $(this).val();
            lookup = data;
        });
        return data;
    };
    /***********Column split************/
    function split_column(selected){
        if(cur.length == 0){
            alert('Could not find the current position, please try clicking the Split Column link again.');
            return;
        }
        var h = selected.attr('href').split('#')[1],
            row_class = '',
            cols = [];
            isTextOnButton = [];
        if(selected.length > 0){
            switch(h){
                case 'split-half':
                    cols = ['split-half', 'split-half'];
                    isTextOnButton = [true, true];
                break;
                case 'one-third-second':
                    cols = ['split-two-thirds', 'split-one-third'];
                    isTextOnButton = [true, false];
                break;
                case 'one-third-first':
                    cols = ['split-one-third', 'split-two-thirds'];
                    isTextOnButton = [false, true];
                break;
                case 'one-thirds':
                    cols = ['split-one-third', 'split-one-third', 'split-one-third'];
                    isTextOnButton = [false, false, false];
                break;
                case 'one-fourth-second':
                    cols = ['split-three-fourths', 'split-one-fourth'];
                    isTextOnButton = [true, false];
                break;
                case 'one-fourth-first':
                    cols = ['split-one-fourth', 'split-three-fourths'];
                    isTextOnButton = [false, true];
                break;
                case 'one-fourths':
                    cols = ['split-one-fourth', 'split-one-fourth', 'split-one-fourth', 'split-one-fourth'];
                    isTextOnButton = [false, false, false, false];
                break;
            }
            var html = '';
            for(var i=0,cl=cols.length;i<cl;i++){
                var btnClass = '';
                if (isTextOnButton[i]) btnText = '<span>Add Element</span>'; else btnText = '';
                html += '<div class="'+cols[i]+' column cols subcol"><div class="element-container sort-disabled"></div><div class="add-element-container"><a href="#add_element" class="add-new-element"><img src="'+OptimizePress.imgurl+'/live_editor/add_new.png" alt="Add New Element" />' + btnText + '</a></div></div>';
            }
            btnText = '<span>Add element</span>';
            html += '</div></div><div class="clearcol"></div>';
            html += '<div class="element-container sort-disabled"></div><div class="add-element-container"><a href="#add_element" class="add-new-element"><img src="'+OptimizePress.imgurl+'/live_editor/add_new.png" alt="Add New Element" />' + btnText + '</a></div>';
            html = $(html);
            cur[0][cur[1]](html);
            // cur[0].find('.add-element-container > a.add-new-element').click(function(e){
            cur[0].on('click', '.add-element-container > a.add-new-element', function(e){
                e.preventDefault();
                child_element = false;
                cur = [$(this).parent().prev(),'before'];
                refresh_item = null;
                OP_AB.open_dialog();
            });
            var area = cur[0].closest('.editable-area');
            refresh_sortables(area);
            custom_item_ids(area);
            $.fancybox.close();
        } else {
            alert('Please select split column type');
        }
    };
    /***********************/
    function add_new_row(selected){
        if(cur.length == 0){
            alert('Could not find the current position, please try clicking the Add new row link again.');
            return;
        }
        var h = selected.attr('href').split('#')[1],
            row_class = '',
            cols = [];
            isTextOnButton = [];
        if(selected.length > 0){
            switch(h){
                case 'one-col':
                    row_class = 'one-column';
                    cols = ['one-column'];
                    isTextOnButton = [true];
                    break;
                case 'two-col':
                    row_class = 'two-columns';
                    cols = ['one-half','one-half'];
                    isTextOnButton = [true, true];
                    break;
                case 'three-col':
                    row_class = 'three-columns';
                    cols = ['one-third','one-third','one-third'];
                    isTextOnButton = [true, true, true];
                    break;
                case 'four-col':
                    row_class = 'four-columns';
                    cols = ['one-fourth','one-fourth','one-fourth','one-fourth'];
                    isTextOnButton = [false, false, false, false];
                    break;
                case 'five-col':
                    row_class = 'five-columns';
                    cols = ['one-fifth','one-fifth','one-fifth','one-fifth','one-fifth'];
                    isTextOnButton = [false, false, false, false, false];
                    break;
                default:
                    switch(h){
                        case '1':
                            row_class = 'three-columns';
                            cols = ['two-thirds','one-third'];
                            isTextOnButton = [true, true];
                            break;
                        case '2':
                            row_class = 'three-columns';
                            cols = ['one-third','two-thirds'];
                            isTextOnButton = [true, true];
                            break;
                        case '3':
                            row_class = 'four-columns';
                            cols = ['two-fourths','one-fourth','one-fourth'];
                            isTextOnButton = [true, false, false];
                            break;
                        case '4':
                            row_class = 'four-columns';
                            cols = ['one-fourth','one-fourth','two-fourths'];
                            isTextOnButton = [false, false, true];
                            break;
                        case '5':
                            row_class = 'four-columns';
                            cols = ['three-fourths','one-fourth'];
                            isTextOnButton = [true, false];
                            break;
                        case '6':
                            row_class = 'four-columns';
                            cols = ['one-fourth','three-fourths'];
                            isTextOnButton = [false, true];
                            break;
                        case '7':
                            row_class = 'five-columns';
                            cols = ['two-fifths','one-fifth','one-fifth','one-fifth'];
                            isTextOnButton = [true, false, false, false];
                            break;
                        case '8':
                            row_class = 'five-columns';
                            cols = ['one-fifth','one-fifth','one-fifth','two-fifths'];
                            isTextOnButton = [false, false, false, true];
                            break;
                        case '9':
                            row_class = 'five-columns';
                            cols = ['three-fifths','one-fifth','one-fifth'];
                            isTextOnButton = [true, false, false];
                            break;
                        case '10':
                            row_class = 'five-columns';
                            cols = ['one-fifth','one-fifth','three-fifths'];
                            isTextOnButton = [false, false, true];
                            break;
                        case '11':
                            row_class = 'five-columns';
                            cols = ['four-fifths','one-fifth'];
                            isTextOnButton = [true, false];
                            break;
                        case '12':
                            row_class = 'five-columns';
                            cols = ['one-fifth','four-fifths'];
                            isTextOnButton = [false, true];
                            break;
                        case '13':
                            row_class = 'four-columns';
                            cols = ['one-fourth','two-fourths','one-fourth'];
                            isTextOnButton = [false, true, false];
                            break;
                        case '14':
                            row_class = 'five-columns';
                            cols = ['two-fifths','three-fifths'];
                            isTextOnButton = [true, true];
                            break;
                        case '15':
                            row_class = 'five-columns';
                            cols = ['three-fifths','two-fifths'];
                            isTextOnButton = [true, true];
                            break;
                        case '16':
                            row_class = 'five-columns';
                            cols = ['one-fifth','two-fifths','two-fifths'];
                            isTextOnButton = [false, true, true];
                            break;
                        case '17':
                            row_class = 'five-columns';
                            cols = ['two-fifths','two-fifths','one-fifth'];
                            isTextOnButton = [true, true, false];
                            break;
                        case '18':
                            row_class = 'five-columns';
                            cols = ['one-fifth','three-fifths','one-fifth'];
                            isTextOnButton = [false, true, false];
                            break;
                    }
                    break;
            }
            var html = '';
            if (h.indexOf('feature_') === 0) { // feature areas
                $.fancybox.showLoading();
                var data1 = {
                    action: OptimizePress.SN+'-live-editor-get-predefined-template',
                    template: h,
                    page_id: $('#page_id').val()
                };
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: OptimizePress.ajaxurl,
                    data: data1
                }).done(function(data) {
                    if (data.output != 'error') {
                        html += data.output;
                        html = $(html);
                        html.find('.cols > .add-element-container > a.add-new-element').click(function(e){
                            e.preventDefault();
                            child_element = false;
                            cur = [$(this).parent().prev(),'before'];
                            refresh_item = null;
                            OP_AB.open_dialog();
                        });
                        cur[0][cur[1]](html);
                        var area = cur[0].closest('.editable-area');
                        refresh_sortables(area);
                        custom_item_ids(area);
                        $.fancybox.hideLoading();
                        $.fancybox.close();
                    }
                });
            } else { // normal row insert
                html += '<div class="row ' + row_class + ' cf"><div class="fixed-width"><div class="op-row-links"><div class="op-row-links-content"><a id="copy_row" href="#copy-row" class="copy-row"></a><a id="row_options" href="#options" class="edit-row"></a><a id="row_options" href="#clone-row" class="clone-row"></a><a href="#add-new-row" class="add-new-row"><img src="'+OptimizePress.imgurl+'/live_editor/add_new.png" alt="'+translate('add_new_row')+'" /><span>'+translate('add_new_row')+'</span></a><a href="#move" class="move-row"></a><a href="#paste-row" class="paste-row"></a><a href="#delete-row" class="delete-row"></a></div></div>';
                var splitColumn;
                for(var i=0,cl=cols.length;i<cl;i++){
                    var btnClass = '';
                    if (isTextOnButton[i]) btnText = '<span>Add Element</span>'; else btnText = '';
                    var narrowClass = '';
                    switch(cols[i]) {
                        case 'one-third':
                        case 'one-fourth':
                        case 'one-fifth':
                        case 'two-fifths':
                            narrowClass = ' narrow';
                        break;
                        default:
                            narrowClass = '';
                        break;
                    }
                    switch (cols[i]) {
                    case 'one-half':
                    case 'two-thirds':
                    case 'two-fourths':
                    case 'three-fourths':
                    case 'three-fifths':
                    case 'four-fifths':
                        splitColumn = '<a href="#'+cols[i]+'" class="split-column"><img src="'+OptimizePress.imgurl+'/live_editor/split_column.png" alt="Split Column" /></a>';
                        break;
                    default:
                        splitColumn = '';
                        break;
                    }
                    html += '<div class="'+cols[i]+' column cols'+narrowClass+'"><div class="element-container sort-disabled"></div><div class="add-element-container">' + splitColumn + '<a href="#add_element" class="add-new-element"><img src="'+OptimizePress.imgurl+'/live_editor/add_new.png" alt="Add New Element" />' + btnText + '</a></div></div>';
                }


                html += '</div></div>';
                html = $(html);
                html.find('.cols > .add-element-container > a.add-new-element').click(function(e){
                    e.preventDefault();
                    child_element = false;
                    cur = [$(this).parent().prev(),'before'];
                    refresh_item = null;
                    OP_AB.open_dialog();
                });
                cur[0][cur[1]](html);
                var area = cur[0].closest('.editable-area');
                refresh_sortables(area);
                custom_item_ids(area);
                if (!localStorage.getItem('op_row')) {
                    $('.paste-row').hide();
                }
                $.fancybox.close();
            }
        } else {
            alert('Please select a column type');
        }
    };

    function refresh_sortables(area){
        area.sortable($.extend({},sort_default,{
            handle:'.op-row-links .move-row',
            items:'> div.row',
            update: function(){
                custom_item_ids(area);
            }
        })).disableSelection();
        area.find('div.row:not(.element-container)').sortable($.extend({},sort_default,{
            handle:'.op-element-links .element-move',
            items:'div.element-container:not(.row)',
            connectWith: '.row',
            update: function(){
                custom_item_ids(area);
            }
        }));
    };

    function init_uploader(){
        var nonce = $('#_wpnonce').val(),
            processing = $('#li-content-layout-processing'),
            queue = $('#le-content-layout-file-list'),
            row,
            login = $('#le-content-layout-login'),
            resp_func = function(resp){
                if(typeof resp.show_login != 'undefined'){
                    if(login.length == 0){
                        processing.after('<div id="le-content-layout-login" />');
                    }
                    $('#le-content-layout-login').append(resp.login_html).find('form').submit(function(e){
                        row.slideDown('fast').fadeIn('fast');
                        e.preventDefault();
                        $.post($(this).attr('action'),$(this).serialize(),resp_func,'json');
                        $('#le-content-layout-login').remove();
                    });
                }
                if(typeof resp.error != 'undefined'){
                    alert(resp.error);
                } else if(typeof resp.content_layout != 'undefined'){
                    $('#le-layouts-dialog div.tab-predefined').html(resp.content_layout);
                    $('#le-layouts-dialog ul.op-bsw-grey-panel-tabs a[href$="#predefined"]').trigger('click');
                }
                row.fadeOut('fast').slideUp('fast');
            },
            uploader = new qq.FileUploader({
                element: document.getElementById('le-content-layout-upload'),
                listElement: queue.get(0),
                action: OptimizePress.ajaxurl,
                params: {
                    action: OptimizePress.SN+'-live-editor-upload-layout',
                    _wpnonce: nonce
                },
                allowedExtensions: ['zip'],
                onComplete: function(id,fileName,resp){
                    queue.find('li:eq('+id+')').fadeOut('fast').slideUp('fast');
                    row = $('<li />').html('Processing '+fileName+' <img src="images/wpspin_light.gif" alt="" />');
                    processing.append(row);
                    $.post(OptimizePress.ajaxurl,{
                        action: OptimizePress.SN+'-live-editor-process-layout',
                        _wpnonce: nonce,
                        attachment_id: resp.fileid
                    },resp_func,'json');
                }
            });
    };
    window.op_live_editor = true;
    window.op_le_column_width = function(){
        return $(cur[0]).closest('.cols').width();
    };
    window.op_refresh_content_layouts = function(){
        $.post(OptimizePress.ajaxurl,{
            action: OptimizePress.SN+'-live-editor-load-layouts',
            _wpnonce: $('#_wpnonce').val()
        },
        function(resp){
            if(typeof resp.error != 'undefined'){
                alert(resp.error);
            } else if(typeof resp.content_layout != 'undefined'){
                $('#le-layouts-dialog div.tab-predefined').html(resp.content_layout);
                $('#le-layouts-dialog ul.op-bsw-grey-panel-tabs a[href$="#predefined"]').trigger('click');
            }
        },
        'json');
    };
    window.op_le_insert_content = function(str){
        element = str.substr(1, str.indexOf(' ') - 1);
        if(cur.length == 0){
            alert('Could not find the current position, please try clicking the Add new row link again.');
            return;
        }
        var sc = str;
        if(refresh_item !== null && child_element === false){
            refresh_item.val(sc);
            refresh_element(refresh_item);
            if (element === 'content_toggle' || element === 'order_box' || element === 'delayed_content'
            || element === 'feature_box' || element === 'feature_box_creator' || element === 'terms_conditions'
            || element === 'op_popup') {
                var textarea_val = refresh_item.val();
                // textarea_val = textarea_val.replace(/\[op_liveeditor_elements\].*\[\/op_liveeditor_elements\]/gi, '#OP_CHILD_ELEMENTS#');
                textarea_val = textarea_val.replace(/\[op_liveeditor_elements\][\s\S]*\[\/op_liveeditor_elements\]/gi, '#OP_CHILD_ELEMENTS#');
                refresh_item.val(textarea_val);
            }
            return $.fancybox.close();
        }

        var html = '';
        var classname = '';
        var hideChildClassname = '';

        if(child_element){
            hideChildClassname = cur[0].hasClass('hide-tablet') ? ' hide-tablet' : '';
            hideChildClassname += cur[0].hasClass('hide-mobile') ? ' hide-mobile' : '';
            html = $('<div class="row element-container cf ' + hideChildClassname + '" data-style="' + (cur[0].attr('data-style') || '') + '" />');
            classname = 'op-le-child-shortcode';
        } else {
            html = $('<div class="element-container cf" />');
            classname = 'op-le-shortcode';
        }

        var area = cur[0].closest('.editable-area');
        cur[0][cur[1]](html);

        if (element === 'content_toggle' || element === 'order_box' || element === 'delayed_content'
        || element === 'feature_box' || element === 'feature_box_creator' || element === 'terms_conditions' || element === 'op_popup') {
            html.append('<div class="op-element-links"><a class="element-parent-settings" href="#parent-settings"><img alt="'+translate('edit_parent_element')+'" title="'+translate('edit_parent_element')+'" src="'+OptimizePress.imgurl+'pencil.png" /></a><a href="#settings" class="element-settings"><img alt="'+translate('edit_element')+'" title="'+translate('edit_element')+'" src="'+OptimizePress.imgurl+'pencil.png" /></a><a class="element-clone" href="#clone-element"><img alt="'+translate('clone_element')+'" title="'+translate('clone_element')+'" src="'+OptimizePress.imgurl+'pencil.png" /></a><a href="#op-le-advanced" class="element-advanced"><img alt="'+translate('edit_element')+'" title="'+translate('advanced_element')+'" src="'+OptimizePress.imgurl+'pencil.png" /></a><a href="#move" class="element-move"><img src="'+OptimizePress.imgurl+'move-icon.png" alt="'+translate('move')+'" /></a><a href="#delete" class="element-delete"><img src="'+OptimizePress.imgurl+'remove-row.png" alt="'+translate('delete')+'" /></a></div><div class="op-waiting"><img src="images/wpspin_light.gif" alt="" class="op-bsw-waiting op-show-waiting" /></div><div class="element cf"></div><div class="op-hidden"><textarea name="shortcode[]" class="'+classname+'"></textarea></div>');
        } else {
            html.append('<div class="op-element-links"><a href="#settings" class="element-settings"><img alt="'+translate('edit_element')+'" title="'+translate('edit_element')+'" src="'+OptimizePress.imgurl+'pencil.png" /></a><a class="element-clone" href="#clone-element"><img alt="'+translate('clone_element')+'" title="'+translate('clone_element')+'" src="'+OptimizePress.imgurl+'pencil.png" /></a><a href="#op-le-advanced" class="element-advanced"><img alt="'+translate('edit_element')+'" title="'+translate('advanced_element')+'" src="'+OptimizePress.imgurl+'pencil.png" /></a><a href="#move" class="element-move"><img src="'+OptimizePress.imgurl+'move-icon.png" alt="'+translate('move')+'" /></a><a href="#delete" class="element-delete"><img src="'+OptimizePress.imgurl+'remove-row.png" alt="'+translate('delete')+'" /></a></div><div class="op-waiting"><img src="images/wpspin_light.gif" alt="" class="op-bsw-waiting op-show-waiting" /></div><div class="element cf"></div><div class="op-hidden"><textarea name="shortcode[]" class="'+classname+'"></textarea></div>');
        }


        //[op_popup_element]

        var sc_textarea = html.find('textarea').val(sc);
        $.fancybox.close();
        op_cur_html = html;
        html.find('.op-waiting').fadeIn('fast').end().find('.op-waiting .op-show-waiting').fadeIn('fast');
        $.post(OptimizePress.ajaxurl,
            {
                action: OptimizePress.SN+'-live-editor-parse',
                _wpnonce: $('#_wpnonce').val(),
                shortcode: sc,
                depth: (child_element ? 1 : 0),
                page_id: $('#page_id').val()
            },
            function(resp){
                if(resp.check !== null){
                    var valid = true;
                    for(var i in resp.check){
                        if($(i).length > 0){
                            if(resp.check[i] != ''){
                                html.fadeOut('fast',function(){
                                    $(this).remove();
                                });
                                alert(resp.check[i]);
                                return;
                            }
                        }
                    };
                    if(valid === true){
                        if (typeof resp.font != 'undefined' && resp.font !== '' && resp.font[0] === 'google') {
                            WebFont.load({google:{families:[resp.font[1] + resp.font[2].properties]}});
                        }

                        //Yes, second check is to test if data-style is not string undefined.
                        elDataStyle = html.attr('data-style') && html.attr('data-style') !== 'undefined' ? JSON.parse(atob(html.attr('data-style'))) : {};
                        elDataStyle.codeBefore = elDataStyle.codeBefore || '';
                        elDataStyle.codeAfter = elDataStyle.codeAfter || '';
                        elDataStyle.advancedClass = elDataStyle.advancedClass || '';

                        var el = html.addClass(elDataStyle.advancedClass)
                                    .find('.element').html(elDataStyle.codeBefore + resp.output+resp.js + elDataStyle.codeAfter);

                        child_element ? init_child_sortables(true) : refresh_sortables(area);
                    }
                } else {
                    if (typeof resp.font != 'undefined' && resp.font !== '' && resp.font[0] === 'google') {
                        WebFont.load({google:{families:[resp.font[1] + resp.font[2].properties]}});
                    }

                    //Yes, second check is to test if data-style is not string undefined.
                    elDataStyle = html.attr('data-style') && html.attr('data-style') !== 'undefined' ? JSON.parse(atob(html.attr('data-style'))) : {};
                    elDataStyle.codeBefore = elDataStyle.codeBefore || '';
                    elDataStyle.codeAfter = elDataStyle.codeAfter || '';
                    elDataStyle.advancedClass = elDataStyle.advancedClass || '';

                    var el = html.addClass(elDataStyle.advancedClass)
                                .find('.element').html(elDataStyle.codeBefore + resp.output+resp.js + elDataStyle.codeAfter);

                    child_element ? init_child_sortables(true) : refresh_sortables(area);

                }

                if(typeof el != 'undefined'){
                    if(typeof resp.shortcode != 'undefined'){
                        sc_textarea.val(resp.shortcode);
                    }
                    html.find('.op-waiting').fadeOut('fast',function(){
                        el.fadeIn('fast', function () {
                            resize_epicbox();
                        });
                    });
                    custom_item_ids(cur[0].closest('.editable-area'));
                }
            },
            'json'
        );
    };

    if (window.op_dont_show_loading) {
        window.op_hide_loading();
    } else {
        window.op_dont_show_loading = false;
    }

    function rgb2hex(rgb){
         rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
         return "#" +
          ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
          ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
          ("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
        }

    function translate(str){
        return OP_AB.translate(str);
    };


    /**
     * Autohiding and showing of toolbars
     */
    $(document).ready(function(){
        var $ = opjq;

        var $sidebar = $('#op-le-settings-toolbar');
        var $sidebarToggleBtn = $('#op-le-toggle-sidebar-btn');
        var showPanelsHtml;

        $('html').addClass('op-le-settings-toolbar--shown');

        var toggleSidebar = function () {
            $('html').toggleClass('op-le-settings-toolbar--hidden op-le-settings-toolbar--shown');
            //$showLiveEditorPanels.addClass('showLiveEditorPanels--hidden');
            // setTimeout(function () {
            //  $('html').removeClass('op-toolbars--hidden');
            // }, 200);
            return false;
        }

        var hideEditorPanels = function () {
            $('html').addClass('op-toolbars--hidden');
            setTimeout(function () {
                $showLiveEditorPanels.removeClass('showLiveEditorPanels--hidden');
            }, 200);
            return false;
        }

        $sidebarToggleBtn.parent().on('click', toggleSidebar);

        // This is a fix for Firefox behaviour, in which form data is remembered upon refresh.
        $('.op_child_shortcode_form').each(function () {
            this.reset();
        });

        // Sometimes we want to open the liveeditor in full window. This is a handy shortcut.
        if (window.top.location.href !== window.location.href) {
            $('#op-liveeditor-logo').on('click', function (e) {
                if (e.shiftKey) {
                    OP.disable_alert = true;
                    window.top.location.href = window.location;
                }
                e.preventDefault();
            });
        }

    });

    $(window).load(function () {
        op_hide_loading();
    });

}(opjq));
