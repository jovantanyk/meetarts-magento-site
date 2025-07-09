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

class Productlist
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_product = $product;
        $this->_request = $request;
    }

    /**
     * AfterGetAllProducts
     *
     * @param \Webkul\Marketplace\Block\Product\Productlist $subject
     * @param array $coll
     */
    public function afterGetAllProducts(
        \Webkul\Marketplace\Block\Product\Productlist $subject,
        $coll
    ) {
        $actionname = $this->_request->getFullActionName();
        if ($actionname == 'marketplace_product_productlist') {
            $eticketProIds = $this->_product->create()->getCollection()->addFieldToFilter(
                'type_id',
                ['eq' => 'etickets']
            )->getColumnValues('entity_id');
            $coll->addFieldToFilter('mageproduct_id', ['nin' => $eticketProIds]);
            return $coll;
        }
        return $coll;
    }
}
