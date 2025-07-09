<?php

namespace Atome\MagentoPayment\Vendor\Http\Factory\Guzzle;

use Atome\MagentoPayment\Vendor\GuzzleHttp\Psr7\Uri;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\UriFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\UriInterface;
class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = '') : UriInterface
    {
        return new Uri($uri);
    }
}
