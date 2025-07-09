<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\TwoFactorAuth\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use PragmaRX\Google2FAQRCode\Google2FA;
use Psr\Log\LoggerInterface;

/**
 * Twofactorauth data helper
 */
class TOTPAuth extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor function
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        LoggerInterface $logger
    ) {
        $this->_customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * Get QR function
     */
    public function getQR()
    {
        $google2fa = new Google2FA();
        $key  = $google2fa->generateSecretKey();
        $custom_key ='CYNEAJDJDJSYPMPD';
         $this->_customerSession->setSecretKey($custom_key);
        $qrCodeUrl = $google2fa->getQRCodeInline(
            'OTP',
            'Verification Code',
            $this->_customerSession->getSecretKey()
        );
        return $qrCodeUrl;
    }

    /**
     * VerifyCode function
     *
     * @param array $secret
     * @param integer $code
     */
    public function verifyCode($secret, $code)
    {
        $google2fa = new Google2FA();
        return  $google2fa->verifyKey($secret, (string)$code);
    }
}
