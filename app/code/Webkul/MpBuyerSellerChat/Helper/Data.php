<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Helper;

use Magento\Customer\Helper\View as CustomerHelperView;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerData\CollectionFactory;
use Webkul\MpBuyerSellerChat\Helper\Http as HttpDriver;

/**
 * BuyerSeller data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var CollectionFactory
     */
    protected $onlineCustomerCollectionFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var CustomerHelperView
     */
    protected $customerHelperView;

    /**
     * @var DriverFile
     */
    protected $driverFile;

    /**
     * @var HttpDriver
     */
    protected $httpDriver;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param CollectionFactory $onlineCustomerCollectionFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CustomerHelperView $customerHelperView
     * @param HttpDriver $httpDriver
     * @param DriverFile $driverFile
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        CollectionFactory $onlineCustomerCollectionFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerHelperView $customerHelperView,
        HttpDriver $httpDriver,
        DriverFile $driverFile
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->onlineCustomerCollectionFactory = $onlineCustomerCollectionFactory;
        $this->httpContext = $httpContext;
        $this->customerHelperView = $customerHelperView;
        $this->httpDriver = $httpDriver;
        $this->driverFile = $driverFile;
    }

    /**
     * Retrieve information from carrier configuration
     *
     * @param string $group
     * @param string $field
     * @param string $other
     * @return void
     */
    public function getConfigData($group, $field, $other = '')
    {
        $path = 'buyer_seller_chat/'.$group.'/'.$field;
        if ($other) {
            $path = $other;
        }
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        );
    }

    /**
     * Check is Customer LoggedIn
     *
     * @param int $customerId
     * @param bool $statusReq
     * @return boolean
     */
    public function isCustomerLoggedIn($customerId, $statusReq = false)
    {
        $onlineCollection = $this->onlineCustomerCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('chat_status', ['neq' => 0]);
        if ($onlineCollection->getSize()) {
            if ($statusReq) {
                foreach ($onlineCollection as $collectionData) {
                    $status = $collectionData['chat_status'];
                }
                return $status;
            }
            return true;
        }
        return false;
    }

    /**
     * Send New CustomerEmail
     *
     * @param object $customer
     * @param object $sender
     * @param array $templateParams
     * @param int $storeId
     * @return void
     */
    public function sendNewCustomerEmail(
        $customer,
        $sender,
        $templateParams = [],
        $storeId = null
    ) {
        $customerViewHelper = $this->customerHelperView;
        $storeId = $this->storeManager->getStore()->getId();
        $email = $customer->getEmail();
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($this->_scopeConfig
            ->getValue($sender, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId))
            ->addTo($email, $customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Start/Stop Server
     *
     * @return boolean
     */
    public function isServerRunning()
    {
        $host = $this->getConfigData('general_settings', 'host_name');
        $port = (int) $this->getConfigData('general_settings', 'port_number');
        $connection = $this->httpDriver->getHttpDriver($host, $port);
        if (is_resource($connection)) {
            $result = true;
            $this->driverFile->fileClose($connection);
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Function to get customer id from context
     *
     * @return int customerId
     */
    public function getCustomerId()
    {
        return $this->httpContext->getValue('mpchat_customer_id');
    }

    /**
     * Function to set customer id from context
     *
     * @param int $customerId
     * @return void
     */
    public function setCustomerId($customerId)
    {
        $this->httpContext->setValue(
            'mpchat_customer_id',
            $customerId,
            false
        );
    }
}
