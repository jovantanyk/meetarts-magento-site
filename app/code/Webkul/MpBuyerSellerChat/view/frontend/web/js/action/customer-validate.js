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

        return function (sellerProductId, sellerId, sellerEmail) {
            var serviceUrl,
                payload;
            serviceUrl = 'rest/V1/chat/is-customer-validate';
            payload = {
                sellerId: sellerId,
                sellerProductId: sellerProductId,
                sellerEmail: sellerEmail
            };
            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            )
        };
    }
);
