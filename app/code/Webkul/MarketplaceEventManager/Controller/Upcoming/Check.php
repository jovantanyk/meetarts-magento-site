<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceEventManager\Controller\Upcoming;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Check extends Action
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonData
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\CatalogInventory\Helper\Stock $stockFilter
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->jsonData = $jsonData;
        $this->timezone = $timezone;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->_product = $product;
        $this->_stockFilter = $stockFilter;
        $this->eventManager = $eventManager;
        parent::__construct($context);
    }

    /**
     * Search Result Page
     *
     * @return PageFactory
     */
    public function execute()
    {
        try {
            $match = 1;
            $eventHelper = $this->_objectManager->create(
                \Webkul\MarketplaceEventManager\Helper\Data::class
            );
            $data = $this->getRequest()->getParam('data');
            $data = $this->jsonData->jsonDecode($data);
            $collection = $this->_product->create()
                                ->getCollection()
                                ->addAttributeToSelect('*')
                                ->addFieldToFilter('type_id', ['eq' => 'etickets']);
            $this->_stockFilter->addInStockFilterToCollection($collection);
            if ($eventHelper->getEventToDate()) {
                $collection->addFieldToFilter('event_start_date', ['lteq' => $eventHelper->getEventToDate()]);
            }
            if ($eventHelper->getEventFromDate()) {
                $collection->addFieldToFilter('event_start_date', ['gteq' => $eventHelper->getEventFromDate()]);
            }
            if (!$eventHelper->getEventToDate() && !$eventHelper->getEventFromDate()) {
                $today = $eventHelper->getTodayDate(); //default timezone
                $collection->addFieldToFilter('event_start_date', ['gteq' => $today]);
            }
            $entities = $collection->getColumnValues('entity_id');
            if (!empty(array_diff($data, $entities)) || array_diff($entities, $data)) {
                $match = 0;
            }
            if (!$match) {
                $types = ['block_html','full_page'];
                foreach ($types as $type) {
                    $this->cacheTypeList->cleanType($type);
                }
                $this->eventManager->dispatch('adminhtml_cache_flush_all');
                foreach ($this->cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }
            }
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody(
                $this->jsonData->jsonEncode(
                    [
                            'success' => 1,
                            'match' => $match
                        ]
                )
            );
        } catch (\Exception $e) {
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody(
                $this->jsonData->jsonEncode(
                    [
                            'success' => 0,
                            'match' => 0
                        ]
                )
            );
        }
    }
}
