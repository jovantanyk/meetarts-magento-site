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

namespace Webkul\MarketplaceEventManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul Marketplace Event Manager SalesOrderCreditmemoSaveAfterObserver Observer.
 */
class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * [$_coreSession description].
     *
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    protected $_memHelepr;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param SessionManager $coreSession
     * @param \Webkul\MarketplaceEventManager\Helper\Data $memHelper
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        SessionManager $coreSession,
        \Webkul\MarketplaceEventManager\Helper\Data $memHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_coreSession = $coreSession;
        $this->_memHelper = $memHelper;
        $this->productRepository = $productRepository;
        $this->_date = $date;
    }

    /**
     * Sales Order Creditmemo Save After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->_memHelper->isEventManagerEnable()) {
                $creditmemo = $observer->getCreditmemo();
                $order = $creditmemo->getOrder();
                foreach ($creditmemo->getAllItems() as $item) {
                    $product = $this->productRepository->getById($item->getProductId());
                    if ($product->getTypeId() == 'etickets') {
                        $this->saveTicket($product, $order);
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * SaveTicket
     *
     * @param object $product
     * @param object $order
     */
    public function saveTicket($product, $order)
    {
        foreach ($order->getAllItems() as $orderItem) {
            if ($item->getOrderItemId() != $orderItem->getItemId()) {
                continue;
            }
            $options = $product->getProductOptionsCollection();
            $productoptions = $orderItem->getProductOptions();
            foreach ($options as $option) {
                foreach ($option->getValues() as $opval) {
                    if ($opval['option_type_id'] ==
                        $productoptions['info_buyRequest']['options'][$opval->getOptionId()]
                        ) {
                        $opval->setQty((int)$opval->getQty()+(int)$item->getQty());
                        $opval->save();
                    }
                }
            }
        }
    }
}
