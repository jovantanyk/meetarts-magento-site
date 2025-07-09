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

namespace Webkul\MarketplaceEventManager\Controller\Event;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Product Edit Controller.
 */
class Edit extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Array of actions which can be processed without secret key validation.
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context         $context
     * @param Webkul\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory    $resultPageFactory
     * @param \Magento\Customer\Model\Session               $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\Marketplace\Controller\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct(
            $context
        );
        $this->productBuilder = $productBuilder;
        $this->_resultPageFactory = $resultPageFactory;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(\Magento\Customer\Model\Url::class)
        ->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Seller Product Edit Action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Data::class
        );
        $eventHelper = $this->_objectManager->create(
            \Webkul\MarketplaceEventManager\Helper\Data::class
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1 && $eventHelper->isEventManagerEnable()) {
            $productId = (int) $this->getRequest()->getParam('id');
            $rightseller = $helper->isRightSeller($productId);
            if ($rightseller == 1) {
                $helper = $this->_objectManager->get(
                    \Webkul\Marketplace\Helper\Data::class
                );
                $product = $this->productBuilder->build(
                    $this->getRequest()->getParams(),
                    $helper->getCurrentStoreId()
                );

                if ($productId && !$product->getId()) {
                    $this->messageManager->addError(
                        __('This product no longer exists.')
                    );
                    /*
                     * @var \Magento\Backend\Model\View\Result\Redirect
                     */
                    $resultRedirect = $this->resultRedirectFactory->create();

                    return $resultRedirect->setPath(
                        '*/*/productlist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                if ($productId) {
                    $resultPage = $this->_resultPageFactory->create();
                    if ($helper->getIsSeparatePanel()) {
                        $resultPage->addHandle('marketplaceeventmanager_layout2_event_edit');
                    }
                    $resultPage->getConfig()->getTitle()->set(
                        __('Edit Event')
                    );

                    return $resultPage;
                } else {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/add',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplaceeventmanager/event/eventlist',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
