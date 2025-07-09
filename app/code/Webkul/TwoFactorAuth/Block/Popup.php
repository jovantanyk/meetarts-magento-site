<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_TwoFactorAuth
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\TwoFactorAuth\Block;

use Webkul\TwoFactorAuth\Helper\Data;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\TwoFactorAuth\Logger\Logger;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var $helper
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $helper
     * @param JsonHelper $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Get serialized data
     *
     * @return string
     */
    public function getSerializedData()
    {
        $data = [
            "apiKey"    =>  $this->helper->getServerKey(),
            'swPath'    =>  $this->getViewFileUrl('TwoFactorAuth::js/service-worker-script.js'),
            'ajaxUrl'   =>  $this->getUrl('twofactorauth/users/save')
        ];
        return $this->jsonHelper->jsonEncode($data);
    }
}
