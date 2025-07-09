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

class Generatecode extends Action
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
     * @var \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Webkul\TwoFactorAuth\Model\BackupCode
     */
    protected $backupcodeModel;

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
     * @param \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Webkul\TwoFactorAuth\Model\BackupCode $backupcodeModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $loginUrl,
        \Webkul\TwoFactorAuth\Helper\Data $helper,
        \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\TwoFactorAuth\Model\BackupCode $backupcodeModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->loginUrl = $loginUrl;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerRepository = $customerRepository;
        $this->backupcodeModel = $backupcodeModel;
        $this->_storeManager = $storeManager;
        $this->resultFactory = $resultFactory;
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
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $backupcodes =[];
        $result = 0;
        $customerEmail = $this->customerRepository->getById($this->_customerSession->getCustomerId())->getEmail();
        for ($i = 0; $i < 10; $i++) {
            $backupcodes[$i] = rand(10000000, 99999999);
        }
        foreach ($backupcodes as $passwprd) {
            $data = [
            'email' =>$customerEmail,
            'backupcode' =>$passwprd,
            'active' =>1,
            'created_at' =>time(),
            'updated_at' =>time()
            ];
            $this->backupcodeModel->setData($data);
            $this->backupcodeModel->save();
            $result = 1;
        }

        if ($result) {
            $this->messageManager->addSuccessMessage(__('Backup Codes Successfully Generated'));
            $redirect->setUrl(
                $this->_storeManager->getStore()->getBaseUrl().'twofactorauth/account/backupcode',
                ['_secure' => true]
            );

            return $redirect;
        }
    }
}
