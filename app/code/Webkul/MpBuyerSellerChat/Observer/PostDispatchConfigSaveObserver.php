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
namespace Webkul\MpBuyerSellerChat\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Driver\File as DriverFile;

/**
 * PostDispatchConfigSaveObserver Observer.
 */
class PostDispatchConfigSaveObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DriverFile
     */
    protected $driverFile;

    /**
     * @param ManagerInterface $messageManager
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DriverFile $driverFile
     */
    public function __construct(
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DriverFile $driverFile
    ) {
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->_baseDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->reader = $reader;
        $this->driverFile = $driverFile;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $moduleEtcPath = $this->reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Webkul_MpBuyerSellerChat');
            
            /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = $this->_baseDirectory;

            $observerRequestData = $observer['request'];
            $params = $observerRequestData->getParams();
            
            if ($params['section'] == 'buyer_seller_chat') {
                $paramsData = $params['groups']['general_settings']['fields'];
                if (isset($paramsData['port_number']['value']) && $paramsData['port_number']['value']) {
                    $baseFilePath = $this->_baseDirectory->getAbsolutePath().'server.js';
                    $baseDirPath = $this->_baseDirectory->getAbsolutePath();
                    $validator = new \Zend\Validator\File\Exists();
                    if ($validator->isValid($baseFilePath)) {
                        if (!$this->isCurrentlySecure()) {
                            $serverFile = $this->driverFile->fileOpen($baseDirPath."/server.js", "w");
                            $serverFileData = $this->getServerFileData($paramsData);
                            
                            $this->driverFile->fileWrite($serverFile, $serverFileData);
                            $this->driverFile->fileClose($serverFile);

                        } else {
                            $serverFile = $this->driverFile->fileOpen($baseDirPath."/server.js", "w");
                            $serverFileData = $this->getSecureServerFileData($paramsData);
                            
                            $this->driverFile->fileWrite($serverFile, $serverFileData);
                            $this->driverFile->fileClose($serverFile);
                        }
                    } else {
                        if ($this->isCurrentlySecure()) {
                            $filePath = $moduleEtcPath.'/serverJs/secure/server.js';
                            $this->_baseDirectory->copyFile($filePath, $baseFilePath, $filesystem);
                        } else {
                            $filePath = $moduleEtcPath.'/serverJs/server.js';
                            $this->_baseDirectory->copyFile($filePath, $baseFilePath, $filesystem);
                            $serverFile = $this->driverFile->fileOpen($baseDirPath."/server.js", "w");
                            $serverFileData = $this->getServerFileData($paramsData);
                            $this->driverFile->fileWrite($serverFile, $serverFileData);
                            $this->driverFile->fileClose($serverFile);
                        }
                    }
                    
                }

                $hostValue = $paramsData['host_name']['value'];
                
                $cspWhitelistPath = $moduleEtcPath.'/csp_whitelist.xml';
                $cspWhitelist = simplexml_load_file($cspWhitelistPath);
                $socket = 'http://'.$hostValue.':'.$paramsData['port_number']['value'].'/socket.io/';
                $sockets = 'https://'.$hostValue.':'.$paramsData['port_number']['value'].'/socket.io/';
                $websocket = 'ws://'.$hostValue.':'.$paramsData['port_number']['value'].'/socket.io/';
                $cspWhitelist->policies->policy[0]->values->value[0] = $socket;
                $cspWhitelist->policies->policy[0]->values->value[1] = $sockets;
                $cspWhitelist->policies->policy[0]->values->value[2] = $websocket;
                $cspWhitelist->asXML($cspWhitelistPath);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Get Server FileData
     *
     * @param array $paramsData
     * @return void
     */
    private function getServerFileData($paramsData)
    {
        $str = '';
        if (isset($paramsData['port_number']['value']) && $paramsData['port_number']['value']) {
            $port = $paramsData['port_number']['value'];
            $str = <<<EOD

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 var app = require('http').createServer(function (req, res) {
     res.writeHead(200, { 'Content-Type': 'text/plain' });
     res.end('okay');
 })
 var io = require('socket.io')(app);
 var siofu = require('socketio-file-upload');
 var fs = require('fs');
 var roomUsers = {};
 
 app.listen($port, function () {
     console.log('listening');
 });
 
 io.on('connection', function (socket) {
 
     var uploader = new siofu();
 
     // Do something when a file is saved:
     uploader.on("saved", function (event) {
         console.log(event.file);
         event.file.clientDetail.fileName = event.file.name;
     });
 
     // Error handler:
     uploader.on("error", function (event) {
         console.log("Error from uploader", event);
     });
 
     uploader.uploadValidator = function (event, callback) {
         fs.mkdir('pub/media/marketplace/chatsystem', function (err, folder) {
             if (err) {
                 if (err.code == 'EEXIST') {
                     uploader.dir = err.path;
                     callback(true);
                 } else {
                     callback(false); // abort
                 }
             }
             else {
                 uploader.dir = folder;
                 callback(true); // ready
             }
         });
     };
 
     uploader.listen(socket);
 
 
     socket.on('newSellerConneted', function (details) {
         var index = details.sellerUniqueId;
         roomUsers[index] = socket.id;
     });
     socket.on('newCustomerConneted', function (details) {
         var index = details.customerUniqueId;
         roomUsers[index] = socket.id;
         Object.keys(roomUsers).forEach(function (key, value) {
             if (key === details.receiverUniqueId) {
                 receiverSocketId = roomUsers[key];
                 socket.broadcast.to(receiverSocketId).emit('refresh seller chat list', details);
             }
         });
     });
 
     socket.on('customer send new message', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.receiverUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('seller new message received', data);
                 }
             });
         }
     });
     socket.on('seller send new message', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.receiverUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('customer new message received', data);
                 }
             });
         }
     });
     socket.on('customer block event', function (data) {
         console.log('customer-block-event');
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.customerUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('customer blocked by seller', data);
                 }
             });
         }
     });
     socket.on('customer status change', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.sellerUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('send customer status change', data);
                 }
             });
         }
     });
 
     socket.on('seller status change', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 Object(data.customers).forEach(function (k) {
                     if (key === k.customerUniqueId) {
                         receiverSocketId = roomUsers[key];
                         socket.broadcast.to(receiverSocketId).emit('send seller status change', data);
                     }
                 });
             });
         }
     });
 });

