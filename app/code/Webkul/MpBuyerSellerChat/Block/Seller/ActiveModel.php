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
namespace Webkul\MpBuyerSellerChat\Block\Seller;

use Magento\Store\Model\ScopeInterface;

class ActiveModel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\MpBuyerSellerChat\Model\ChatDataConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHepler;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializerJson;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpBuyerSellerChat\Model\EnableUserConfigProvider $configProvider
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Webkul\Marketplace\Helper\Data $mpHepler
     * @param \Magento\Framework\Serialize\Serializer\Json $serializerJson
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpBuyerSellerChat\Model\EnableUserConfigProvider $configProvider,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Marketplace\Helper\Data $mpHepler,
        \Magento\Framework\Serialize\Serializer\Json $serializerJson,
        array $data = []
    ) {

        $this->scopeConfig = $context->getScopeConfig();
        $this->configProvider = $configProvider;
        $this->request = $request;
        $this->mpHepler = $mpHepler;
        $this->serializerJson = $serializerJson;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'customer_termandcondition/parameter/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * Retrieve ChatBox Config Data
     *
     * @return array
     */
    public function getChatBoxConfig()
    {
        $configData = $this->configProvider->getConfig();

        $sellerImage = isset($configData['sellerChatData']['sellerImage']) ?
                        $configData['sellerChatData']['sellerImage'] : null;

        if (isset($configData['sellerChatData']) && !$sellerImage) {
            $configData['sellerChatData']['sellerImage'] =
                $this->getViewFileUrl('Webkul_MpBuyerSellerChat::images/sellerimage.png');
        }
        return $configData;
    }

    /**
     * Check ChatWindow View on Seller End
     *
     * @return bool
     */
    public function checkChatWindowView()
    {
        $routeName = $this->request->getRouteName();
        if ($routeName == 'marketplace') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->mpHepler;
    }

    /**
     * Encode data
     *
     * @param array $data
     * @return string
     */
    public function jsonFormat($data)
    {
        return $this->serializerJson->serialize($data);
    }
}
