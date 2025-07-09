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
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul MarketplaceEventManager CheckoutCartSaveBefore Observer.
 */
class CheckoutCartSaveBeforeObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Date
     */
    protected $_date;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Option\Value $productOptionValues
     */
    protected $_productOptionValues;

    /**
     * @param \Webkul\MarketplaceEventManager\Helper\Data $dataHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\Product\Option\Value $productOptionValues
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        \Webkul\MarketplaceEventManager\Helper\Data $dataHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\Product\Option\Value $productOptionValues,
        \Magento\Quote\Model\Quote\Item $item,
        ResultFactory $resultFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;
        $this->productRepository = $productRepository;
        $this->_date = $date;
        $this->_productOptionValues = $productOptionValues;
        $this->resultFactory = $resultFactory;
        $this->_item = $item;
    }

    /**
     * Cart save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            if ($this->_dataHelper->isEventManagerEnable()) {
                $items =  $this->_checkoutSession->getQuote()->getAllVisibleItems();
                foreach ($items as $item) {
                    $productId = $item->getProductId();
                    $product = $this->productRepository->getById($productId);
                    if ($product->getIsMpEvent()) {
                        // check if event dates are expired
                        $eventStartDate = $product->getEventStartDate();
                        $eventEndDate = $product->getEventEndDate();
                        $expiredEventStatus = $this->_dataHelper->getEventExpiredStatus(
                            $eventStartDate,
                            $eventEndDate
                        );
                        if ($expiredEventStatus) {
                            $this->_messageManager->addNotice(__('Event has been expired.'));
                            $item->delete();
                        } else {
                            $options = $product->getOptions();
                            $cartoption = $item->getProduct()
                                            ->getTypeInstance(true)
                                            ->getOrderOptions($item->getProduct());
                            $this->eventInfo($options, $cartoption, $item);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    /**
     * EventInfo
     *
     * @param array $options
     * @param array $cartoption
     * @param array $item
     */
    public function eventInfo($options, $cartoption, $item)
    {
        foreach ($options as $option) {
            $optionValuesCollection = $this->_productOptionValues
                ->getCollection()
                ->addFieldToFilter('option_id', $option->getOptionId());
            foreach ($optionValuesCollection as $optval) {
                if (isset($cartoption['info_buyRequest'])) {
                    if ($cartoption['info_buyRequest']['options'][$optval->getOptionId()] == $optval->getOptionTypeId()
                        ) {
                        if ($optval->getQty() > 0 && $optval->getQty() < $item->getQty()) {
                            $item->setQty($optval->getQty());
                            $item->save();
                            $this->_messageManager->addError(
                                __(
                                    'Requested quantity is not available for selected 
                                    ticket type. Only %1 quantity is available for selected ticket type.',
                                    $optval->getQty()
                                )
                            );
                        }
                    }
                }
            }
        }
    }
}
