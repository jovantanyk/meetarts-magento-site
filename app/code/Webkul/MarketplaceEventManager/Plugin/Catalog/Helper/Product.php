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
namespace Webkul\MarketplaceEventManager\Plugin\Catalog\Helper;

class Product
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    private $helper;
    
    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\MarketplaceEventManager\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MarketplaceEventManager\Helper\Data $helper
    ) {
        $this->_date = $date;
        $this->helper = $helper;
    }

    /**
     * AfterInitProduct
     *
     * @param \Magento\Catalog\Helper\Product $subject
     * @param object $product
     */
    public function afterInitProduct(
        \Magento\Catalog\Helper\Product $subject,
        $product
    ) {
        try {
            if ($product && $product->getTypeId() == 'eticket' && !$this->helper->getShowExpireProduct()) {
                $currenttime = strtotime($this->_date->gmtDate('Y-m-d G:i:s'));
                $eventendTime = strtotime($product->getEventEndDate());
                if ($currenttime < $eventendTime) {
                    return $product;
                }
                return false;
            }
            return $product;
        } catch (\Exception $e) {
            return false;
        }
    }
}
