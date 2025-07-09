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

use Webkul\MpBuyerSellerChat\Api\Data\MessageInterface;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\Message as ResourceMessage;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Message history model
 *
 */
class Message extends AbstractModel implements MessageInterface, IdentityInterface
{
    /**
     * Message history cache tag
     */
    public const CACHE_TAG = 'mp_chat_message_history';

    /**#@+
     * customer chat statuses
     */
    public const STATUS_BUSY = 2;
    public const STATUS_ACTIVE = 1;
    public const STATUS_DISABLED = 0;

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'mp_chat_message_history';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mp_chat_message_history';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpBuyerSellerChat\Model\ResourceModel\Message::class);
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
     * Get sender ID
     *
     * @return string|null
     */
    public function getSenderUniqueId()
    {
        return $this->getData(self::SENDER_UNIQUE_ID);
    }

    /**
     * Get receiver ID
     *
     * @return string|null
     */
    public function getReceiverUniqueId()
    {
        return $this->getData(self::RECEIVER_UNIQUE_ID);
    }

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * Get SenderName
     *
     * @return string|null
     */
    public function getSenderName()
    {
        return $this->getData(self::SENDER_NAME);
    }
    
    /**
     * Get ReceiverName
     *
     * @return string|null
     */
    public function getReceiverName()
    {
        return $this->getData(self::RECEIVER_NAME);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set sender unique id
     *
     * @param string $senderUniqueId
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface
     */
    public function setSenderUniqueId($senderUniqueId)
    {
        return $this->setData(self::SENDER_UNIQUE_ID, $senderUniqueId);
    }

    /**
     * Set receiver unique id
     *
     * @param string $receiverUniqueId
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface
     */
    public function setReceiverUniqueId($receiverUniqueId)
    {
        return $this->setData(self::RECEIVER_UNIQUE_ID, $receiverUniqueId);
    }

    /**
     * Set receiver id
     *
     * @param string $message
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Set date
     *
     * @param string $date
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    /**
     * Set SenderName
     *
     * @param string $senderName
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface
     */
    public function setSenderName($senderName)
    {
        return $this->setData(self::SENDER_NAME, $senderName);
    }

    /**
     * Set ReceiverName
     *
     * @param string $receiverName
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface
     */
    public function setReceiverName($receiverName)
    {
        return $this->setData(self::RECEIVER_NAME, $receiverName);
    }
}
