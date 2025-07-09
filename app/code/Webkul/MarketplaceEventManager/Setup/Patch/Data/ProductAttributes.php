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
namespace Webkul\MarketplaceEventManager\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class ProductAttributes implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ControllersRepository $controllersRepository
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ControllersRepository $controllersRepository,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->controllersRepository = $controllersRepository;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->setPriceAttribute();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeGroup = 'Ticket Booking';

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'event_terms',
            [
                'group'          => $attributeGroup,
                'input'          => 'textarea',
                'type'           => 'text',
                'label'          => 'Event Terms',
                'visible'        => true,
                'required'         => false,
                'user_defined'         => false,
                'searchable'         => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => 'required-entry',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );
        
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'event_venue',
            [
                'group'          => $attributeGroup,
                'input'          => 'textarea',
                'type'           => 'text',
                'label'          => 'Event Venue',
                'visible'        => true,
                'required'         => false,
                'user_defined'         => false,
                'searchable'         => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => 'required-entry',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'refund_policy',
            [
                'group'          => $attributeGroup,
                'input'          => 'hidden',
                'type'           => 'datetime',
                'label'          => 'Refund Policy',
                'visible'        => true,
                'required'         => false,
                'user_defined'         => false,
                'searchable'         => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => 'required-entry',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'event_start_date',
            [
                'group'          => $attributeGroup,
                'input'          => 'hidden',
                'type'           => 'datetime',
                'label'          => 'Event Start Time',
                'visible'        => true,
                'required'         => false,
                'user_defined'         => false,
                'searchable'         => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => 'required-entry',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'event_end_date',
            [
                'group'          => $attributeGroup,
                'input'          => 'hidden',
                'type'           => 'datetime',
                'label'          => 'Event End Time',
                'visible'        => true,
                'required'         => false,
                'user_defined'         => false,
                'searchable'         => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => 'required-entry',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_mp_event',
            [
                'group'          => $attributeGroup,
                'input'          => 'boolean',
                'type'           => 'int',
                'label'          => 'Is Event Product',
                'visible'        => true,
                'required'       => false,
                'user_defined'   => false,
                'searchable'    => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => '',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'enable_event_terms',
            [
                'group'          => $attributeGroup,
                'input'          => 'boolean',
                'type'           => 'int',
                'label'          => 'Enable Terms',
                'visible'        => true,
                'required'       => false,
                'user_defined'   => false,
                'searchable'    => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => 'enable_event_terms',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'event_ticket_prefix',
            [
                'group'          => $attributeGroup,
                'input'          => 'text',
                'type'           => 'varchar',
                'label'          => 'Set Ticket Prefix',
                'visible'        => true,
                'required'       => false,
                'user_defined'   => false,
                'searchable'    => false,
                'filterable'         => false,
                'comparable'         => false,
                'visible_on_front'       => false,
                'visible_in_advanced_search' => false,
                'is_html_allowed_on_front'   => false,
                'used_for_promo_rules'       => true,
                'frontend_class'       => 'event_ticket_prefix',
                'global'         => ScopedAttributeInterface::SCOPE_GLOBAL
            ]
        );
    }

    /**
     * SetPriceAttribute
     */
    private function setPriceAttribute()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $eavSetup = $objectManager->create(\Magento\Eav\Setup\EavSetup::class);
            $fieldList = [
                'price',
                'cost',
                'tax_class_id',
                'special_price',
                'special_from_date',
                'special_to_date',
                'minimal_price',
                'cost',
                'tier_price'
            ];
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array('etickets', $applyTo)) {
                    $applyTo[] = 'etickets';
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
