/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function($, $t, mageTemplate, alert) {
    'use strict';
    $.widget('mage.verifyCustomerMobile', {
        options: {
            twoFactorDiv: '#wk-twofactorauth-div',
            phoneNumberDiv: '#wk_twofactor_mobile_number',
            emailAddress: '#email_address'
        },
        _create: function() {
            var self = this;
            $(self.options.emailAddress).parents('div.field').after($(self.options.twoFactorDiv));
            $(self.options.twoFactorDiv).show();
            $(self.options.phoneNumberDiv).show();
            $("#wk_mobile_number").focus(function() {
                $('#wk-mobile-number-error-msg').remove();
            }).blur(function() {
                var number = $('#wk_mobile_number').val();
                if (!self.isNumeric(number)) {
                    var str = '<span id="wk-mobile-number-error-msg">' + self.options.errorMessage + '</span>';
                    $('#wk_mobile_number').after(str);
                    $('#wk-mobile-number-error-msg').css("color", "red");
                    $('#wk_mobile_number').val('');
                }
            });
        },
        isNumeric: function(number) {
            var regex = /^[0-9]*[1-9][0-9]*$/;
            if (!regex.test(number)) {
                return false;
            } else {
                return true;
            }
        },
    });
});