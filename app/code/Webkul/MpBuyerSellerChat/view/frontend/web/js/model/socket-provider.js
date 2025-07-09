/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'underscore',
        'Webkul_MpBuyerSellerChat/js/socket.io',
        'Webkul_MpBuyerSellerChat/js/client',
        'Magento_Customer/js/customer-data',
        'Webkul_MpBuyerSellerChat/js/emoji.min',
        'Webkul_MpBuyerSellerChat/js/bin/push.min'
    ],
    function (ko, _, io, SocketIOFileUpload, storage, emojify, push) {
        'use strict';

        var socketWorking = ko.observable(false);
        var socketObject = ko.observable(false);
        var socketIOFileUpload = ko.observable(false);
        var showLoader = ko.observable(false);
        var isSellerOnline = ko.observable(false);
        var chatStarted = ko.observable(false);
        var userBlockedList = ko.observableArray([]);
        var customerProfile = ko.observable('');
        var customerStatusOnSellerEnd = ko.observable('');
        var chatBoxData = window.mpChatboxConfig;
        if (window.chatboxCoreConfig.serverRunning === true) {
            var host = window.chatboxCoreConfig.host;
            socketObject(io(host));
            socketWorking(true);
            socketIOFileUpload(new SocketIOFileUpload(socketObject()));
        }
        var getBuyerChatStatus = function () {
            let sections = ['mpbuyerchat-data'];
            storage.invalidate(sections);
            var self = this,
            mpchatData = storage.get('mpbuyerchat-data');
            var customerStatus = mpchatData().customerData;
            if(_.isUndefined(customerStatus)) {
                return false;
            } else {
                var customerStatus = mpchatData().customerData.chatStatus;
            }
            if (!_.isUndefined(chatBoxData) && _.isUndefined(customerStatus)) {
                return false;
            }
            if (!_.isUndefined(chatBoxData) && customerStatus != 0) {
                chatStarted(true);
                return true;
            }
            return false;
        };
        chatStarted(getBuyerChatStatus());

        return {
            getSocketObject: function () {
                if (socketWorking()) {
                    return socketObject();
                }
                return false;
            },

            getSocketFileUpload: function () {
                return new SocketIOFileUpload(this.getSocketObject());
                //eturn socketIOFileUpload();
            },

            isServerRunning: function () {
                return socketWorking;
            },
            isChatStarted: function () {
                return chatStarted;
            },
            setIsChatStarted: function (value) {
                chatStarted(value);
            },

            getIsUserBlocked: function (value) {
                return userBlockedList;
            },

            setIsUserBlocked: function (value) {
                userBlockedList.push(value);
            },


            /**
             * send socket event when seller logged in
             */
            setSellerConected: function (sellerDetails) {
                if (socketWorking()) {
                    var socket = socketObject();
                    socket.emit('newSellerConneted', sellerDetails);
                }
            },
            /**
             * send socket event when customer logged in
             */
            setCustomerConected: function (customerDetails, sellerDetails) {
                var socket = socketObject();
                var details = {};
                details.customerData = customerDetails;
                details.sellerId = sellerDetails.sellerId;
                details.customerUniqueId = customerDetails.customerUniqueId;
                details.receiverUniqueId = sellerDetails.receiverUniqueId;
                if (socketWorking()) {
                    socket.emit('newCustomerConneted', details);
                }
            },

            getReceiverData: function (receiverType) {
                if (receiverType == 'seller') {
                    return chatBoxData.sellerData;
                }
            },

            getSellerOnline: function () {
                if (_.isUndefined(chatBoxData.sellerData.sellerOnline)) {
                    return isSellerOnline;
                }
                return isSellerOnline;
            },
            setSellerOnline: function (value) {
                isSellerOnline(value);
            },
            /**
             * show loader set to true/false
             */
            setShowLoader: function (value) {
                showLoader(value);
            },
            /**
             * show loader
             */
            getshowLoader: function () {
                return showLoader;
            },
            /**
             * set customer chat status for seller panel
             */
            setCustomerStatusOnSellerEnd: function (value) {
                customerStatusOnSellerEnd(value);
            },
            /**
             * get customer chat status on seller panel
             */
            getCustomerStatusOnSellerEnd: function () {
                return customerStatusOnSellerEnd();
            },
            /**
             * set customer profile image
             */
            setCustomerProfile: function (value) {
                return customerProfile(value);
            },
            /**
             * get customer profile image
             */
            getCustomerProfile: function () {
                return customerProfile;
            },
            getDate: function () {
                var now = new Date();
                var year = "" + now.getFullYear();
                var month = "" + (now.getMonth() + 1);
                if (month.length == 1) {
                    month = "0" + month;
                }
                var day = "" + now.getDate(); if (day.length == 1) {
                    day = "0" + day;
                }
                return year + "-" + month + "-" + day + " ";
            },
            getTime: function () {
                var date = new Date();
                var hours = date.getHours() > 12 ? date.getHours() - 12 : date.getHours();
                var am_pm = date.getHours() >= 12 ? "PM" : "AM";
                hours = hours < 10 ? "0" + hours : hours;
                var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
                var seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
                var time = hours + ":" + minutes + ":" + seconds + " " + am_pm;

                return time;
            },
            callEmojify: function (id) {
                emojify.setConfig({
                    tag_type: 'img',           // Only run emojify.js on this element
                    only_crawl_id: id,            // Use to restrict where emojify.js applies
                    img_dir: window.chatboxCoreConfig.emojiImagePath,  // Directory for emoji images
                    ignored_tags: {                // Ignore the following tags
                        'SCRIPT': 1,
                        'TEXTAREA': 1,
                        'A': 1,
                        'PRE': 1,
                        'CODE': 1
                    }
                });
                emojify.run();
            },
            showNotification: function (data) {
                var self = this,
                    message = '';

                push.Permission.request(onGranted, onDenied);

                function onGranted() {
                    push.create(data.customerName, {
                        title: '',
                        body: data.message,
                        icon: data.image,
                        tag: 'notification' + data.senderUniqueId,
                        timeout: 8000,
                        onClick: function () {
                            window.focus();
                            this.close();
                        },
                    });
                }
                function onDenied(status) {
                    console.log('Notification permission status: denied');
                }
            }
        };
    }
);
