<?php
namespace SoftBuild\HitPay\Plugin;
class OrderIdentityPlugin
{
    protected $checkoutSession;
    protected $orderRepository;
    protected $helper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \SoftBuild\HitPay\Helper\Data $helper
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
    }
 
    /**
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsEnabled(\Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject, callable $proceed)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->get('SoftBuild\HitPay\Model\Pay');
        $returnValue = $proceed();
        $quoteId = $this->checkoutSession->getHitpayUsingQuoteId();
        $lastOrderId = (int)$this->helper->getOrderIdByQuoteId($quoteId);
        if ($lastOrderId > 0) {
            $order = $this->orderRepository->get($lastOrderId);
            if ($order && $order->getId()) {
                $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
                if ($paymentMethod == 'hitpay') {
                    $state = $order->getState();
                    if (!($state == \Magento\Sales\Model\Order::STATE_PROCESSING || $state == \Magento\Sales\Model\Order::STATE_NEW)) {
                        $returnValue = false;
                    }
                }
            }
        }

        return $returnValue;
    }
}