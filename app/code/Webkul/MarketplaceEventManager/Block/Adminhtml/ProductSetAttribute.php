<?php
/**
 * Webkul_DailyDeals Product Product Attribute Adminhtml Block.
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceEventManager\Block\Adminhtml;

class ProductSetAttribute extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    public $_template = 'product/setattribute.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * GetDealsDateTime
     *
     * @return false|string
     */
    public function getEventDateTime()
    {
        $product = $this->coreRegistry->registry('product');
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $dateFrom = $product->getEventStartDate();
        $dateTo = $product->getEventEndDate();
        $proType = $this->getRequest()->getParam('type');
        $proType = $proType ? $proType : $product->getTypeId();
        
        return [
            'event_start_date'=> $this->_localeDate->date($dateFrom)->format('m/d/Y H:i:s') ,
            'event_end_date'=> $this->_localeDate->date($dateTo)->format('m/d/Y H:i:s'),
            'date_format' => $dateFormat,
            'productType' => $proType,
            'productId' => $product->getId(),
        ];
    }
}
