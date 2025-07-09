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

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;

class Verify extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $loginUrl;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\TwoFactorAuth\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface
     */
    protected $twoFactorRepositoryInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    
    /**
     * Constructor function
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $loginUrl
     * @param \Webkul\TwoFactorAuth\Helper\Data $helper
     * @param PageFactory $resultPageFactory
     * @param \Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $loginUrl,
        \Webkul\TwoFactorAuth\Helper\Data $helper,
        PageFactory $resultPageFactory,
        \Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_customerSession = $customerSession;
        $this->loginUrl = $loginUrl;
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
        $this->_storeManager = $storeManager;
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
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
       
        if ($this->helper->isModuleEnable()) {
            $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $customerEmail = $this->_customerSession->getCustomer()->getEmail();
            $authData = $this->twoFactorRepositoryInterface->getByEmail($customerEmail);
            if ($authData->getVerified() == 1) {
                $redirect->setUrl(
                    $this->_storeManager->getStore()->getBaseUrl().'customer/account',
                    ['_secure' => true]
                );
                return $redirect;
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('TwoFactor Auth Verification'));
            return $resultPage;
        }
        return $this->resultPageFactory->create();
    }
}
