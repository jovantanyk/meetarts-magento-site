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
 
interface SaveCustomerInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $message Buyer Message.
     * @param int $sellerId Seller Id.
     * @param string $sellerUniqueId Seller Unique Id.
     * @param string $dateTime Seller Message date time.
     * @return string Greeting message with users name.
     */
    public function save($message, $sellerId, $sellerUniqueId, $dateTime);
}
