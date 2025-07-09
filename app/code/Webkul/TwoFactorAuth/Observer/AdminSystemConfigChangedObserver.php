<?php
/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Webkul TwoFactorAuth admin_system_config_changed_section_twofactorauth Observer.
 */
class AdminSystemConfigChangedObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var IoFile
     */
    protected $_filesystemFile;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_http;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var  requestInterface
     */
    protected $_request;

    /**
     * @var baseDirectory
     */
    protected $_baseDirectory;

     /**
      * @var Filesystem
      */
    protected $filesystem;

    /**
     * @var \Webkul\TwoFactorAuth\Helper\Data
     */
    private $twoFactorHelper;

    /**
     * Constructor function
     *
     * @param RequestInterface $requestInterface
     * @param ManagerInterface $messageManager
     * @param Filesystem $filesystem
     * @param IoFile $filesystemFile
     * @param File $file
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $http
     * @param \Webkul\TwoFactorAuth\Helper\Data $twoFactorHelper
     */
    public function __construct(
        RequestInterface $requestInterface,
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        IoFile $filesystemFile,
        File $file,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $http,
        \Webkul\TwoFactorAuth\Helper\Data $twoFactorHelper
    ) {
        $this->_request = $requestInterface;
        $this->_messageManager = $messageManager;
        $this->_baseDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_filesystemFile = $filesystemFile;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->_http = $http;
        $this->twoFactorHelper = $twoFactorHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Admin System Configuration Change Observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->twoFactorHelper->sendOtpSource() == 'pushnotify') {
            $manifestData = [];
            $data =  $this->_request->getParams();
            $fields = $data['groups']['enable']['fields'];
            try {
                if (!empty($fields['application_server_key']['value']) &&
                !empty($fields['application_sender_id']['value'])) {
                    $manifestData['name'] = 'Webkul Push Notification';
                    $manifestData['gcm_sender_id'] = "103953800507";
                    $jsonFileName = 'manifest.json';
                    $mageDir = '/code/Webkul/TwoFactorAuth/view/frontend/web/json/manifest.json';
                    $writer = $this->filesystem->
                    getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
                    $file = $writer->openFile($mageDir, 'w+');
                    try {
                        $file->lock();
                        try {
                            $file->write(json_encode($manifestData));
                        } finally {
                            $file->unlock();
                        }
                    } finally {
                        $file->close();
                    }
                }
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }

            try {
                /**
                * @var \Magento\Framework\ObjectManagerInterface $objManager
                */
                $objManager = \Magento\Framework\App\ObjectManager::getInstance();
                /**
                 * @var \Magento\Framework\Module\Dir\Reader $reader
                */
                $reader = $objManager->get(\Magento\Framework\Module\Dir\Reader::class);

                /**
                 * @var \Magento\Framework\Filesystem $filesystem
                */
                $filesystem = $objManager->get(\Magento\Framework\Filesystem::class);

                $serviceWorkerJsFile = $reader->getModuleDir(
                    '',
                    'Webkul_TwoFactorAuth'
                ).'/view/frontend/web/js/firebase-messaging-sw.js';
                $serviceWorkerJsDestination = $this->_baseDirectory->getAbsolutePath().'pub/firebase-messaging-sw.js';
                $serviceWorkerJsDestination2 = $this->_baseDirectory->getAbsolutePath().'firebase-messaging-sw.js';
                $parts = explode('/', $this->_baseDirectory->getAbsolutePath());
                $last = array_pop($parts);
                $last = array_pop($parts);
                $parts = [implode('/', $parts), $last];
                $this->_filesystemFile->cp($serviceWorkerJsFile, $parts[0].'/firebase-messaging-sw.js');
                $this->_filesystemFile->cp($serviceWorkerJsFile, $serviceWorkerJsDestination);
                $this->_filesystemFile->cp($serviceWorkerJsFile, $serviceWorkerJsDestination2);
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }
}
