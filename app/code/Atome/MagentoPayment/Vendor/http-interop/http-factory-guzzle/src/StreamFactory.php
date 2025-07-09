<?php

namespace Atome\MagentoPayment\Vendor\Http\Factory\Guzzle;

use Atome\MagentoPayment\Vendor\GuzzleHttp\Psr7\Stream;
use Atome\MagentoPayment\Vendor\GuzzleHttp\Psr7\Utils;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\StreamInterface;
class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = '') : StreamInterface
    {
        return Utils::streamFor($content);
    }
    public function createStreamFromFile(string $file, string $mode = 'r') : StreamInterface
    {
        return $this->createStreamFromResource(Utils::tryFopen($file, $mode));
    }
    public function createStreamFromResource($resource) : StreamInterface
    {
        return new Stream($resource);
    }
}
