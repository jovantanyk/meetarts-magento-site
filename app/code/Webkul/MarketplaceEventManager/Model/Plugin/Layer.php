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
namespace Webkul\MarketplaceEventManager\Model\Plugin;

class Layer
{
    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper
    ) {
        $this->productFactory = $productFactory;
        $this->_date = $date;
        $this->_mpEventManagerHelper = $mpEventManagerHelper;
    }

    /**
     * AfterGetProductCollection
     *
     * @param string $subject
     * @param object $collection
     */
    public function afterGetProductCollection($subject, $collection)
    {
        $expireProductStatus = $this->_mpEventManagerHelper->getShowExpireProduct();
        if (!$expireProductStatus) {
            $currenttime = $this->_date->gmtDate('Y-m-d G:i:s');
            $eticketsProIds = $this->productFactory->create()->getCollection()
                                            ->addAttributeToSelect('event_end_date, type_id')
                                            ->addFieldToFilter('type_id', 'etickets')
                                            ->addFieldToFilter('event_end_date', ['gt'=>$currenttime])
                                            ->getColumnValues('entity_id');
            $notEticketsProIds = $this->productFactory->create()->getCollection()
                                            ->addAttributeToSelect('type_id')
                                            ->addFieldToFilter('type_id', ['neq'=>'etickets'])
                                            ->getColumnValues('entity_id');
            $proIds = array_merge($eticketsProIds, $notEticketsProIds);
            if (!empty($proIds)) {
                $collection->addAttributeToFilter('entity_id', ['in' => $proIds]);
            }
        }
        return $collection;
    }
}
