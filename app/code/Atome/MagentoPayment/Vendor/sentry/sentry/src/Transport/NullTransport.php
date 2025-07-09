<?php

declare (strict_types=1);
namespace Atome\MagentoPayment\Vendor\Sentry\Transport;

use Atome\MagentoPayment\Vendor\GuzzleHttp\Promise\FulfilledPromise;
use Atome\MagentoPayment\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Atome\MagentoPayment\Vendor\Sentry\Event;
use Atome\MagentoPayment\Vendor\Sentry\Response;
use Atome\MagentoPayment\Vendor\Sentry\ResponseStatus;
/**
 * This transport fakes the sending of events by just ignoring them.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
final class NullTransport implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(Event $event) : PromiseInterface
    {
        return new FulfilledPromise(new Response(ResponseStatus::skipped(), $event));
    }
    /**
     * {@inheritdoc}
     */
    public function close(?int $timeout = null) : PromiseInterface
    {
        return new FulfilledPromise(\true);
    }
}
