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
namespace Webkul\MpBuyerSellerChat\Model;

use Webkul\MpBuyerSellerChat\Api\Data\CustomerBlockInterface;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerBlock as ResourceCustomerData;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Customer data model
 *
 */
class CustomerBlock extends AbstractModel implements CustomerBlockInterface, IdentityInterface
{
    /**
     * Customer data cache tag
     */
    public const CACHE_TAG = 'mp_chat_customer_block';

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'mp_chat_customer_block';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mp_chat_customer_block';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerBlock::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get customer unique ID
     *
     * @return string|null
     */
    public function getCustomerUniqueId()
    {
        return $this->getData(self::CUSTOMER_UNIQUE_ID);
    }

    /**
     * Get seller unique ID
     *
     * @return string|null
     */
    public function getSellerUniqueId()
    {
        return $this->getData(self::SELLER_UNIQUE_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerBlockInterface
     */
    public function setId($id)
    {
        $this->setData(self::ENTITY_ID, $id);
    }

     /**
      * Set customer unique ID
      *
      * @param string $customerId
      * @return \Webkul\MagentoChatSystem\Api\Data\CustomerBlockInterface
      */
    public function setCustomerUniqueId($customerId)
    {
        $this->setData(self::CUSTOMER_UNIQUE_ID, $customerId);
    }

    /**
     * Set seller unique ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerBlockInterface
     */
    public function setSellerUniqueId($uniqueId)
    {
        $this->setData(self::SELLER_UNIQUE_ID, $uniqueId);
    }
}
