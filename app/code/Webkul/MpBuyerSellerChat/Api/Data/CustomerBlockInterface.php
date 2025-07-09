<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Api\Data;

/**
 * MpBuyerSellerChat blocked customer interface.
 * @api
 */
interface CustomerBlockInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID             = 'entity_id';
    public const CUSTOMER_UNIQUE_ID    = 'customer_unique_id';
    public const SELLER_UNIQUE_ID      = 'seller_unique_id';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get customer unique ID
     *
     * @return string|null
     */
    public function getCustomerUniqueId();

    /**
     * Get seller unique ID
     *
     * @return string|null
     */
    public function getSellerUniqueId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerBlockInterface
     */
    public function setId($id);

     /**
      * Set customer unique ID
      *
      * @param string $customerId
      * @return \Webkul\MagentoChatSystem\Api\Data\CustomerBlockInterface
      */
    public function setCustomerUniqueId($customerId);

    /**
     * Set seller unique ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerBlockInterface
     */
    public function setSellerUniqueId($uniqueId);
}