EOD;
            return $str;
        }
    }

    /**
     * GetSecure ServerFileData
     *
     * @param array $paramsData
     * @return mixed
     */
    private function getSecureServerFileData($paramsData)
    {
        $str = '';
        if (isset($paramsData['port_number']['value']) && $paramsData['port_number']['value']) {
            $port = $paramsData['port_number']['value'];
            $str = <<<EOD

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 var https = require('https');
 var fs = require('fs');
 var siofu = require('socketio-file-upload');
 
 var options_https = {
     key: fs.readFileSync('pub/media/server_files/default/server.key', 'utf8'),
     cert: fs.readFileSync('pub/media/server_files/default/server.crt', 'utf8'),
     ca: fs.readFileSync('pub/media/server_files/default/server.cabundle'),
     requestCert: true,
     rejectUnauthorized: false
 };
 
 var app = https.createServer(options_https, function (req, res) {
     res.writeHead(200, { 'Content-Type': 'text/plain' });
     res.end('okay');
 });
 
 var io = require('socket.io')(app);
 
 var roomUsers = {};
 
 app.listen($port, function () {
     console.log('listening');
 });
 
 io.on('connection', function (socket) {
 
     var uploader = new siofu();
 
     // Do something when a file is saved:
     uploader.on("saved", function (event) {
         console.log(event.file);
         event.file.clientDetail.fileName = event.file.name;
     });
 
     // Error handler:
     uploader.on("error", function (event) {
         console.log("Error from uploader", event);
     });
 
     uploader.uploadValidator = function (event, callback) {
         fs.mkdir('pub/media/marketplace/chatsystem', function (err, folder) {
             if (err) {
                 if (err.code == 'EEXIST') {
                     uploader.dir = err.path;
                     callback(true);
                 } else {
                     callback(false); // abort
                 }
             }
             else {
                 uploader.dir = folder;
                 callback(true); // ready
             }
         });
     };
 
     uploader.listen(socket);
 
 
     socket.on('newSellerConneted', function (details) {
         var index = details.sellerUniqueId;
         roomUsers[index] = socket.id;
     });
     socket.on('newCustomerConneted', function (details) {
         var index = details.customerUniqueId;
         roomUsers[index] = socket.id;
         Object.keys(roomUsers).forEach(function (key, value) {
             if (key === details.receiverUniqueId) {
                 receiverSocketId = roomUsers[key];
                 socket.broadcast.to(receiverSocketId).emit('refresh seller chat list', details);
             }
         });
     });
 
     socket.on('customer send new message', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.receiverUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('seller new message received', data);
                 }
             });
         }
     });
     socket.on('seller send new message', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.receiverUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('customer new message received', data);
                 }
             });
         }
     });
     socket.on('customer block event', function (data) {
         console.log('customer-block-event');
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.customerUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('customer blocked by seller', data);
                 }
             });
         }
     });
     socket.on('customer status change', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 if (key === data.sellerUniqueId) {
                     receiverSocketId = roomUsers[key];
                     socket.broadcast.to(receiverSocketId).emit('send customer status change', data);
                 }
             });
         }
     });
 
     socket.on('seller status change', function (data) {
         if (typeof (data) !== 'undefined') {
             Object.keys(roomUsers).forEach(function (key, value) {
                 Object(data.customers).forEach(function (k) {
                     if (key === k.customerUniqueId) {
                         receiverSocketId = roomUsers[key];
                         socket.broadcast.to(receiverSocketId).emit('send seller status change', data);
                     }
                 });
             });
         }
     });
 });
EOD;
            return $str;
        }
    }

    /**
     * Check if current requested URL is secure
     *
     * @return boolean
     */
    public function isCurrentlySecure()
    {
        return $this->storeManager->getStore()->isCurrentlySecure();
    }
}
