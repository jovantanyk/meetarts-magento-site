<?php

declare (strict_types=1);
namespace Atome\MagentoPayment\Vendor\Http\Client\Common;

use Atome\MagentoPayment\Vendor\Psr\Http\Client\ClientInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\RequestInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\ResponseInterface;
/**
 * Decorates an HTTP Client.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
trait HttpClientDecorator
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    /**
     * {@inheritdoc}
     *
     * @see ClientInterface::sendRequest
     */
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }
}
