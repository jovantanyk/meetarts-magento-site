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
 
interface LoadHistoryInterface
{
    /**
     * Fetch chat history
     *
     * @api
     * @param string $senderUniqueId
     * @param string $receiverUniqueId
     * @param int $loadTime
     * @return string
     */
    public function loadHistory($senderUniqueId, $receiverUniqueId, $loadTime);
}
