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
        'mage/storage'
    ],
    function ($, storage) {
        'use strict';

        return function (historyData) {
            var serviceUrl,
                payload;
            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/chat/mp-load-history';
            payload = {
                senderUniqueId: historyData.senderUniqueId,
                receiverUniqueId: historyData.customerUniqueId,
                loadTime: historyData.loadTime
            };
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            )
        };
    }
);
