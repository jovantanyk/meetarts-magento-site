<?php
namespace Webkul\MarketplaceEventManager\ViewModel;

class MpViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var mpHelper
     */
    protected $mpHelper;

    /**
     * @var jsonHelper
     */
    protected $jsonHelper;

    /**
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->mpHelper= $mpHelper;
        $this->jsonHelper= $jsonHelper;
    }

    /**
     * GetMpHelper
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    /**
     * GetJsonHelper
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
