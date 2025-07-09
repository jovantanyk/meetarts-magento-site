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
namespace Webkul\MpBuyerSellerChat\Block\ChatBox;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\ResourceModel\Online\Grid\CollectionFactory;

class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\MpBuyerSellerChat\Model\ChatDataConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Webkul\MpBuyerSellerChat\Api\CustomerDataRepositoryInterface
     */
    protected $chatCustomerRepository;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\MpBuyerSellerChat\Model\ChatDataConfigProvider $configProvider
     * @param \Webkul\MpBuyerSellerChat\Api\CustomerDataRepositoryInterface $chatCustomerRepository
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Registry $registry,
        \Webkul\MpBuyerSellerChat\Model\ChatDataConfigProvider $configProvider,
        \Webkul\MpBuyerSellerChat\Api\CustomerDataRepositoryInterface $chatCustomerRepository,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->configProvider = $configProvider;
        $this->chatCustomerRepository = $chatCustomerRepository;
        $this->mpHelper = $mpHelper;
        $this->_coreRegistry = $registry;
        $this->customerSessionFactory = $customerSessionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'customer_termandcondition/parameter/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * GetProduct function
     *
     * @return object
     */
    public function getProduct()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product;
    }

    /**
     * GetSellerId function
     *
     * @return int
     */
    public function getSellerId()
    {
        $productId = $this->getProduct()->getId();
        $sellerCollection = $this->mpHelper->getSellerProductDataByProductId($productId);
        return $sellerCollection->getFirstItem()->getSellerId();
    }

    /**
     * GetSellerChatData function
     *
     * @return \Webkul\MpBuyerSellerChat\Model\CustomerData
     */
    public function getSellerChatData()
    {
        return $this->chatCustomerRepository->getByCustomerId($this->getSellerId(), 'seller');
    }

    /**
     * CanChatEnable function
     *
     * @return boolean
     */
    public function canChatEnable()
    {
        $customerId = $this->customerSessionFactory->create()->getCustomerId();
        $sellerChatStatus = $this->getSellerChatData()->getChatStatus();
        if ($this->getSellerId() !== $customerId) {
            return true;
        }
        return false;
    }

    /**
     * Get ChatBoxConfig
     *
     * @return array
     */
    public function getChatBoxConfig()
    {
        $chatSellerData = $this->getSellerChatData();
        $configData = $this->configProvider->getConfig();
        $configData['sellerData']['sellerOnline'] = $chatSellerData->getChatStatus();
        $configData['sellerData']['sellerId'] = $chatSellerData->getCustomerId();
        $configData['sellerData']['receiverUniqueId'] = $chatSellerData->getUniqueId();
        $configData['sellerData']['sellerProductId'] = $this->getProduct()->getId();
        $configData['sellerData']['image'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/sellerimage.png');
        if ($chatSellerData->getImage() != '') {
            $defaultImageUrl = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                            'mpchatsystem/profile/'
                            .$chatSellerData->getCustomerId().'/'.$chatSellerData->getImage();
            $configData['sellerData']['image'] = $defaultImageUrl;
        }
        return $configData;
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->mpHelper;
    }
}
