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

interface ChangeStatusInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param int $status
     * @param string $type
     * @return string.
     */
    public function changeStatus($status, $type = '');
}
