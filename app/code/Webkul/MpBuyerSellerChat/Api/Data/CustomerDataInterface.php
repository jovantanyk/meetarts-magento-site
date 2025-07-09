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
namespace Webkul\MpBuyerSellerChat\Api\Data;

/**
 * MpBuyerSellerChat customer interface.
 * @api
 */
interface CustomerDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID     = 'entity_id';
    public const CUSTOMER_ID   = 'customer_id';
    public const UNIQUE_ID     = 'unique_id';
    public const CHAT_STATUS   = 'chat_status';
    public const REGISTERED_AS = 'registered_as';
    public const IMAGE         = 'image';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get customer unique ID
     *
     * @return string|null
     */
    public function getUniqueId();

    /**
     * Get chat status
     *
     * @return int
     */
    public function getChatStatus();

    /**
     * Get chat status
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Get chat registration type
     *
     * @return string|null
     */
    public function getRegisteredAs();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setId($id);

     /**
      * Set customer ID
      *
      * @param int $customerId
      * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
      */
    public function setCustomerId($customerId);

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setUniqueId($uniqueId);

    /**
     * Set chat status
     *
     * @param int $status
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setChatStatus($status);

    /**
     * Set chat status
     *
     * @param string $image
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setImage($image);

    /**
     * Set chat registration type
     *
     * @param string $type
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setRegisteredAs($type);
}
