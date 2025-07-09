define([
    "jquery",
    'Webkul_TwoFactorAuth/js/action/post',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'mage/url'
],function($, sendPost, alert, $t, url) {
    $.widget('mage.resendAuthCode', {
        options: {},
        /**
         * Widget initialization
         * @private
         */
         _create: function() {
            var loginReferrerUrl = this.options.loginReferrerUrl;
            var isLoginUrl = this.options.isLoginUrl;
            $(document).ready(function() {
                $(document).on("click", ".resendCode", function(){
                    $('body').trigger('processStart');
                    var resendData = {
                            resendUrl : url.build("twofactorauth/index/index"),
                            resendModalTitle : $t("Resend Code"),
                            resendContentSuccess: $t("Authentication code sent successfully"),
                            resendContentError: $t("Authentication code could not be sent"),
                            resendFormData:{
                                "email" : $("#email").val(),
                                "form_key" : $(".form-edit-account [name=form_key]").val(),
                                "resend" : 1
                            }
                        };
                        sendPost(resendData.resendFormData, resendData.resendUrl, false)
                            .done(function (response) {
                                $('body').trigger('processStop');
                                if (response.error) {
                                    alert({
                                        title: resendData.resendModalTitle,
                                        content: resendData.resendContentError
                                    });
                                } else {
                                alert({
                                       title: resendData.resendModalTitle,
                                       content: resendData.resendContentSuccess
                                   });
                                }
                            }.bind(this)).fail(function () {
                                $('body').trigger('processStop');
                                alert({
                                    title: resendData.resendModalTitle,
                                    content: resendData.resendContentError
                                });
                            }.bind(this));
                });
                // prevent login 
                $(document).on("click",".action.login.primary, .action.action-login.secondary",function(e){
                    $(".form-login[data-role=email-with-possible-login] [name=username]"+
                     " , .form-login[data-role=email-with-possible-login] [name=password]"+
                     " , form[data-role=login] [name=username] , form[data-role=login] [name=password]"+
                     ", .popup-authentication [name=username], .popup-authentication [name=password]").val("")
                    if($(this).hasClass("secondary")){
                        $(".popup-authentication .action-close").click();
                    }
                    if(!isLoginUrl && loginReferrerUrl){
                        window.location.href = loginReferrerUrl;
                    }
                });
            });
        }
        
    });
    return $.mage.resendAuthCode;
});