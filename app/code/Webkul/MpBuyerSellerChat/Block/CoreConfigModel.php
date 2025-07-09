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
namespace Webkul\MpBuyerSellerChat\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\ResourceModel\Online\Grid\CollectionFactory;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Webkul\MpBuyerSellerChat\Helper\Http as HttpDriver;

class CoreConfigModel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DriverFile
     */
    protected $driverFile;
    
    /**
     * @var HttpDriver
     */
    protected $httpDriver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param DriverFile $driverFile
     * @param HttpDriver $httpDriver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        DriverFile $driverFile,
        HttpDriver $httpDriver,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->driverFile = $driverFile;
        $this->httpDriver = $httpDriver;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve information from carrier configuration
     *
     * @param [string] $group
     * @param [string] $field
     * @return void|false|string
     */
    public function getConfigData($group, $field)
    {
        $path = 'buyer_seller_chat/'.$group.'/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * Retrive ChatBox data from CoreConfig
     *
     * @return void
     */
    public function getChatBoxCoreConfig()
    {
        $configData['serverRunning'] = $this->isServerRunning();
        $configData['loaderImage'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/loader-2.gif');
        $configData['soundUrl'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/sound/notify.ogg');
        $configData['soundImage'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/sound.png');
        $configData['settingImage'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/setting.png');
        $configData['customerImage'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/default.png');
        $configData['attachmentImage'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/attachment.png');
        $configData['sellerImage'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/sellerimage.png');
        $configData['emojiImagePath'] = $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/emoji');
        $configData['host'] = $this->getConfigData('general_settings', 'host_name').
            ':'.$this->getConfigData('general_settings', 'port_number');
        $configData['chatName'] = $this->getConfigData('general_settings', 'chat_name');
        $configData['storeCode'] = $this->_storeManager->getStore()->getCode();
        $configData['maxFileSize'] = (int)$this->getConfigData('general_settings', 'max_size');
        return $configData;
    }

    /**
     * Start/Stop Server
     *
     * @return boolean
     */
    private function isServerRunning()
    {
        $host = $this->getConfigData('general_settings', 'host_name');
        $port = (int) $this->getConfigData('general_settings', 'port_number');
        $connection = $this->httpDriver->getHttpDriver($host, $port);
        if (is_resource($connection)) {
            $result = true;
            $this->driverFile->fileClose($connection);
        } else {
            $result = false;
        }
        return $result;
    }
}
