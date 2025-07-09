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

class Checkticketstatus extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * View Store Page
     *
     * @return PageFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Data::class
        );
        $resultPage = $this->_resultPageFactory->create();
        if ($helper->getIsSeparatePanel()) {
            $resultPage->addHandle('marketplaceeventmanager_layout2_index_checkticketstatus');
        }
        $resultPage->getConfig()->getTitle()->set(__('Ticket Details'));
        return $resultPage;
    }
}
