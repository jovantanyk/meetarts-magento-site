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

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Webkul\TwoFactorAuth\Helper\Customer as CustomerHelper;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorHelper;

class ValidatePhonenumberCreatePostObserver implements ObserverInterface
{
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var TwoFactorHelper
     */
    private $twoFactorHelper;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var UrlInterface
     */
    private $urlModel;

    /**
     * @param CustomerHelper $customerHelper
     * @param TwoFactorHelper $twoFactorHelper
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param ActionFlag $actionFlag
     * @param SessionManagerInterface $session
     * @param UrlInterface $urlModel
     */
    public function __construct(
        CustomerHelper $customerHelper,
        TwoFactorHelper $twoFactorHelper,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        ActionFlag $actionFlag,
        SessionManagerInterface $session,
        UrlInterface $urlModel
    ) {
        $this->twoFactorHelper = $twoFactorHelper;
        $this->customerHelper = $customerHelper;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->session = $session;
        $this->urlModel = $urlModel;
    }

    /**
     * Observer for validate mobile number
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($this->twoFactorHelper->isModuleEnable()) {
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $callingCode = $request->getPostValue('region');
            $mobile = $request->getPostValue('mobile_number');
            $phoneNumber = $callingCode && $mobile
                ? $this->customerHelper->getTelephoneWithCallingCode($callingCode, $mobile)
                : null;
            $result = $this->customerHelper->validatePhonenumber($phoneNumber);
            if ($result['errors']) {
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->session->setCustomerFormData($request->getPostValue());
                $response = $controller->getResponse();
                $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                $response->setRedirect($this->redirect->error($defaultUrl));
                $this->messageManager->addErrorMessage(
                    $result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT]
                        ?? $result['messages'][CustomerHelper::PHONENUMBER_ALREADY_EXISTS]
                );
            }
        }
        return $this;
    }
}
