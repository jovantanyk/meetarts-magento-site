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

namespace Webkul\TwoFactorAuth\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Url;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Model\Config;
use Twilio\Rest\Client;
use Magento\Framework\Exception\FileSystemException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;

/**
 * Twofactorauth data helper
 */
class Data extends AbstractHelper
{
    public const XML_PATH_MODULE_ENABLE = 'twofactorauth/enable/twofactorauth_enable';
    public const XML_PATH_MODULE_ENABLE_REGISTRATION = 'twofactorauth/enable/twofactorauth_enable_registration';
    public const TWILIO_ENABLED = 'twofactorauth/twiliosettings/twillo_auth_enabled';
    public const TWILIO_AUTH_CODE_MESSAGE = 'twofactorauth/twiliosettings/message';
    public const TWILLO_AUTH_ID = 'twofactorauth/twiliosettings/authId';
    public const TWILLO_TOKEN = 'twofactorauth/twiliosettings/token';
    public const SENDER_NUMBER = 'twofactorauth/twiliosettings/number';
    public const AUTH_CODE_EXPIRY = 'twofactorauth/enable/expiry';
    public const SEND_SMS_SOURCE = 'twofactorauth/enable/send_otp_source';
    public const SENDER_MAIL = 'twofactorauth/enable/sender_mail';
    public const SENDGRID_KEY = 'twofactorauth/enable/sendgrid_key';
    private const SEND_URL_CHROME = 'https://fcm.googleapis.com/fcm/send';
    private const SEND_URL_FIREFOX = 'https://updates.push.services.mozilla.com/wpush/v1/';

    /**
     * @var Magento\Framework\Module\Dir\Reader
     */
    protected $_baseDirectory;
    /**
     * @var Countries
     */
    protected $countriesHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $enc;

    /**
     * @var \Magento\Framework\Filesystem\Driver\Http
     */
    protected $driver;

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlModel;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerAddressDataProvider
     */
    protected $customerAddressData;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $storeScope;

    /**
     * @var Country
     */
    protected $countries;

    /**
     *
     * @var \Magento\Framework\App\RequestInterface $httpRequest
     */
    protected $request;

    /**
     *
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Webkul\TwoFactorAuth\Helper\TOTPAuth
     */
    protected $totpauth;

