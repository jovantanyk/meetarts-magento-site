/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'jquery',
    'mage/template',
    'uiComponent',
    'mage/validation',
    'ko',
    'underscore',
    'Webkul_MpBuyerSellerChat/js/model/socket-provider',
    'Webkul_MpBuyerSellerChat/js/model/message-sender',
    'Webkul_MpBuyerSellerChat/js/action/load-history',
    'Webkul_MpBuyerSellerChat/js/action/update-status',
    'Webkul_MpBuyerSellerChat/js/action/update-profile',
    'Webkul_MpBuyerSellerChat/js/action/customer-available-check',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (
    $,
    mageTemplate,
    Component,
    validation,
    ko,
    _,
    socketProvider,
    messageSender,
    loadHistoryAction,
    updateStatusAction,
    updateProfile,
    customerAvailableCheck,
    customCustomerData
) {
        'use strict';

        var mpBuyerChat = $('[data-block=\'mpbuyerchat\']');
        /**
         * @return {Boolean}
         */
        function initSidebar() {
            mpBuyerChat.trigger('contentUpdated');
        }

        mpBuyerChat.on('load', function () {
            initSidebar();
        });

        return Component.extend({
            minimized: ko.observable(false),
            showProfileBox: ko.observable(false),
            chatBoxData: window.mpChatboxConfig,
            isChatStarted: socketProvider.isChatStarted(),
            customerProfile: socketProvider.getCustomerProfile(),
            customerStatus: ko.observable(''),
            sellerChatStatus: ko.observable(),
            customerStatusOnSellerEnd: messageSender.isCustomerStatusChanged(),
            sellerStatusChange: messageSender.isSellerStatusChanged(),
            customerData: ko.observable(''),
            getSoundUrl: window.chatboxCoreConfig.soundUrl,
            sellerImage: ko.observable(),
            showLoader: socketProvider.getshowLoader(),
            //showFileLoader: ko.observable(false),
            attachmentImg: ko.observable(window.chatboxCoreConfig.attachmentImage),
            attachedImageData: ko.observable(null),
            //uploadPercentage: ko.observable(''),
            chatSellerError: ko.observable(''),
            isSellerChatError: ko.observable(false),
            dynamicObserverList: ko.observableArray([]),
            defaults: {
                template: 'Webkul_MpBuyerSellerChat/chatbox'
            },
            chat: {},
            initialize: function (chatWindow, uploadObserverName) {
                this._super();
                let sections = ['mpbuyerchat-data'];
                customCustomerData.invalidate(sections);
                customCustomerData.reload(sections, true);

                var self = this,
                    mpchatData = customCustomerData.get('mpbuyerchat-data');
                this.update(mpchatData());

                self.chatWindowId = chatWindow;
                self.showFileLoader = this[chatWindow] = ko.observable();
                self.uploadPercentage = this[uploadObserverName] = ko.observable();
                this.resTmpl = mageTemplate('#reply_template');
                this.loadHistoryTmpl = mageTemplate('#customer_reply_template');

                $(".wk-block-title-css").append($('#chat'));

                if (_.isObject(chatWindow)) {
                    self.openChatWindow();
                }

                if (!_.isUndefined(window.mpChatboxConfig)) {
                    var customerData = this.chatBoxData.customerData;
                    self.customerData(customerData);
                    var sellerData = this.chatBoxData.sellerData;
                    self.sellerImage(window.mpChatboxConfig.sellerData.image);
                    if (!_.isUndefined(customerData.customerId) &&
                        !_.isUndefined(sellerData.sellerId)) {
                        if (!_.isEqual(customerData.customerId, sellerData.sellerId)) {
                            socketProvider.setCustomerConected(customerData, sellerData);
                        }
                    }
                }

                if (!_.isUndefined(window.mpChatboxConfig)) {
                    var customerStatusCode = window.mpChatboxConfig.customerData.chatStatus;
                    var sellerStatusCode = window.mpChatboxConfig.sellerData.sellerOnline;
                    this.customerStatus(this.getStatus(parseInt(customerStatusCode)));
                    this.sellerChatStatus(this.getStatus(parseInt(sellerStatusCode)));
                    socketProvider.setCustomerProfile(window.mpChatboxConfig.customerData.customerImage);
                }
                
                this._manageSocketResponse();

                return this;
            },

            customerCurrentStatus: function () {
                var self = this,
                    mpchatData = customCustomerData.get('mpbuyerchat-data');
                 var customerStatus = mpchatData().customerData.chatStatus;
                if(customerStatus == 1) {
                    return "online";
                } else if (customerStatus == 2) {
                    return "busy";
                } else {
                    return "offline";
                }
            },

            customerProfileImage: function () {
                var self = this,
                    mpchatData = customCustomerData.get('mpbuyerchat-data');                    
                    if(_.isUndefined(mpchatData().customerData)) {
                        customerImage = '';
                    } else {
                        var customerImage = mpchatData().customerData.customerImage;
                    }
                return customerImage;
            },

            customerUniqueId: function () {
                var self = this,
                    mpchatData = customCustomerData.get('mpbuyerchat-data');
                var customerUniqueId = mpchatData().customerData.customerUniqueId;
                return customerUniqueId;
            },

            refreshData: function () {
                let sections = ['mpbuyerchat-data'];
                customCustomerData.invalidate(sections);
                customCustomerData.reload(sections, true);
            },
            /**
             * Update chatbox content.
             *
             * @param {Object} updatedChat
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

            /**
             * Get chatbox param by name.
             * @param {String} name
             * @returns {*}
             */
            getChatParam: function (name) {
                if (!_.isUndefined(name)) {
                    if (!this.chat.hasOwnProperty(name)) {
                        this.chat[name] = ko.observable();
                    }
                }
                return this.chat[name]();
            },
    
            /**
             * manage all socket responses
             */
            _manageSocketResponse: function () {
                var self = this;
                this.customerStatusOnSellerEnd.subscribe(function (newValue) {
                    var status = self.getStatus(parseInt(newValue.status));
                    $('#chat-window-' + newValue.customerUniqueId + ' #status-' + newValue.customerUniqueId).removeClass();
                    $('#chat-window-' + newValue.customerUniqueId + ' #status-' + newValue.customerUniqueId).addClass('chat_status ' + status);
                });

                this.sellerStatusChange.subscribe(function (newData) {
                    self.sellerChatStatus(self.getStatus(parseInt(newData.status)));
                });
            },
            /**
             * open chat window of customer
             */
            openChatWindow: function () {
                $('#mpchatbox-component').addClass('_show');
                $('#mpchatbox-component').addClass('_maxmimize');

                this.minimized(false);
                socketProvider.callEmojify('mpchatbox-component');
            },
            /**
             * minimize chat window of seller and customer
             */
            minimizeChatWindow: function (data, el) {
                $(el.target).parents('.chat-module').parent().removeClass('_maxmimize');
                $(el.target).parents('.chat-module').parent().addClass('_minimize');
                this.minimized(true);
            },
            /**
             * maxmize chat window of seller and customer
             */
            maxmizeChatWindow: function (data, el) {
                var id = $(el.target).parents('.chat-module').parent();
                $(el.target).parents('.chat-module').parent().removeClass('_minimize');
                $(el.target).parents('.chat-module').parent().addClass('_maxmimize');
                this.minimized(false);
            },
            /**
             * minimize chat window of seller
             */
            minimizeSellerChatWindow: function (data, el) {
                $(el.target).parents('.chat-module').parent().removeClass('_maxmimize');
                $(el.target).parents('.chat-module').parent().addClass('_minimize');
                $(el.target).hide();
                $(el.target).siblings('.typicons-plus').show();
            },
            /**
             * maxmize chat window of seller
             */
            maxmizeSellerChatWindow: function (data, el) {
                var id = $(el.target).parents('.chat-module').parent();
                $(el.target).parents('.chat-module').parent().removeClass('_minimize');
                $(el.target).parents('.chat-module').parent().addClass('_maxmimize');
                $(el.target).hide();
                $(el.target).siblings('.typicons-minus').show();
            },
            /**
             * manage chat header minimize and maximize on customer end.
             */
            showHideCustomerChatWindow: function (data, el) {

                if (!_.isEqual($(el.target).attr('class'), "fa-ellipsis-h") &&
                    !_.isEqual($(el.target).attr('class'), "_expanded fa-ellipsis-h") &&
                    !_.isEqual($(el.target).attr('class'), "icon typicons-times")) {
                    if ($(el.currentTarget).hasClass('opened') &&
                        ($(el.target).hasClass('top-bar') || _.isUndefined($(el.target).attr('class')))) {
                        $(el.currentTarget).removeClass('opened');
                        $(el.currentTarget).parents('.chat-module').parent().removeClass('_minimize');
                        $(el.currentTarget).parents('.chat-module').parent().addClass('_maxmimize');
                        $(el.currentTarget).find('.typicons-plus').hide();
                        $(el.currentTarget).find('.typicons-minus').show();
                        $('.fa-ellipsis-h').removeClass('_expanded');
                        $('.chat-controls').removeClass('_show');
                        $('.chat-module').css('height', 'auto');
                        this.minimized(false);
                    } else if ($(el.target).hasClass('top-bar') || _.isUndefined($(el.target).attr('class'))) {
                        $(el.currentTarget).addClass('opened');
                        $(el.currentTarget).parents('.chat-module').parent().removeClass('_maxmimize');
                        $(el.currentTarget).parents('.chat-module').parent().addClass('_minimize');
                        $(el.currentTarget).find('.typicons-plus').show();
                        $(el.currentTarget).find('.typicons-minus').hide();
                        $('.chat-module').css('height', '267px');
                        this.minimized(true);
                    }
                }
            },
            /**
             * manage chat header minimize and maximize on seller end.
             */
            showHideSellerChatWindow: function (data, el) {
                $(el.currentTarget).removeClass('msg-notify');
                if (!_.isEqual($(el.target).attr('class'), "fa-ellipsis-h") &&
                    !_.isEqual($(el.target).attr('class'), "_expanded fa-ellipsis-h") &&
                    !_.isEqual($(el.target).attr('class'), "icon typicons-times")) {
                    if ($(el.currentTarget).hasClass('opened')) {
                        $(el.currentTarget).removeClass('opened');
                        $(el.currentTarget).parents('.chat-module').parent().removeClass('_minimize');
                        $(el.currentTarget).parents('.chat-module').parent().addClass('_maxmimize');
                        $(el.currentTarget).find('.typicons-plus').hide();
                        $(el.currentTarget).find('.typicons-minus').show();
                        $('.fa-clock-o').removeClass('_expended');
                        $('.wk_chat_history_options').css('display','none');
                        $('.chat-module').css('height', 'auto');
                    } else {
                        $(el.currentTarget).addClass('opened');
                        $(el.currentTarget).parents('.chat-module').parent().removeClass('_maxmimize');
                        $(el.currentTarget).parents('.chat-module').parent().addClass('_minimize');
                        $(el.currentTarget).find('.typicons-plus').show();
                        $(el.currentTarget).find('.typicons-minus').hide();
                        $('.chat-module').css('height', '267px');                    }
                }
            },
            /**
             * close chat window of seller and customer
             */
            closeChatWindow: function (data, el) {
                $(el.target).parents('.chat-module').parent().removeClass('_minimize');
                $(el.target).parents('.chat-module').parent().removeClass('_maxmimize');
                $(el.target).parents('.chat-module').parent().removeClass('_show');
                this.minimized(false);
            },

            //this is used to close a popup
            closeSellerChatWindow: function (data, el) {
                var id = $(el.target).parents('.chat-module').parent().attr('id');
                var chatWindows = messageSender.getChatWindows();
                var self = this;
                for (var iii = 0; iii < chatWindows.length; iii++) {
                    if (id == chatWindows[iii]) {
                        messageSender.remove(chatWindows, iii);
                        document.getElementById(id).classList.remove("_show");
                        messageSender.calculateChatWindows();
                    }
                }
            },

            /**
             * magen chat window controls popup
             */
            showHideControls: function (data, el) {
                this._refreshPopus();
                if ($(el.target).hasClass('_expanded')) {
                    $('.chat-controls').removeClass('_show');
                    $(el.target).removeClass('_expanded');
                    // $(el.target).removeClass('typicons-up');
                    // $(el.target).addClass('typicons-down')
                } else {
                    $('.chat-controls').addClass('_show');
                    $(el.target).addClass('_expanded');
                    // $(el.target).removeClass('typicons-down');
                    // $(el.target).addClass('typicons-up')
                }

            },
            hideChatControls: function () {
                //$('.chat-controls').removeClass('_show');
            },
            enableDisableSound: function (data, event) {
                this._refreshPopus();
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
             * show the control list on click control icon
             */
            showControlList: function (data, event) {

                if ($(event.target).hasClass('_expended')) {
                    $(event.target).removeClass('_expended');
                    $(event.target).children('.list-group').hide();
                    this._refreshPopus();
                } else {
                    this._refreshPopus();
                    $(event.target).addClass('_expended');
                    $(event.target).children('.list-group').show();

                }
            },
            hideControlList: function () {
                $('.list-group').each(function () {
                    $(this).hide();
                    $(this).parent('span').removeClass('_expended');
                });
            },
            openEmojiBox: function (data, event) {
                if ($(event.currentTarget).hasClass('open')) {
                    $(event.currentTarget).removeClass('open');
                } else {
                    $(event.currentTarget).addClass('open');
                }
            },
            /**
             * upload image
             */
            uploadProfileImage: function (profileForm) {
                var data = new FormData();
                data.append('type', 'customer');
                data.append('file', $('#profile_image')[0].files[0]);
                updateProfile(data, $('.profile-setting-box'), this.showLoader, this.customerProfile);
            },

            /**
             * show image when user select
             */
            showSelectedImage: function (event) {
                var oFReader = new FileReader();
                if (!_.isUndefined(document.getElementById("profile_image").files[0])) {
                    oFReader.readAsDataURL(document.getElementById("profile_image").files[0]);

                    oFReader.onload = function (oFREvent) {
                        document.getElementById("user-profile-image").src = oFREvent.target.result;
                    };
                }
            },

            removeErrorMessage: function () {
                var self = this;
                if (self.isSellerChatError()) {
                    setTimeout(function () {
                        self.isSellerChatError(false);
                    }, 5000);
                }
            },

            /**
             * show hide customer profile box
             */
            showHideProfileBox: function () {
                var newValue = !this.showProfileBox();
                this.showProfileBox(newValue);
                this._refreshPopus();
            },
            _refreshPopus: function () {
                $('.list-group').each(function () {
                    $(this).hide();
                    $(this).parent('i').removeClass('_expended');
                });
            },

            selectFile: function ($model, e) {
                e.stopImmediatePropagation();
                var self = $model,
                    currentElement = $(e.currentTarget),
                    fileType = e.originalEvent.target.files[0].type,
                    data = {};
                var restrictedFiles = ["php", "exe", "js"],
                    error = false;
                var element = $(e.currentTarget).attr('data-form');
                if (restrictedFiles.indexOf(e.originalEvent.target.files[0].name.split('.').pop()) > -1) {
                    error = true;
                    self.isSellerChatError(element);
                    self.chatSellerError($.mage.__('File type not supported'));
                    self.removeErrorMessage();
                }

                if (!error) {
                    if (fileType.indexOf("image") >= 0) {
                        var type = 'image';
                    } else {
                        var type = 'file';
                    }
                    data[element] = ko.observable();
                    self.dynamicObserverList.push(data);
                    //console.log(self.showFileLoader);
                    self.siofu = socketProvider.getSocketFileUpload();

                    // Do something when a file is uploaded: 
                    self.siofu.addEventListener("complete", function (event) {
                        self.showFileLoader(false);
                        var replyData = {};
                        replyData.message_type = type;
                        replyData.content = event.detail.fileName;
                        self.attachedImageData(replyData);
                        $(e.target.form).submit();
                    });

                    self.siofu.addEventListener("start", function (event) {

                    });
                    // Do something on upload progress: 
                    self.siofu.addEventListener("progress", function (event) {
                        self.showFileLoader(true);
                        var percent = event.bytesLoaded / event.file.size * 100;
                        self.uploadPercentage("File is " + percent.toFixed(2) + "% percent loaded");
                    });

                    self.siofu.addEventListener("error", function (data) {
                        if (data.code === 1) {
                            self.isSellerChatError(element);
                            self.chatSellerError($.mage.__('Maximum allowed size is ' + window.chatboxCoreConfig.maxFileSize + 'MB'));
                        }
                        self.showFileLoader(false);
                        self.removeErrorMessage();
                    });
                    self.siofu.resetFileInputs = true
                    self.siofu.maxFileSize = parseInt(window.chatboxCoreConfig.maxFileSize) * 1024 * 1024;
                    self.siofu.listenOnInput(e.currentTarget);
                    e.currentTarget.removeEventListener("change", self.siofu.prompt, false);
                    self.siofu = null;
                }
            },

            addAttachment: function (model, e) {
                $(e.currentTarget).parents('form').find('.msg-attachment').trigger('click');

            },

            /**
             * send new message by seller
             */
            sendSellerMessage: function (sellerMessageForm) {
                var self = this;
                var error = "";
                var socket = socketProvider.getSocketObject();
                //if (socket !== false) {
                var sendData = {},
                    resTmpl,
                    formDataArray = $(sellerMessageForm).serializeArray();

                formDataArray.forEach(function (entry) {
                    sendData[entry.name] = entry.value.replace(/\n/g, "<br>");
                });
                if (!_.isNull(self.attachedImageData()) && self.attachedImageData().content) {
                    sendData.message = self.attachedImageData().content;
                    sendData.message_type = self.attachedImageData().message_type;
                }
                self.attachedImageData(null);
                sendData.dateTime = socketProvider.getDate() + ' ' + socketProvider.getTime();
                sendData.message = sendData.message.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, "");

                if ($.trim(sendData.message) !== '') {
                    sendData.customerName = window.sellerChatboxConfig.sellerChatData.sellerName;

                    if (!_.isUndefined(sendData.message_type)) {
                        messageSender.saveMessage(
                            sendData
                        ).fail(function (response) {
                            console.log(response);
                        }).done(function (response) {
                            $(sellerMessageForm).trigger('reset');
                            var data = $.parseJSON(response);
                            if (data.errors == false) {
                                sendData.message = data.message;
                                messageSender.sendMessageToCustomer(sendData);
                                //response = $.parseJSON(response);
                                self.appendMessage(sendData);
                            }
                            /*fullScreenLoader.stopLoader();*/
                        });
                    } else {
                        $(sellerMessageForm).trigger('reset');
                        messageSender.saveMessage(
                            sendData
                        );
                        messageSender.sendMessageToCustomer(sendData);
                        self.appendMessage(sendData);
                    }
                }
                //}
            },

            appendMessage: function (sendData) {
                self = this;
                sendData.class = 'self';
                sendData.image = window.sellerChatboxConfig.sellerChatData.sellerImage;
                sendData.customerName = window.sellerChatboxConfig.sellerChatData.sellerName;
                /**
                 * append message
                 */
                if (sendData !== 'undefined') {
                    sendData.type = 'customer';
                    /**
                     * before check customer is available or not
                     */
                    customerAvailableCheck(sendData).fail(function (response) {
                        if (response.status == 401) {
                            location.reload();
                        }
                    }).done(function (response) {
                        var responseData = $.parseJSON(response);
                        var statusClass = '';
                        if (responseData.available == true && responseData.isStatus == 1) {
                            var error = "";
                            statusClass = 'online';
                        } else if (responseData.available == true && responseData.isStatus == 2) {
                            var error = "";
                            statusClass = 'busy';
                        } else {
                            statusClass = 'offline';
                            error = $.mage.__('Receiver is offline.');
                        }

                        $('#chat-window-' + sendData.receiverUniqueId + ' #status-' + sendData.receiverUniqueId).removeClass();
                        $('#chat-window-' + sendData.receiverUniqueId + ' #status-' + sendData.receiverUniqueId).addClass('chat_status ' + statusClass);
                        $('#customer-' + sendData.receiverUniqueId + ' #status-' + sendData.receiverUniqueId).removeClass();
                        $('#customer-' + sendData.receiverUniqueId + ' #status-' + sendData.receiverUniqueId).addClass('chat_status ' + statusClass);

                        sendData.error = error;
                        var resTmpl = self.resTmpl({
                            data: sendData
                        });
                        $(resTmpl)
                            .appendTo($('#chat-window-' + sendData.receiverUniqueId + ' .discussion'));
                        $('#chat-window-' + sendData.receiverUniqueId + ' .reply-container').animate({
                            scrollTop: $('#chat-window-' + sendData.receiverUniqueId + ' .reply-container')[0].scrollHeight
                        }, 500);
                        socketProvider.callEmojify('chat_window_container');
                    });
                }
            },

            /**
             * from seller chat window
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
             * load customer chat history when customer/seller load manually
             */
            loadChatHistory: function (dataModel, event, el) {
                var self = dataModel;
                var historyData = {};
                self._refreshPopus();

                var parentId = '';
                if ($(event.target).hasClass('customer')) {
                    historyData.customerUniqueId = self.customerUniqueId();
                    historyData.senderUniqueId = window.mpChatboxConfig.sellerData.receiverUniqueId;
                    parentId = '#mpchatbox-component';
                }

                $(parentId + ' .discussion').html('');
                historyData.loadTime = event.target.id;
                $(parentId + ' .chat-loading-mask').show();
                loadHistoryAction(historyData).fail(function (response) {
                    $(parentId + ' .chat-loading-mask').hide();
                    if (response.status == 401) {
                        location.reload();
                    }
                    //error logic
                }).done(function (response) {
                    $(parentId + ' .chat-loading-mask').hide();
                    var responseData = $.parseJSON(response);

                    _.each(responseData.messages, function (value) {
                        if (_.isEqual(historyData.customerUniqueId, value.sender_unique_id)) {
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
                            resTmpl = self.loadHistoryTmpl({
                                data: value
                            });

                            $(resTmpl)
                                .appendTo($(parentId + ' .discussion'));
                        }
                    });
                    $('.reply-container').animate({
                        scrollTop: $('.reply-container')[0].scrollHeight
                    }, 1000);
                    socketProvider.callEmojify('mpchatbox-component');
                });
            },

            /**
             * load customer chat history when customer/seller load manually
             */
            loadSellerChatHistory: function (dataModel, event) {
                var self = dataModel,
                    data = {};
                data.senderUniqueId = window.sellerChatboxConfig.sellerChatData.sellerUniqueId;
                data.customerUniqueId = $(event.target).attr('data-value');
                data.loadTime = event.target.id;
                $('#chat-window-' + $(event.target).attr('data-value') + ' .chat-loading-mask').show();
                return loadHistoryAction(data).fail(function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                    $('#chat-window-' + $(event.target).attr('data-value') + ' .chat-loading-mask').hide();
                    //error logic
                }).done(function (response) {
                    $('#chat-window-' + $(event.target).attr('data-value') + ' .chat-loading-mask').hide();
                    var responseData = $.parseJSON(response);
                    $('#chat-window-' + $(event.target).attr('data-value') + ' .discussion').html('');
                    _.each(responseData.messages, function (value) {
                        if (_.isEqual($(event.target).attr('data-value'), value.sender_unique_id)) {
                            value.class = 'other';
                            value.image = $(event.target).attr('data-image');
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
                                .appendTo($('#chat-window-' + $(event.target).attr('data-value') + ' .discussion'));
                        }
                    });

                    $('#chat-window-' + $(event.target).attr('data-value') + ' .reply-container').animate({
                        scrollTop: $('#chat-window-' + $(event.target).attr('data-value') + ' .reply-container')[0].scrollHeight
                    }, 200);
                    socketProvider.callEmojify('chat_window_container');
                });
            },

            /**
             * get status class
             */
            getStatus: function (status) {
                if (status == 1) {
                    return 'online';
                } else if (status == 2) {
                    return 'busy';
                } else {
                    return 'offline';
                }
            },
            /**
             * customer change chat status
             */
            changeChatStatus: function (dataModel, event) {
                var self = dataModel,
                    customerData = self.chatBoxData.customerData,
                    sellerData = self.chatBoxData.sellerData,
                    statusData = {};
                statusData.status = event.currentTarget.id;
                statusData.customerUniqueId = customerData.customerUniqueId;
                statusData.sellerUniqueId = sellerData.receiverUniqueId;
                statusData.type = 'customer';
                updateStatusAction(statusData).fail(function (response) {
                    if (response.status == 401) {
                        location.reload();
                    }
                }).done(function (response) {
                    var data = $.parseJSON(response);
                    var statusClass = self.getStatus(parseInt(event.currentTarget.id));
                    messageSender.customerStatusChange(statusData);
                    self.customerStatus(statusClass);
                    // if (parseInt(event.currentTarget.id) == 0) {
                    //     socketProvider.setIsChatStarted(false);
                    // }
                });
            },
        });
    });
