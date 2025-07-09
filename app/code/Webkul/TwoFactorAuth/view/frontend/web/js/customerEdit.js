require([
    "jquery",
    'mage/translate'
], function($, $t){
    $(document).ready(function() {
        $('#change-password').parents('div.choice').after($('#change-phone-number-checkbox-div'));
        $('#change-phone-number-checkbox-div').show();
        if ($('#password-confirmation').parents('div.confirm').length) {
            $('#password-confirmation').parents('div.confirm').after($('#change-phone-number-div'));
        } else if ($('#password-confirmation').parents('div.confirmation').length) {
            $('#password-confirmation').parents('div.confirmation').after($('#change-phone-number-div'));
        }
        $('#change-phone-number-div').show();
    });
});