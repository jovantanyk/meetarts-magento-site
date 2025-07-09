<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\TwoFactorAuth\Plugin;

use Magento\Framework\Controller\ResultFactory;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorAuthHelper;
use Magento\Framework\UrlInterface;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;

class Redirect
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var TwoFactorAuthHelper
     */
    private $twoFactorAuthHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    private $twoFactorRepositoryInterface;

    /**
     * @param UrlInterface $url
     * @param ResultFactory $resultFactory
     * @param TwoFactorAuthHelper $twoFactorAuthHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
     */
    public function __construct(
        UrlInterface $url,
        ResultFactory $resultFactory,
        TwoFactorAuthHelper $twoFactorAuthHelper,
        \Magento\Customer\Model\Session $customerSession,
        TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
    ) {
        $this->url = $url;
        $this->resultFactory = $resultFactory;
        $this->twoFactorAuthHelper = $twoFactorAuthHelper;
        $this->_customerSession = $customerSession;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
    }

    /**
     * Redirect to two factor auth url if phone number is not verified
     *
     * @param \Magento\Customer\Model\Account\Redirect $subject
     * @param \Closure $proceed
     */
    public function aroundGetRedirect(
        \Magento\Customer\Model\Account\Redirect $subject,
        \Closure $proceed
    ) {
        if ($this->twoFactorAuthHelper->isModuleEnable()) {
            
            $isEnableOnRegister = $this->twoFactorAuthHelper->isEnableAtRegistration();
            if ($this->_customerSession->isLoggedIn() && $isEnableOnRegister) {
                
                $customer = $this->_customerSession->getCustomer();
                $authData = $this->twoFactorRepositoryInterface->getByEmail($customer->getEmail());
                if ($authData->getVerified() == 0) {
                    /** @var \Magento\Framework\Controller\Result\Redirect $result */
                    $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $result->setUrl($this->url->getUrl('twofactorauth/account/verify', ['_secure' => true]));
                    return $result;
                } else {
                    if ($this->twoFactorAuthHelper->sendOtpSource() =='emaillink') {
                         /** @var \Magento\Framework\Controller\Result\Redirect $result */
                        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $result->setUrl($this->url->getUrl('twofactorauth/account/verify', ['_secure' => true]));
                        return $result;
                    }
                    
                }
            }
        }

        return $proceed();
    }
}
