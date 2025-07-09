<?php

namespace SoftBuild\HitPay\Controller\Onepage;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Success extends \Magento\Checkout\Controller\Onepage implements HttpGetActionInterface
{
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        
        $params = $this->getRequest()->getParams();
        if (!isset($params['order_id'])) {
            if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
        }

        $session->clearQuote();
        //@todo: Refactor it to match CQRS
        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            [
                'order_ids' => [$session->getLastOrderId()],
                'order' => $session->getLastRealOrder()
            ]
        );
        return $resultPage;
    }
}
