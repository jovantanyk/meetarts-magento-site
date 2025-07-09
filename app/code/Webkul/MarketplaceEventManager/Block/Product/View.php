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
namespace Webkul\MarketplaceEventManager\Block\Product;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Option
     */
    protected $_option;

    /**
     * @var Value
     */
    protected $_value;

    /**
     * @var EventHelper
     */
    protected $_mpEventManagerHelper;
    
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Product $product
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @param \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Catalog\Model\Product\Option\Value $value,
        \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper,
        array $data = []
    ) {
        $this->_product = $product;
        $this->_option = $option;
        $this->_value = $value;
        $this->_mpEventManagerHelper = $mpEventManagerHelper;
        parent::__construct($context, $data);
    }

    /**
     * GetProduct
     */
    public function getProduct()
    {
        if ($this->getRequest()->getFullActionName() == 'wishlist_index_configure') {
            $id = $this->getRequest()->getParam('product_id');
        } else {
            $id = $this->getRequest()->getParam('id');
        }
        return $this->_product->create()->load($id);
    }

    /**
     * GetProductOptions
     *
     * @param object $product
     */
    public function getProductOptions($product)
    {
        return $this->_option->getProductOptionCollection($product)->getData();
    }

    /**
     * GetValueCollectionOfOption
     *
     * @param int $option
     */
    public function getValueCollectionOfOption($option)
    {
        return $this->_value->getValuesCollection($this->_option->load($option));
    }

    /**
     * GetEventExpiredStatus
     */
    public function getEventExpiredStatus()
    {
        $product = $this->getProduct();
        $eventStartTime = $product->getEventStartDate();
        $eventEndTime = $product->getEventEndDate();
        return $this->_mpEventManagerHelper->getEventExpiredStatus(
            $eventStartTime,
            $eventEndTime
        );
    }

    /**
     * GetMpEventManagerHelper
     */
    public function getMpEventManagerHelper()
    {
        return $this->_mpEventManagerHelper;
    }
}
