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
namespace Webkul\MpBuyerSellerChat\Api;
 
interface SaveMessageInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $senderUniqueId
     * @param string $receiverUniqueId
     * @param string $message
     * @param string $dateTime
     * @param string $msgType
     * @return string Greeting message with users name.
     */
    public function saveMessage($senderUniqueId, $receiverUniqueId, $message, $dateTime, $msgType = '');
}
