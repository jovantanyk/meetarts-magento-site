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
namespace Webkul\MarketplaceEventManager\Plugin\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Data
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * Construct
     *
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeInterface
    ) {
        $this->scopeConfig = $scopeInterface;
    }

    /**
     * AfterGetAllowedProductType
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param array $result
     */
    public function afterGetAllowedProductType(
        \Webkul\Marketplace\Helper\Data $subject,
        $result
    ) {
        $resultData =  explode(',', $result);
        $isModEnable = $this->scopeConfig->getValue(
            'marketplaceeventmanager/settings/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isModEnable) {
            $resultData[] = 'etickets';
        }
        return implode(',', $resultData);
    }
}
