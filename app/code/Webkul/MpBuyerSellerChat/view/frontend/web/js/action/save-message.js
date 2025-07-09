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
        'Magento_Ui/js/modal/alert'
    ],
    function ($, storage, alert) {
        'use strict';

        return function (messageData) {
            var serviceUrl,
                payload;

            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/message/save-message';
            payload = {
                senderUniqueId: messageData.senderUniqueId,
                receiverUniqueId: messageData.receiverUniqueId,
                message: messageData.message,
                dateTime: messageData.dateTime,
                msgType: messageData.message_type
            };

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            );
        };
    }
);