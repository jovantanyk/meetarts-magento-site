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
        'ko',
        'underscore',
        'Webkul_MpBuyerSellerChat/js/model/socket-provider',
        'Webkul_MpBuyerSellerChat/js/action/save-message',
        'Magento_Customer/js/customer-data'
    ],
    function (ko, _, socketProvider, saveMessageAction, storage) {
        'use strict';
        var socket = socketProvider.getSocketObject();
        var customerMessageRecieved = ko.observable();
        var sellerMessageRecieved = ko.observable();
        var customerStatusUpdated = ko.observable();
        var sellerStatusUpdated = ko.observable();
        var totalChatWindows = ko.observable();
        var isChatError = ko.observable(false);
        var chatErrorData = ko.observable('');
        var chatWindows = ko.observableArray();
        var customerBlocked = ko.observable('');
        if (socket !== false) {
            /**
             * if customer recieved message.
             */
            socket.on('customer new message received', function (data) {
                customerMessageRecieved(data);
            });
            /**
             * if customer recieved message.
             */
            socket.on('seller new message received', function (data) {
                sellerMessageRecieved(data);
            });

            /**
             * if customer status change and notify seller about it.
             */
            socket.on('send customer status change', function (data) {
                customerStatusUpdated(data);
            });

            /**
             * if seller status change and notify customers about it.
             */
            socket.on('send seller status change', function (data) {
                sellerStatusUpdated(data);
            });

            /**
             * if customer status change and notify seller about it.
             */
            socket.on('customer blocked by seller', function (data) {
                customerBlocked(data);
            });
        }
        return {
            /**
             * send new message to customer by seller
             */
            sendMessageToCustomer: function (sendDetails) {
                var socket = socketProvider.getSocketObject();
                if (socket !== false) {
                    socket.emit('seller send new message', sendDetails);
                }

            },

            /**
             * send new message to seller by customer
             */
            sendMessageToSeller: function (sendDetails) {
                var socket = socketProvider.getSocketObject();
                if (socket !== false) {
                    socket.emit('customer send new message', sendDetails);
                }
            },

            saveMessage: function (sendDetails) {
                return saveMessageAction(sendDetails); //save message to database
            },

            sellerStatusChange: function (sendDetails) {
                var socket = socketProvider.getSocketObject();
                if (socket !== false) {
                    socket.emit('seller status change', sendDetails);
                }
            },
            sellerBlockCustomer: function (data) {
                var socket = socketProvider.getSocketObject();
                if (socket !== false) {
                    socket.emit('customer block event', data);
                }
            },

            customerStatusChange: function (sendDetails) {
                var socket = socketProvider.getSocketObject();
                if (socket !== false) {
                    socket.emit('customer status change', sendDetails);
                }
            },

            /**
             * return observer to reply-management.js
             */
            isCustomerBlocked: function () {
                return customerBlocked;
            },

            /**
             * return observer to reply-management.js
             */
            isCustomerHasNewMessage: function () {
                return customerMessageRecieved;
            },
            /**
             * return observer to active-model.js
             */
            isSellerHasNewMessage: function () {
                return sellerMessageRecieved;
            },
            /**
             * return observer to active-model.js
             */
            isCustomerStatusChanged: function () {
                return customerStatusUpdated;
            },

            /**
             * return observer to active-model.js
             */
            isSellerStatusChanged: function () {
                return sellerStatusUpdated;
            },
            /**
             * set if chat has an error
             */
            setChatError: function (value) {
                isChatError(value);
            },
            /**
             * return observable
             */
            getChatError: function () {
                return isChatError;
            },
            /**
             * set chat text
             */
            setChatErrorData: function (value) {
                chatErrorData(value);
            },
            /**
             * get chat text
             */
            getChatErrorData: function () {
                return chatErrorData;
            },

            /**
             * return total chat windows opened on seller panel
             */
            getTtotalChatWindows: function () {
                return totalChatWindows();
            },
            /**
             * return array of chat windows
             */
            getChatWindows: function () {
                return chatWindows();
            },
            /**
             * set total chat windows opened on seller panel
             */
            setTtotalChatWindows: function (value) {
                return totalChatWindows(value);
            },
            /**
             * set total chat windows to array
             */
            setChatWindows: function (value) {
                return chatWindows.push(value);
            },
            /**
             * remove chat window from array
             */
            remove: function (array, from, to) {
                var rest = array.slice((to || from) + 1 || array.length);
                array.length = from < 0 ? array.length + from : from;
                return array.push.apply(array, rest);
            },
            //displays the popups. Displays based on the maximum number of popups that can be displayed on the current viewport width
            displayChatWindow: function () {
                var right = 0;
                var self = this;
                var iii = 0;

                for (iii; iii < totalChatWindows(); iii++) {
                    if (chatWindows()[iii] != undefined) {
                        var element = document.getElementById(chatWindows()[iii]);
                        element.style.right = right + "px";
                        right = right + 295;
                        if (element.classList.contains('_show') == false) {
                            element.className += ' _show';
                        }

                    }
                }

                for (var jjj = iii; jjj < chatWindows().length; jjj++) {
                    var element = document.getElementById(chatWindows()[jjj]);
                    element.classList.remove("_show");
                }
            },
            //calculate the total number of popups suitable and then populate the toatal_popups variable.
            calculateChatWindows: function () {
                var self = this;
                var width = window.innerWidth;

                if (width < 350) {
                    totalChatWindows(0);
                } else {
                    //width = width - 200;
                    //320 is width of a single popup box
                    totalChatWindows(parseInt(width / 320));
                }
                self.displayChatWindow();
            },
        };
    }
);
