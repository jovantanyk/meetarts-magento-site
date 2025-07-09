<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceEventManager\Plugin\Block\Order;

use Webkul\MarketplaceEventManager\Helper\Data as EventHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\ResultFactory;

class View
{
    /**
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    protected $_eventHelper;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var UrlInterface
     */
    protected $_url;
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param EventHelper $eventHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param UrlInterface $url
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        EventHelper $eventHelper,
        \Magento\Framework\App\Request\Http $request,
        UrlInterface $url,
        ResultFactory $resultFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventHelper = $eventHelper;
        $this->_request = $request;
        $this->_url = $url;
        $this->_resultFactory = $resultFactory;
    }

    /**
     * AroundIsOrderCanShip
     *
     * @param object $subject
     * @param object $proceed
     * @param object $order
     */
    public function aroundIsOrderCanShip(
        \Webkul\Marketplace\Block\Order\View $subject,
        \Closure $proceed,
        $order
    ) {
        if ($order->canShip()) {
            $collection = $this->_objectManager->create(\Webkul\Marketplace\Model\Saleslist::class)
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $order->getId()]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $this->_eventHelper->getCustomerId()]
            );
            $flag = 0;
            foreach ($collection as $key => $value) {
                $productId = $value->getmageproductId();
                try {
                    $product = $this->_objectManager->create(
                        \Magento\Catalog\Api\ProductRepositoryInterface::class
                    )->getById($productId);
                    if ($product->getTypeId() != 'virtual' && $product->getTypeId() != 'etickets') {
                        $flag = 1;
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            if ($flag) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        $proceed($order);
    }
}
