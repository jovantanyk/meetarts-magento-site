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
namespace Webkul\MarketplaceEventManager\Model;

use Webkul\MarketplaceEventManager\Api\Data\MpeventInterface;

class Mpevent extends \Magento\Framework\Model\AbstractModel implements MpeventInterface
{
    /**
     * No route page id
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Test Record cache tag
     */
    public const CACHE_TAG = 'marketplace_mpevent';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_mpevent';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_mpevent';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MarketplaceEventManager\Model\ResourceModel\Mpevent::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteStore();
        }
        return parent::load($id, $field);
    }
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * SetId
     *
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * GetQrcode
     */
    public function getQrcode()
    {
        return parent::getData(self::QRCODE);
    }

    /**
     * SetQrcode
     *
     * @param int $id
     */
    public function setQrcode($id)
    {
        return $this->setData(self::QRCODE, $qrcode);
    }

    /**
     * GetItemId
     */
    public function getItemId()
    {
        return parent::getData(self::ITEM_ID);
    }

    /**
     * SetItemId
     *
     * @param int $id
     */
    public function setItemId($id)
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * GetOrderId
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * SetOrderId
     *
     * @param int $id
     */
    public function setOrderId($id)
    {
        return $this->setData(self::ORDER_ID, $id);
    }

    /**
     * GetOptionId
     */
    public function getOptionId()
    {
        return parent::getData(self::OPTION_ID);
    }

    /**
     * SetOptionId
     *
     * @param int $id
     */
    public function setOptionId($id)
    {
        return $this->setData(self::OPTION_ID, $id);
    }

    /**
     * GetOptionQty
     */
    public function getOptionQty()
    {
        return parent::getData(self::OPTION_QTY);
    }

    /**
     * SetOptionQty
     *
     * @param int $qty
     */
    public function setOptionQty($qty)
    {
        return $this->setData(self::OPTION_QTY, $qty);
    }

    /**
     * GetSellerId
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * SetSellerId
     *
     * @param int $id
     */
    public function setSellerId($id)
    {
        return $this->setData(self::SELLER_ID, $id);
    }

    /**
     * GetStatus
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * SetStatus
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    
    /**
     * GetCreatedAt
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * SetCreatedAt
     *
     * @param datetime $createdat
     */
    public function setCreatedAt($createdat)
    {
        return $this->setData(self::CREATED_AT, $createdat);
    }

    /**
     * GetUpdatedAt
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }
    
    /**
     * SetUpdatedAt
     *
     * @param datetime $updatedat
     */
    public function setUpdatedAt($updatedat)
    {
        return $this->setData(self::UPDATED_AT, $updatedat);
    }
}
