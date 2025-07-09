<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftBuild\HitPay\Model;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;
use SoftBuild\HitPay\Services\Client;

class Pay extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'hitpay';
    
    protected $_isGateway = true;
    protected $_canAuthorize = false;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_isInitializeNeeded = true;
    protected $_canRefundInvoicePartial = true;
    
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     *
     * @var Magento\Checkout\Model\Session
     */
    protected $orderSession;
    
    /**
     *
     * @var SoftBuild\HitPay\Helper\Data
     */
    protected $hitpayHelper;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
    protected $directory_list;
    
    public function __construct(
        \SoftBuild\HitPay\Helper\Data $hitpayHelper,
        \Magento\Checkout\Model\Session $orderSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list, 
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Directory\Helper\Data $directory = null
    ) {
        $this->hitpayHelper = $hitpayHelper;
        $this->orderSession = $orderSession;
        $this->orderFactory = $orderFactory;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->directory_list = $directory_list;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return '';
    }
    
    public function getConfigValue($key)
    {
        $pathConfig = 'payment/' . $this->_code . "/" . $key;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue($pathConfig, $storeScope);
    }
    
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $isAvailable = parent::isAvailable($quote);
        if ($isAvailable) {
            $api_key = $this->getConfigValue('api_key', $quote ? $quote->getStoreId() : null);
            $salt = $this->getConfigValue('salt', $quote ? $quote->getStoreId() : null);
            
            if (empty($api_key) || empty($salt)) {
                $isAvailable = false;
            }
        }
        return $isAvailable;
    }
    
    public function getTitle()
    {
        $title = $this->getConfigData('title');
        $title = trim($title);
        if (empty($title)) {
            $title = 'HitPay Payment Gateway';
        }
        return $title;
    }
    
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }
    
    public function isInitializeNeeded()
    {
        return true;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $state = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);
    }
    
    public function getCheckout()
    {
        return $this->orderSession;
    }
    
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
    
    public function getOrder()
    {
        return $this->getCheckout()->getLastRealOrder();
    }

    public function getCheckoutRedirectUrl($params = []) 
    {
        return $this->urlBuilder->getUrl('hitpay/pay/create', $params);
    }

    public function getWebhookUrl($params = [])
    {
        return $this->urlBuilder->getUrl('hitpay/pay/webhook', $params);
    }
    
    public function getReturnUrl($params = [])
    {
        return $this->urlBuilder->getUrl('hitpay/pay/confirmation', $params);
    }
    
    public function getAwaitingUrl($params = [])
    {
        return $this->urlBuilder->getUrl('hitpay/pay/awaiting', $params);
    }
    
    public function getStatusUrl($params = [])
    {
        return $this->urlBuilder->getUrl('hitpay/pay/status', $params);
    }
    
    public function getCheckoutCartUrl($params = [])
    {
        return $this->urlBuilder->getUrl('checkout/cart', $params);
    }
    
    public function getCheckoutSuccessUrl($params = [])
    {
        return $this->urlBuilder->getUrl('checkout/onepage/success', $params);
    }
    
    public function getStoreUrl()
    {
        return $this->urlBuilder->getUrl();
    }
    
    public function getStoreName()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeName = $storeManager->getStore()->getName();
        if (empty($storeName)) {
            $storeName = $storeManager->getStore()->getStoreUrl();
        }
        return $storeName;
    }
    
    public function log($message)
    {
        $debug = true;
        if ($debug) {
            file_put_contents($this->directory_list->getPath('log') . '/hitpay.log', date("Y-m-d H:i:s").": ", FILE_APPEND);
            file_put_contents($this->directory_list->getPath('log') . '/hitpay.log', print_r($message, true), FILE_APPEND);
            file_put_contents($this->directory_list->getPath('log') . '/hitpay.log', "\n", FILE_APPEND);
        }
    }
    
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $order = $payment->getOrder();
        if ($order && $order->getId()) {
            $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
            if ($paymentMethod == 'hitpay') {
                $payment_id = $payment->getRefundTransactionId();
  
                if (!empty($payment_id) && $amount > 0) {
                    
                    try {
                        $client = new Client(
                            $this->getConfigValue("api_key"),
                            $this->getConfigValue("mode")
                        );
 
                        $refund_request_param = 'Order Id: '.$order->getId().', Payment Id: '.$payment_id.', Amount: '.$amount;

                        $this->log('Refund Payment Request:');
                        $this->log($refund_request_param);

                        $result = $client->refund($payment_id, $amount);

                        $this->log('Refund Payment Response:');
                        $this->log((array)$result);

                        $message = __('Refund successful. Refund Reference Id: '.$result->getId().', '
                                . 'Payment Id: '.$payment_id.', Amount Refunded: '.$result->getAmountRefunded().', '
                                . 'Payment Method: '.$result->getPaymentMethod().', Created At: '.$result->getCreatedAt());
                        $order->addStatusHistoryComment($message);
                        $order->save();
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                     }
                }
            }
        }
        return $this;
    }
}
