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
namespace Webkul\MpBuyerSellerChat\Controller\Index;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Filesystem\Io\File as FilesystemIo;

class Viewfile extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $_urlDecoder;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var FilesystemIo
     */
    protected $filesystemIo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage
     */
    protected $fileStorage;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param FilesystemIo $filesystemIo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Helper\File\Storage $fileStorage
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        FilesystemIo $filesystemIo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Helper\File\Storage $fileStorage
    ) {
        parent::__construct($context);
        $this->_urlDecoder  = $urlDecoder;
        $this->_fileFactory = $fileFactory;
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
            $file = $this->_urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );
        } elseif ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = $this->_urlDecoder->decode(
                str_replace('/', '', $this->getRequest()->getParam('image'))
            );
            
            $plain = true;
        } else {
            throw new NotFoundException(__('Page not found.'));
        }

        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = 'marketplace/chatsystem/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);
        if (!$directory->isFile($fileName)
            && !$this->fileStorage->processStorageFile($path)
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        $name = $this->filesystemIo->getPathInfo($path, PATHINFO_BASENAME);
        
        $this->_fileFactory->create(
            $name['basename'],
            ['type' => 'filename', 'value' => $fileName],
            DirectoryList::MEDIA
        );
    }
}
