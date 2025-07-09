<?php

namespace Atome\MagentoPayment\Vendor\Http\Discovery\Strategy;

use Atome\MagentoPayment\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\ResponseFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\ServerRequestFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\UploadedFileFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\UriFactoryInterface;
/**
 * @internal
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * Don't miss updating src/Composer/Plugin.php when adding a new supported class.
 */
final class CommonPsr17ClassesStrategy implements DiscoveryStrategy
{
    /**
     * @var array
     */
    private static $classes = [RequestFactoryInterface::class => ['Atome\\MagentoPayment\\Vendor\\Phalcon\\Http\\Message\\RequestFactory', 'Atome\\MagentoPayment\\Vendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'Atome\\MagentoPayment\\Vendor\\GuzzleHttp\\Psr7\\HttpFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Diactoros\\RequestFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Guzzle\\RequestFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Slim\\RequestFactory', 'Atome\\MagentoPayment\\Vendor\\Laminas\\Diactoros\\RequestFactory', 'Atome\\MagentoPayment\\Vendor\\Slim\\Psr7\\Factory\\RequestFactory', 'Atome\\MagentoPayment\\Vendor\\HttpSoft\\Message\\RequestFactory'], ResponseFactoryInterface::class => ['Atome\\MagentoPayment\\Vendor\\Phalcon\\Http\\Message\\ResponseFactory', 'Atome\\MagentoPayment\\Vendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'Atome\\MagentoPayment\\Vendor\\GuzzleHttp\\Psr7\\HttpFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Diactoros\\ResponseFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Guzzle\\ResponseFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Slim\\ResponseFactory', 'Atome\\MagentoPayment\\Vendor\\Laminas\\Diactoros\\ResponseFactory', 'Atome\\MagentoPayment\\Vendor\\Slim\\Psr7\\Factory\\ResponseFactory', 'Atome\\MagentoPayment\\Vendor\\HttpSoft\\Message\\ResponseFactory'], ServerRequestFactoryInterface::class => ['Atome\\MagentoPayment\\Vendor\\Phalcon\\Http\\Message\\ServerRequestFactory', 'Atome\\MagentoPayment\\Vendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'Atome\\MagentoPayment\\Vendor\\GuzzleHttp\\Psr7\\HttpFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Diactoros\\ServerRequestFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Guzzle\\ServerRequestFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Slim\\ServerRequestFactory', 'Atome\\MagentoPayment\\Vendor\\Laminas\\Diactoros\\ServerRequestFactory', 'Atome\\MagentoPayment\\Vendor\\Slim\\Psr7\\Factory\\ServerRequestFactory', 'Atome\\MagentoPayment\\Vendor\\HttpSoft\\Message\\ServerRequestFactory'], StreamFactoryInterface::class => ['Atome\\MagentoPayment\\Vendor\\Phalcon\\Http\\Message\\StreamFactory', 'Atome\\MagentoPayment\\Vendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'Atome\\MagentoPayment\\Vendor\\GuzzleHttp\\Psr7\\HttpFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Diactoros\\StreamFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Guzzle\\StreamFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Slim\\StreamFactory', 'Atome\\MagentoPayment\\Vendor\\Laminas\\Diactoros\\StreamFactory', 'Atome\\MagentoPayment\\Vendor\\Slim\\Psr7\\Factory\\StreamFactory', 'Atome\\MagentoPayment\\Vendor\\HttpSoft\\Message\\StreamFactory'], UploadedFileFactoryInterface::class => ['Atome\\MagentoPayment\\Vendor\\Phalcon\\Http\\Message\\UploadedFileFactory', 'Atome\\MagentoPayment\\Vendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'Atome\\MagentoPayment\\Vendor\\GuzzleHttp\\Psr7\\HttpFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Diactoros\\UploadedFileFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Guzzle\\UploadedFileFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Slim\\UploadedFileFactory', 'Atome\\MagentoPayment\\Vendor\\Laminas\\Diactoros\\UploadedFileFactory', 'Atome\\MagentoPayment\\Vendor\\Slim\\Psr7\\Factory\\UploadedFileFactory', 'Atome\\MagentoPayment\\Vendor\\HttpSoft\\Message\\UploadedFileFactory'], UriFactoryInterface::class => ['Atome\\MagentoPayment\\Vendor\\Phalcon\\Http\\Message\\UriFactory', 'Atome\\MagentoPayment\\Vendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'Atome\\MagentoPayment\\Vendor\\GuzzleHttp\\Psr7\\HttpFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Diactoros\\UriFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Guzzle\\UriFactory', 'Atome\\MagentoPayment\\Vendor\\Http\\Factory\\Slim\\UriFactory', 'Atome\\MagentoPayment\\Vendor\\Laminas\\Diactoros\\UriFactory', 'Atome\\MagentoPayment\\Vendor\\Slim\\Psr7\\Factory\\UriFactory', 'Atome\\MagentoPayment\\Vendor\\HttpSoft\\Message\\UriFactory']];
    public static function getCandidates($type)
    {
        $candidates = [];
        if (isset(self::$classes[$type])) {
            foreach (self::$classes[$type] as $class) {
                $candidates[] = ['class' => $class, 'condition' => [$class]];
            }
        }
        return $candidates;
    }
}
