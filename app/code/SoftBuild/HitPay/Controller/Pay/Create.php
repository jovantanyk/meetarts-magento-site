<?php
namespace SoftBuild\HitPay\Controller\Pay;

use SoftBuild\HitPay\Services\Client;
use SoftBuild\HitPay\Services\Request\CreatePayment;

class Create extends \Magento\Framework\App\Action\Action
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
        try {
            $model = $this->_objectManager->get('SoftBuild\HitPay\Model\Pay');
            $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
            $order = $model->getOrder();
            if ($order && $order->getId() > 0) {
                
                $client = new Client(
                    $model->getConfigValue("api_key"),
                    $model->getConfigValue("mode")
                );
                
                $redirectUrl = $model->getReturnUrl(array('order_id' => $order->getIncrementId()) );
                $webhook = $model->getWebhookUrl(array('order_id' => $order->getIncrementId()) );
                
                $createPaymentRequest = new CreatePayment();
                $createPaymentRequest->setAmount(number_format($order->getGrandTotal(), 2, '.', ''))
                    ->setCurrency($order->getOrderCurrencyCode())
                    ->setReferenceNumber($order->getIncrementId())
                    ->setWebhook($webhook)
                    ->setRedirectUrl($redirectUrl)
                    ->setChannel('api_magento');
                
                $createPaymentRequest->setName($order->getCustomerFirstname() . ' ' . $order->getCustomerLastname());
                $createPaymentRequest->setEmail($order->getCustomerEmail());
                
                $createPaymentRequest->setPurpose($model->getStoreName());
                
                $model->log('Create Payment Request:');
                $model->log((array)$createPaymentRequest);

                $result = $client->createPayment($createPaymentRequest);
                
                $model->log('Create Payment Response:');
                $model->log((array)$result);
                
                $savePayment = [
                    'payment_id' => $result->getId(),
                    'amount' => number_format($order->getGrandTotal(), 2, '.', ''),
                    'currency_id' => $order->getOrderCurrencyCode(),
                    'status' => $result->getStatus(),
                    'increment_id' => $order->getIncrementId(),
                ];
                $this->helper->addPaymentResponse($order->getId(), json_encode($savePayment));
                                
                if ($result->getStatus() == 'pending') {
                    echo '<script>window.top.location.href = "'.$result->getUrl().'";</script>';
                } else {
                    throw new \Exception(sprintf(__('Status from gateway is %s .'), $result->getStatus()));
                }
            } else {
                echo '<script>window.top.location.href = "'.$model->getCheckoutCartUrl().'";</script>';
            }
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
            $message = __('HitPay Create Payment Request failed. '.$error_message);
            if ($order && $order->getId() > 0) {
                $order->cancel();
                $order->addStatusHistoryComment($message, \Magento\Sales\Model\Order::STATE_CANCELED);
                $order->save();
                $session->restoreQuote();
            }
            $this->messageManager->addError($message);
            echo '<script>window.top.location.href = "'.$model->getCheckoutCartUrl().'";</script>';
        }
        exit;
    }
}
