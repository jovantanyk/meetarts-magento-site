<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerData\CollectionFactory;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerBlock\CollectionFactory as CustomerBlockCollection;

/**
 * Provides data as per private content
 */
class MpBuyerChatData extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var CollectionFactory
     */
    protected $dataCollection;

    /**
     * @var CustomerBlockCollection
     */
    protected $blockedCollection;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_viewFileSystem;

    /**
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param CollectionFactory $dataCollection
     * @param CustomerBlockCollection $blockedCollection
     * @param HttpContext $httpContext
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param FormKey $formKey
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $viewFileSystem
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        CollectionFactory $dataCollection,
        CustomerBlockCollection $blockedCollection,
        HttpContext $httpContext,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        FormKey $formKey,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        array $data = []
    ) {
        parent::__construct($data);
        $this->dataCollection = $dataCollection;
        $this->blockedCollection = $blockedCollection;
        $this->httpContext = $httpContext;
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->_viewFileSystem = $viewFileSystem;
        $this->customerSessionFactory = $customerSessionFactory;
    }
    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $this->customerSession = $this->customerSessionFactory->create();
        $output = [];
        $output['formKey'] = $this->formKey->getFormKey();
        $output['customerData'] = $this->getCustomerData();
        $output['isCustomerLoggedIn'] = $this->isCustomerLoggedIn();
        return $output;
    }

    /**
     * Creating customer data with messages history
     *
     * @return array
     */
    private function getCustomerData()
    {
        $defaultImageUrl = $this->_viewFileSystem->getUrlWithParams('Webkul_MpBuyerSellerChat::images/default.png', []);
        $customerData = [];
        $customerId = $this->customerSessionFactory->create()->getCustomerId();

        $customer = $this->customerSessionFactory->create()->getCustomer();
        if ($customerId) {
            $collection = $this->dataCollection->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('registered_as', ['eq' => 'customer']);
            if ($collection->getSize()) {
                $chatCustomer = $collection->getFirstItem();
                $customerData['customerId'] = $customerId;
                $customerData['customerName'] = $customer->getName();
                $customerData['customerEmail'] = $customer->getEmail();
                $customerData['customerUniqueId'] = $chatCustomer->getUniqueId();
                $customerData['customerImage'] = $defaultImageUrl;
                $customerData['chatStatus'] = $chatCustomer->getChatStatus();

                $customerData['blockedBySellers'] = [];
                $collection = $this->blockedCollection->create()
                    ->addFieldToFilter('customer_unique_id', ['eq' => $chatCustomer->getUniqueId()]);

                if ($collection->getSize()) {
                    foreach ($collection as $blockData) {
                        $customerData['blockedBySellers'][] = $blockData->getSellerUniqueId();
                    }
                }
                
                if ($chatCustomer->getImage() != '') {
                    $customerData['customerImage'] = $this->storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                    'mpchatsystem/profile/'
                    .$customerId.'/'.$chatCustomer->getImage();
                }
            }
        }
        return $customerData;
    }
   
    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Retrieve store code
     *
     * @return string
     */
    private function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}
