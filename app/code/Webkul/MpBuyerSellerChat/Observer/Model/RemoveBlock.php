<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpBuyerSellerChat\Observer\Model;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveBlock implements ObserverInterface
{
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('active_customer_model');

        if ($block) {
            $remove = $this->_scopeConfig->getValue(
                'marketplace/layout_settings/is_separate_panel',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            
            if ($remove) {
                $layout->unsetElement('active_customer_model');
            }

            if ($remove == 0) {
                $layout->unsetElement('core_config_model_layout2');
                $layout->unsetElement('active_customer_model_layout2');
            }
        }
    }
}
