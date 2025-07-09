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
namespace Webkul\MarketplaceEventManager\Api\Data;

interface MpeventInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ID          = 'id';
    public const QRCODE      = 'qrcode';
    public const ITEM_ID     = 'item_id';
    public const ORDER_ID    = 'order_id';
    public const OPTION_ID   = 'option_id';
    public const OPTION_QTY  = 'option_qty';
    public const SELLER_ID   = 'seller_id';
    public const STATUS      = 'status';
    public const CREATED_AT  = 'created_at';
    public const UPDATED_AT  = 'updated_at';

    /***/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     *
     * @return \Webkul\MarketplaceEventManager\Api\Data\MpeventInterface
     */
    public function setId($id);

    /**
     * GetQrcode
     *
     * @return string|null
     */
    public function getQrcode();

    /**
     * SetQrcode
     *
     * @param string $qrcode
     * @return $this
     */
    public function setQrcode($qrcode);

    /**
     * GetItemId
     *
     * @return string|null
     */
    public function getItemId();

    /**
     * SetItemId
     *
     * @param string $id
     * @return $this
     */
    public function setItemId($id);

    /**
     * GetOrderId
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * SetOrderId
     *
     * @param string $id
     * @return $this
     */
    public function setOrderId($id);

    /**
     * GetOptionId
     *
     * @return string|null
     */
    public function getOptionId();

    /**
     * SetOptionId
     *
     * @param string $id
     * @return $this
     */
    public function setOptionId($id);

    /**
     * GetOptionQty
     *
     * @return string|null
     */
    public function getOptionQty();

    /**
     * SetOptionQty
     *
     * @param string $qty
     * @return $this
     */
    public function setOptionQty($qty);

    /**
     * GetSellerId
     *
     * @return string|null
     */
    public function getSellerId();

    /**
     * SetSellerId
     *
     * @param string $id
     * @return $this
     */
    public function setSellerId($id);

    /**
     * GetStatus
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * SetStatus
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * GetCreatedAt
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * SetCreatedAt
     *
     * @param datetime $createdat
     * @return $this
     */
    public function setCreatedAt($createdat);

    /**
     * GetUpdatedAt
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * SetUpdatedAt
     *
     * @param datetime $updatedat
     * @return $this
     */
    public function setUpdatedAt($updatedat);
}
