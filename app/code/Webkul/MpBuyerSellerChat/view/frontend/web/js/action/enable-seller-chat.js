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
    ],
    function ($, storage) {
        'use strict';

        return function (chatEnabled, showLoader) {
            var serviceUrl,
                payload;

            serviceUrl = 'rest/V1/chat/enable-chat';
            payload = {};
            showLoader(true);
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(function (response) {

            }).done(function (response) {
                var data = $.parseJSON(response);
                location.reload();
            });
        };
    }
);
