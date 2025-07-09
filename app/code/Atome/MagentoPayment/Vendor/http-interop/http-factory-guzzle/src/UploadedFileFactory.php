<?php

namespace Atome\MagentoPayment\Vendor\Http\Factory\Guzzle;

use Atome\MagentoPayment\Vendor\GuzzleHttp\Psr7\UploadedFile;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\UploadedFileFactoryInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\StreamInterface;
use Atome\MagentoPayment\Vendor\Psr\Http\Message\UploadedFileInterface;
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null) : UploadedFileInterface
    {
        if ($size === null) {
            $size = $stream->getSize();
        }
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}
