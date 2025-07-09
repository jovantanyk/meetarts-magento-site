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
namespace Webkul\MarketplaceEventManager\Plugin\Controller\Product;

use Webkul\MarketplaceEventManager\Helper\Data as EventHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\ResultFactory;

class Edit
{
    /**
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    protected $_eventHelper;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var UrlInterface
     */
    protected $_url;
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @param EventHelper $eventHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param UrlInterface $url
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        EventHelper $eventHelper,
        \Magento\Framework\App\Request\Http $request,
        UrlInterface $url,
        ResultFactory $resultFactory
    ) {
        $this->_eventHelper = $eventHelper;
        $this->_request = $request;
        $this->_url = $url;
        $this->_resultFactory = $resultFactory;
    }

    /**
     * AroundExecute
     *
     * @param \Webkul\Marketplace\Controller\Product\Edit $subject
     * @param \Closure $proceed
     */
    public function aroundExecute(
        \Webkul\Marketplace\Controller\Product\Edit $subject,
        \Closure $proceed
    ) {
        $params = $this->_request->getParams();
        $productId = $params['id'];
        $helper = $this->_eventHelper;
        if ($helper->isEventProduct($productId)) {
            $result = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setUrl(
                $this->_url->getUrl(
                    'marketplaceeventmanager/event/edit',
                    [
                        'id'=> $productId,
                        '_secure' => $this->_request->isSecure()
                    ]
                )
            );
            return $result;
        }
        return $proceed();
    }
}
