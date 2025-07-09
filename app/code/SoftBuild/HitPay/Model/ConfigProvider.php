<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftBuild\HitPay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use SoftBuild\HitPay\Model\System\Source\Paymentlogos;
use Magento\Framework\View\Asset\Repository;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        'hitpay'
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;
    
    protected $paymentlogos;
    
    protected $assetRepo;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Paymentlogos $paymentlogos,
        Repository $assetRepo
    ) {
        $this->escaper = $escaper;
        $this->paymentlogos = $paymentlogos;
        $this->assetRepo = $assetRepo;
        
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['instructions'][$code] = $this->getInstructions($code);
                $config['payment'][$code]['redirectUrl'] = $this->methods[$code]->getCheckoutRedirectUrl();
                $config['payment'][$code]['images'] = $this->getLogos($code);
                $config['payment'][$code]['status'] = $this->getLogosStatus($code);
            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }
    
    public function getLogos($code)
    {
        $pngs = [
            'pesonet',
        ];
        $images = [];
        foreach ($this->methodCodes as $code) {
            $enabledLogos = $this->methods[$code]->getConfigValue('paymentlogos');
            if (!empty($enabledLogos)) {
                $enabledLogos = explode(',', $enabledLogos);
                foreach ($enabledLogos as $logoCode) {
                    $extn = 'svg';
                    if (in_array($logoCode, $pngs)) {
                        $extn = 'png';
                    }
                    $images[$logoCode] = $this->assetRepo->getUrl('SoftBuild_HitPay::images/'.$logoCode.'.'.$extn);
                }
            }
        }
        return $images;
    }
    
    public function getLogosStatus($code)
    {
        $status = [];
        foreach ($this->methodCodes as $code) {
            $enabledLogos = $this->methods[$code]->getConfigValue('paymentlogos');
            if (!empty($enabledLogos)) {
                $enabledLogos = explode(',', $enabledLogos);
            } else {
                $enabledLogos = [];
            }
            $logos = $this->paymentlogos->toOptionArray();
            foreach ($logos as $logo) {
                $logoCode = $logo['value'];
                $status[$logoCode] = (int)(in_array($logoCode, $enabledLogos));
            }
        }
        return $status;
    }
}
