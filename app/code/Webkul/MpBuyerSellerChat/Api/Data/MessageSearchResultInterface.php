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
 * Interface message search results.
 * @api
 */
interface MessageSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get message list.
     *
     * @return \Webkul\MpBuyerSellerChat\Api\Data\MessageInterface[]
     */
    public function getItems();

    /**
     * Set message list.
     *
     * @param \Webkul\MpBuyerSellerChat\Api\Data\MessageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
