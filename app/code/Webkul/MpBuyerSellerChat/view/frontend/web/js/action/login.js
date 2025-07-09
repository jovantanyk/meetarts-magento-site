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
            action = function (loginData, customerLoggedIn) {
                socketProvider.setShowLoader(true);
                return storage.post(
                    'customer/ajax/login',
                    JSON.stringify(loginData),
                    true
                ).done(function (response) {
                    socketProvider.setShowLoader(false);
                    if (response.errors) {
                        alert({
                            title: 'Error!',
                            content: response.message,
                            actions: {
                                always: function () {
                                }
                            }
                        });
                    } else {
                        customerLoggedIn(true);
                        location.reload();
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
                });
            };

        action.registerLoginCallback = function (callback) {
            callbacks.push(callback);
        };

        return action;
    }
);