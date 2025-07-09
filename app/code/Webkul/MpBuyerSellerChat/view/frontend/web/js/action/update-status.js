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

        return function (statusData) {
            var serviceUrl,
                payload;
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/chat/change-status';
            payload = {
                status: statusData.status,
                type: statusData.type
            };
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            );
        };
    }
);
