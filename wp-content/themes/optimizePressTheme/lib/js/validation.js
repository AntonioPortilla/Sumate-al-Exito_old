(function($){
    $(document).ready(function(){
        var $forms = $('form.op-optin-validation');
        var emailExp = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        /*
         * Validation
         */
        $forms.submit(function() {
            var returnValue = true;
            $.each($(this).find('input[required="required"]'), function(i, field) {
                if ($(field).attr('name').indexOf('email') > -1 && false === emailExp.test($(field).val())) {
                    alert(OPValidation.labels.email);
                    returnValue = false;
                } else if ($(field).val().length == 0) {
                    alert(OPValidation.labels.text);
                    returnValue = false;
                }
            });
            return returnValue;
        });
        /*
         * GTW submission
         */
        $forms.submit(function() {
            var provider    = $(this).find('input[name=provider]').val(),
                gtw         = $(this).find('input[name=gotowebinar]').val(),
                email       = $(this).find('input[type=email]').val();

            if ((typeof provider === 'undefined' || provider === 'infusionsoft') && typeof gtw !== 'undefined') {
                /*
                 * We need to switch FORM action param, we couldn't set original URL because of legacy installations
                 */
                $(this).attr('action', $(this).find('input[name=redirect_url]').val());

                /*
                 * Async request
                 */
                $.ajax({
                    type:   'POST',
                    url:    OPValidation.ajaxUrl,
                    data:   $(this).serialize() + '&action=optimizepress_process_gtw&email=' + email + '&webinar=' + gtw + '&nonce=' + OPValidation.nonce,
                    async:  false,
                    success: function() {
                        return true;
                    }
                });
            }
        });
    });
}(opjq));