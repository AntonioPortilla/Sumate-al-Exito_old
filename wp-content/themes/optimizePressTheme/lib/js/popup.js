(function ($) {

    $('.op-popup-button').on('click', function (e) {

        var $this = $(this);
        var $popup = $this.parent();
        //var $popupContent = $this.next().clone(true, true);
        var $popupContent = $this.next();
        var userWidth = $popup.data('width') || '0';
        var openEffect;
        var userOpenEffect = $popup.data('open-effect') || 'fade';
        var openMethod;
        var userOpenMethod = $popup.data('open-method') || 'zoomIn';
        var closeEffect;
        var userCloseEffect = $popup.data('close-effect') || 'fade';
        var borderColor = $popup.data('border-color') || '#ffffff';
        var borderSize = $popup.data('border-size');
        var autoSize;
        var width;
        var paddingTop = $popup.data('padding-top');
        var paddingBottom = $popup.data('padding-bottom');
        var paddingLeft = $popup.data('padding-left');
        var paddingRight = $popup.data('padding-right');
        var padding;

        e.preventDefault();

        if (typeof borderSize !== 'number') {
            borderSize = 15;
        }

        if (parseInt(userWidth, 10) === 0) {
            autoSize = true;
            width = 'auto';
            minWidth = 20;
        } else {
            autoSize = false;
            width = userWidth;
            minWidth = userWidth;
        }

        switch (userOpenEffect) {
            case 'fade':
                openEffect = 'fade';
                openMethod = 'zoomIn';
                break;
            case 'elastic':
                openEffect = 'fade';
                openMethod = 'changeIn';
                break;
            case 'none':
                openEffect = 'none';
                openMethod = 'zoomIn';
                break;
        }

        switch (userCloseEffect) {
            case 'fade':
                closeEffect = 'fade';
                break;
            case 'zoomOut':
                closeEffect = 'elastic';
                break;
            case 'none':
                closeEffect = 'none';
                break;
        }

        $popupContent
            .css({ padding: paddingTop + 'px ' + paddingRight + 'px ' + paddingBottom + 'px ' + paddingLeft + 'px' })
            .addClass('op-popup-content-visible');

        $.fancybox({
            content: $popupContent,

            autoSize: autoSize,
            minHeight: 20,
            width: width,
            minWidth: minWidth,
            padding: borderSize,
            autoHeight: true,
            height: 'auto',

            openEffect: openEffect,
            openMethod: openMethod,     // zoomIn value must be paired with fade effect
            closeEffect: closeEffect,   // none, elastic or fade (elastic works as zoom out)
            closeMethod: 'zoomOut',     // Fanybox causes errors if this isn't fixed to zoom
            openSpeed: $popup.data('open-speed') || 'normal',
            closeSpeed: $popup.data('close-speed') || 'normal',

            wrapCSS: 'op-popup-fancybox',

            beforeShow: function () {
                $popupContent.parent().parent().css('background-color', borderColor);
            },

            afterShow: function () {
                // JS plugins can hook up to this event like so: $(window).on('op-popup-opened', func);
                $(window).trigger('op-popup-opened');
            },

            // When window is resized, or popup opened, make sure that width of the popup/fancybox is proper.
            onUpdate: function () {

                var $fancyboxWrap = $popupContent.parent().parent().parent();
                var fancyboxOuterWidth = $fancyboxWrap.width();
                var fancyboxOuterPadding = parseInt($fancyboxWrap.css('left'), 10) * 2;
                var windowWidth = $(window).width();

                if (windowWidth <= fancyboxOuterWidth) {
                    $fancyboxWrap.css({
                        width: (fancyboxOuterWidth - fancyboxOuterPadding - 12) + 'px',
                        left: '26px'
                    });
                    $('#fancybox-overlay').css({ width: windowWidth + 'px' });
                }

            },

            afterClose: function () {
                $popupContent.removeClass('op-popup-content-visible');
                $(window).trigger('op-popup-closed', $popupContent[0]);
                $this.after($popupContent);
            }

        });

    });

}(opjq));