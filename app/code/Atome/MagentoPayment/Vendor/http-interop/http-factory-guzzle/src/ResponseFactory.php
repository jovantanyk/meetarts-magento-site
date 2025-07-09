<?php

namespace Atome\MagentoPayment\Vendor\Http\Factory\Guzzle;

use Atome\MagentoPayment\Vendor\GuzzleHttp\Psr7\Response;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\ResponseFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\ResponseInterface;
class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface
    {
        return new Response($code, [], null, '1.1', $reasonPhrase);
    }
}
