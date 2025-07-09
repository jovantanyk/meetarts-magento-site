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

class CustomerLogOutObserver implements ObserverInterface
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param CustomerDataRepository $dataRepository
     * @param CustomerDataInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        $customerId = $observer->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer) {
            $chatCustomerColection = $this->dataRepository->getByCustomerId($customerId, '', true);
            foreach ($chatCustomerColection as $chatCustomer) {
                if ($chatCustomer->getId()) {
                    $savedData = (array) $chatCustomer->getData();
                    $customerData = $this->mergeCustomerData($savedData, $customer->getId());
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

    /**
     * Merge customer data
     *
     * @param array $savedData
     * @param int $customerId
     * @return array
     */
    public function mergeCustomerData($savedData, $customerId)
    {
        return $customerData = array_merge(
            $savedData,
            ['customer_id' => $customerId,'chat_status' => 0]
        );
    }
}
