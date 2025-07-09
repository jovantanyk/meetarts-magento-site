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

use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Webkul\TwoFactorAuth\Helper\Customer as CustomerHelper;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorAuthHelper;

class ValidatePhonenumberLoginPostObserver implements ObserverInterface
{
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var TwoFactorAuthHelper
     */
    private $twoFactorAuthHelper;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var UrlInterface
     */
    private $urlModel;

    /**
     * @param CustomerHelper $customerHelper
     * @param TwoFactorAuthHelper $twoFactorAuthHelper
     * @param RedirectInterface $redirect
     * @param UrlInterface $urlModel
     */
    public function __construct(
        CustomerHelper $customerHelper,
        TwoFactorAuthHelper $twoFactorAuthHelper,
        RedirectInterface $redirect,
        UrlInterface $urlModel
    ) {
        $this->twoFactorAuthHelper = $twoFactorAuthHelper;
        $this->customerHelper = $customerHelper;
        $this->redirect = $redirect;
        $this->urlModel = $urlModel;
    }

    /**
     * Validate Phone number login post observer
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($this->twoFactorAuthHelper->isModuleEnable()) {
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $loginParams = $request->getPostValue('login');
            $login = is_array($loginParams) && array_key_exists('username', $loginParams)
                ? $loginParams['username']
                : null;
            if ($this->customerHelper->isEmail($login)) {
                return $this;
            }
        }
        return $this;
    }
}
