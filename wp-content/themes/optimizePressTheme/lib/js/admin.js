opjq(document).ready(function($){

    var el = $('.op_live_editor .op-pagebuilder, .op_page_builder .op-pagebuilder,#toplevel_page_optimizepress a[href$="page=optimizepress-page-builder"],#op-pagebuilder-container a.op-pagebuilder, form.op-bsw-settings a.op-pagebuilder');

    var $body = $('body');

    var defaults = {
            width: '98%',
            height: '98%',
            padding: 0,
            closeClick: false,
            type: 'iframe',

            beforeShow: function() {
                $.fancybox.showLoading();
                setTimeout(function() {$.fancybox.hideLoading();}, 2000);

                //This is necessary in order to hide the parent fancybox scrollbars and close button
                $('html').css({
                    overflow: 'hidden',
                    height: '100%'
                });
                $(window.parent.document).find('.fancybox-close').css({ display: 'none' });
            },

            afterShow: function () {
                $('.fancybox-opened').find('iframe').focus();

                // We do this to resize revisions dialog iframes properly
                if (OP._pageRevisionsActive) {
                    $(document).trigger('pageRevisionsFancyboxOpen');
                }
            },

            beforeClose: function(){
                if (!OP.disable_alert && !OP._pageRevisionsActive) {
                    return confirm(OptimizePress.pb_unload_alert);
                }
                OP.disable_alert = false;
            },

            afterClose: function(){

                if (OP._pageRevisionsActive) {
                    OP._pageRevisionsActive = false;
                    $('#op-current-iframe').attr('src', '');
                    $('#op-revisions-iframe').attr('src', '');
                    $(document).off('pageRevisionsFancyboxOpen');
                    $(window).off('resize', OP._repositionRevisionsPopup);
                }

                //This is necessary in order to hide the parent fancybox scrollbars and close button
                $('html').css({
                    overflow: 'auto'
                });
                $(window.parent.document).find('.fancybox-close').css({ display: 'block' });
                /*
                 * If user is on the pages list screen, it will refresh his page (so he'll be able to view his newly created page)
                 */
                if (window.location.pathname.indexOf('wp-admin/edit.php') >= 0 && window.location.search.indexOf('post_type=page') >= 0
                && typeof window.OP.reload_page !== 'undefined' && window.OP.reload_page === true) {
                    setTimeout(function () {
                        window.location = window.location.href;
                    }, 0);
                }
            }
        };
    el.fancybox(defaults);

    $('body.widgets-php').find('#available-widgets #widget-list .widget .widget-description').each(function(){
        $(this).html($(this).text());
    });

    /*
     * This is a fix for missing ready.promise() on jQuery 1.7.2
     */
    $.Deferred(function(defer) {
        $(defer.resolve);
        $.ready.promise = defer.promise;
    });

    /**
     * Tabbed module
     * Related to /lib/tpl/generic/tabbed_module.php
     * It needs to be triggered after all event listeners in document are already set, that's why it's in $.ready.promise()
     */
    $.ready.promise().done(function() {
        if (window.location.hash) {
            var hash = window.location.hash.split('--');
            $tab = $('.op-bsw-grey-panel-tabs a[href="' + hash[0] + '"]');//$('.tab-' + window.location.hash);
            if ($tab.length > 0) {
                $tab.trigger('click');
                if (hash.length == 2) {
                    $provider = $('.tab-' + hash[0].substring(1) + ' .section-' + hash[1] + ' .op-bsw-grey-panel-header h3 a');
                    $provider.trigger('click');
                }
            }
        }
    });

    // A helper flag which indicates if revisios are (being) opened.
    OP._pageRevisionsActive = false;

    /**
     * Resizes the revisions iframes to make them max. available size on current screen.
     * It is also used in live_editor.js
     */
    OP._repositionRevisionsPopup = function () {

        var $fancyBox = $('.fancybox-outer');
        var fancyboxHeight = $fancyBox.height();
        var $revisionsDialog = $fancyBox.find('#op-revisions-dialog');
        var revisionsDialogH1Height = $revisionsDialog.find('> h1').outerHeight();
        var $dialogContent = $revisionsDialog.find('.dialog-content');
        var dialogContentPadding = parseInt($dialogContent.css('paddingTop'), 10) + parseInt($dialogContent.css('paddingBottom'), 10);
        var $revisionsList = $revisionsDialog.find('.op-revisions-list');
        var thHeight = $dialogContent.find('.op-diff-th').outerHeight() + 3;   // 3 is to account for top and bottom border of th and bottom border from container

        revisionsListHeight = $revisionsList.outerHeight() + parseInt($revisionsList.css('marginBottom'), 10);
        $revisionsDialog.find('.op-diff').height(fancyboxHeight - revisionsDialogH1Height - dialogContentPadding - revisionsListHeight);
        $revisionsDialog.find('.op-revisions-iframe').height(fancyboxHeight - revisionsDialogH1Height - dialogContentPadding - revisionsListHeight - thHeight);

    }

    /**
     * Loads latest revisions from the database,
     * then initializes page revisions
     * @param {object} fancy_defaults [fancybox default options for initialization]
     */
    OP._initPageRevisions = function (fancy_defaults, targetEl) {

        var data = {
            action: OptimizePress.SN+'-op_ajax_get_page_revisions',
            page_id: $('#page_id').val() || targetEl.getAttribute('data-post_id')
        };

        OP._pageRevisionsActive = true;

        if (typeof op_show_loading !== 'undefined') {
            op_show_loading();
        }

        // We want latest revisions, that's why we remove any existing revisions.
        $('#op-revisions-dialog').remove();

        $.post(OptimizePress.ajaxurl, data, function(resp){

            var $currentIframe;

            $body.append(resp);
            OP._renderPageRevisions(fancy_defaults);

            $currentIframe = $('#op-current-iframe');
            $currentIframe.attr('src', $currentIframe.attr('data-src'));

            op_hide_loading();

        });

    }

    /**
     * Opens fancybox, loads iframes and binds scroll events to them.
     * @param {object} fancy_defaults [fancybox default options for initialization]
     */
    OP._renderPageRevisions = function (fancy_defaults) {

        // pageRevisionsFancyboxOpen is custom event triggered after fancybox is shown
        $(document).on('pageRevisionsFancyboxOpen', OP._repositionRevisionsPopup);

        $(document).on('pageRevisionsFancyboxOpen', function () {

            // We set load event on every iframe
            $('.op-revisions-iframe').each(function () {

                $(this).on('load', function (e) {

                    // Current iframe repositions the scroll of the another iframe (there are only two on the page)
                    var otherIframe = this.getAttribute('id') === 'op-revisions-iframe' ? 'op-current-iframe' : 'op-revisions-iframe';

                    // When user scrolls one iframe, other should be scrolled as well.
                    $(e.target.contentWindow).on('scroll', function () {
                        $(document.getElementById(otherIframe).contentWindow).scrollTop($(this).scrollTop());
                    });

                });

            });

        });

        // If user resizes the window, we need to resize revisions iframes
        $(window).on('resize', OP._repositionRevisionsPopup);

        $.fancybox($.extend({}, fancy_defaults, {
            minWidth: $('#op-revisions-dialog').width(),
            type: 'inline',
            wrapCSS: 'fancybox-revisions',
            href: '#op-revisions-dialog',
            autoSize: false,
            width: '98%',
            height: '98%'
        }));

        // We never want to ask user to confirm when working with revisions
        OP.disable_alert = false;

    }


    /**
     * Shows the loading indicator and blocks the UI
     */
    window.op_show_loading = function () {
        if (window.op_dont_show_loading || $('#op_overlay').length > 0) {
            //Loading is already showing
            return;
        }

        if (window.top.opjq.fancybox) {
            window.top.opjq.fancybox.showLoading();
            $body.append('<div id="op_overlay" style="opacity:1;"></div>');
        } else {
            $body.append('<div id="op_loading"></div><div id="op_overlay"></div>');
            setTimeout(function () {
                $('#op_overlay, #op_loading').css('opacity', 1);
            }, 100);
        }
    }

    /**
     * Hides the OP loading indicator (invoked with op_show_loading)
     */
    window.op_hide_loading = function () {
        if (window.op_dont_hide_loading) {
            return;
        }

        if (window.top.opjq.fancybox) {
            window.top.opjq.fancybox.hideLoading();
        }

        $('#op_overlay, #op_loading').css('opacity', 0);
        setTimeout(function () {
            $('#op_overlay, #op_loading').remove();
        }, 200);
    }

    // revisions button click
    $body.on('click', '#op-revisions-button', function(e){
        e.preventDefault();
        OP._initPageRevisions(defaults, e.target);
    });

    $body.on('click', '.op-revision-preview', function(e){
        e.preventDefault();
        var previewLink = $(this).attr('href');
        $('#op-revisions-iframe').attr('src', previewLink);
    });

    //$('#op-current-iframe').remove();
    $body.on('change', '.op-revisions-radio', function(e){

        e.preventDefault();
        var previewLink = $(this).val();

        // Set current revisions list item as selected (and unselect previosly selected one)
        $('#op-revisions-dialog').find('.op-revisions-list-item').removeClass('op-revisions-list-item--selected');
        $(this).parent().parent().addClass('op-revisions-list-item--selected')

        $('#op-revisions-iframe').attr('src', previewLink);
        $('#op-open-revision-new-tab').css({ display: 'inline' }).attr('href', previewLink);
    });

    $body.on('click', '.op-revision-restore', function(e){

        var data = {
            action: OptimizePress.SN+'-restore-page-revision',
            postID: $(this).data('postid'),
            revisionID: $(this).data('revisionid')
        };

        e.preventDefault();

        OP.disable_alert = true;

        $.post(OptimizePress.ajaxurl, data,
            function(resp){
                if(typeof resp.error != 'undefined'){
                    alert(resp.error);
                } else {
                    $.fancybox.close();
                    if (typeof op_show_loading !== 'undefined') {
                        op_show_loading();
                    }
                    window.location.reload(true);
                }
            },
            'json'
        );
    });

});