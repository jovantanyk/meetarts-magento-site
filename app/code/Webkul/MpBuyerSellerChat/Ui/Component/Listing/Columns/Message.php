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
namespace Webkul\MpBuyerSellerChat\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as FilesystemIo;

/**
 * Class ViewAction.
 */
class Message extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var FilesystemIo
     */
    protected $filesystemIo;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Filesystem $filesystem
     * @param FilesystemIo $filesystemIo
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Filesystem $filesystem,
        FilesystemIo $filesystemIo,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->filesystem = $filesystem;
        $this->urlDecoder = $urlDecoder;
        $this->filesystemIo = $filesystemIo;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    if ($this->getMessageType($item[$this->getData('name')]) == 'image') {
                        $item[$this->getData('name')] =
                        '<a target="_blank" href="'.$this->_urlBuilder
                        ->getUrl('mpchatsystem/index/viewfile', ['image'
                        => $item[$this->getData('name')]]).'"><img src="'.$this->_urlBuilder
                        ->getUrl('mpchatsystem/index/viewfile', ['image'
                        => $item[$this->getData('name')]]).'" width="50" height="50"/></a>';
                    } elseif ($this->getMessageType($item[$this->getData('name')]) == 'file') {
                        $item[$this->getData('name')] = '<a href="'.$this->_urlBuilder
                        ->getUrl('mpchatsystem/index/viewfile', ['file'
                        => $item[$this->getData('name')]]).'">'.__('Click to download file').'</a>';
                    } else {
                        $item[$this->getData('name')] = '<p>'.$item[$this->getData('name')].'</p>';
                    }
                }
            }
        }

        return $dataSource;
    }

    /**
     * Fetch Message Type
     *
     * @param string $message
     * @return string
     */
    public function getMessageType($message)
    {
        $type = 'text';
        $file = $this->urlDecoder->decode($message);
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $fileName = 'marketplace/chatsystem/'.ltrim($file, '/');

        $filePath = $directory->getAbsolutePath($fileName);
        
        if ($directory->isFile($fileName)) {
            $extension = $this->filesystemIo->getPathInfo($filePath, PATHINFO_EXTENSION);
            switch (strtolower($extension['extension'])) {
                case 'gif':
                    $contentType = 'image/gif';
                    $type = 'image';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    $type = 'image';
                    break;
                case 'jpeg':
                    $contentType = 'image/jpeg';
                    $type = 'image';
                    break;
                case 'PNG':
                    $contentType = 'image/png';
                    $type = 'image';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    $type = 'image';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    $type = 'file';
                    break;
            }
        }
        return $type;
    }
}
