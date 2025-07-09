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
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Sendremindermail extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\MarketplaceEventManager\Model\Sendmail
     */
    protected $_sendmail;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MarketplaceEventManager\Model\Sendmail $sendmail
     * @param Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MarketplaceEventManager\Model\Sendmail $sendmail,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_sendmail = $sendmail;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(\Magento\Customer\Model\Url::class)->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Add Store Page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Data::class
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $params = $this->getRequest()->getParams();
            $res = false;
            if (isset($params['buyerlist'])) {
                $res = $this->_sendmail->sendReminderMail($params);
            }
            if (!$res['error']) {
                $this->messageManager->addSuccess('Mail send successfully');
            } else {
                $this->messageManager->addError(__($res['message']));
            }
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/eventlist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
