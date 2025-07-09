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
namespace Webkul\MarketplaceEventManager\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Marketplace event manager validate Controller.
 */
class Invalidate extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\MarketplaceEventManager\Model\Mpevent
     */
    protected $_mpevent;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MarketplaceEventManager\Model\Mpevent $mpevent
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MarketplaceEventManager\Model\Mpevent $mpevent
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_mpevent = $mpevent;
        parent::__construct($context);
    }

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $param = $this->getRequest()->getParam('qrcode');
        if ($param) {
            $collection = $this->_mpevent
                ->getCollection()
                ->addFieldToFilter('qrcode', ['eq'=>$param]);
            $this->getResponse()->setHeader('Content-type', 'application/json');
            if ($collection->getSize() > 0) {
                foreach ($collection as $col) {
                    $col->setStatus(0);
                    $col->save();
                    $msg = 'success';
                    $this->getResponse()->setBody(json_encode(['msg'=>'success']));
                }
            } else {
                $msg = 'error';
                $this->getResponse()->setBody(json_encode(['msg'=>'error']));
            }
        } else {
            $msg = 'error';
            $this->getResponse()->setBody(json_encode(['msg'=>'error']));
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('validate ticket'));
        return $resultPage;
    }
}
