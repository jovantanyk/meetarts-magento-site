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

namespace Webkul\TwoFactorAuth\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;

class Validate extends Action
{
    /**
     * @var \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory
     */
    private $twoFactorFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJson;

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    private $twoFactorRepositoryInterface;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $customerformKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $loginUrl;

    /**
     * @var \Webkul\TwoFactorAuth\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $_redirectInterface;

    /**
     * @var \Webkul\TwoFactorAuth\Helper\TOTPAuth
     */
    protected $totpauth;
        
    /**
     * Constructor function
     *
     * @param \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     * @param TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
     * @param \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorFactory
     * @param \Webkul\TwoFactorAuth\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Form\FormKey\Validator $customerformKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $loginUrl
     * @param Context $context
     * @param \Magento\Framework\App\Response\RedirectInterface $redirectInterface
     * @param \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth
     */
    public function __construct(
        \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface,
        \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorFactory,
        \Webkul\TwoFactorAuth\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger, //log injection
        \Magento\Framework\Data\Form\FormKey\Validator $customerformKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $loginUrl,
        Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJson = $resultJson;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
        $this->twoFactorFactory = $twoFactorFactory;
        $this->_logger = $logger;
        $this->customerformKeyValidator = $customerformKeyValidator;
        $this->_storeManager = $storeManager;
        $this->resultFactory = $resultFactory;
        $this->_customerSession = $customerSession;
        $this->loginUrl = $loginUrl;
        $this->helper = $helper;
        $this->_redirectInterface = $redirectInterface;
        $this->totpauth = $totpauth;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->loginUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Execute function for validate the Otp from Customers
     *
     * @param none
     * @return mixed
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        if ($this->customerformKeyValidator->validate($this->getRequest())) {
            $email = $this->getRequest()->getParam('email');
            $otp = $this->getRequest()->getParam('user_otp');
            $data = $this->getRequest()->getParams();
            if ($this->helper->sendOtpSource() === 'totp') {

                $authcodeverify = $this->totpauth->verifyCode(
                    $this->_customerSession->getSecretKey(),
                    $otp
                );
                if ($authcodeverify) {
                    $authData = $this->twoFactorRepositoryInterface->getByEmail($email);
                    $authData->setAuthCode(0);
                    $authData->setVerified(1);
                    $this->twoFactorRepositoryInterface->save($authData);

                    $this->messageManager->addSuccessMessage(__('Account Successfully Verified'));
                    $redirect->setUrl(
                        $this->_storeManager->getStore()->getBaseUrl().'customer/account/index',
                        ['_secure' => true]
                    );
                   
                } else {
                    $this->messageManager->addErrorMessage(__('You have entered a wrong code. Please try again.'));
                    $redirect->setUrl($this->_redirectInterface->getRefererUrl());
                }
                return $redirect;
            }

            $authData = $this->twoFactorRepositoryInterface->getByEmail($email);
           
            if (is_array($authData->getData())) {
                $otpCreatedTimestamp = strtotime($authData->getCreatedAt());
                $currentTimestamp = time();
                $timeDiff = $currentTimestamp - $otpCreatedTimestamp;
                $authExpiryTime = $this->helper->authCodeExpiry();
               
                if ($authExpiryTime >= 60 && $authExpiryTime <= 300) {
                    $authExpiryTime = $authExpiryTime;
                } else {
                    $authExpiryTime = 60;
                }
                if ($timeDiff >= $authExpiryTime) {
                   
                    $this->messageManager->addErrorMessage(__('Auth Code expired.Please resend Auth and try again.'));
                    return $redirect->setUrl($this->_redirectInterface->getRefererUrl());
                }

                if ($authData->getAuthCode() == $otp) {
                    $authData->setAuthCode(0);
                    $authData->setVerified(1);
                    $this->twoFactorRepositoryInterface->save($authData);
                    $this->messageManager->addSuccessMessage(__('Account Successfully Verified'));
                    $redirect->setUrl(
                        $this->_storeManager->getStore()->getBaseUrl().'customer/account/index',
                        ['_secure' => true]
                    );
                } else {
                    $this->messageManager->addErrorMessage(__('You have entered a wrong code. Please try again.'));
                    $redirect->setUrl($this->_redirectInterface->getRefererUrl());
                }
            } else {
                $this->messageManager->addErrorMessage(__('Something went wrong'));
                $redirect->setUrl($this->_redirectInterface->getRefererUrl());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong'));
            $redirect->setUrl($this->_redirectInterface->getRefererUrl());
        }
        return $redirect;
    }
}
