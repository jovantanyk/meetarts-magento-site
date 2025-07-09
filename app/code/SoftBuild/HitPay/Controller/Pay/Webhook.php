<?php
namespace SoftBuild\HitPay\Controller\Pay;

use SoftBuild\HitPay\Services\Client;

class Webhook extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $payment;
    protected $orderFactory;
    protected $orderSender;

    public function __construct(
        \SoftBuild\HitPay\Helper\Data $helper,
        \SoftBuild\HitPay\Model\Pay $payment,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->helper = $helper;
        $this->payment = $payment;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        
        $model = $this->_objectManager->get('SoftBuild\HitPay\Model\Pay');
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        
        $model->log('Webhook Triggered:');
        $model->log(json_encode($params));
        
        if (!isset($params['order_id']) || !$params['hmac']) {
            $model->log('order_id + hmac check failed');
            exit;
        }

        if (isset($params['order_id']) && !empty($params['order_id'])) {
            $order_id = trim($params['order_id']);
            $order = $this->orderFactory->create()->loadByIncrementId($order_id);
            if ($order && $order->getId() > 0) {
                $orderEntityId = $order->getId();
                $webhookTriggered = (int)$this->helper->isWebhookTriggered($order->getId());
                if ($webhookTriggered == 1) {
                    $model->log('Alredy Webhook Triggered, so skipped.');
                    exit;
                }

                $this->helper->addWebhookTriggered($order->getId());
                
                $HitPay_payment_id = $this->helper->getPaymentResponseSingle($orderEntityId, 'payment_id');
                if (!$HitPay_payment_id || empty($HitPay_payment_id)) {
                    $model->log('Saved payment not valid');
                    exit;
                }

                try {
                    $data = $_POST;
                    unset($data['hmac']);

                    $salt = $model->getConfigValue("salt");
                    if (Client::generateSignatureArray($salt, $data) == $params['hmac']) {
                        $model->log('Hmac check passed');

                        $HitPay_is_paid = $this->helper->getPaymentResponseSingle($orderEntityId, 'is_paid');

                        if (!$HitPay_is_paid) {
                            $status = trim($params['status']);
                            $status = strip_tags($params['status']);

                            if ($status == 'completed'
                                && number_format($order->getGrandTotal(), 2, '.', '') == $params['amount']
                                && $order_id == $params['reference_number']
                                && $order->getOrderCurrencyCode() == $params['currency']
                            ) {
                                $model->log('status is completed and passed other conditions').
                                $payment_id = $params['payment_id'];
                                $payment_request_id = $params['payment_request_id'];
                                $hitpay_currency = $params['currency'];
                                $hitpay_amount = $params['amount'];
                                
                                $orderState = $model->getConfigValue('new_order_status');
                                if (empty($orderState)) {
                                    $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                                }
                                $orderStatus = $orderState;
                                
                                $order->setState($orderState);
                                $order->setStatus($orderStatus);
                                $order->setTotalPaid($order->getGrandTotal());
                                $comment = __('HitPay payment is successful. '). __('Transaction ID: '). $payment_id;
                                $order->addStatusHistoryComment($comment, $orderStatus, true);
                                $order->save();
                                
                                $this->orderSender->send($order, true);
                                
                                $this->helper->updatePaymentData($orderEntityId, 'transaction_id', $payment_id);
                                $this->helper->updatePaymentData($orderEntityId, 'payment_request_id', $payment_request_id);
                                $this->helper->updatePaymentData($orderEntityId, 'is_paid', 1);
                                $this->helper->updatePaymentData($orderEntityId, 'status', $status);
                                
                                if ($model->getConfigValue("auto_invoice")) {
                                    $this->createInvoice($order, $payment_id);
                                }

                            } elseif ($status == 'failed') {
                                $payment_id = $params['payment_id'];
                                $hitpay_currency = $params['currency'];
                                $hitpay_amount = $params['amount'];
                                
                                $comment =  __('HitPay payment is failed. Transaction Id: '.$payment_id);
                                $order->addStatusHistoryComment($comment, \Magento\Sales\Model\Order::STATE_CANCELED);
                                $order->save();
                                
                                $this->helper->updatePaymentData($orderEntityId, 'transaction_id', $payment_id);
                                $this->helper->updatePaymentData($orderEntityId, 'is_paid', 2);
                                $this->helper->updatePaymentData($orderEntityId, 'status', $status);

                            } elseif ($status == 'pending') {
                                $payment_id = $params['payment_id'];
                                $hitpay_currency = $params['currency'];
                                $hitpay_amount = $params['amount'];
                                
                                $status = 'completed';
                                
                                $comment =  __('HitPay payment is pending. Transaction Id: '.$payment_id);
                                $order->addStatusHistoryComment($comment, \Magento\Sales\Model\Order::STATE_NEW, true);
                                $order->save();
                                
                                $this->orderSender->send($order, true);

                                $this->helper->updatePaymentData($orderEntityId, 'transaction_id', $payment_id);
                                $this->helper->updatePaymentData($orderEntityId, 'is_paid', 2);
                                $this->helper->updatePaymentData($orderEntityId, 'status', $status);
                            } else {
                                $payment_id = $params['payment_id'];
                                $hitpay_currency = $params['currency'];
                                $hitpay_amount = $params['amount'];

                                $comment =  __('HitPay payment returned unknown status. Transaction Id: '.$payment_id);
                                $order->addStatusHistoryComment($comment, \Magento\Sales\Model\Order::STATE_CANCELED);
                                $order->save();
                                
                                $this->helper->updatePaymentData($orderEntityId, 'transaction_id', $payment_id);
                                $this->helper->updatePaymentData($orderEntityId, 'is_paid', 2);
                                $this->helper->updatePaymentData($orderEntityId, 'status', $status);
                            }
                        }
                    } else {
                        throw new \Exception('HitPay: hmac is not the same like generated');
                    }
                } catch (\Exception $e) {
                    $model->log('Webhook Catch');
                    $model->log('Exception:'.$e->getMessage());

                    $status = 'failed';
                    
                    $comment =  __('Payment failed. Error: '.$e->getMessage());
                    $order->addStatusHistoryComment($comment, \Magento\Sales\Model\Order::STATE_CANCELED);
                    $order->save();

                    $this->helper->updatePaymentData($orderEntityId, 'is_paid', 2);
                    $this->helper->updatePaymentData($orderEntityId, 'status', $status);
                }
            } else {
                $model->log('$order && $order->getId() > 0');
            }
        } else {
            $model->log('else isset($params[order_id]) && !empty($params[order_id])');
        }
        exit;
    }
    
    public function createInvoice($order, $payment_id)
    {
        if(!$order->canInvoice()) {
            throw new \Exception(__('Cannot create an invoice.'));
        }
        
        $invoiceService = $this->_objectManager->get('Magento\Sales\Model\Service\InvoiceService');

        $invoice = $invoiceService->prepareInvoice($order);

        if (!$invoice->getTotalQty()) {
            throw new \Exception(__('Cannot create an invoice without products.'));
        }

        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->setTransactionId($payment_id);
        $invoice->save();
        
        $transaction  = $this->_objectManager->get('Magento\Framework\DB\Transaction');
        $transactionSave = $transaction->addObject($invoice);
        $transaction->addObject($invoice->getOrder());
        $transactionSave->save();
    }
}
