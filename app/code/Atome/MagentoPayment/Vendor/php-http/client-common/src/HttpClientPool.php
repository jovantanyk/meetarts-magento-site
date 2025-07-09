<?php

declare (strict_types=1);
namespace Atome\MagentoPayment\Vendor\Http\Client\Common;

use Atome\MagentoPayment\Vendor\Http\Client\Common\HttpClientPool\HttpClientPoolItem;
use Atome\MagentoPayment\Vendor\Http\Client\HttpAsyncClient;
use Atome\MagentoPayment\Vendor\Http\Client\HttpClient;
use Atome\MagentoPayment\Vendor\Psr\Http\Client\ClientInterface;
/**
 * A http client pool allows to send requests on a pool of different http client using a specific strategy (least used,
 * round robin, ...).
 */
interface HttpClientPool extends HttpAsyncClient, HttpClient
{
    /**
     * Add a client to the pool.
     *
     * @param ClientInterface|HttpAsyncClient|HttpClientPoolItem $client
     */
    public function addHttpClient($client) : void;
}
