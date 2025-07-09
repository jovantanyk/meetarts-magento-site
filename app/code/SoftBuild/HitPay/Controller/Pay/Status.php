<?php
namespace SoftBuild\HitPay\Controller\Pay;

class Status extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $payment;
    protected $orderFactory;

    public function __construct(
        \SoftBuild\HitPay\Helper\Data $helper,
        \SoftBuild\HitPay\Model\Pay $payment,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->helper = $helper;
        $this->payment = $payment;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
 
        $status = 'wait';
        $redirect = '';
        $message = '';
        
        try {
            $params = $this->getRequest()->getParams();
            
            $model = $this->_objectManager->get('SoftBuild\HitPay\Model\Pay');
            $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
            
            $payment_id = $params['payment_id'];
            $payment_id = trim($payment_id);
            $payment_id = strip_tags($payment_id);
            
            if (empty($payment_id)) {
                throw new \Exception(__('No payment id found.'));
            }

            $order_id = (int)$params['order_id'];
            $order = $this->orderFactory->create()->load($order_id);
            
            if ((int)$order->getId() == 0) {
                throw new \Exception(__('This order is not found.'));
            }
            
            $status = $this->helper->getPaymentResponseSingle($order_id, 'status');

            if ($status == 'pending') {
                $status = 'wait';
            } else if ($status == 'completed') {
                $orderState = $model->getConfigValue('new_order_status');
                if (empty($orderState)) {
                    $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                }

                $orderStatus = $order->getStatus();
                if ($orderStatus == $orderState) {
                    $status = 'completed';
                } else {
                    $status = $orderStatus;
                }
                $redirect = $model->getCheckoutSuccessUrl();
            } else if ($status == 'failed') {
                //$session->restoreQuote();
                $redirect = $model->getCheckoutCartUrl();
            }
        } catch (\Exception $e) {
            $status = 'error';
            $message = $e->getMessage();
        }

        $response = [
            'status' => $status,
            'redirect' => $redirect,
            'message' => $message
        ];
        
        echo json_encode($response);
        exit;
    }
}
