<?php

declare (strict_types=1);
namespace Atome\MagentoPayment\Vendor\Sentry\Transport;

use Atome\MagentoPayment\Vendor\GuzzleHttp\Promise\FulfilledPromise;
use Atome\MagentoPayment\Vendor\GuzzleHttp\Promise\PromiseInterface;
use Atome\MagentoPayment\Vendor\GuzzleHttp\Promise\RejectedPromise;
use Atome\MagentoPayment\Vendor\Http\Client\HttpAsyncClient as HttpAsyncClientInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\ResponseInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Log\LoggerInterface;
use Atome\MagentoPayment\Vendor\Psr\Log\NullLogger;
use Atome\MagentoPayment\Vendor\Sentry\Event;
use Atome\MagentoPayment\Vendor\Sentry\EventType;
use Atome\MagentoPayment\Vendor\Sentry\Options;
use Atome\MagentoPayment\Vendor\Sentry\Response;
use Atome\MagentoPayment\Vendor\Sentry\ResponseStatus;
use Atome\MagentoPayment\Vendor\Sentry\Serializer\PayloadSerializerInterface;
/**
 * This transport sends the events using a syncronous HTTP client that will
 * delay sending of the requests until the shutdown of the application.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
final class HttpTransport implements TransportInterface
{
    /**
     * @var Options The Sentry client options
     */
    private $options;
    /**
     * @var HttpAsyncClientInterface The HTTP client
     */
    private $httpClient;
    /**
     * @var StreamFactoryInterface The PSR-7 stream factory
     */
    private $streamFactory;
    /**
     * @var RequestFactoryInterface The PSR-7 request factory
     */
    private $requestFactory;
    /**
     * @var PayloadSerializerInterface The event serializer
     */
    private $payloadSerializer;
    /**
     * @var LoggerInterface A PSR-3 logger
     */
    private $logger;
    /**
     * @var RateLimiter The rate limiter
     */
    private $rateLimiter;
    /**
     * Constructor.
     *
     * @param Options                    $options           The Sentry client configuration
     * @param HttpAsyncClientInterface   $httpClient        The HTTP client
     * @param StreamFactoryInterface     $streamFactory     The PSR-7 stream factory
     * @param RequestFactoryInterface    $requestFactory    The PSR-7 request factory
     * @param PayloadSerializerInterface $payloadSerializer The event serializer
     * @param LoggerInterface|null       $logger            An instance of a PSR-3 logger
     */
    public function __construct(Options $options, HttpAsyncClientInterface $httpClient, StreamFactoryInterface $streamFactory, RequestFactoryInterface $requestFactory, PayloadSerializerInterface $payloadSerializer, ?LoggerInterface $logger = null)
    {
        $this->options = $options;
        $this->httpClient = $httpClient;
        $this->streamFactory = $streamFactory;
        $this->requestFactory = $requestFactory;
        $this->payloadSerializer = $payloadSerializer;
        $this->logger = $logger ?? new NullLogger();
        $this->rateLimiter = new RateLimiter($this->logger);
    }
    /**
     * {@inheritdoc}
     */
    public function send(Event $event) : PromiseInterface
    {
        $dsn = $this->options->getDsn();
        if (null === $dsn) {
            throw new \RuntimeException(\sprintf('The DSN option must be set to use the "%s" transport.', self::class));
        }
        $eventType = $event->getType();
        if ($this->rateLimiter->isRateLimited($eventType)) {
            $this->logger->warning(\sprintf('Rate limit exceeded for sending requests of type "%s".', (string) $eventType), ['event' => $event]);
            return new RejectedPromise(new Response(ResponseStatus::rateLimit(), $event));
        }
        if ($this->options->isTracingEnabled() || EventType::transaction() === $eventType || EventType::checkIn() === $eventType) {
            $request = $this->requestFactory->createRequest('POST', $dsn->getEnvelopeApiEndpointUrl())->withHeader('Content-Type', 'application/x-sentry-envelope')->withBody($this->streamFactory->createStream($this->payloadSerializer->serialize($event)));
        } else {
            $request = $this->requestFactory->createRequest('POST', $dsn->getStoreApiEndpointUrl())->withHeader('Content-Type', 'application/json')->withBody($this->streamFactory->createStream($this->payloadSerializer->serialize($event)));
        }
        try {
            /** @var ResponseInterface $response */
            $response = $this->httpClient->sendAsyncRequest($request)->wait();
        } catch (\Throwable $exception) {
            $this->logger->error(\sprintf('Failed to send the event to Sentry. Reason: "%s".', $exception->getMessage()), ['exception' => $exception, 'event' => $event]);
            return new RejectedPromise(new Response(ResponseStatus::failed(), $event));
        }
        $sendResponse = $this->rateLimiter->handleResponse($event, $response);
        if (ResponseStatus::success() === $sendResponse->getStatus()) {
            return new FulfilledPromise($sendResponse);
        }
        return new RejectedPromise($sendResponse);
    }
    /**
     * {@inheritdoc}
     */
    public function close(?int $timeout = null) : PromiseInterface
    {
        return new FulfilledPromise(\true);
    }
}
