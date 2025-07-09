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

namespace Webkul\MarketplaceEventManager\Block\Customer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Eventlist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /** @var \Magento\Catalog\Model\Product */
    protected $_productlists;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    protected $_itemCollection;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Construct
     *
     * @param Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CollectionFactory $productCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $productCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_imageHelper = $context->getImageHelper();
        $this->_priceCurrency = $priceCurrency;
        $this->_orderFactory = $orderFactory;
        $this->_itemCollection = $itemCollection;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Tickets'));
    }

    /**
     * Get formatted by price and currency.
     *
     * @param float $price
     * @param string $currency
     *
     * @return array || float
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_priceCurrency->format(
            $price,
            true,
            2,
            null,
            $currency
        );
    }

    /**
     * GetAllTickets
     *
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllTickets()
    {
        if (!$this->_productlists) {
            $paramData = $this->getRequest()->getParams();
            $filter = '';
            if (isset($paramData['q'])) {
                $filter = $paramData['q'] != '' ? $paramData['q'] : '';
            }
            $customer = $this->_customerSession->getCustomer();
            $collection = $this->_orderFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq'=>$customer->getId()]);
            $pid = [];
            
            foreach ($collection as $orders) {
                $order = $this->_orderFactory->create()->load($orders->getId());
                foreach ($order->getAllVisibleItems() as $items) {
                    if ($items->getStatusId() == 9) {
                        if ($items->getProductType() == 'etickets'
                            && ($filter == ''
                                || strpos(strtolower($items->getName()), strtolower($filter)) !== false)) {
                            $pid[] = $items->getItemId();
                        }
                    }
                }
            }
            $eventProducts = $this->_itemCollection
                ->addFieldToFilter('item_id', ['in'=>$pid]);
            $eventProducts->setOrder('item_id', 'desc');

            $this->_productlists = $eventProducts;
        }

        return $this->_productlists;
    }
    /**
     * PrepareLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllTickets()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.product.list.pager'
            )->setCollection(
                $this->getAllTickets()
            );
            $this->setChild('pager', $pager);
            $this->getAllTickets()->load();
        }

        return $this;
    }
    /**
     * GetPagerHtml
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * ImageHelperObj
     */
    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }

    /**
     * GetProductData
     *
     * @param int $id
     * @param object $item
     */
    public function getProductData($id, $item)
    {
        $product = $this->_productFactory->create()->load($id);
        if ($item->getWkmpEventStart()) {
            $product->setEventTicketPrefix($item->getWkmpEventQrprefix());
            $product->setEventVenue($item->getWkmpEventLocation());
            $product->setEventStartDate($item->getWkmpEventStart());
            $product->setEventEndDate($item->getWkmpEventEnd());
        }
        $product->setProductId($item->getProductId());
        $product->setProductName($item->getName());
        return $product;
    }

    /**
     * Get order increment id
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderIncrementId($orderId)
    {
        return $this->_orderFactory->create()->load($orderId)->getIncrementId();
    }
}
