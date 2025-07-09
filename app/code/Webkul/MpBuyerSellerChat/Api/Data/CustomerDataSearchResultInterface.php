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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface customer data search results.
 * @api
 */
interface CustomerDataSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get customer data list.
     *
     * @return \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface[]
     */
    public function getItems();

    /**
     * Set customer data list.
     *
     * @param \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
