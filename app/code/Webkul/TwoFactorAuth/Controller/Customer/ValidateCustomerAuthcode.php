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

namespace Webkul\TwoFactorAuth\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface as TwoFactorAuthRepositoryInterface;
use Webkul\TwoFactorAuth\Helper\Customer as CustomerHelper;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorHelper;
use Webkul\TwoFactorAuth\Helper\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

class ValidateCustomerAuthCode extends Action
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var $customerHelper
     */
    private $customerHelper;

    /**
     * @var TwoFactorHelper
     */
    private $twoFactorHelper;

    /**
     * @var JsonResult
     */
    private $jsonHelper;

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    private $twoFactorAuthRepository;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Webkul\TwoFactorAuth\Helper\TOTPAuth
     */
    protected $totpauth;

    /**
     * @var \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor function
     *
     * @param FormKeyValidator $formKeyValidator
     * @param ResultJsonFactory $resultJsonFactory
     * @param CustomerHelper $customerHelper
     * @param TwoFactorHelper $twoFactorHelper
     * @param JsonHelper $jsonHelper
     * @param TwoFactorAuthRepositoryInterface $twoFactorAuthRepository
     * @param Session $customerSession
     * @param \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth
     * @param \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        FormKeyValidator $formKeyValidator,
        ResultJsonFactory $resultJsonFactory,
        CustomerHelper $customerHelper,
        TwoFactorHelper $twoFactorHelper,
        JsonHelper $jsonHelper,
        TwoFactorAuthRepositoryInterface $twoFactorAuthRepository,
        Session $customerSession,
        \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth,
        \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerHelper = $customerHelper;
        $this->twoFactorHelper = $twoFactorHelper;
        $this->jsonHelper = $jsonHelper;
        $this->twoFactorAuthRepository = $twoFactorAuthRepository;
        $this->customerSession = $customerSession;
        $this->totpauth = $totpauth;
        $this->collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->resource = $resource;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute function for validating the customer auth code.
     */
    public function execute()
    {
        $response = [
            'error' => true,
            'message' => __('Bad Request.')
        ];
        $authCodeCredentials = false;
        try {
            $authCodeCredentials = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
            
            if (!$authCodeCredentials ||
                !$this->getRequest()->getMethod() === 'POST' ||
                !$this->getRequest()->isXmlHttpRequest() ||
                !$this->formKeyValidator->validate($this->getRequest())
            ) {
                throw new LocalizedException(__('Bad Request'));
            }
        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData($response);
        }
        if ($this->twoFactorHelper->sendOtpSource() === 'totp') {
            $authcodeverify = $this->totpauth->verifyCode(
                $this->customerSession->getSecretKey(),
                $authCodeCredentials['otp']
            );
            
            if ($authcodeverify) {
                $response = ['error' => false, 'message'=> __('Auth Code verified.')];
                return $this->resultJsonFactory->create()->setData($response);
            } else {
                $response = ['error' => true, 'message'=> __('You have entered a wrong code. Please try again.')];
                return $this->resultJsonFactory->create()->setData($response);
            }

        } if ($this->twoFactorHelper->sendOtpSource() === 'backupcode') {
            $connection  = $this->resource->getConnection();
            $collectiondata = $this->collectionFactory->create();
            $collectiondata->addFieldToFilter('email', $authCodeCredentials['email']);
            $collectiondata->addFieldToFilter('backupcode', $authCodeCredentials['otp']);
            $collectiondata->addFieldToFilter('active', 1);
            if ($collectiondata->getSize() > 0) {
                $data = ["active" => 0]; // Key_Value Pair
                $where = ['email = ?' => $authCodeCredentials['email'],'backupcode = ?' => $authCodeCredentials['otp']];
                $tableName = $connection->getTableName("wk_backupcode");
                $connection->update($tableName, $data, $where);
                $response = ['error' => false, 'message'=> __('Backup Code verified.')];
                return $this->resultJsonFactory->create()->setData($response);

            } else {
                $response = ['error' => true, 'message'=> __('You have entered a Backup code. Please try again.')];
                return $this->resultJsonFactory->create()->setData($response);
            }
            
        }
        $authCodeData = $this->twoFactorAuthRepository->getByEmail($authCodeCredentials['email']);
        if (is_array($authCodeData->getData())) {
            $authCodeCreatedTimestamp = strtotime($authCodeData->getCreatedAt());
            $currentTimestamp = time();
            $timeDiff = $currentTimestamp - $authCodeCreatedTimestamp;
            $authCodeExpiryTime = $this->twoFactorHelper->authCodeExpiry();
            if ($authCodeExpiryTime >= 60 && $authCodeExpiryTime <= 300) {
                $authCodeExpiryTime = $authCodeExpiryTime;
            } else {
                $authCodeExpiryTime = 60;
            }
            if ($timeDiff >= $authCodeExpiryTime) {
                $response = [
                    'error' => true,
                    'message'=> __('Auth Code expired. Please resend Auth Code and try again.')
                ];
                $this->customerHelper->processAuthenticationFailure($authCodeCredentials['email']);
                return $this->resultJsonFactory->create()->setData($response);
            }
            if ($authCodeData->getAuthCode() == $authCodeCredentials['otp']) {
                $authCodeData->setAuthCode(0);
                $this->twoFactorAuthRepository->save($authCodeData);
                $response = ['error' => false, 'message'=> __('Auth Code verified.')];
                return $this->resultJsonFactory->create()->setData($response);
            } else {
                $this->customerHelper->processAuthenticationFailure($authCodeCredentials['email']);
                $response = ['error' => true, 'message'=> __('You have entered a wrong code. Please try again.')];
                return $this->resultJsonFactory->create()->setData($response);
            }
        }
    }
}
