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
namespace Webkul\MpBuyerSellerChat\Model;

use Webkul\MpBuyerSellerChat\Api\ChangeStatusInterface;
use Webkul\MpBuyerSellerChat\Model\CustomerDataRepository as CustomerDataRepository;
use Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MpBuyerSellerChat\Api\Data\CustomerBlockInterfaceFactory;
use Webkul\Marketplace\Model\ProductFactory as MpProductCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class CustomerAvailable
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
     * @var CustomerBlockInterfaceFactory
     */
    protected $blockedCustomerModelFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpBuyerSellerChat\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Filesystem\Driver
     */
    protected $filesystemDriver;

    /**
     * @var MpProductCollectionFactory
     */
    protected $mpProductCollection;

    /**
     * @var \Magento\Customer\Model\CustomerFactory,
     */
    protected $customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storemanager;

    /**
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @param CustomerDataRepository $dataRepository
     * @param CustomerDataInterfaceFactory $customerDataFactory
     * @param CustomerBlockInterfaceFactory $blockedCustomerModelFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\MpBuyerSellerChat\Helper\Data $helper
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param MpProductCollectionFactory $mpProductCollection
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storemanager
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        CustomerBlockInterfaceFactory $blockedCustomerModelFactory,
        \Magento\Framework\App\RequestInterface $request,
        DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpBuyerSellerChat\Helper\Data $helper,
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        MpProductCollectionFactory $mpProductCollection,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        SerializerJson $serializerJson
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->blockedCustomerModelFactory = $blockedCustomerModelFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_request = $request;
        $this->helper = $helper;
        $this->filesystemDriver = $filesystemDriver;
        $this->mpProductCollection = $mpProductCollection;
        $this->customerFactory = $customerFactory;
        $this->storemanager = $storemanager;
        $this->serializerJson = $serializerJson;
        $header = $this->_request->getHeader('content-type');
        $postValues = $this->_request->getPostValue();
        if ($header == 'application/json') {
            $postValues = $this->filesystemDriver->fileGetContents('php://input');
            if ($postValues) {
                $postValues = $this->serializerJson->unserialize($postValues, true);
            }
        }
        $this->_request->setPostValue($postValues);
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param int $customerId
     * @param string $type
     * @return string chat data
     */
    public function check($customerId, $type = '')
    {
        if (!$customerId) {
            $customerData = [
                'available' => false,
                'isStatus' => 0
            ];
            
            return $this->serializerJson->serialize($customerData);
        }
        $isVailable = $this->helper->isCustomerLoggedIn($customerId, true);

        if ($isVailable == 1 || $isVailable == 2) {
            $customerData['isStatus'] = $isVailable;
            $isVailable = true;
        }
        $customerData['available'] = $isVailable;
        
        if (!$isVailable) {
            $collection = $this->customerDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('registered_as', ['eq' => $type]);
                
            $savedData = $collection->getFirstItem();
            $customerData['entity_id'] = $savedData->getId();
            $customerData['unique_id'] = $savedData->getUniqueId();
            $customerData['image'] = $savedData->getImage();
            $savedData = (array) $savedData->getData();
            $customerData = array_merge(
                $savedData,
                ['chat_status' => 0]
            );
            
            $dataObject = $this->customerDataFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $dataObject,
                $customerData,
                \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface::class
            );
            try {
                $this->dataRepository->save($dataObject);
                $customerData['message'] = __('seller status changed.');
                $customerData['available'] = false;
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        } else {
            $collection = $this->customerDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('registered_as', ['eq' => $type]);
            $savedData = $collection->getFirstItem();
            
            if ($savedData->getChatStatus() == 0) {
                $customerData ['available'] = false;
            }
        }
        return $this->serializerJson->serialize($customerData);
    }

    /**
     * Authenticate Customer
     *
     * @param int $sellerId
     * @param int $sellerProductId
     * @param string $sellerEmail
     * @return boolean
     */
    public function isCustomerValidate($sellerId, $sellerProductId, $sellerEmail = '')
    {
        $email = str_replace('"', '', $sellerEmail);
        $websiteID = $this->storemanager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()->setWebsiteId($websiteID)->loadByEmail($email);
        $customerId = $customer->getId();

        if ($customerId == $sellerId) {
            return true;
        }
        return false;
    }

    /**
     * Block Customer for chat
     *
     * @api
     * @param int $sellerId
     * @return string chat data
     */
    public function blockCustomer($sellerId)
    {
        $response = [
            'errors' => true,
            'msg' => __('Not Blocked.')
        ];
        $data = $this->_request->getParam('customerData');
        
        $collection = $this->customerDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $sellerId])
                ->addFieldToFilter('registered_as', ['eq' => 'seller']);
        $sellerData = $collection->getFirstItem();

        if ($sellerData->getId()) {
            $blockModel = $this->blockedCustomerModelFactory->create();
            $collection = $blockModel->getCollection()
                ->addFieldToFilter('customer_unique_id', ['eq' => $data['customerUniqueId']])
                ->addFieldToFilter('seller_unique_id', ['eq' => $sellerData->getUniqueId()]);
            if (!$collection->getSize()) {
                $data['sellerUniqueId'] = $sellerData->getUniqueId();
                $blockModel = $this->blockedCustomerModelFactory->create();

                $blockModel->setCustomerUniqueId($data['customerUniqueId']);
                $blockModel->setBlockReason($data['block_reason']);
                $blockModel->setSellerUniqueId($sellerData->getUniqueId());
                $blockModel->save();

                $response = [
                    'errors' => false,
                    'msg' => __('User blocked.'),
                    'blocked' => true,
                    'data' => $data
                ];
            } else {
                $data['sellerUniqueId'] = $sellerData->getUniqueId();
                $blockModel = $this->blockedCustomerModelFactory->create()->load(
                    $collection->getFirstItem()->getId()
                )->delete();

                $response = [
                    'errors' => false,
                    'blocked' => false,
                    'msg' => __('User un-blocked.'),
                    'data' => $data
                ];
            }
        }
        return $this->serializerJson->serialize($response);
    }
}
