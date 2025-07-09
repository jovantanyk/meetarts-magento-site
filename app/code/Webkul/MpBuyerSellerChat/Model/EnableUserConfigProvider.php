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

use Webkul\MpBuyerSellerChat\Api\CustomerDataRepositoryInterface;
use Webkul\MpBuyerSellerChat\Model\CustomerData;
use Magento\Framework\Data\Form\FormKey;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\Message\CollectionFactory;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerBlock\CollectionFactory as CustomerBlockCollection;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class EnableUserConfigProvider
{
    /**
     * @var CustomerDataRepositoryInterface
     */
    protected $customerDataRepository;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_viewFileSystem;

    /**
     * @var CollectionFactory
     */
    protected $_chatMessageCollection;

    /**
     * @var CustomerBlockCollection
     */
    protected $blockCustomerCollection;

    /**
     * @var CustomerSessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModelFactory;
    
    /**
     * @param FormKey $formKey
     * @param CustomerDataRepositoryInterface $customerDataRepository
     * @param CustomerDataFactory $customerDataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CollectionFactory $dataCollection
     * @param CustomerBlockCollection $blockCustomerCollection
     * @param \Magento\Framework\View\Asset\Repository $viewFileSystem
     * @param CustomerSessionFactory $customerSessionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerModelFactory
     */
    public function __construct(
        FormKey $formKey,
        CustomerDataRepositoryInterface $customerDataRepository,
        CustomerDataFactory $customerDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $dataCollection,
        CustomerBlockCollection $blockCustomerCollection,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        CustomerSessionFactory $customerSessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory
    ) {
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->customerDataRepository = $customerDataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_chatMessageCollection = $dataCollection;
        $this->blockCustomerCollection = $blockCustomerCollection;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerModelFactory = $customerModelFactory;
    }
    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $output['formKey'] = $this->formKey->getFormKey();
        $output['enableUserData'] = $this->getEnableUsers();
        $output['sellerChatData'] = $this->getSellerChatData();
        $output['blockedCustomerData'] = $this->getBlockedCustomerList();
        $output['chatEnabled'] = $this->isChatEnabled();

        return $output;
    }

    /**
     * Retrieve customer data
     *
     * @return array
     */
    private function getEnableUsers()
    {
        $customerData = [];
        $customer = $this->customerSessionFactory->create()->getCustomer();
        $collection = $this->customerDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customer->getId()])
            ->addFieldToFilter('registered_as', ['eq' => 'seller']);
        if ($collection->getSize()) {
            $chatCustomer = $collection->getFirstItem();
            $customerUniqueId = $chatCustomer->getUniqueId();
            $messageModel = $this->_chatMessageCollection->create()
                ->addFieldToFilter(
                    ['sender_unique_id', 'receiver_unique_id'],
                    [['eq' => $customerUniqueId], ['eq' => $customerUniqueId]]
                )->setOrder('date', 'ASC');
            $messageModel->getSelect()->distinct(true)->group('sender_unique_id');

            foreach ($messageModel as $messageData) {
                if ($messageData->getSenderUniqueId() != $customerUniqueId) {
                    $chatCustomer = $this->customerDataRepository
                        ->getByUniqueId($messageData->getSenderUniqueId());
                    $customer = $this->customerModelFactory->create()->load($chatCustomer->getCustomerId());
                    
                    if (!$customer->getId()) {
                        continue;
                    }
                    if ($chatCustomer->getImage() != null ||
                        $chatCustomer->getImage() != '') {
                        $defaultImageUrl = $this->storeManager
                        ->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                        'mpchatsystem/profile/'
                        .$customer->getId().'/'.$chatCustomer->getImage();
                    } else {
                        $defaultImageUrl = $this->_viewFileSystem->getUrlWithParams(
                            'Webkul_MpBuyerSellerChat::images/default.png',
                            []
                        );
                    }
                    $customerData[] = [
                        'customerId' => $customer->getId(),
                        'customerName' => $customer->getName(),
                        'customerEmail' => $customer->getEmail(),
                        'customerUniqueId' => $chatCustomer->getUniqueId(),
                        'customerImage' => $defaultImageUrl,
                        'chatStatus' => $chatCustomer->getChatStatus()
                    ];
                }
            }
        }

        return $customerData;
    }

    /**
     * Check Chat Status
     *
     * @return boolean
     */
    private function isChatEnabled()
    {
        $customer = $this->customerSessionFactory->create()->getCustomer();
        $collection = $this->customerDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customer->getId()])
            ->addFieldToFilter('registered_as', ['eq' => 'seller']);
        if ($collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Collect current Seller Data
     *
     * @return array|boolean
     */
    public function getSellerChatData()
    {
        $customer = $this->customerSessionFactory->create()->getCustomer();
        
        $defaultImageUrl = '';

        $data = [
            'sellerImage' => ''
        ];
        
        $collection = $this->customerDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customer->getId()])
            ->addFieldToFilter('registered_as', ['eq' => 'seller']);

        if ($collection->getSize()) {
            $chatCustomer = $collection->getFirstItem();
            if ($chatCustomer->getImage() != '') {
                $defaultImageUrl = $this->storeManager
                ->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                            'mpchatsystem/profile/'
                            .$customer->getId().'/'.$chatCustomer->getImage();
            }
            $data = [
                'sellerId' => $chatCustomer->getCustomerId(),
                'sellerUniqueId' => $chatCustomer->getUniqueId(),
                'sellerImage' => $defaultImageUrl,
                'chatStatus' => $chatCustomer->getChatStatus(),
                'sellerName' => $customer->getName()
            ];
            return $data;
        }

        return $data;
    }

    /**
     * Get Blocked Customer List
     *
     * @return array $list
     */
    public function getBlockedCustomerList()
    {
        $list = [];
        $customer = $this->customerSessionFactory->create()->getCustomer();
        
        $collection = $this->customerDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customer->getId()])
            ->addFieldToFilter('registered_as', ['eq' => 'seller']);

        if ($collection->getSize()) {
            $sellerData = $collection->getFirstItem();
            $blockedCustomerList = $this->blockCustomerCollection->create()
                ->addFieldToFilter('seller_unique_id', ['eq' => $sellerData->getUniqueId()]);
            
            foreach ($blockedCustomerList as $blockCustomer) {
                $list[] = $blockCustomer->getCustomerUniqueId();
            }
        }
        return $list;
    }
}
