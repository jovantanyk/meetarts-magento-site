/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'jquery',
        'mage/storage',
        'Webkul_MpBuyerSellerChat/js/model/socket-provider'
    ],
    function ($, storage, socketProvider) {
        'use strict';

        return function (details, chatStarted, customerData) {
            var serviceUrl,
                payload;

            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/customer/buyer-start-chat';
            payload = {
                message: details.message,
                sellerId: details.receiverData.sellerId,
                sellerUniqueId: details.receiverData.receiverUniqueId,
                dateTime: details.dateTime
            };
            socketProvider.setShowLoader(true);
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).fail(function (response) {
                socketProvider.setShowLoader(false);
            }).done(function (response) {
                socketProvider.setShowLoader(false);
                var data = $.parseJSON(response);
                if (data.error == false) {
                    chatStarted(true);
                    customerData(data);
                }
            });
        };
    }
);
