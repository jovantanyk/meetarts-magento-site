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
namespace Webkul\MarketplaceEventManager\Plugin\Model\Product\Source;

class Producttype
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Module\Manager $manager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $manager
    ) {
        $this->manager = $manager;
    }

    /**
     * Get options
     *
     * @param \Webkul\Marketplace\Model\Product\Source\Producttype $subject
     * @param array $options
     * @return array
     */
    public function afterToOptionArray(\Webkul\Marketplace\Model\Product\Source\Producttype $subject, $options)
    {
        if ($this->manager->isEnabled('Webkul_MarketplaceEventManager')) {
            array_push($options, ['value' => 'etickets', 'label' => __('Event Ticket')]);
        }
        return $options;
    }
}
