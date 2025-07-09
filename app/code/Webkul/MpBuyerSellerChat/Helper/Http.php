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
namespace Webkul\MpBuyerSellerChat\Helper;

/**
 * BuyerSeller data helper.
 */
class Http extends \Magento\Framework\Filesystem\Driver\Http
{

    /**
     * Get HttpDriver
     *
     * @param [mixed] $host
     * @param [int] $port
     * @return connection
     */
    public function getHttpDriver($host, $port)
    {
        try {
            $connection = $this->open($host, $port);
        } catch (\Exception $e) {
            return false;
        }

        return $connection;
    }
}
