/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
var config = {
    deps: [
        'Webkul_TwoFactorAuth/js/validation',
        'Webkul_TwoFactorAuth/js/lib/knockout/bindings/mage-init-update',
    ],
    map: {
        '*': {
            verifyOtp: 'Webkul_TwoFactorAuth/js/verifyAuthCode',
            changePhoneNumber: 'Webkul_TwoFactorAuth/js/account/change-phone-number',
            resendAuthCode: 'Webkul_TwoFactorAuth/js/resend-auth-code',
            subscribeUsers: 'Webkul_TwoFactorAuth/js/subscribe-user-script',
            "@firebase/app": "Webkul_TwoFactorAuth/js/firebase-app",
            "@firebase/messaging": "Webkul_TwoFactorAuth/js/firebase-messaging"
        }
    }
};