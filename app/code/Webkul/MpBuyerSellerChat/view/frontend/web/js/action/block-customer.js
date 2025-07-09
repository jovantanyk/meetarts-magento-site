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

        return function (customerData) {
            var serviceUrl,
                payload;

            /**
             * Checkout for guest and registered customer.
             */
            serviceUrl = 'rest/V1/customer/me/chat/:sellerId/block';
            payload = {
                customerData: customerData
            };
            //socketProvider.setShowLoader(true);
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            );
        };
    }
);
