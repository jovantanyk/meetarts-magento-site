<?php

declare (strict_types=1);
namespace Atome\MagentoPayment\Vendor\Http\Client\Common\Plugin;

use Atome\MagentoPayment\Vendor\Http\Client\Common\Plugin;
use Atome\MagentoPayment\Vendor\Http\Promise\Promise;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\RequestInterface;
/**
 * Removes headers from the request.
 *
 * @author Soufiane Ghzal <sghzal@gmail.com>
 */
final class HeaderRemovePlugin implements Plugin
{
    /**
     * @var array
     */
    private $headers = [];
    /**
     * @param array $headers List of header names to remove from the request
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first) : Promise
    {
        foreach ($this->headers as $header) {
            if ($request->hasHeader($header)) {
                $request = $request->withoutHeader($header);
            }
        }
        return $next($request);
    }
}
