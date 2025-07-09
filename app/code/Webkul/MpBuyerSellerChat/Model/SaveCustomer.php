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
namespace Webkul\MpBuyerSellerChat\Model;

use Webkul\MpBuyerSellerChat\Api\SaveCustomerInterface;
use Webkul\MpBuyerSellerChat\Model\CustomerDataRepository as CustomerDataRepository;
use Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class SaveCustomer implements SaveCustomerInterface
{

    /**
     * @var CustomerDataRepository
     */
    protected $dataRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerDataInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @param CustomerDataRepository $dataRepository
     * @param CustomerDataInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\View\Asset\Repository $viewFileSystem
     * @param \Webkul\MpBuyerSellerChat\Model\SaveMessageFactory $saveMessageFactory
     * @param StoreManagerInterface $storeManager
     * @param \Webkul\MpBuyerSellerChat\Helper\Data $helper
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        \Webkul\MpBuyerSellerChat\Model\SaveMessageFactory $saveMessageFactory,
        StoreManagerInterface $storeManager,
        \Webkul\MpBuyerSellerChat\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        SerializerJson $serializerJson
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->_viewFileSystem = $viewFileSystem;
        $this->saveMessageFactory = $saveMessageFactory;
        $this->helper = $helper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerFactory = $customerFactory;
        $this->serializerJson = $serializerJson;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $message Buyer Message.
     * @param int $sellerId Seller Id.
     * @param string $sellerUniqueId Seller Unique Id.
     * @param string $dateTime Seller Message date time.
     * @return string Greeting message with users name.
     */
    public function save($message, $sellerId, $sellerUniqueId, $dateTime)
    {
        $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        if (!$customerId) {
            $customerId = $this->helper->getCustomerId();
        }
        $customer = $this->customerFactory->create()->load($customerId);

        if ($customer) {
            $collection = $this->customerDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('registered_as', ['eq' => 'customer']);

            if ($collection->getSize()) {
                $customerDataModel = $collection->getFirstItem();
                $entityId = $customerDataModel->getId();
                $uniqueId = $customerDataModel->getUniqueId();
                $savedData = (array) $customerDataModel->getData();
                $customerData = array_merge(
                    $savedData,
                    ['customer_id' => $customer->getId(),'chat_status' => 1]
                );
                $customerData['entity_id'] = $entityId;
                $customerData['unique_id'] = $uniqueId;
                $customerData['image'] = $customerDataModel->getImage();
            } else {
                $customerData = [
                    'customer_id' => $customer->getId(),
                    'unique_id' => $this->generateUniqueId(),
                    'chat_status' => 1,
                    'registered_as' => 'customer',
                    'image' => ''
                ];
            }
            
            $dataObject = $this->customerDataFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $dataObject,
                $customerData,
                \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface::class
            );

            try {
                $customerExists = $this->dataRepository->getByCustomerId($customer->getId());
                $this->dataRepository->save($dataObject);
                $defaultImageUrl = $this->_viewFileSystem->getUrl(
                    'Webkul_MpBuyerSellerChat::images/default.png',
                    []
                );
                $responseData['customerId'] = $customerId;
                $responseData['customerName'] = $this->customerSessionFactory->create()->getCustomer()->getName();
                $responseData['customerEmail'] = $this->customerSessionFactory->create()->getCustomer()->getEmail();
                $responseData['customerUniqueId'] = $customerData['unique_id'];
                $responseData['customerImage'] = '';
                $responseData['chatStatus'] = 1;
                
                if ($customerData['image'] != '') {
                    $responseData['customerImage'] = $this->storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ).
                    'mpchatsystem/profile/'
                    .$customerId.'/'.$customerDataModel->getImage();
                }
                $responseData['message'] = __('chat enabled');
                $responseData['error'] = false;
            } catch (\Exception $e) {
                $responseData['error'] = true;
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
            
            return $this->serializerJson->serialize($responseData);
        }
    }

    /**
     * Generate UniqueId
     *
     * @return string
     */
    public function generateUniqueId()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pass [] = 'W';
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}
