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

namespace Webkul\TwoFactorAuth\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface as TwoFactorAuthRepositoryInterface;
use Webkul\TwoFactorAuth\Helper\Customer as CustomerHelper;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Url\DecoderInterface;

class Varifyclient extends Action
{
    
    /**
     * @var $customerHelper
     */
    private $customerHelper;

    /**
     * @var TwoFactorHelper
     */
    private $twoFactorHelper;

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    private $twoFactorAuthRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var DecoderInterface
     */
    private $urlDecoder;
    
    /**
     * Constructor function
     *
     * @param CustomerHelper $customerHelper
     * @param TwoFactorHelper $twoFactorHelper
     * @param TwoFactorAuthRepositoryInterface $twoFactorAuthRepository
     * @param Session $session
     * @param CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storemanger
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param EncoderInterface $urlEncoder
     * @param DecoderInterface $urlDecoder
     * @param Context $context
     */
    public function __construct(
        CustomerHelper $customerHelper,
        TwoFactorHelper $twoFactorHelper,
        TwoFactorAuthRepositoryInterface $twoFactorAuthRepository,
        Session $session,
        CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storemanger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        EncoderInterface $urlEncoder,
        DecoderInterface $urlDecoder,
        Context $context
    ) {
        $this->customerHelper = $customerHelper;
        $this->twoFactorHelper = $twoFactorHelper;
        $this->twoFactorAuthRepository = $twoFactorAuthRepository;
        $this->session = $session;
        $this->customerFactory = $customerFactory;
        $this->_storeManager = $storemanger;
        $this->messageManager = $messageManager;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        parent::__construct($context);
    }

    /**
     * Execute function for validating the customer auth code.
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $email = $this->urlDecoder->decode($this->getRequest()->getParam('email'));
        $customer = $this->customerFactory->create();
        $loadCustomer = $customer->setWebsiteId(1)->loadByEmail($email);
        $this->session->setCustomerAsLoggedIn($customer);
        $this->session->setAfterAuthUrl($this->_storeManager->getStore()->getBaseUrl());
        $collection = $this->twoFactorAuthRepository->getByEmail($email);
        $collection->setEmail($email);
                $collection->setVerified(1);
                $collection->save($collection);
        $this->messageManager->addSuccess(__('Account Successfully Verified'));
        $redirect->setUrl($this->_storeManager->getStore()->getBaseUrl().'customer/account/');
        return $redirect;
    }
}