    /**
     * Constructor function
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $enc
     * @param \Magento\Framework\Filesystem\Driver\Http $driver
     * @param \Magento\Framework\Url $urlModel
     * @param AssetRepository $assetRepository
     * @param JsonHelper $jsonHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerAddressDataProvider $customerAddressData
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param Countries $countriesHelper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $enc,
        \Magento\Framework\Filesystem\Driver\Http $driver,
        \Magento\Framework\Url $urlModel,
        AssetRepository $assetRepository,
        JsonHelper $jsonHelper,
        CustomerRepositoryInterface $customerRepository,
        CustomerAddressDataProvider $customerAddressData,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\App\RequestInterface $httpRequest,
        Countries $countriesHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->enc = $enc;
        $this->driver = $driver;
        $this->urlModel = $urlModel;
        $this->assetRepository = $assetRepository;
        $this->jsonHelper = $jsonHelper;
        $this->customerRepository = $customerRepository;
        $this->customerAddressData = $customerAddressData;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_baseDirectory = $moduleReader;
        $this->request = $httpRequest;
        $this->countriesHelper = $countriesHelper;
        $this->curl = $curl;
        $this->totpauth = $totpauth;
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * GetSecureUrl
     *
     * @return string
     */
    public function getSecureUrl()
    {
        return $this->scopeConfig->getValue(
            'web/secure/base_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

     /**
      * Get Sender Id
      *
      * @return string
      */
    public function getSenderId()
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'twofactorauth/enable/application_sender_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

        /**
         * Get Server Key
         *
         * @return string
         */
    public function getServerKey()
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'twofactorauth/enable/application_server_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Get Public Key
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'twofactorauth/enable/application_public_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * Server Detail
     *
     * @return string
     */
    public function serverDetail()
    {
        $domain = $this->request->getServer('HTTP_USER_AGENT');
        return $domain;
    }

     /**
      * GetFCMConfigEncrypted
      *
      * @param string $value
      * @return string
      */
    public function getFCMConfigEncrypted($value)
    {
        return $this->enc->decrypt($this->scopeConfig->getValue(
            'twofactorauth/enable/'.$value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

      /**
       * GetFCMConfig
       *
       * @param string $value
       * @return string
       */
    public function getFCMConfig($value)
    {
        return $this->scopeConfig->getValue(
            'twofactorauth/enable/'.$value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to return status of Module
     *
     * @return bool
     */
    public function isModuleEnable()
    {
        return $this->getConfigValue(self::XML_PATH_MODULE_ENABLE, $this->getStore()->getStoreId());
    }

    /**
     * Function to return status of otp validation at Registration
     *
     * @return bool
     */
    public function isEnableAtRegistration()
    {
        return $this->getConfigValue(self::XML_PATH_MODULE_ENABLE_REGISTRATION, $this->getStore()->getStoreId());
    }

    /**
     * Return store.
     *
     * @return object
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Enable TwoFactorAuth config
     *
     * @return bool
     */
    public function isTwilioAuthEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_MODULE_ENABLE,
            $this->storeScope
        );
    }

    /**
     * Function to get auth code expiry time.
     *
     * @return int
     */
    public function authCodeExpiry()
    {
        return $this->getConfigValue(
            self::AUTH_CODE_EXPIRY,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * TwoFactorAuth Config Message
     *
     * @return string
     */
    public function getTwilioConfigMessage()
    {
        return $this->scopeConfig->getValue(self::TWILIO_AUTH_CODE_MESSAGE, $this->storeScope);
    }

    /**
     * Make Twilio Client
     *
     * @return array|Twilio\Rest\Client
     */
    public function makeTwilloClient()
    {
        try {
            $sid = $this->scopeConfig->getValue(self::TWILLO_AUTH_ID, $this->storeScope);
            $token = $this->scopeConfig->getValue(self::TWILLO_TOKEN, $this->storeScope);
            $client = new Client($this->enc->decrypt($sid), $this->enc->decrypt($token));
            return $client;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = substr($message, (strpos($message, ":") ?: -2)+2);
            $result = ['error' => true,'message'=>$message];
            return $result;
        }
    }

    /**
     * Get countries with calling codes and name
     *
     * @param boolean $refresh
     * @return array
     */
    public function getCountries($refresh = false): array
    {
        if (empty($this->countries) || $refresh) {
            try {
                $countriesData = $this->countriesHelper->getCountries();
                $countriesData = $countriesData['countries'];
            } catch (\Exception $exception) {
                $countriesData = [];
            }
            $countries = array_map(
                function ($country) {
                    return [
                        'name' => $country['name'],
                        'callingCode' => str_replace(" ", "", $country['code'])
                    ];
                },
                $countriesData
            );
            $this->countries = $countries
                ? array_filter(
                    $countries,
                    function ($country) {
                        return $country['callingCode'] !== "";
                    }
                )
                : $this->countries;
        }
        return $this->countries;
    }

    /**
     * Get calling code by country code
     *
     * @param string $countryCode
     * @return string
     */
    public function getCallingCodeByCountryCode($countryCode): string
    {
        try {
            $countriesData = $this->countriesHelper->getCallingCode($countryCode);
            return $countriesData;
        } catch (\Exception $exception) {
            $callingCodeArray = [];
        }
        return isset($callingCodeArray['callingCodes']) ? $callingCodeArray['callingCodes'][0] : [];
    }

    /**
     * Send notification message
     *
     * @param object $client
     * @param string $receiver
     * @param string $message
     * @return array
     */
    public function sendMessage($client, $receiver, $message = null)
    {
        if (empty($message)) {
            $message = $this->scopeConfig->getValue(self::TWILIO_OTP_MESSAGE, $this->storeScope);
        }
        try {
            $messageInstance = $client->messages->create(
                $receiver,
                ['from' =>  $this->scopeConfig->getValue(self::SENDER_NUMBER, $this->storeScope), 'body' => $message]
            );
            if (!empty($messageInstance->errorCode) || !empty($messageInstance->errorMessage) ||
                (!empty($messageInstance->status) && in_array($messageInstance->status, ['failed', 'undeliveried']))
            ) {
                return ['error' => true, 'message' => $messageInstance->errorMessage];
            }

            return [
                'error' => false,
                'message' => !empty($messageInstance->status)
                ? ("Status: " . $messageInstance->status)
                : "Message has been sent successfully." .
                    "But Unable to vefify status with Twilio Server",
            ];
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = substr($message, (strpos($message, ":") ?: -2)+2);
            $result = ['error' => true,'message'=>$message];
            return $result;
        }
    }

    /**
     * Retrieve customer data
     *
     * @param int $id
     * @return array $customerData
     */
    public function getCustomerData($id = null): array
    {
        // dd($id);
        // @TODO: Move this to Customer helper
        $customerData = [];
        if (empty($id)) {
            return [];
        }
      
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerRepository->getById($id);
        $customerData = $customer->__toArray();
        $customerData['addresses'] = $this->customerAddressData->getAddressDataByCustomer($customer);

        return $customerData;
    }

    /**
     * Function to get OTP modal configuration
     *
     * @return array
     */
    public function getOtpModalConfig(): array
    {
        $twofactorauthAction = $this->urlModel->getUrl('twofactorauth');
        $twofactorauthValidateAction = $this->urlModel->getUrl('twofactorauth/index/validate');
        $authCodeTimeToExpireString = $this->authCodeExpiry();
        $authCodeModalConfig = [
            'isModuleEnabled' => $this->isModuleEnable(),
            'isOtpSource' => $this->sendOtpSource(),
            'qrcode' => $this->totpauthenticator(),
            'resendText' => __("Resend Auth Code"),
            'validateNumberError' => __("Please enter a valid number."),
            'otpAction' => $twofactorauthAction,
            'otpValidateAction' => $twofactorauthValidateAction,
            'submitButtonText' => __("Submit"),
            'authCodeTimeToExpireString' =>  __($authCodeTimeToExpireString),
            'isMobileOtpEnabled' => $this->isTwilioAuthEnabled(),
            'loaderUrl' =>
                $this->assetRepository->createAsset('Webkul_TwoFactorAuth::images/ajax-loader.gif')->getUrl(),
            'customerData' => $this->getCustomerData(),
            'otpTimeToExpireMessage' => $this->sendOtpSource() === 'backupcode'
            ? __('Once you use a backup code to sign in, that code will become inactive.')
            :__("Your Auth Code will expire in $authCodeTimeToExpireString."),
            'otpInputPlaceholder' => $this->sendOtpSource() === 'backupcode' ? __('Enter the Backup Code here')
                :__('Enter the Auth Code here'),
            'telephoneInputPlaceholder' => __('Telephone number with country code'),
            'modalTitle' => __('Auth Code Verification'),
            'validateCustomerCredentialsUrl' =>
            $this->urlModel->getUrl('twofactorauth/customer/validatecustomercredentials'),
            'validateCustomerOtpUrl' => $this->urlModel->getUrl('twofactorauth/customer/validatecustomerauthcode'),
        ];

        return $authCodeModalConfig;
    }

     /**
      * Send the Source of OTP
      *
      * @return string
      */
    public function sendOtpSource()
    {
        return $this->getConfigValue(self::SEND_SMS_SOURCE, $this->getStore()->getStoreId());
    }

      /**
       * Get Send Grid Id
       *
       * @return string
       */
    public function getSendGridId()
    {
        return $this->getConfigValue(self::SENDGRID_KEY, $this->getStore()->getStoreId());
    }

       /**
        * Get Sender Mail ID
        *
        * @return string
        */
    public function getSenderMailID()
    {
        return $this->getConfigValue(self::SENDER_MAIL, $this->getStore()->getStoreId());
    }

    /**
     * Returns a formatted string representation of OPT expiry time
     *
     * @return string
     */
    public function getOtpTimeToExpireString(): string
    {
        $timeToExpireInSeconds = $this->authCodeExpiry();
        $timeToExpireInSeconds = $timeToExpireInSeconds < 60 || $timeToExpireInSeconds > 300
        ? 60 : $timeToExpireInSeconds;
        $timeToExpireMinutes = floor(($timeToExpireInSeconds / 60));
        $timeToExpireSeconds = $timeToExpireInSeconds % 60;
        $timeToExpireMinutesString = $timeToExpireMinutes > 0
        ? "$timeToExpireMinutes minute" . ($timeToExpireMinutes > 1 ? 's' : '')
        : '';
        $timeToExpireSecondsString = $timeToExpireSeconds > 0
        ? "$timeToExpireSeconds second" . ($timeToExpireSeconds > 1 ? 's' : '')
        : '';
        $timeToExpireString = join(
            " and ",
            array_filter(
                [$timeToExpireMinutesString, $timeToExpireSecondsString],
                function ($value) {
                    return !empty($value);
                }
            )
        );

        return $timeToExpireString;
    }

    /**
            * Get notificationmessage for chrome browser.
            *
            * @param int $registrationId
            * @param string $notification
            * @throws \Magento\Framework\Exception\LocalizedException
            */
        public function sendToChrome($registrationId, $notification)
        {
            $url = 'https://fcm.googleapis.com/fcm/send';
            $fields = [
                'data' => $notification,
                'to' => $registrationId,
            ];
            $headers = [
                'Authorization'=> 'key='.$this->getServerKey(),
                'Content-Type'=> 'application/json',
            ];
            $this->curl->get($url);
            $this->curl->setHeaders($headers);
            $this->curl->post($url, json_encode($fields));
            $this->curl->setOption(CURLOPT_POST, true);
            $response = $this->curl->getBody();
            return $response;
        }

      /**
       * Send to firefox
       *
       * @param  array $userIds
       * @return object
       */
    public function sendToFirefox($userIds)
    {
        try {
            $fields = "";
            $url = self::SEND_URL_FIREFOX;
            $headers = [
                'Content-Type' => 'application/json',
                'TTL' => '600000',
            ];
            $this->curl->setHeaders($headers);
            foreach ($userIds as $key => $value) {
                $finalUrl=$url.$value;
                $result = $this->curl->post($finalUrl, $fields);
                $this->_logger->info(' firefox Result ');
                $this->_logger->info(json_encode($result));
            }
            return $result;
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     * Totp Authenticator function
     *
     * @return string
     */
    public function totpauthenticator()
    {
        $qrCode = $this->totpauth->getQR();
        return $qrCode;
    }
}
