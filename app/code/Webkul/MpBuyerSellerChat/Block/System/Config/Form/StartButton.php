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
namespace Webkul\MpBuyerSellerChat\Block\System\Config\Form;

class StartButton extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    public const BUTTON_TEMPLATE = 'system/config/start_stop_button.phtml';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\MpBuyerSellerChat\Helper\Data
     */
    protected $mpBuyerChatHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializerJson;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MpBuyerSellerChat\Helper\Data $mpBuyerChatHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializerJson
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpBuyerSellerChat\Helper\Data $mpBuyerChatHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializerJson,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->mpBuyerChatHelper = $mpBuyerChatHelper;
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
        $path = 'buyer_seller_chat/general_settings/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }
    
    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }

        return $this;
    }

    /**
     * Render button.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return ajax url for button.
     *
     * @return string
     */
    public function getAjaxStartUrl()
    {
        return $this->getUrl('mpchatsystem/server/start');
    }

    /**
     * Return ajax url for button.
     *
     * @return string
     */
    public function getAjaxStopUrl()
    {
        return $this->getUrl('mpchatsystem/server/stop');
    }

    /**
     * Get the button and scripts contents.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'demomanagement_button',
                'onclick' => 'javascript:check(); return false;',
            ]
        );

        return $this->_toHtml();
    }

    /**
     * Retrive label for Start Button
     *
     * @return string
     */
    public function getStartButtonLabel()
    {
        return __('Start Server');
    }

    /**
     * Retrive label for Stop Button
     *
     * @return string
     */
    public function getStopButtonLabel()
    {
        return __('Stop Server');
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->mpBuyerChatHelper;
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
