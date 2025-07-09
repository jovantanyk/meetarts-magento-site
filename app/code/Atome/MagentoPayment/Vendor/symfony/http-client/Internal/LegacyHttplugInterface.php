<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Atome\MagentoPayment\Vendor\Symfony\Component\HttpClient\Internal;

use Atome\MagentoPayment\Vendor\Http\Client\HttpClient;
use Atome\MagentoPayment\Vendor\Http\Message\RequestFactory;
use Atome\MagentoPayment\Vendor\Http\Message\StreamFactory;
use Atome\MagentoPayment\Vendor\Http\Message\UriFactory;
if (\interface_exists(RequestFactory::class)) {
    /**
     * @internal
     *
     * @deprecated since Symfony 6.3
     */
    interface LegacyHttplugInterface extends HttpClient, RequestFactory, StreamFactory, UriFactory
    {
    }
} else {
    /**
     * @internal
     *
     * @deprecated since Symfony 6.3
     */
    interface LegacyHttplugInterface extends HttpClient
    {
    }
}
