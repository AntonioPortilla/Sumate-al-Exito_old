/*
 *
 *  Note: The only code that should go in this file is code that can and should be
 *  executed globally. This means not only the user facing pages but the admin as well
 *
 */

(function($){
    //Init obejcts
    var Assets = {},
        Sharrre = {};

    //Init Assets object
    Assets.init = {}; //Used to hold all assets init functions

    //Init Sharrre constants
    Sharrre.urlCurl = OP.paths.js + 'jquery/sharrre.inc.php';
    Sharrre.services = [
        'twitter',
        'facebook',
        'googlePlus'
    ];
    Sharrre.options = {
        enableHover: false,
        enableTracking: true,
        urlCurl: Sharrre.urlCurl,
        buttons: {
            twitter: {},
            facebook: {},
            googlePlus: {}
        }
    };

    //Init document ready
    $(document).ready(function(){
        //Init general
        init_sharrre();
        init_selectnav();
        init_dropkick();
        init_tooltipster();
        init_reveal();
        addTextAttributes();

        //Init assets
        Assets.init.countdown();
        Assets.init.countdown_cookie();
    });

    function addTextAttributes() {
        $('input').each(function(){
            if (!$(this).attr('type')) {
                $(this).attr('type', 'text');
            }
        });
    }

    //Init the Sharrre widget functionality
    function init_sharrre(){
        $.each(Sharrre.services, function(index, val){
            var localOptions = Sharrre.options;

            //Set the click functionality
            localOptions.click = function(api, options){
                api.simulateClick();
                api.openPopup(val);
            }

            //Init share widgets
            $('.social-sharing .' + val).each(function(){
                //Get the language for this element
                var lang = (typeof($(this).data('lang'))=='undefined' ? 'en_US' : $(this).data('lang'));
                var via = (typeof($(this).data('via'))=='undefined' ? '' : $(this).data('via'));
                var title = (typeof($(this).data('title'))=='undefined' ? '' : $(this).data('title'));
                var url = (typeof($(this).data('url'))=='undefined' ? '' : $(this).data('url'));

                //Enable/disable counter
                localOptions.enableCounter = $(this).parent().data('counter');

                //Set social variables
                switch(val){
                    case 'twitter':
                        localOptions.share = { twitter: true };
                        localOptions.buttons.twitter.lang = lang;
                        localOptions.buttons.twitter.via = via;
                        localOptions.buttons.twitter.title = title;
                        localOptions.buttons.twitter.url = url;
                        break;
                    case 'facebook':
                        localOptions.share = { facebook: true };
                        localOptions.buttons.facebook.lang = lang;
                        break;
                    case 'googlePlus':
                        localOptions.share = { googlePlus: true };
                        localOptions.buttons.googlePlus.lang = lang;
                        break;
                }

                //Apply sharrre to element
                $(this).sharrre(localOptions);
            });
        });
    }

    $(window).on('op_init_sharrre', init_sharrre);

    function init_selectnav(){
        selectnav('navigation-above', {indent: '<span>-</span>'});
        selectnav('navigation-below', {indent: '<span>-</span>'});
        selectnav('navigation-alongside', {indent: '<span>-</span>'});
    }

    //Init the dropkick JS functionality
    function init_dropkick(){
        var navSelector = '.navigation .dk';
        var otherSelector = ($('body').hasClass('blog') ? '.main-content .dk' : '.content .dk');

        dropkickListener = function () {
            if (parseInt($(this).width(), 10) < 960) {
                $(navSelector).each(function () {
                    if (!$(this).data('dropkickInitialized')) {
                        $(this).dropkick({
                            change: function (value, label) {
                                if (value) {
                                    window.location = value;
                                }
                            }
                        });
                        $(this).data('dropkickInitialized', 'true')
                    }

                    var item = $(this).siblings('ul').find('li:first-child a');
                    var color = item.css('color');
                    $(this).prev('.dk_container').find('.dk_label').css({ color: color });
                });
            }
        }

        //Init the nav dropkick functionality and trigger it
        $(window).on('resize', dropkickListener).trigger('resize');

        //Init the other content dropkick dropdowns
        $(otherSelector).each(function(){
            if (!$(this).data('dropkickInitialized')) {
                $(this).dropkick({
                    change: function () {
                        if (value) {
                            window.location = value;
                        }
                    }
                });
                $(this).data('dropkickInitialized', 'true')
            }
        });

        $('li.op-pagebuilder a').fancybox({
            width: '98%',
            height: '98%',
            padding: 0,
            //minWidth: 900,
            //minHeight: 600,
            closeClick: false,
            type: 'iframe',
            afterShow: function () {
                $('.fancybox-opened').find('iframe').focus();
            },
            beforeClose: function(){
                if (!OP.disable_alert) {
                    OP.disable_alert = false;
                    return confirm('This page is asking you to confirm that you want to leave - data you have entered may not be saved.');
                }
            }
        });
    }

    function init_tooltipster(){
        $('.tooltip').tooltipster({animation: 'grow'});
    }

    function init_reveal(){
        $('.optin-modal-container').each(function(){
            $(this).on('click', '.optin-modal-link', function(e) {
                e.preventDefault();
                $(this).next('.optin-modal').reveal();
            });
            $(this).on('click', ' .optin-modal .css-button', function(e){
                e.preventDefault();
                $(this).parent('form').submit();
            });
        });
    }

    //Countdown Asset
    Assets.init.countdown = function(){
        //Find each timer instance
        $('div.countdown-timer').each(function(){
            //Extract date and time
            var obj = $(this),
                data = obj.data('end').split(' '),
                date = (typeof(data[0])=='undefined' ? '00/00/0000' : data[0].split('/')),
                time = (typeof(data[1])=='undefined' ? '00:00:00' : data[1].split(':')),
                isSince = (typeof(obj.data('end'))!='undefined' ? false : true),
                newDateObj = new Date(date[0], parseInt(date[1])-1, date[2], time[0], time[1], time[2]),
                labels = [obj.data('years_text'), obj.data('months_text'), 'Weeks', obj.data('days_text'), obj.data('hours_text'), obj.data('minutes_text'), obj.data('seconds_text')],
                labels1 = [obj.data('years_text_singular'), obj.data('months_text_singular'), 'Week', obj.data('days_text_singular'), obj.data('hours_text_singular'), obj.data('minutes_text_singular'), obj.data('seconds_text_singular')],
                width = 0,
                widthOffset = 9;

            //Download the script if it isn't loaded and initiate countdown
            $.loadScript(OP.paths.js + 'jquery/countdown.js', function(){
                    //Init countdown
                    obj.countdown({
                        until: newDateObj,
                        format: 'yodhms',
                        labels: labels,
                        labels1: labels1,
                        'timezone': data[data.length-1]
                    });

                    //Get countdown sections and add each width to width variable
                    obj.find('span.countdown_section').each(function(){
                        width += $(this).width() + widthOffset;
                    });

                    //Set width to main obj
                    //obj.width(width + 'px');
                    obj.width('100%');
            });
        });
    }

    // Expose this script for when it's needed
    OP.initCountdownElements = Assets.init.countdown;

    //Countdown Cookie Asset
    Assets.init.countdown_cookie = function(){
        //Find each timer instance
        $('div.countdown-cookie-timer').each(function(){
            //Extract date and time
            var obj = $(this),
                data = obj.data('end').split(' '),
                date = (typeof(data[0])=='undefined' ? '00/00/0000' : data[0].split('/')),
                time = (typeof(data[1])=='undefined' ? '00:00:00' : data[1].split(':')),
                newDateObj = new Date(date[0], parseInt(date[1])-1, date[2], time[0], time[1], time[2]),
                labels = ['Years', 'Months', 'Weeks', 'Days', 'Hours', 'Minutes', 'Seconds'],
                labels1 = ['Year', 'Month', 'Week', 'Day', 'Hour', 'Minute', 'Second'],
                width = 0,
                widthOffset = 9;

            //Download the script if it isn't loaded and initiate countdown
            $.loadScript(OP.paths.js + 'jquery/countdown.js', function(){
                    //Init countdown
                    obj.countdown({
                        until: newDateObj,
                        format: 'yodhms',
                        labels: labels,
                        labels1: labels1
                    });

                    //Get countdown sections and add each width to width variable
                    obj.find('span.countdown_section, span.countdown_row').each(function(){
                        width += $(this).width() + widthOffset;
                    });

                    //Set width to main obj
                    obj.width(width + 'px');
            });
        });
    }
})(opjq);





