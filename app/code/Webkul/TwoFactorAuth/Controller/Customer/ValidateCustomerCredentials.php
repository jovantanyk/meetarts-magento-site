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
use Webkul\TwoFactorAuth\Helper\Customer as CustomerHelper;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorHelper;
use Webkul\TwoFactorAuth\Helper\FormKey\Validator as FormKeyValidator;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;
use Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory;

class ValidateCustomerCredentials extends Action
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
     * @var TwoFactorAuthFactory
     */
    private $twoFactorAuthFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    protected $twoFactorRepositoryInterface;

    /**
     * @param FormKeyValidator $formKeyValidator
     * @param ResultJsonFactory $resultJsonFactory
     * @param CustomerHelper $customerHelper
     * @param TwoFactorHelper $twoFactorHelper
     * @param JsonHelper $jsonHelper
     * @param TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
     * @param TwoFactorAuthFactory $twoFactorAuthFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Context $context
     */
    public function __construct(
        FormKeyValidator $formKeyValidator,
        ResultJsonFactory $resultJsonFactory,
        CustomerHelper $customerHelper,
        TwoFactorHelper $twoFactorHelper,
        JsonHelper $jsonHelper,
        TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface,
        TwoFactorAuthFactory $twoFactorAuthFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Context $context
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerHelper = $customerHelper;
        $this->twoFactorHelper = $twoFactorHelper;
        $this->jsonHelper = $jsonHelper;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
        $this->twoFactorAuthFactory = $twoFactorAuthFactory;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Execute function for validating the customer credentials.
     */
    public function execute()
    {
        $credentials = null;
        $response = [
            'error' => true,
            'message' => __('Bad Request.')
        ];
        try {
            $credentials = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
            if (!$credentials ||
                !$this->getRequest()->getMethod() === 'POST' ||
                !$this->getRequest()->isXmlHttpRequest() ||
                !$this->formKeyValidator->validate($this->getRequest())
            ) {
                throw new LocalizedException(__('Bad Request'));
            }
        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData($response);
        }

        $credentials = $this->formatCredentials($credentials);
        if (!isset($credentials['username'], $credentials['password'])) {
            return $this->resultJsonFactory->create()->setData($response);
        }
        $customerData = $this->customerHelper->getCustomerDataByCredentials(
            $credentials['username'],
            $credentials['password']
        );
        if (empty($customerData)) {
            $response = [
                'error' => true,
                'message' => __('Validation Failed.')
            ];
        } else {
            $response = [
                'error' => false,
                'message' => __('Validation Successfull'),
            ];
            $customer = $customerData['customer'];
            $twoFactorAuthFactory = $this->twoFactorAuthFactory->create()
                                         ->getCollection()
                                         ->addFieldToFilter("email", $customer->getEmail());
            if ($twoFactorAuthFactory->getSize() <= 0) {
                $date = $this->date->gmtDate();
                $twoFactorAuthFactory = $this->twoFactorAuthFactory->create();
                $twoFactorAuthFactory->setEmail($customer->getEmail());
                $twoFactorAuthFactory->setCreatedAt($date);
                $twoFactorAuthFactory->setAuthCode(0);
                $twoFactorAuthFactory->setVerified(1);
                $twoFactorAuthFactory->save();
            }
            $authData = $this->twoFactorRepositoryInterface->getByEmail($customer->getEmail());
            $needToSendAuthCode = 0;
            if ($authData->getVerified() == 1) {
                $needToSendAuthCode = 1;
            }
            $response['data']['needToSendAuthCode'] = $needToSendAuthCode;
            $customerAddress = $customerData['customerAddress'];
            $response['data']['medium'] = $customerData['medium'];
            $response['data']['email'] = $customer->getEmail();
            $response['data']['firstname'] = $customer->getFirstname();
            $response['data']['customerData'] = $customer->getData();
            if ($customerAddress) {
                $response['data']['telephone'] = $customerAddress->getTelephone();
                $callingCode = '+'.$this->twoFactorHelper->getCallingCodeByCountryCode(
                    $customerAddress->getCountryId()
                );
                $response['data']['callingCode'] = $callingCode;
                $response['data']['telehoneWithCountryCode'] = $this->customerHelper
                    ->getTelephoneWithCallingCode(
                        $callingCode,
                        $customerAddress->getTelephone()
                    );
                $response['data']['countryId'] = $customerAddress->getCountryId();
                $response['data']['customerAddressData'] = $customerAddress->getData();
            }
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * Format Credentials
     *
     * @param array $credentials
     * @return array
     */
    private function formatCredentials($credentials): array
    {
        if (isset($credentials['username'], $credentials['password'], $credentials['form_key'])) {
            return $credentials;
        }
        $formattedCredentials = [];
        foreach ($credentials as $credential) {
            if (isset($credential['name'], $credential['value'])) {
                $formattedCredentials[$credential['name']] = $credential['value'];
            }
        }
        return empty($formattedCredentials) ? $credentials : $formattedCredentials;
    }
}
