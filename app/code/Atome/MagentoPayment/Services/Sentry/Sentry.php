<?php

namespace Atome\MagentoPayment\Services\Sentry;


use Atome\Config\Config;
use Atome\MagentoPayment\Services\Config\Atome;
use Atome\MagentoPayment\Services\Config\PaymentGatewayConfig;
use Magento\Framework\App\ObjectManager;

require_once __DIR__ . '/../../Vendor/autoload.php';

class Sentry
{

    /**
     * @var Sentry
     */
    static protected $instance;

    protected $paymentGatewayConfig;

    public function __construct(PaymentGatewayConfig $paymentGatewayConfig)
    {
        $this->paymentGatewayConfig = $paymentGatewayConfig;
    }


    public static function capture($exception)
    {
        if (!isset(static::$instance)) {
            static::$instance = ObjectManager::getInstance()->create(static::class);
            static::$instance->bootstrap();
        }

        self::$instance->sentryCapture($exception);
    }


    public function sentryCapture($exception)
    {
        if ($this->paymentGatewayConfig->getEnableSendErrors()) {
            \Atome\MagentoPayment\Vendor\Sentry\captureException($exception);
        }
    }


    public function bootstrap()
    {
        \Atome\MagentoPayment\Vendor\Sentry\init(
            [
                'dsn' => Atome::SENTRY_DSN,
                'release' => Atome::version(),
                'sample_rate' => 0.1,
                'server_name' => $_SERVER['SERVER_NAME'],
                'default_integrations' => false,
            ]
        );

        \Atome\MagentoPayment\Vendor\Sentry\configureScope(function ($scope): void {
            $scope->setTag('atome.platform', Atome::PLATFORM);
        });
    }

}
