<?php

namespace Atome\MagentoPayment\Vendor\Http\Factory\Guzzle;

use Atome\MagentoPayment\Vendor\GuzzleHttp\Psr7\Request;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\RequestInterface;
class RequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, $uri) : RequestInterface
    {
        return new Request($method, $uri);
    }
}
