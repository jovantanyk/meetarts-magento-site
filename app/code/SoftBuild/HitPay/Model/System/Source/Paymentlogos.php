<?php

namespace SoftBuild\HitPay\Model\System\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Mode
 */
class Paymentlogos implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'visa',
                'label' => __('Visa')
           ],
            [
                'value' => 'master',
                'label' => __('Mastercard')
           ],
            [
                'value' => 'american_express',
                'label' => __('American Express')
           ],
            [
                'value' => 'apple_pay',
                'label' => __('Apple Pay')
           ],
            [
                'value' => 'google_pay',
                'label' => __('Google Pay')
           ],
            [
                'value' => 'paynow',
                'label' => __('PayNow QR')
           ],
            [
                'value' => 'grabpay',
                'label' => __('GrabPay')
           ],
            [
                'value' => 'wechatpay',
                'label' => __('WeChatPay')
           ],
            [
                'value' => 'alipay',
                'label' => __('AliPay')
           ],
            [
                'value' => 'shopeepay',
                'label' => __('Shopee Pay')
           ],
            [
                'value' => 'fpx',
                'label' => __('FPX')
           ],
            [
                'value' => 'zip',
                'label' => __('Zip')
           ],
            [
                'value' => 'atomeplus',
                'label' => __('ATome+')
            ],
            [
                'value' => 'unionbank',
                'label' => __('Unionbank Online')
            ],
            [
                'value' => 'qrph',
                'label' => __('Instapay QR PH')
            ],
            [
                'value' => 'pesonet',
                'label' => __('PESONet')
            ],
            [
                'value' => 'gcash',
                'label' => __('GCash')
            ],
            [
                'value' => 'billease',
                'label' => __('Billease BNPL')
            ],
        ];
    }
}
