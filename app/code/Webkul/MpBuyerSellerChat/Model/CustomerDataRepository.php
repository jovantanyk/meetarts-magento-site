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

use Webkul\MpBuyerSellerChat\Api\Data;
use Webkul\MpBuyerSellerChat\Api\CustomerDataRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerData as ResourceCustomerData;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerData\CollectionFactory as CustomerDataCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer Data Repository
 * save customer data
 */
class CustomerDataRepository implements CustomerDataRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $timeSlotConfigFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $customerDataCollectionFactory;

    /**
     * @var Data\BlockSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory
     */
    protected $dataCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceCustomerData $resource
     * @param CustomerDataFactory $customerDataFactory
     * @param \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory $dataCustomerFactory
     * @param CustomerDataCollectionFactory $customerDataCollectionFactory
     * @param Data\CustomerDataSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceCustomerData $resource,
        CustomerDataFactory $customerDataFactory,
        \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory $dataCustomerFactory,
        CustomerDataCollectionFactory $customerDataCollectionFactory,
        Data\CustomerDataSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerDataCollectionFactory = $customerDataCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCustomerFactory = $dataCustomerFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Customer data
     *
     * @param \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface $customerData
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\CustomerDataInterface $customerData)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $customerData->setStoreId($storeId);
        try {
            $this->resource->save($customerData);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $customerData;
    }

    /**
     * Load customer data by given chat unique id Identity
     *
     * @param string $id
     * @return CustomerData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $customerData = $this->customerDataFactory->create();
        $this->resource->load($customerData, $id);
        if (!$customerData->getEntityId()) {
            throw new NoSuchEntityException(__('Customer with id "%1" does not exist.', $id));
        }
        return $customerData;
    }
    /**
     * Load customer data by given chat customer id Identity
     *
     * @param string $customerId
     * @param string $type
     * @param bool $logOutCheck
     * @return CustomerData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCustomerId($customerId, $type = "", $logOutCheck = false)
    {
        $collection = $this->customerDataCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);

        if ($type) {
            $collection->addFieldToFilter('registered_as', ['eq' => $type]);
        }
        if ($logOutCheck) {
            return $collection;
        }
        return $collection->getFirstItem();
    }

    /**
     * Load customer data by given chat customer id Identity
     *
     * @param string $uniqueId
     * @return CustomerData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByUniqueId($uniqueId)
    {
        $customerData = $this->customerDataFactory->create();
        $this->resource->load($customerData, $uniqueId, 'unique_id');
        return $customerData;
    }

    /**
     * Load Customer data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerData\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->customerDataCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $customerData = [];
        /** @var PreorderComplete $timeSlotData */
        foreach ($collection as $customerDataModel) {
            $timeSlot = $this->dataCustomerFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $timeSlot,
                $customerDataModel->getData(),
                \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface::class
            );
            $customerData[] = $this->dataObjectProcessor->buildOutputDataArray(
                $timeSlot,
                \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface::class
            );
        }
        $searchResults->setItems($customerData);
        return $searchResults;
    }

    /**
     * Delete PreorderComplete
     *
     * @param \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface $customerData
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\CustomerDataInterface $customerData)
    {
        try {
            $this->resource->delete($customerData);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete PreorderComplete by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
