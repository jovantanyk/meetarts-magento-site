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
namespace Webkul\MarketplaceEventManager\Block;

class Checkstatus extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product;
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var Saleslist
     */
    protected $_saleslist;

    /**
     * @var Event
     */
    protected $_mpevent;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var MpEventManagerHelper
     */
    protected $_mpEventManagerHelper;
    
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\Marketplace\Model\Saleslist $saleslist
     * @param \Webkul\MarketplaceEventManager\Model\Mpevent $mpevent
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\Saleslist $saleslist,
        \Webkul\MarketplaceEventManager\Model\Mpevent $mpevent,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper,
        array $data = []
    ) {
        $this->_order = $order;
        $this->productFactory = $productFactory;
        $this->_customerSession = $customerSession;
        $this->_saleslist = $saleslist;
        $this->_mpevent = $mpevent;
        $this->_mpEventManagerHelper = $mpEventManagerHelper;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * GetCustomerId
     */
    public function getCustomerId()
    {
        if ($this->_customerSession->getCustomer()->getId()) {
            return $this->_customerSession->getCustomer()->getId();
        }
        return false;
    }

    /**
     * Get ticket event list
     *
     * @param int $orderId
     * @param string $qrcode
     * @param int $itemId
     * @return \Webkul\Marketplace\Model\Saleslist\Collection
     */
    public function getEventProduct($orderId, $qrcode, $itemId)
    {
        $collection  = $this->_saleslist
            ->getCollection()
            ->addFieldToFilter('order_id', ['eq' => $orderId])
            ->addFieldToFilter('order_item_id', ['eq' => $itemId])
            ->addFieldToFilter('qrcode', ['eq' => $qrcode]);
    
        if ($collection->getSize() > 0) {
            foreach ($collection as $col) {
                $productid = $col->getMageproductId();
                break;
            }
        }
        return $this->productFactory->create()->load($productid);
    }

    /**
     * Get current customer ticket
     *
     * @param string $qrcode
     * @return \Webkul\MarketplaceEventManager\Model\Mpevent\Collection
     */
    public function getQrCollection($qrcode)
    {
        $qrCollection = $this->_mpevent
            ->getCollection()
            ->addFieldToFilter('qrcode', ['eq'=>$qrcode]);
        // get valid collection for current customer or seller
        $sellerId = $this->getMpEventManagerHelper()->getLoggedInSellerId();
        $qrCollection->getValidQrCollection($this->getCustomerId(), $sellerId);
        return $qrCollection;
    }

    /**
     * Get Status if right seller is validating ticket
     *
     * @param string $qrcode
     * @return bool
     */
    public function isValidSeller($qrcode)
    {
        $sellerId = $this->getMpEventManagerHelper()->getLoggedInSellerId();
        $collection  = $this->_mpevent
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('qrcode', ['eq' => $qrcode]);
        $status = 0;
        if ($collection->getSize() > 0) {
            $status = 1;
        }
        return $status;
    }

    /**
     * GetImageUrl
     *
     * @param string $image
     */
    public function getImageUrl($image)
    {
        if ($image) {
            return $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) .'catalog/product'.$image;
        } else {
            return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
        }
    }

    /**
     * GetRealOrderIdVal
     */
    public function getRealOrderIdVal()
    {
        return $this->_order->create()->load($this->getRequest()->getParam('order_id'))->getRealOrderId();
    }

    /**
     * GetMpEventManagerHelper
     */
    public function getMpEventManagerHelper()
    {
        return $this->_mpEventManagerHelper;
    }
}
