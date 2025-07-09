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
namespace Webkul\MarketplaceEventManager\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeViewCart implements ObserverInterface
{
    /**
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    protected $_eventHelper;

    /**
     * Construct
     *
     * @param \Webkul\MarketplaceEventManager\Helper\Data $helper
     */
    public function __construct(\Webkul\MarketplaceEventManager\Helper\Data $helper)
    {
        $this->_eventHelper = $helper;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_eventHelper->checkStatus();
    }
}
