/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/payment/additional-validators',
], function ($,
        Component,
        placeOrderAction,
        additionalValidators,
        ) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'SoftBuild_HitPay/hitpay',
            visa: window.checkoutConfig.payment.hitpay.status.visa,
            master: window.checkoutConfig.payment.hitpay.status.master,
            american_express: window.checkoutConfig.payment.hitpay.status.american_express,
            apple_pay: window.checkoutConfig.payment.hitpay.status.apple_pay,
            google_pay: window.checkoutConfig.payment.hitpay.status.google_pay,
            paynow: window.checkoutConfig.payment.hitpay.status.paynow,
            grabpay: window.checkoutConfig.payment.hitpay.status.grabpay,
            wechatpay: window.checkoutConfig.payment.hitpay.status.wechatpay,
            alipay: window.checkoutConfig.payment.hitpay.status.alipay,
            shopeepay: window.checkoutConfig.payment.hitpay.status.shopeepay,
            fpx: window.checkoutConfig.payment.hitpay.status.fpx,
            zip: window.checkoutConfig.payment.hitpay.status.zip,
            atomeplus: window.checkoutConfig.payment.hitpay.status.atomeplus,
            unionbank: window.checkoutConfig.payment.hitpay.status.unionbank,
            qrph: window.checkoutConfig.payment.hitpay.status.qrph,
            pesonet: window.checkoutConfig.payment.hitpay.status.pesonet,
            gcash: window.checkoutConfig.payment.hitpay.status.gcash,
            billease: window.checkoutConfig.payment.hitpay.status.billease,
        },
        getInstructions: function () {
            return window.checkoutConfig.payment.instructions[this.item.method];
        },
        getData: function () {
            return {
                "method": 'hitpay',
                "additional_data": {
                }
            };
        },
        getHitpayLogoPath: function (logo) {
            return window.checkoutConfig.payment.hitpay.images[logo];
        },
        placeOrder: function (data, event) {
            if (event) {
                event.preventDefault();
            }
            var self = this,
                    placeOrder;
            if (additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);
                placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                $.when(placeOrder).fail(function () {
                    self.isPlaceOrderActionAllowed(true);
                }).done(this.afterPlaceOrder.bind(this));
                return true;
            }
            return false;
        },
        afterPlaceOrder: function () {
            var method = this.getCode();
            var urlRedirect = window.checkoutConfig.payment[method].redirectUrl;
            window.location.replace(urlRedirect);
        }
    });
});