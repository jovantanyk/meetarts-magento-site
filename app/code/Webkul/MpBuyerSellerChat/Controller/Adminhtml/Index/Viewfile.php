<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Controller\Adminhtml\Index;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Filesystem\Io\File as FilesystemIo;

class Viewfile extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var FilesystemIo
     */
    protected $filesystemIo;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param FilesystemIo $filesystemIo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Helper\File\Storage $fileStorage
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        FilesystemIo $filesystemIo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Helper\File\Storage $fileStorage
    ) {
        parent::__construct($context);
        $this->urlDecoder  = $urlDecoder;
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->filesystemIo = $filesystemIo;
        $this->filesystem = $filesystem;
        $this->fileStorage = $fileStorage;
    }

    /**
     * Customer view file action
     *
     * @return \Magento\Framework\Controller\ResultInterface|void
     * @throws NotFoundException
     */
    public function execute()
    {
        $file = null;
        $plain = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );
        } elseif ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = $this->urlDecoder->decode(
                str_replace('/', '', $this->getRequest()->getParam('image'))
            );
            
            $plain = true;
        } else {
            throw new NotFoundException(__('Page not found.'));
        }

        $fileDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = 'marketplace/chatsystem/' . ltrim($file, '/');
        $path = $fileDirectory->getAbsolutePath($fileName);
        if (!$fileDirectory->isFile($fileName)
            && !$this->fileStorage->processStorageFile($path)
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        if ($plain) {
            $extension = $this->filesystemIo->getPathInfo($path, PATHINFO_EXTENSION);
            switch (strtolower($extension['extension'])) {
                case 'gif':
                    $contextType = 'image/gif';
                    break;
                case 'jpg':
                    $contextType = 'image/jpeg';
                    break;
                case 'png':
                    $contextType = 'image/png';
                    break;
                default:
                    $contextType = 'application/octet-stream';
                    break;
            }
            $stat = $fileDirectory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contextType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($fileDirectory->readFile($fileName));
            return $resultRaw;
        } else {
            $name = $this->filesystemIo->getPathInfo($path, PATHINFO_BASENAME);
            $this->fileFactory->create(
                $name['basename'],
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }
}
