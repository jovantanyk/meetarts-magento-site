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

namespace Webkul\TwoFactorAuth\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorHelper;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;

class RedirectToTwoFactorAuth implements ObserverInterface
{

    /**
     * @var TwoFactorHelper
     */
    private $twoFactorHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    protected $twoFactorRepositoryInterface;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @param TwoFactorHelper $twoFactorHelper
     * @param TwoFactorAuthRepositoryInterface $customerSession
     * @param \Magento\Customer\Model\Session $twoFactorRepositoryInterface
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        TwoFactorHelper $twoFactorHelper,
        \Magento\Customer\Model\Session $customerSession,
        TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->twoFactorHelper = $twoFactorHelper;
        $this->_customerSession = $customerSession;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Redirect to two factor auth page
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $controller = $observer->getControllerAction();
        if (!$this->twoFactorHelper->isModuleEnable()) {
            return $this;
        }
        if ($this->_customerSession->isLoggedIn()) {
            $customer = $this->_customerSession->getCustomer();
            $authData = $this->twoFactorRepositoryInterface->getByEmail($customer->getEmail());
            $url = $this->urlInterface->getUrl('twofactorauth/account/verify');
            $isEnableOnRegister = $this->twoFactorHelper->isEnableAtRegistration();
            // if ($authData->getVerified() == 0 && $isEnableOnRegister) {
            //     $observer->getControllerAction()
            //     ->getResponse()
            //     ->setRedirect($url);
            // }
        }
    }
}
