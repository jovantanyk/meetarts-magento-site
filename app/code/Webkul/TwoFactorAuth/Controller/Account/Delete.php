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

class Delete extends Action
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
     * @var \Webkul\TwoFactorAuth\Model\BackupCodeFactory
     */
    protected $backupcode;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * Constructor function
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $loginUrl
     * @param \Webkul\TwoFactorAuth\Helper\Data $helper
     * @param PageFactory $resultPageFactory
     * @param \Webkul\TwoFactorAuth\Model\BackupCodeFactory $backupcode
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $loginUrl,
        \Webkul\TwoFactorAuth\Helper\Data $helper,
        PageFactory $resultPageFactory,
        \Webkul\TwoFactorAuth\Model\BackupCodeFactory $backupcode,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->loginUrl = $loginUrl;
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->backupcode = $backupcode;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->collectionFactory = $collectionFactory;
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
        $data = $this->getRequest()->getParams();
        $customerEmail = $this->customerRepository->getById($this->_customerSession->getCustomerId())->getEmail();
        $collectiondata = $this->collectionFactory->create();
        $collectiondata->addFieldToFilter('email', $customerEmail);
        $collectiondata->addFieldToFilter('active', 1);

        $myModelFactory = $this->backupcode->create();
        foreach ($collectiondata as $myModel) {
            $myModel->load($myModel->getId());
            $myModel->delete();
        }
        // $model = $this->backupcode->create();
        // $model->delete();
         $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['result' => 'true']);
    }
}
