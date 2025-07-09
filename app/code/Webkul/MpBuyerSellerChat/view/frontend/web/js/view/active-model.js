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
    'jquery',
    'mage/template',
    'uiComponent',
    'mage/validation',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'Webkul_MpBuyerSellerChat/js/model/socket-provider',
    'Webkul_MpBuyerSellerChat/js/model/message-sender',
    'Webkul_MpBuyerSellerChat/js/action/load-history',
    'Webkul_MpBuyerSellerChat/js/action/update-status',
    'Webkul_MpBuyerSellerChat/js/action/update-profile',
    'Webkul_MpBuyerSellerChat/js/action/enable-seller-chat',
    'Webkul_MpBuyerSellerChat/js/chatbox',
    'Webkul_MpBuyerSellerChat/js/action/customer-available-check',
    'Webkul_MpBuyerSellerChat/js/action/block-customer',
    'mage/translate'
], function (
    $,
    mageTemplate,
    Component,
    validation,
    ko,
    _,
    modal,
    socketProvider,
    messageProvider,
    loadHistoryAction,
    updateStatusAction,
    updateProfile,
    startSellerChat,
    chatbox,
    checkCustomerAction,
    blockUserAction
) {
        'use strict';
        return Component.extend({
            chatEnabled: ko.observable(window.sellerChatboxConfig.chatEnabled),
            enabledCustomerList: ko.observableArray([]),
            showProfileBox: ko.observable(false),
            chatName: window.chatboxCoreConfig.chatName,
            sellerProfile: ko.observable(window.sellerChatboxConfig.sellerChatData.sellerImage),
            isServerRunning: socketProvider.isServerRunning(),
            customerStatusOnSellerEnd: messageProvider.isCustomerStatusChanged(),
            sellerMessageRecieved: messageProvider.isSellerHasNewMessage(),
            sellerChatStatus: ko.observable(),
            totalChatWindows: messageProvider.getTtotalChatWindows(),
            chatWindows: messageProvider.getChatWindows(),
            getSoundUrl: window.chatboxCoreConfig.soundUrl,
            showSellerLoader: ko.observable(false),
            blockUserUniqueId: ko.observable(''),
            showBlockBox: ko.observable(false),
            searchQuery: ko.observable(''),
            tempEnabledCustomerList: ko.observableArray([]),
            // customerBlocked: socketProvider.getBlockObervable(),
            blockedList: ko.observableArray(window.sellerChatboxConfig.blockedCustomerData),
            defaults: {
                template: 'Webkul_MpBuyerSellerChat/view/active-model'
            },
            initialize: function () {
                var self = this;
                this._super();

                this.resTmpl = mageTemplate('#reply_template');
                this.notifyTmpl = mageTemplate('#notification-template');

                /**
                 * update the open chat windows on page resize.
                 */

                window.addEventListener("load", messageProvider.calculateChatWindows(this));
                $(window).resize(function () {
                    messageProvider.calculateChatWindows(this)
                });

                if (_.isUndefined(window.sellerChatboxConfig.chatEnabled) === false) {
                    socketProvider.setSellerConected(window.sellerChatboxConfig.sellerChatData);
                }
                if (!_.isUndefined(window.sellerChatboxConfig.sellerChatData)) {
                    var statusCode = window.sellerChatboxConfig.sellerChatData.chatStatus;
                    var statusClass = self.getCustomerStatus(parseInt(statusCode));
                    self.sellerChatStatus(statusClass);
                }
                /**
                 * assign chat customer on page refresh for seller.
                 */
                if (_.isEmpty(this.enabledCustomerList())) {
                    _.each(window.sellerChatboxConfig.enableUserData, function (data) {
                        self.enabledCustomerList.push(data);
                    });

                }
                this.tempEnabledCustomerList(this.enabledCustomerList.slice());

                this.enabledCustomerList = ko.pureComputed(function() {
                    var query = self.searchQuery();
                    if (query) {
                        return self.tempEnabledCustomerList().filter(function(i) {
                        return i.customerName.toLowerCase().indexOf(query) >= 0;
                        });
                    } else {
                        return self.tempEnabledCustomerList();
                    }
                });

                $('body').delegate('#chat_window_container .smiley_pad > .emoji', 'click', function (event) {

                    var emoji = $(this).attr('alt');
                    $(this).parents('.message-box').children('textarea').val(function (i, text) {
                        return text + emoji;
                    });
                    $(this).parents('.message-box').children('textarea').focus();
                });

                this._manageSocketResponse();
            },
            /**
             * open seller right chat panel
             */
            openChatPanel: function (data, el) {
                this.showProfileBox(false);
                if ($(el.target).hasClass('opened')) {
                    this.closeChatPanel();
                } else {
                    $('.chat__menu').addClass('_show');
                    $(el.target).addClass('opened');
                }
            },
            /**
             * hide seller right chat panel
             */
            closeChatPanel: function () {
                $('.chat__menu').removeClass('_show');
                $('.pannel-control').removeClass('opened');
                $('.wk_control_status').removeClass('_expended');
                $('.wk_chat_status_options').css('display','none');
                $('.wk_chat_setting').removeClass('_expended');
                $('.wk_chat_setting_options').css('display','none');
            },
            /**
             * enable disable chat sound on seller panel
             */
            enableDisableSound: function (data, event) {
                if ($(event.target).hasClass('disable')) {
                    $(event.target).removeClass('disable');
                    $(event.target).addClass('enable');
                    $(event.target).removeClass('fa-volume-off');
                    $(event.target).addClass('fa-volume-up');
                } else {
                    $(event.target).removeClass('enable');
                    $(event.target).addClass('disable');
                    $(event.target).removeClass('fa-volume-up');
                    $(event.target).addClass('fa-volume-off');
                }
            },
            /**
             * upload image
             */
            uploadProfileImage: function (profileForm) {
                var data = new FormData();
                data.append('type', 'seller');
                data.append('file', $('#seller_profile_image')[0].files[0]);
                updateProfile(data, $('.profile-setting-box'), this.showSellerLoader, this.sellerProfile);
                this.showProfileBox(false);
            },

            /**
             * show image when seller select
             */
            showSelectedImage: function () {
                var oFReader = new FileReader();
                if (!_.isUndefined(document.getElementById("seller_profile_image").files[0])) {
                    oFReader.readAsDataURL(document.getElementById("seller_profile_image").files[0]);

                    oFReader.onload = function (oFREvent) {
                        document.getElementById("seller-profile-image").src = oFREvent.target.result;
                    };
                }
            },

            /**
             * show hide seller profile box
             */
            showHideProfileBox: function () {
                var newValue = !this.showProfileBox();
                this.showProfileBox(newValue);

            },

            /**
             * Called first time for each seller
             */
            enableSellerChat: function () {
                startSellerChat(this.chatEnabled, this.showSellerLoader);
            },
            /**
             *
             */
            getPostData: function ($data) {
                return JSON.stringify($data);
            },

            /**
             * 
             */
            checkIsBlocked: function (uiModel, $data, event) {
                //console.log();
                var isBlocked = false;
                isBlocked = _.contains(
                    this.blockedList(),
                    $data.customerUniqueId
                );
                return isBlocked;
            },

            showblockUserBox: function ($model, $data) {
                $model.blockUserUniqueId($data.customerUniqueId);
                $model.showBlockBox(!$model.showBlockBox());
            },

            closeBlockBox: function () {
                this.showBlockBox(false);
            },

            blockUserFormSubmit: function (blockForm) {
                var data = {},
                    formDataArray = $(blockForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    data[entry.name] = entry.value.replace(/\n/g, "<br>");
                });
                data.block_reason = data.block_reason.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, "");
                if ($.trim(data.block_reason) !== '') {
                    this.blockUser(this, data);
                    this.showBlockBox(false);
                }
            },

            /**
             * Block Customer By Seller
             */
            blockUser: function ($model, $data) {
                $model.showSellerLoader(true);
                blockUserAction($data).fail(function (response) {
                    $model.showSellerLoader(false);
                }).done(function (response) {
                    $model.showSellerLoader(false);
                    var response = $.parseJSON(response);
                    if (response.errors == false) {
                        messageProvider.sellerBlockCustomer(response.data);
                        if (response.blocked == true) {
                            var isBlocked = _.contains(
                                $model.blockedList(),
                                $data.customerUniqueId
                            );
                            if (!isBlocked) {
                                $model.blockedList.push($data.customerUniqueId);
                                console.log();
                                $('#form-reply-' + $data.customerUniqueId).find('.message-box').addClass('disable');
                            }

                        } else {

                            var newValues = _.without($model.blockedList(), $data.customerUniqueId);
                            $model.blockedList(newValues);
                            //socketProvider.setIsBlocked(false);
                            $('#form-reply-' + $data.customerUniqueId).find('.message-box').removeClass('disable');
                        }

                        //location.reload();
                    }
                });
            },

            /**
             * Manage all socket events for seller chat panel
             */
            _manageSocketResponse: function () {
                var self = this;
                var sellerData = window.sellerChatboxConfig.sellerChatData;
                var socket = socketProvider.getSocketObject();
                if (socket !== false) {
                    /**
                     * if any new customer start chat.
                     */
                    socket.on('refresh seller chat list', function (data) {
                        if (!_.contains(_.pluck(self.enabledCustomerList(), 'customerUniqueId'), data.customerUniqueId)) {
                            self.enabledCustomerList().splice(0, 0, data.customerData);
                            self.tempEnabledCustomerList(self.enabledCustomerList().slice());
                        }
                    });
                    /**
                     * append response template to chat window
                     */
                    this.sellerMessageRecieved.subscribe(function (messageData) {

                        var data = {};
                        data.customerUniqueId = messageData.senderUniqueId;
                        if ($('#chat-window-' + messageData.senderUniqueId).length == 0) {
                            var list = $('#customer-' + messageData.senderUniqueId).addClass('msg-notify');
                        } else {
                            $('#chat-window-' + messageData.senderUniqueId + ' header.top-bar').addClass('msg-notify');
                        }
                        /** update the position of chat customer in panel */
                        var object = _.findWhere(self.enabledCustomerList(), { customerUniqueId: messageData.senderUniqueId });
                        var i = self.enabledCustomerList().indexOf(object);
                        if (i >= 1) {
                            var array = self.enabledCustomerList();
                            self.enabledCustomerList().splice(i - 1, 2, array[i], array[i - 1]);
                        }

                        messageData.class = 'other';
                        messageData.error = "";
                        var data = messageData,
                            resTmpl,
                            notifyTmpl;
                        if (data !== 'undefined') {
                            resTmpl = self.resTmpl({
                                data: data
                            });
                            $(resTmpl)
                                .appendTo($('#chat-window-' + data.senderUniqueId + ' .discussion'));


                            if ($('#chat-window-' + data.senderUniqueId + ' .reply-container').length) {
                                $('#chat-window-' + data.senderUniqueId + ' .reply-container').animate({
                                    scrollTop: $('#chat-window-' + data.senderUniqueId + ' .reply-container')[0].scrollHeight
                                }, 1000);
                            }

                            if ($('.model-chat-controls').find('.wk_chat_sound').hasClass('enable')) {
                                $('.model-chat-controls').find('#myAudio').get(0).play();
                            }

                            if (data.message_type == "image") {
                                data.message = $.mage.__('You have received an image.');
                            }
                            if (data.message_type == "file") {
                                data.message = $.mage.__('You have received a file.');
                            }

                            self.blinkTab(data.message_type);
                            socketProvider.callEmojify('chat_window_container');

                            socketProvider.showNotification(data);
                            var isSafari = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/);
                            if (isSafari) {
                                notifyTmpl = self.notifyTmpl({
                                    data: data
                                });
                                $(notifyTmpl).prependTo('body');
                                setTimeout(function () {
                                    $('body').find('.chat-message-notification').fadeOut('fast', function () {
                                        $('body').find('.chat-message-notification').remove();
                                    });

                                }, 8000);
                            }
                        }
                    });
                }
                this.customerStatusOnSellerEnd.subscribe(function (newValue) {
                    var status = self.getCustomerStatus(parseInt(newValue.status));
                    $('#customer-' + newValue.customerUniqueId + ' #status-' + newValue.customerUniqueId).removeClass();
                    $('#customer-' + newValue.customerUniqueId + ' #status-' + newValue.customerUniqueId).addClass('chat_status ' + status);
                });
            },
            /**
             * open chat window on seller click from chat panel
             */
            openChatWindow: function (uiModel, event) {
                var self = this;
                var id = "chat-window-" + uiModel.customerUniqueId;
                if ($(event.currentTarget).hasClass('blocked-user')) {
                    return;
                }
                $('#customer-' + uiModel.customerUniqueId).removeClass('msg-notify');
                var chatData = uiModel;
                for (var iii = 0; iii < messageProvider.getChatWindows().length; iii++) {
                    //already registered. Bring it to front.
                    if (id == messageProvider.getChatWindows()[iii]) {
                        messageProvider.remove(messageProvider.getChatWindows(), iii);
                        messageProvider.getChatWindows().unshift(id);
                        messageProvider.calculateChatWindows();
                        return;
                    }
                }
                if (_.isNull(document.getElementById("chat-window-" + chatData.customerUniqueId))) {
                    var sellerData = window.sellerChatboxConfig.sellerChatData;
                    var chatTmplate = mageTemplate('#customer_chat_window');
                    chatData.senderUniqueId = sellerData.sellerUniqueId;
                    chatData.statusClass = this.getCustomerStatus(chatData.chatStatus);
                    var data = chatData,
                        chatTmpl;
                    if (data !== 'undefined') {
                        chatTmpl = chatTmplate({
                            data: data
                        });
                        $(chatTmpl).appendTo($('#chat_window_container'));

                        ko.applyBindings(
                            chatbox(
                                chatData.customerUniqueId,
                                'upload' + chatData.customerUniqueId
                            ),
                            document.getElementById("chat-window-" + chatData.customerUniqueId)
                        );

                    messageProvider.getChatWindows().unshift("chat-window-" + chatData.customerUniqueId);
                    messageProvider.calculateChatWindows();
                    }
                } else {
                    messageProvider.getChatWindows().unshift("chat-window-" + chatData.customerUniqueId);
                    messageProvider.calculateChatWindows();
                }
                this.checkCustomerAvailable(event.currentTarget);
                this.loadChatHistory(uiModel);
            },

            /**
             * check if customer is available or not, change the status
             */
            checkCustomerAvailable: function (element) {
                var data = $(element).data('post');
                data.type = 'customer';
                checkCustomerAction(data).fail(function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                }).done(function (response) {
                    var responseData = $.parseJSON(response);
                    var statusClass = '';
                    if (responseData.available == true && responseData.isStatus == 1) {
                        statusClass = 'online';
                    } else if (responseData.available == true && responseData.isStatus == 2) {
                        statusClass = 'busy';
                    } else {
                        statusClass = 'offline';
                    }
                    $('#chat-window-' + data.customerUniqueId + ' #status-' + data.customerUniqueId).removeClass();
                    $('#chat-window-' + data.customerUniqueId + ' #status-' + data.customerUniqueId).addClass('chat_status ' + statusClass);
                    $('#customer-' + data.customerUniqueId + ' #status-' + data.customerUniqueId).removeClass();
                    $('#customer-' + data.customerUniqueId + ' #status-' + data.customerUniqueId).addClass('chat_status ' + statusClass);
                });
            },

            /**
             * load customer chat history
             */
            loadChatHistory: function (data) {
                var self = this;
                data.receiverUniqueId = window.sellerChatboxConfig.sellerChatData.sellerUniqueId;
                data.loadTime = 1;
                $('#chat-window-' + data.customerUniqueId + ' .chat-loading-mask').show();
                return loadHistoryAction(data).fail(function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                    $('#chat-window-' + data.customerUniqueId + ' .chat-loading-mask').hide();
                    //error logic
                }).done(function (response) {
                    $('#chat-window-' + data.customerUniqueId + ' .chat-loading-mask').hide();
                    var responseData = $.parseJSON(response);
                    $('#chat-window-' + data.customerUniqueId + ' .discussion').html('');
                    _.each(responseData.messages, function (value) {
                        if (_.isEqual(data.customerUniqueId, value.sender_unique_id)) {
                            value.class = 'other';
                            value.image = data.customerImage;
                        } else {
                            value.class = 'self';
                            value.image = window.sellerChatboxConfig.sellerChatData.sellerImage;
                        }

                        value.dateTime = value.date;
                        value.error = "";
                        var resTmpl;
                        if (value !== 'undefined') {
                            resTmpl = self.resTmpl({
                                data: value
                            });
                            $(resTmpl)
                                .appendTo($('#chat-window-' + data.customerUniqueId + ' .discussion'));
                        }
                    });

                    $('#chat-window-' + data.customerUniqueId + ' .reply-container').animate({
                        scrollTop: $('#chat-window-' + data.customerUniqueId + ' .reply-container')[0].scrollHeight
                    }, 200);
                    socketProvider.callEmojify('chat_window_container');
                });
            },
            /**
             * Blink Browser Tab
             */
            blinkTab: function (message) {
                var oldTitle = document.title,
                    timeoutId,
                    blink = function () {
                        document.title = document.title == message ? ' ' : message;
                    },
                    clear = function () {
                        clearInterval(timeoutId);
                        document.title = oldTitle;
                        window.onmousemove = null;
                        timeoutId = null;
                    };

                if (!timeoutId) {
                    timeoutId = setInterval(blink, 1000);
                    window.onmousemove = clear;
                }
            },
            /**
             * get customer status
             */
            getCustomerStatus: function (status) {
                if (status == 1) {
                    return 'online';
                } else if (status == 2) {
                    return 'busy';
                } else {
                    return 'offline';
                }
            },

            /**
             * End Chat for seller
             */
            changeSellerStatusEnd(model){
                var self = model;

                this.chatEnabled(false);
                var statusCode = 0;
                var statusData = {};
                statusData.status = statusCode;
                statusData.type = 'seller';
                updateStatusAction(statusData).fail(function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                }).done(function (response) {
                    var data = $.parseJSON(response);
                    var statusClass = self.getCustomerStatus(parseInt(statusCode));
                    var data = {};
                    data.status = statusCode;
                    data.customers = self.enabledCustomerList();
                    messageProvider.sellerStatusChange(data);
                    self.sellerChatStatus(statusClass);
                });
            },

            /**
             * right panel controls manage
             */
            showControlList: function (model, event) {
                if ($(event.target).hasClass('_expended')) {
                    $(event.target).removeClass('_expended');
                    $(event.target).children('.list-group').hide();
                } else {
                    this._refreshControls();
                    $(event.target).addClass('_expended');
                    $(event.target).children('.list-group').show();
                }
            },
            /**
             * hide all opened controls
             */
            _refreshControls: function () {
                $('.controls').find('._expended').removeClass('_expended');
                $.each($('.list-group'), function () {
                    $(this).hide();
                });
            },
            /**
             * when seller change his status
             */
            changeSellerStatus(model, event) {
                var self = model;
                var statusCode = event.currentTarget.id;
                var statusData = {};
                statusData.status = statusCode;
                statusData.type = 'seller';
                updateStatusAction(statusData).fail(function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                }).done(function (response) {
                    var data = $.parseJSON(response);
                    var statusClass = self.getCustomerStatus(parseInt(statusCode));
                    var data = {};
                    data.status = statusCode;
                    data.customers = self.enabledCustomerList();
                    messageProvider.sellerStatusChange(data);
                    self.sellerChatStatus(statusClass);
                });

            },
            /**
             * return current status of seller
             */
            getSellerChatStatus() {
                return this.sellerChatStatus();
            }
        });
    });
