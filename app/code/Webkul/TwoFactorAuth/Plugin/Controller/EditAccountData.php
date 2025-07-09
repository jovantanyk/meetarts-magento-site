<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Plugin\Controller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

class EditAccountData
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param CustomerRepositoryInterface $customerRepository
     * @param RequestInterface $request
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerModel,
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->_customerModel = $customerModel;
        $this->_customerRepository = $customerRepository;
        $this->_request = $request;
        $this->_customerSession = $customerSession->create();
        $this->_messageManager = $messageManager;
        $this->_resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Plugin for \Magento\Customer\Controller\Account\EditPost
     *
     * @param \Magento\Customer\Controller\Account\EditPost $subject
     * @param void $proceed
     * @param string $data
     * @param boolean $requestInfo
     * @return void
     */
    public function aroundExecute(
        \Magento\Customer\Controller\Account\EditPost $subject,
        $proceed,
        $data = "null",
        $requestInfo = false
    ) {
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getId();
        } else {
            $this->_messageManager->addError(
                __("The account sign-in was incorrect or your account is disabled temporarily."
                . "Please wait and try again later.")
            );
            return $this->_resultRedirectFactory->create()->setPath('customer/account/login', ['_secure' => true]);
        }
        $customerData = $this->_request->getParams();
        $currentCustomerDataObject = $this->_customerRepository->getById($customerId);
        // whether a customer enabled change phone number option
        if ($this->_request->getParam('change_phone_number')) {
            // authenticate user for changing phone number
            try {
                $this->getAuthentication()->authenticate(
                    $currentCustomerDataObject->getId(),
                    $this->_request->getPost('current_password')
                );
            } catch (InvalidEmailOrPasswordException $e) {
                $this->_messageManager->addError(
                    __($e->getMessage())
                );
                return $this->_resultRedirectFactory->create()->setPath('customer/account/edit', ['_secure' => true]);
            }
        }
        if (isset($customerData['phone_number_input'])) {
            $result = $proceed();
            $customer = $this->_customerRepository->getById($customerId);
            $customer->setCustomAttribute('twofactorauth_phone_number', $customerData['phone_number_input']);
            $this->_customerRepository->save($customer);
            return $result;
        }
        return $proceed();
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }
}
