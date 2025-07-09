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
namespace Webkul\MarketplaceEventManager\Block\Event;

class Reminder extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magetno\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $_mpproduct;

    /**
     * @var \Magetno\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Model\Product $mpproduct
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Webkul\MarketplaceEventManager\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Model\Product $mpproduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\Order $order,
        \Webkul\MarketplaceEventManager\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_mpproduct = $mpproduct;
        $this->_product = $productFactory;
        $this->_order = $order;
        $this->helper = $helper;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * GetCustomerId
     */
    public function getCustomerId()
    {
        return $this->helper->getLoggedInSellerId();
    }

    /**
     * GetMarketplaceProductData
     */
    public function getMarketplaceProductData()
    {
        $collection =  $this->_mpproduct
            ->getcollection()
            ->addFieldToFilter('mageproduct_id', $this->getRequest()->getParam('pid'));
        if ($collection->getSize()) {
            foreach ($collection as $key => $mpproduct) {
                return $mpproduct->getData();
            }
        }
    }
    
    /**
     * GetProductDetails
     */
    public function getProductDetails()
    {
        return $this->_product->create()->load($this->getRequest()->getParam('pid'));
    }

    /**
     * GetBuyerList
     */
    public function getBuyerList()
    {
        $pid = $this->getRequest()->getParam('pid');
        $product = $this->getProductDetails();
        $customerList = [];
        $order = $this->_order->getCollection();
        foreach ($order as $o) {
            foreach ($o->getAllVisibleItems() as $item) {
                if ($item->getProductId() == $pid) {
                    if ($o->getCustomerFirstname() && $o->getCustomerLastname()) {
                        $customerList[$o->getCustomerEmail()] =
                         $o->getCustomerFirstname()." ".$o->getCustomerLastname();
                    } else {
                        $customerList[$o->getCustomerEmail()] = $o->getCustomerEmail().__('(Guest User)');
                    }
                }
            }
        }
        return $customerList;
    }

    /**
     * GetImageUrl
     *
     * @param string $image
     */
    public function getImageUrl($image)
    {
        if ($image && $image != 'no_selection') {
            return $this->_storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product'.$image;
        } else {
            return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
        }
    }
}
