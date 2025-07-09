<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Controller\Rewrite\Account;

use Magento\Framework\App\Action\Context;
use Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;
use Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken\CollectionFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Customer\Controller\Account\Index
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     *
     */

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    private $twoFactorRepositoryInterface;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor function
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface,
        PageFactory $resultPageFactory,
        Context $context
    ) {
        $this->customerSession = $customerSession;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
        $this->storeManager = $storeManager;
        parent::__construct($context, $resultPageFactory);
    }
    
    /**
     * Execute function
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $customerEmail = $this->customerSession->getCustomer()->getEmail();
            $authData = $this->twoFactorRepositoryInterface->getByEmail($customerEmail);
        if ($authData->getVerified() == 0) {
            $redirect->setUrl(
                $this->storeManager->getStore()->getBaseUrl().'twofactorauth/account/verify',
                ['_secure' => true]
            );
            return $redirect;
        }
        return parent::execute();
    }
}
