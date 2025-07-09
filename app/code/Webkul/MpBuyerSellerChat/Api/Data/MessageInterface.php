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
 * MpBuyerSellerChat message history interface.
 * @api
 */
interface MessageInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID     = 'entity_id';
    public const MESSAGE       = 'message';
    public const DATE          = 'date';
    public const SENDER_UNIQUE_ID = 'sender_unique_id';
    public const RECEIVER_UNIQUE_ID = 'receiver_unique_id';
    public const SENDER_NAME = 'sender_name';
    public const RECEIVER_NAME = 'receiver_name';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get sender ID
     *
     * @return string|null
     */
    public function getSenderUniqueId();

    /**
     * Get receiver ID
     *
     * @return string|null
     */
    public function getReceiverUniqueId();

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getDate();

    /**
     * Get SenderName
     *
     * @return string|null
     */
    public function getSenderName();

    /**
     * Get ReceiverName
     *
     * @return string|null
     */
    public function getReceiverName();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setId($id);

     /**
      * Set sender id
      *
      * @param string $senderUniqueId
      * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
      */
    public function setSenderUniqueId($senderUniqueId);

    /**
     * Set receiver unique id
     *
     * @param string $receiverUniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setReceiverUniqueId($receiverUniqueId);

    /**
     * Set receiver id
     *
     * @param string $message
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setMessage($message);

    /**
     * Set date
     *
     * @param string $date
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setDate($date);

    /**
     * Set SenderName
     *
     * @param string $senderName
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setSenderName($senderName);

    /**
     * Set ReceiverName
     *
     * @param string $receiverName
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setReceiverName($receiverName);
}
