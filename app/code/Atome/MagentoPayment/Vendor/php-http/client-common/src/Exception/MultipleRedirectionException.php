<?php

declare (strict_types=1);
namespace Atome\MagentoPayment\Vendor\Http\Client\Common\Exception;

use Atome\MagentoPayment\Vendor\Http\Client\Exception\HttpException;
/**
 * Redirect location cannot be chosen.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class MultipleRedirectionException extends HttpException
{
}
