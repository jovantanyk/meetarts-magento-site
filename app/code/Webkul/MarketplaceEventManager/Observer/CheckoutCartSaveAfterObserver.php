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

/**
 * Webkul MarketplaceEventManager CheckoutCartSaveBefore Observer.
 */
class CheckoutCartSaveAfterObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\Product\Option\Value $productOptionValues
     */
    protected $_productOptionValues;

    /**
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Catalog\Model\Product\Option\Value $productOptionValues
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param ResultFactory $resultFactory
     * @param \Webkul\MarketplaceEventManager\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Catalog\Model\Product\Option\Value $productOptionValues,
        \Magento\Quote\Model\Quote\Item $item,
        ResultFactory $resultFactory,
        \Webkul\MarketplaceEventManager\Helper\Data $dataHelper
    ) {
        $this->messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;
        $this->_product = $product;
        $this->_productOptionValues = $productOptionValues;
        $this->resultFactory = $resultFactory;
        $this->_item = $item;
        $this->_dataHelper = $dataHelper;
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
            $paramData = $this->_request->getParams();
            if (isset($paramData['product'])) {
                $product = $this->_product->create()->load($paramData['product']);
                if ($product->getIsMpEvent()) {
                    // check if event dates are expired
                    $eventStartDate = $product->getEventStartDate();
                    $eventEndDate = $product->getEventEndDate();
                    $expiredEventStatus = $this->_dataHelper->getEventExpiredStatus(
                        $eventStartDate,
                        $eventEndDate
                    );
                    if ($expiredEventStatus) {
                        $this->deleteQuoteItems($paramData['product']);
                        $this->messageManager->addError(__('Event has been expired.'));
                        return $resultRedirect->setPath('checkout/cart');
                    } else {
                        $options = $product->getOptions();
                        $this->setOptions($options);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * SetOptions
     *
     * @param object $options
     */
    public function setOptions($options)
    {
        foreach ($options as $option) {
            $optionValuesCollection = $this->_productOptionValues
                ->getCollection()
                ->addFieldToFilter('option_id', $option->getOptionId());
            foreach ($optionValuesCollection as $optval) {
                if (isset($paramData['options'])) {
                    if ($paramData['options'][$optval->getOptionId()] == $optval->getOptionTypeId()) {
                        if ($optval->getQty() < $paramData['qty']) {
                            $this->deleteQuoteItems($paramData['product']);
                            $msg = 'Event ticket '.strtoupper($optval->getTitle()).' is out of stock';
                            $this->messageManager->addError(__($msg));
                            return $resultRedirect->setPath('checkout/cart');
                        }
                    }
                }
            }
        }
    }
    
    /**
     * DeleteQuoteItems
     *
     * @param int $id
     */
    public function deleteQuoteItems($id)
    {
        $allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($allItems as $item) {
            $itemId = $item->getItemId();
            if ($id == $item->getProductId()) {
                $quoteItem = $this->_item->load($itemId);
                $quoteItem->delete();
            }
        }
    }
}
