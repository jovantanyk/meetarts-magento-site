<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceEventManager\Block\Account;

/**
 * Marketplace Navigation link
 *
 */
class Navigation extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var mpHelper
     */
    protected $_mpHelper;

    /**
     * @var mpEventManagerHelper
     */
    protected $_mpEventManagerHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper,
        array $data = []
    ) {
        $this->_mpHelper = $mpHelper;
        $this->_mpEventManagerHelper = $mpEventManagerHelper;
        parent::__construct($context, $data);
    }
    /**
     * Get Current Url
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    /**
     * GetMpHelper
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }

    /**
     * GetMpEventManagerHelper
     */
    public function getMpEventManagerHelper()
    {
        return $this->_mpEventManagerHelper;
    }
}
