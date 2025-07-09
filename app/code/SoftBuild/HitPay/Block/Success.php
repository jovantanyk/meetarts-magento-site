<?php

namespace SoftBuild\HitPay\Block;

class Success extends \Magento\Checkout\Block\Success
{
    protected $helper;
    protected $payment;
    protected $orderFactory;
    protected $request;

    public function __construct(
        \SoftBuild\HitPay\Helper\Data $helper,
        \SoftBuild\HitPay\Model\Pay $payment,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $orderFactory, $data);
        $this->helper = $helper;
        $this->payment = $payment;
        $this->orderFactory = $orderFactory;
        $this->request = $request;
    }
    
    public function getRealOrder()
    {
        $order_id = $this->request->getParam('order_id');
        return $this->orderFactory->create()->loadByIncrementId($order_id);
    }
    
    public function getTemplateParams()
    {
        $order = $this->getRealOrder();
        if ($order && $order->getId()) {
            $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
            if ($paymentMethod == 'hitpay') {
                $params['order_id'] = $order->getId();
                $params['payment_id'] = $this->helper->getPaymentResponseSingle($order->getId(), 'payment_id');
                $params['status_url'] = $this->payment->getStatusUrl();
                return $params;
            }
        }
        return false;
    }
}
