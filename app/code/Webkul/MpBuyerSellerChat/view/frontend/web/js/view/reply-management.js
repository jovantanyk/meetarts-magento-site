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
define([
    'jquery',
    'mage/template',
    'uiComponent',
    'mage/validation',
    'underscore',
    'ko',
    'Magento_Customer/js/customer-data',
    'Webkul_MpBuyerSellerChat/js/model/socket-provider',
    'Webkul_MpBuyerSellerChat/js/model/message-sender',
    'Webkul_MpBuyerSellerChat/js/action/start-chat',
    'Webkul_MpBuyerSellerChat/js/action/load-history',
    'Webkul_MpBuyerSellerChat/js/action/customer-available-check',
    'Webkul_MpBuyerSellerChat/js/action/login',
    'Webkul_MpBuyerSellerChat/js/action/register',
    'Webkul_MpBuyerSellerChat/js/action/customer-validate',
    'mage/translate'
], function (
    $,
    mageTemplate,
    Component,
    validation,
    _,
    ko,
    customerData,
    socketProvider,
    messageProvider,
    startChatAction,
    loadHistoryAction,
    checkCustomerAction,
    loginAction,
    registerAction,
    customerValidate
) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Webkul_MpBuyerSellerChat/chatbox/reply-management'
            },
            customerLoggedIn: ko.observable(window.mpChatboxConfig.isCustomerLoggedIn),
            attachmentImg: ko.observable(window.chatboxCoreConfig.attachmentImage),
            isServerRunning: socketProvider.isServerRunning(),
            chatStarted: socketProvider.isChatStarted(),
            customerMessageReceived: messageProvider.isCustomerHasNewMessage(),
            chatConfig: window.mpChatboxConfig,
            customerData: ko.observable(window.mpChatboxConfig.customerData),
            sellerAvailable: socketProvider.getSellerOnline(),
            isChatError: messageProvider.getChatError(),
            chatErrorData: messageProvider.getChatErrorData(),
            sellerImage: ko.observable(window.mpChatboxConfig.sellerData.image),
            isRegistration: ko.observable(false),
            attachedImageData: ko.observable(null),
            blockedData: messageProvider.isCustomerBlocked(),
            isBlocked: ko.observable(false),
            showFileLoader: ko.observable(false),
            uploadPercentage: ko.observable(''),
            chat: {},
            initialize: function () {
                this._super();
                let sections = ['mpbuyerchat-data'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);
                var self = this,
                mpchatData = customerData.get('mpbuyerchat-data');

                this.update(mpchatData());
                this.resTmpl = mageTemplate('#customer_reply_template');
                this.notifyTmpl = mageTemplate('#notification-template');

                this.isBlocked(
                    self.isBlockedCheck()
                );

                this.chatStarted.subscribe(function (newValue) {
                    if (newValue == true) {
                        self.customerData.subscribe(function (customerData) {
                            if (customerData.customerImage == '') {
                                socketProvider.setCustomerProfile(window.chatboxCoreConfig.customerImage);
                                customerData.customerImage = window.chatboxCoreConfig.customerImage;
                            }
                            socketProvider.setCustomerConected(customerData, self.chatConfig.sellerData);
                            socketProvider.setIsChatStarted(newValue);
                            socketProvider.setCustomerProfile(customerData.customerImage);
                        });
                    }
                });

                $('body').delegate('#mpchatbox-component .smiley_pad > .emoji', 'click', function (event) {

                    var emoji = $(this).attr('alt');
                    $(this).parents('.message-box').children('textarea').val(function (i, text) {
                        return text + emoji;
                    });
                    $(this).parents('.message-box').children('textarea').focus();
                });
                this._manageSocketResponse();
            },

            loadHistoryOnLoad: function () {
                if (this.chatStarted() === true) {
                    this.loadChatHistory(this.customerData());
                }
            },
            /**
             * start buyer chat
             */
            startChat: function (startChatForm) {
                var self = this;
                var checkData = {};
                var chatData = {},
                    formDataArray = $(startChatForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    chatData[entry.name] = entry.value;
                });
                    var mpchatData = customerData.get('mpbuyerchat-data');
                    if(_.contains(
                        mpchatData().customerData.blockedBySellers,
                        this.chatConfig.sellerData.receiverUniqueId
                    )){
                        this.isBlocked(true);
                        return;
                    }

                chatData.dateTime = socketProvider.getDate() + ' ' + socketProvider.getTime();
                chatData.receiverData = socketProvider.getReceiverData('seller');
                chatData.message = chatData.message.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, "");
                if (chatData.message == '') {
                    return false;
                }
                if (self.isBlocked()) {
                    self.isChatError(true);
                    self.chatErrorData($.mage.__('Seller is not available for chat.'));
                    return;
                }
                /**
                 * before check seller is available or not
                 */
                checkData.customerId = chatData.receiverData.sellerId;
                if (_.isNull(checkData.customerId)) {
                    checkData.customerId = 0;
                }
                checkData.type = 'seller';
                socketProvider.setShowLoader(true);
                checkCustomerAction(checkData).fail(function (response) {
                    socketProvider.setShowLoader(false);
                    if (response.status == 401) {
                        location.reload();
                    }
                }).done(function (response) {
                    socketProvider.setShowLoader(false);
                    var responseData = $.parseJSON(response);
                    self.sellerAvailable(false); //change seller status
                    startChatAction(chatData, self.chatStarted, self.customerData).then(function () {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'receiverUniqueId'; // 'the key/name of the attribute/field that is sent to the server
                        input.value = chatData.receiverData.receiverUniqueId;
                        startChatForm.appendChild(input);

                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'senderUniqueId'; // 'the key/name of the attribute/field that is sent to the server
                        input.value = self.customerData().customerUniqueId;
                        startChatForm.appendChild(input);

                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'customerName'; // 'the key/name of the attribute/field that is sent to the server
                        input.value = self.customerData().customerName;
                        startChatForm.appendChild(input);
                        var customerUniqueId = self.customerData().customerUniqueId;

                        self.loadChatHistory(self.customerData(), startChatForm, customerUniqueId);
                    });

                    self.sellerAvailable(true);//change seller status
                    if (responseData.available != true) {
                        self.isChatError(true);
                        self.chatErrorData($.mage.__('Seller is not available for chat.'));
                    }
                });
            },
            openEmojiBox: function (data, event) {
                if ($(event.currentTarget).hasClass('open')) {
                    $(event.currentTarget).removeClass('open');
                } else {
                    $(event.currentTarget).addClass('open');
                }
            },
            enableRegiserForm: function () {
                this.isRegistration(true);
            },

            enableLoginForm: function () {
                this.isRegistration(false);
            },

            register: function (registerForm) {
                var registerData = {},
                    formDataArray = $(registerForm).serializeArray();
                formDataArray.forEach(function (entry) {
                    registerData[entry.name] = entry.value;
                    
                });
                if ($(registerForm).validation() &&
                $(registerForm).validation('isValid')
                ) {
                    registerAction(registerData, this.customerLoggedIn);
                }
            },

            /**
             * login customer by chat window
             */
            login: function (loginForm) {
                var loginData = {},
                    formDataArray = $(loginForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    loginData[entry.name] = entry.value;
                });
                var sellerProductId = this.chatConfig.sellerData.sellerProductId;
                var sellerId = this.chatConfig.sellerData.sellerId;
                var sellerEmail = JSON.stringify(loginData['username']);
                customerValidate(sellerProductId, sellerId, sellerEmail).fail(function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                }).done(function (response) {
                    if (response === true) {
                        $('#mpchatbox-component').css('display','none');
                    }
                });
                loginAction(loginData, this.customerLoggedIn);
            },

            /**
             * get response from customers
             */
            _manageSocketResponse: function () {
                var self = this;

                this.customerMessageReceived.subscribe(function (newMessageData) {
                    $('#chat').trigger('click');
                    newMessageData.image = self.sellerImage();
                    if ($('#chat-box-' + newMessageData.senderUniqueId).length) {
                        var data = newMessageData,
                            resTmpl;
                        if (data !== 'undefined') {
                            resTmpl = self.resTmpl({
                                data: data
                            });
                            $(resTmpl)
                                .appendTo($('#chat-box-' + newMessageData.senderUniqueId + ' .discussion'));
                        }
                        if (newMessageData.message_type == "image") {
                            newMessageData.message = $.mage.__('You have received an image.');
                        }
                        if (newMessageData.message_type == "file") {
                            newMessageData.message = $.mage.__('You have received a file.');
                        }
                        socketProvider.showNotification(newMessageData);
                        $('#chat-box-' + newMessageData.senderUniqueId + ' .reply-container').animate({
                            scrollTop: $('#chat-box-' + newMessageData.senderUniqueId + ' .reply-container')[0].scrollHeight
                        }, 1000);
                        if ($('#chat-box-' + newMessageData.senderUniqueId).find('.wk_chat_sound').hasClass('enable')) {
                            $('#chat-box-' + newMessageData.senderUniqueId).find('#myAudio').get(0).play();
                        }
                        socketProvider.callEmojify('mpchatbox-component');
                    }

                });

                this.blockedData.subscribe(function (blockData) {
                    if (blockData.customerUniqueId == self.customerData().customerUniqueId) {
                        self.isBlocked(!self.isBlocked());
                    }

                });
            },

            selectFile: function ($model, e) {
                e.stopImmediatePropagation();
                var self = $model,
                    currentElement = $(e.currentTarget),
                    fileType = e.originalEvent.target.files[0].type;
                var restrictedFiles = ["php", "exe", "js"],
                    error = false;
                if (restrictedFiles.indexOf(e.originalEvent.target.files[0].name.split('.').pop()) > -1) {
                    error = true;
                    self.isChatError(true);
                    self.chatErrorData($.mage.__('File type not supported'));
                }
                if (fileType.indexOf("image") >= 0) {
                    var type = 'image';
                } else {
                    var type = 'file';
                }
                if (!error) {
                    self.siofu = socketProvider.getSocketFileUpload();
                    self.siofu.addEventListener("start", function (event) {
                        self.showFileLoader(true);
                    });
                    // Do something on upload progress: 
                    self.siofu.addEventListener("progress", function (event) {
                        var percent = event.bytesLoaded / event.file.size * 100;
                        self.uploadPercentage("File is " + percent.toFixed(2) + "% percent loaded");
                    });

                    // Do something when a file is uploaded: 
                    self.siofu.addEventListener("complete", function (event) {
                        self.showFileLoader(false);
                        var replyData = {};
                        replyData.message_type = type;
                        replyData.content = event.detail.fileName;
                        self.attachedImageData(replyData);
                        $(e.target.form).submit();
                    });

                    self.siofu.addEventListener("error", function (data) {
                        if (data.code === 1) {
                            self.isChatError(true);
                            self.chatErrorData($.mage.__('Maximum allowed size is ' + window.chatboxCoreConfig.maxFileSize + 'MB'));
                        }
                        self.showFileLoader(false);
                    });

                    self.siofu.maxFileSize = parseInt(window.chatboxCoreConfig.maxFileSize) * 1024 * 1024;

                    self.siofu.listenOnInput(e.currentTarget);
                }
            },

            addAttachment: function () {
                $("#attachment-file").find('.msg-attachment').trigger('click');
            },

            /**
             * Send Message to seller by customer
             */
            sendCustomerMessage: function (sendMessageForm) {
                var self = this;

                var socket = socketProvider.getSocketObject();
                var sendData = {},
                    formDataArray = $(sendMessageForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    sendData[entry.name] = entry.value;
                });
                sendData.message_type = "text";
                sendData.dateTime = socketProvider.getDate() + ' ' + socketProvider.getTime();
                sendData.message = sendData.message.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, "");
                if (!_.isNull(self.attachedImageData()) && self.attachedImageData().content) {
                    sendData.message = self.attachedImageData().content;
                    sendData.message_type = self.attachedImageData().message_type;
                }
                self.attachedImageData(null);
                if ($.trim(sendData.message) !== '') {
                    sendData.image = self.customerProfileImage();
                    if (_.isEmpty(self.customerData().customerImage)) {
                        sendData.image = window.chatboxCoreConfig.customerImage;
                    }

                    /**
                     * before check seller is available or not
                     */
                    sendData.customerId = socketProvider.getReceiverData('seller').sellerId;
                    sendData.type = 'seller';
                    checkCustomerAction(sendData).fail(function (response) {
                        if (response.status == 401) {
                            location.reload();
                        }
                    }).done(function (response) {
                        var responseData = $.parseJSON(response);
                        self.sellerAvailable(false); //change seller status
                        if (responseData.available == true) {
                            self.isChatError(false);
                        } else {
                            self.isChatError(true);
                            self.chatErrorData($.mage.__('Seller is offline.'));
                        }
                    });

                    /**
                     * send message to seller
                     */
                    if (!_.isUndefined(sendData.message_type) && sendData.message_type !== 'text') {
                        messageProvider.saveMessage(
                            sendData
                        ).fail(function (response) {
                            throw 'unable to send, please check you message.';
                        }).done(function (response) {
                            var data = $.parseJSON(response);
                            if (data.errors == false) {
                                sendData.message = data.message;
                                messageProvider.sendMessageToSeller(sendData);
                                //response = $.parseJSON(response);
                                $(sendMessageForm).trigger('reset');
                                self.appendMessage(sendData);
                            }
                            /*fullScreenLoader.stopLoader();*/
                        });
                    } else {
                        messageProvider.saveMessage(sendData);
                        messageProvider.sendMessageToSeller(sendData);
                        $(sendMessageForm).trigger('reset');
                        self.appendMessage(sendData);
                    }


                }
            },

            /**
             * append Message to chat window
             */
            appendMessage: function (sendData) {
                var self = this;
                sendData.class = 'self';
                var data = sendData,
                    resTmpl;
                if (data !== 'undefined') {
                    resTmpl = self.resTmpl({
                        data: data
                    });
                    $(resTmpl)
                        .appendTo($('.discussion'));
                }
                $('.reply-container').animate({
                    scrollTop: $('.reply-container')[0].scrollHeight
                }, 1000);
                self.sellerAvailable(true);//change seller status
                socketProvider.callEmojify('mpchatbox-component');
            },

            /**
             * send reply by press Enter Key
             */
            replyByEnter: function (data, event) {
                if (event.which == 13 && !event.shiftKey) {
                    $(event.target).parents('form').submit()
                } else if (event.shiftKey && event.keyCode == 13) {
                    return true;
                } else {
                    return true;
                }

            },

            /**
             * get customer Unique Id
             */
            customerUniqueId: function () {
                var self = this,
                    mpchatData = customerData.get('mpbuyerchat-data');
                var customerUniqueId = mpchatData().customerData.customerUniqueId;
                return customerUniqueId;
            },

            /**
             * get customer Profile Image
             */
            customerProfileImage: function () {
                var self = this,
                    mpchatData = customerData.get('mpbuyerchat-data');
                var customerImage = mpchatData().customerData.customerImage;
                return customerImage;
            },

            /**
             * check and update isBlocked
             */
            isBlockedCheck: function () {
                var self = this;
                var receiverUniqueId = this.chatConfig.sellerData.receiverUniqueId;
                let sections = ['mpbuyerchat-data'];
                customerData.invalidate(sections);
                customerData.reload(sections, true).promise().then(function(val) {
                    var mpchatData = customerData.get('mpbuyerchat-data');
                    return _.contains(
                                mpchatData().customerData.blockedBySellers,
                                receiverUniqueId
                            );
                  }).then(function(result) {
                    updateIsBlocked(result);
                  });
                  function updateIsBlocked(result) {
                    self.isBlocked(result);
                  }
            },

            /**
             * load customer chat history when chat window open
             */
            loadChatHistory: function (data, startChat = false, customerUniqueId = null) {

                var self = this;
                if (customerUniqueId != null) {
                    data.customerUniqueId = customerUniqueId;
                } else {
                    data.customerUniqueId = self.customerUniqueId();
                }
                data.senderUniqueId = this.chatConfig.sellerData.receiverUniqueId;
                data.loadTime = 1;
                socketProvider.setShowLoader(true);
                loadHistoryAction(data).fail(function (response) {
                    socketProvider.setShowLoader(false);
                    //error logic
                }).done(function (response) {
                    socketProvider.setShowLoader(false);
                    var responseData = $.parseJSON(response);
                    $('#mpchatbox-component .discussion').html('');
                    _.each(responseData.messages, function (value) {
                        if (_.isEqual(data.customerUniqueId, value.sender_unique_id)) {
                            value.class = 'self';
                            value.image = self.customerProfileImage();
                            if (self.customerData().customerImage == '') {
                                value.image = window.chatboxCoreConfig.customerImage;
                            }
                        } else {
                            value.class = 'other';
                            value.image = self.sellerImage();
                            if (self.sellerImage() == '') {
                                value.image = window.chatboxCoreConfig.sellerImage;
                            }
                        }
                        value.dateTime = value.date;

                        var resTmpl;
                        if (value !== 'undefined') {
                            resTmpl = self.resTmpl({
                                data: value
                            });
                            $(resTmpl)
                                .appendTo($('#mpchatbox-component .discussion'));
                        }
                    });
                    if (startChat !== false) {
                        self.sendCustomerMessage(startChat);
                    }
                    if ($('.reply-container').length) {
                        $('.reply-container').animate({
                            scrollTop: $('.reply-container')[0].scrollHeight
                        }, 1000);
                    }
                    socketProvider.callEmojify('mpchatbox-component');
                });
            },

        /**
         * Update chatbox content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedChat) {
            _.each(updatedChat, function (value, key) {
                if (!this.chat.hasOwnProperty(key)) {
                    this.chat[key] = ko.observable();
                }
                this.chat[key](value);
            }, this);
        },
        });
    });