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
namespace Webkul\MpBuyerSellerChat\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Webkul\MpBuyerSellerChat\Api\SaveCustomerInterface;
use Webkul\MpBuyerSellerChat\Model\CustomerDataRepository as CustomerDataRepository;
use Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class CustomerLoginObserver implements ObserverInterface
{
    /**
     * @var CustomerDataRepository
     */
    protected $dataRepository;

    /**
     * @var CustomerDataInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Webkul\Marketplace\Model\SellerFactory
     */
    protected $mpSellerFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param CustomerDataRepository $dataRepository
     * @param CustomerDataInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Webkul\Marketplace\Model\SellerFactory $mpSellerFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\Marketplace\Model\SellerFactory $mpSellerFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->mpSellerFactory = $mpSellerFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        $customerId = $observer->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customerId) {
            $chatCustomer = $this->dataRepository->getByCustomerId($customerId);
            $sellerModel = $this->mpSellerFactory->create()->getCollection()
             ->addFieldToFilter(
                 'seller_id',
                 $customerId
             );

            if ($sellerModel->getSize() && $sellerModel->getFirstItem()->getIsSeller() && $chatCustomer->getId()) {
                $savedData = (array) $chatCustomer->getData();
                $customerData = array_merge(
                    $savedData,
                    ['customer_id' => $customer->getId(),'chat_status' => 1]
                );
                $customerData['entity_id'] = $chatCustomer->getId();
                $dataObject = $this->customerDataFactory->create();

                $this->dataObjectHelper->populateWithArray(
                    $dataObject,
                    $customerData,
                    \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface::class
                );
                try {
                    $this->dataRepository->save($dataObject);
                } catch (\Exception $e) {
                    $customerData['error'] = true;
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
            }
        }
    }
}
