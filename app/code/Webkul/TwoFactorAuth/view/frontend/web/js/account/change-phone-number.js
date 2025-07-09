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
    $.widget('mage.changePhoneNumber', {
        options: {
            changeEmailSelector: '[data-role=change-email]',
            changePasswordSelector: '[data-role=change-password]',
            mainContainerSelector: '[data-container=change-email-password]',
            titleSelector: '[data-title=change-email-password]',
            emailContainerSelector: '[data-container=change-email]',
            newPasswordContainerSelector: '[data-container=new-password]',
            confirmPasswordContainerSelector: '[data-container=confirm-password]',
            currentPasswordSelector: '[data-input=current-password]',
            emailSelector: '[data-input=change-email]',
            newPasswordSelector: '[data-input=new-password]',
            confirmPasswordSelector: '[data-input=confirm-password]',
            phoneNumberContainerSelector: '[data-container=change-phone-number-div]',
            changePhoneNumberSelector: '[data-role=change-phone-number]',
            phoneNumberSelector: '[data-input=change-phone-number]'
        },

        /**
         * Create widget
         * @private
         */
        _create: function() {
            this.element.on('change', $.proxy(function() {
                this._checkChoice();
            }, this));

            this._checkChoice();
            this._bind();
        },

        /**
         * Event binding, will monitor change, keyup and paste events.
         * @private
         */
        _bind: function() {
            this._on($(this.options.emailSelector), {
                'change': this._updatePasswordFieldWithEmailValue,
                'keyup': this._updatePasswordFieldWithEmailValue,
                'paste': this._updatePasswordFieldWithEmailValue
            });
        },

        /**
         * Check choice
         * @private
         */
        _checkChoice: function() {
            if ($(this.options.changeEmailSelector).is(':checked') &&
                $(this.options.changePasswordSelector).is(':checked') &&
                $(this.options.changePhoneNumberSelector).is(':checked')) {
                this._showAll();
            } else if ($(this.options.changeEmailSelector).is(':checked') &&
                $(this.options.changePhoneNumberSelector).is(':checked')) {
                this._showEmailAndPhoneNumber();
            } else if ($(this.options.changePasswordSelector).is(':checked') &&
                $(this.options.changePhoneNumberSelector).is(':checked')) {
                this._showPasswordAndPhoneNumber();
            } else if ($(this.options.changePasswordSelector).is(':checked') &&
                $(this.options.changeEmailSelector).is(':checked')) {
                this._showEmailAndPassword();
            } else if ($(this.options.changeEmailSelector).is(':checked')) {
                this._showEmail();
            } else if ($(this.options.changePasswordSelector).is(':checked')) {
                this._showPassword();
            } else if ($(this.options.changePhoneNumberSelector).is(':checked')) {
                this._showPhoneNumber();
            } else {
                this._hideAll();
            }
        },

        /**
         * Show Email And Password input fields
         * @private
         */
        _showEmailAndPassword: function() {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeEmailAndPassword);

            $(this.options.phoneNumberContainerSelector).hide();
            $(this.options.phoneNumberSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show Email And Phone Number input fields
         * @private
         */
        _showEmailAndPhoneNumber: function() {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeEmailAndPhoneNumber);

            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show Password And Phone Number input fields
         * @private
         */
        _showPasswordAndPhoneNumber: function() {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangePasswordAndPhoneNumber);

            $(this.options.emailContainerSelector).hide();
            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show Phone Number input fields
         * @private
         */
        _showPhoneNumber: function() {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangePhoneNumber);

            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);

            $(this.options.emailContainerSelector).hide();
            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show email, phone number and password input fields
         * @private
         */
        _showAll: function() {
            $(this.options.titleSelector).html(this.options.titleChangeEmailPasswordAndPhoneNumber);

            $(this.options.mainContainerSelector).show();
            $(this.options.emailContainerSelector).show();
            $(this.options.newPasswordContainerSelector).show();
            $(this.options.confirmPasswordContainerSelector).show();
            $(this.options.phoneNumberContainerSelector).show();

            $(this.options.currentPasswordSelector).attr('data-validate', '{required:true}').prop('disabled', false);
            $(this.options.emailSelector).attr('data-validate', '{required:true}').prop('disabled', false);
            $(this.options.phoneNumberSelector).attr('data-validate', '{required:true}').prop('disabled', false);
            this._updatePasswordFieldWithEmailValue();
            $(this.options.confirmPasswordSelector).attr(
                'data-validate',
                '{required:true, equalTo:"' + this.options.newPasswordSelector + '"}'
            ).prop('disabled', false);
        },

        /**
         * Hide email, phone number and password input fields
         * @private
         */
        _hideAll: function() {
            $(this.options.mainContainerSelector).hide();
            $(this.options.emailContainerSelector).hide();
            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();
            $(this.options.phoneNumberContainerSelector).hide();

            $(this.options.currentPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.phoneNumberSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show email input fields
         * @private
         */
        _showEmail: function() {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangeEmail);

            $(this.options.newPasswordContainerSelector).hide();
            $(this.options.confirmPasswordContainerSelector).hide();

            $(this.options.newPasswordSelector).removeAttr('data-validate').prop('disabled', true);
            $(this.options.confirmPasswordSelector).removeAttr('data-validate').prop('disabled', true);

            $(this.options.phoneNumberContainerSelector).hide();
            $(this.options.phoneNumberSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Show password input fields
         * @private
         */
        _showPassword: function() {
            this._showAll();
            $(this.options.titleSelector).html(this.options.titleChangePassword);

            $(this.options.emailContainerSelector).hide();

            $(this.options.emailSelector).removeAttr('data-validate').prop('disabled', true);

            $(this.options.phoneNumberContainerSelector).hide();
            $(this.options.phoneNumberSelector).removeAttr('data-validate').prop('disabled', true);
        },

        /**
         * Update password validation rules with email input field value
         * @private
         */
        _updatePasswordFieldWithEmailValue: function() {
            $(this.options.newPasswordSelector).attr(
                'data-validate',
                '{required:true, ' +
                '\'validate-customer-password\':true, ' +
                '\'password-not-equal-to-user-name\':\'' + $(this.options.emailSelector).val() + '\'}'
            ).prop('disabled', false);
        }
    });
});