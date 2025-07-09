/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'mage/storage',
        'Webkul_MpBuyerSellerChat/js/model/socket-provider',
        'Magento_Ui/js/modal/alert'
    ],
    function ($, storage, socketProvider, alert) {
        'use strict';
        var callbacks = [],
            action = function (registerData, customerLoggedIn) {
                socketProvider.setShowLoader(true);
                return storage.post(
                    'mpchatsystem/ajax/createpost',
                    JSON.stringify(registerData),
                    true
                ).done(function (response) {
                    socketProvider.setShowLoader(false);
                    if (response.errors) {
                        alert({
                            title: 'Alert!',
                            content: response.message,
                            actions: {
                                always: function () { }
                            }
                        });
                        $('#register').trigger("reset");
                    } else {
                        customerLoggedIn(true);
                    }
                }).fail(function () {
                    socketProvider.showLoader(false);
                    alert({
                        title: 'Error!',
                        content: 'Something went wrong!',
                        actions: {
                            always: function () { }
                        }
                    });
                    $('#register').trigger("reset");
                });
            };

        action.registerLoginCallback = function (callback) {
            callbacks.push(callback);
        };

        return action;
    }
);