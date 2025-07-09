<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Controller\Ajax;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

class CreatePost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializerJson;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Webkul\MpBuyerSellerChat\Helper\Data
     */
    protected $mpChatHelper;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * Construct function
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializerJson
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Webkul\MpBuyerSellerChat\Helper\Data $mpChatHelper
     * @param AccountManagementInterface $accountManagement
     * @param SessionFactory $customerSessionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializerJson,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Webkul\MpBuyerSellerChat\Helper\Data $mpChatHelper,
        AccountManagementInterface $accountManagement,
        SessionFactory $customerSessionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->serializerJson = $serializerJson;
        $this->mpChatHelper = $mpChatHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->accountManagement = $accountManagement;
        $this->sessionFactory = $customerSessionFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->serializerJson->unserialize($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $redirectUrl = $this->sessionFactory->create()->getBeforeAuthUrl();
            // Get Website ID
            $websiteId  = $this->storeManager->getWebsite()->getWebsiteId();
            // Instantiate object (this is the most important part)
            $customer   = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);

            // Preparing data for new customer
            $customer->setEmail($credentials['email']);
            $customer->setFirstname($credentials['firstname']);
            $customer->setLastname($credentials['lastname']);
            $password = $credentials['password'];
            
            $customer = $this->accountManagement
                ->createAccount($customer, $password, $redirectUrl);

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $emailConfirmation = $this->mpChatHelper->getConfigData('', '', 'customer/create_account/confirm');

            if (!$emailConfirmation) {
                $this->sessionFactory->create()->setCustomerDataAsLoggedIn($customer);
                $this->mpChatHelper->setCustomerId($customer->getId());
                $response = [
                    'errors' => false,
                    'message' => __('Registered successful.')
                ];
            } else {
                $response = [
                    'errors' => true,
                    'message' => __('You must confirm your account. Please check your email for the confirmation link.')
                ];
            }
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        } catch (StateException $e) {
            $message = __(
                'There is already an account with this email address.'
            );
            $response = [
                'errors' => true,
                'message' => $message,
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        } catch (InputException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }
    }
}
