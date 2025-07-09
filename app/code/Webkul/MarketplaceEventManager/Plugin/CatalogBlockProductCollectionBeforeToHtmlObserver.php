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

namespace Webkul\MarketplaceEventManager\Plugin;

use \Magento\Framework\App\Helper\Context;

class CatalogBlockProductCollectionBeforeToHtmlObserver
{
    /**
     *
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    protected $_helper;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * Construct
     *
     * @param Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Webkul\MarketplaceEventManager\Helper\Data $data
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Webkul\MarketplaceEventManager\Helper\Data $data,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_helper = $data;
        $this->_request = $request;
        $this->_reviewFactory = $reviewFactory;
    }

    /**
     * AroundExecute
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param callable $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return string
     */
    public function aroundExecute(
        \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $subject,
        callable $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        $productCollection = $observer->getEvent()->getCollection();
        $productIds = $this->_helper->getEventProductList();
        if ($this->_request->getFullActionName() == 'marketplaceeventmanager_upcoming_events') {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            $observer->setCollection($productCollection);
        }
        $proceed($observer);
        return $this;
    }
}
