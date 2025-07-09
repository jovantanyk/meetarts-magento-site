/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function ($, io) {
    'use strict';
    $.widget('mage.startStopServer', {
        options: {},
        _create: function () {
            //console.log(io);
            var self = this;
            //start node server
            $(self.options.startButton).on('click', function () {
                self._serverStart();
            });
            //stop node server
            $(self.options.stopButton).on('click', function () {
                self._serverStop();
            });
        },
        _serverStart: function () {
            var self = this;
            var hostName = $(self.options.configForm + ' #buyer_seller_chat_config_host_name').val();
            var port = $(self.options.configForm + ' #buyer_seller_chat_config_port_number').val();
            new Ajax.Request(self.options.startUrl, {
                method: 'post',
                data: { form_key: window.FORM_KEY },
                parameters: { hostname: hostName, port: port },
                onSuccess: function (transport) {
                    var response = $.parseJSON(transport.responseText);
                    if (response.error) {
                        $('<div>').html(response.message)
                            .modal({
                                title: $.mage.__('Server Status'),
                                autoOpen: true,
                                buttons: [{
                                    text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }]
                            });
                    } else {
                        location.reload();
                    }
                }
            });
        },
        _serverStop: function () {
            var self = this;
            var hostName = $(self.options.configForm + ' #buyer_seller_chat_config_host_name').val();
            var port = $(self.options.configForm + ' #buyer_seller_chat_config_port_number').val();
            new Ajax.Request(self.options.stopUrl, {
                method: 'post',
                data: { form_key: window.FORM_KEY },
                parameters: { hostname: hostName, port: port },
                onSuccess: function (transport) {
                    var response = $.parseJSON(transport.responseText);
                    if (response.error) {
                        $('<div>').html(response.message)
                            .modal({
                                title: $.mage.__('Server Status'),
                                autoOpen: true,
                                buttons: [{
                                    text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }]
                            });
                    } else {
                        location.reload();
                    }
                }
            });
        },

    });
    return $.mage.startStopServer;
});