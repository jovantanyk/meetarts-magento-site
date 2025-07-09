<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\TwoFactorAuth\Setup\Patch\Data;
 
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Patch Class to create custom attribute
 */
class CustomerAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'twofactorauth_phone_number';
    public const FRONTEND_CLASS = 'wk-twofactorauth-telephone';

    /**
     * @var Config
     */
    private $eavConfig;
 
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
 
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
 
    /**
     * AddressAttribute constructor.
     *
     * @param Config              $eavConfig
     * @param EavSetupFactory     $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }
 
    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }
 
    /**
     * Create strategic account customer attribute
     *
     * @return void
     */
    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create();
 
        $customerEntity = $this->eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
 
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
 
        $eavSetup->addAttribute('customer', self::ATTRIBUTE_CODE, [
            'group' => 'General',
            'type' => 'varchar',
            'label' => 'Phone Number',
            'input' => 'text',
            'backend' => \Webkul\TwoFactorAuth\Model\Attribute\Backend\PhoneNumber::class,
            'required' => false,
            'sort_order' => 50,
            'frontend_class' => self::FRONTEND_CLASS,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'visible' => true,
            'system' => false,
            'is_html_allowed_on_front' => true,
            'visible_on_front' => true,

        ]);
 
        $customAttribute = $this->eavConfig->getAttribute('customer', self::ATTRIBUTE_CODE);
 
        $customAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer', 'customer_account_edit']
        ]);
        $customAttribute->save();
    }
 
    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
