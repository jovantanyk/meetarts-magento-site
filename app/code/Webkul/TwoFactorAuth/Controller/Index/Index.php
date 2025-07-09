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

namespace Webkul\TwoFactorAuth\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Url\DecoderInterface;
use Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken\CollectionFactory;

class Index extends Action
{
    public const XML_PATH_OTP_EMAIL_CHECKOUT = 'twofactorauth/emailsettings/otp_checkout_notification';
    private const CHROME = 'Chrome';

    private const FIREFOX = 'Firefox';

    /**
     * @var \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJson;

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    private $twoFactorRepositoryInterface;

    /**
     * @var TwoFactorAuthInterface
     */
    private $twoFactorInterface;

    /**
     * @var TwoFactorAuthHelper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\TwoFactorAuth\Helper\Customer
     */
    private $customerHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

     /**
      * @var \Magento\Framework\Mail\Template\TransportBuilder
      */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $template;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var DecoderInterface
     */
    private $urlDecoder;

      /**
       * @var CollectionFactory
       */
    protected $_collectionFactory;

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
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\TwoFactorAuth\Helper\Data $helper
     * @param \Webkul\TwoFactorAuth\Helper\Customer $customerHelper
     * @param TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
     * @param TwoFactorAuthInterface $twoFactorInterface
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param EncoderInterface $urlEncoder
     * @param DecoderInterface $urlDecoder
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\TwoFactorAuth\Helper\Data $helper,
        \Webkul\TwoFactorAuth\Helper\Customer $customerHelper,
        TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface,
        TwoFactorAuthInterface $twoFactorInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $url,
        EncoderInterface $urlEncoder,
        DecoderInterface $urlDecoder,
        CollectionFactory $collectionFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Webkul\TwoFactorAuth\Helper\TOTPAuth $totpauth,
        Context $context
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJson = $resultJson;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerHelper = $customerHelper;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
        $this->twoFactorInterface = $twoFactorInterface;
        $this->date = $date;
        $this->jsonHelper = $jsonHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        $this->_collectionFactory = $collectionFactory;
        $this->curl = $curl;
        $this->totpauth = $totpauth;
        parent::__construct($context);
    }

    /**
     * Function execute for Controller
     *
     * @return Json result
     */
    public function execute()
    {
        if ($this->formKeyValidator->validate($this->getRequest()) && $this->helper->isModuleEnable()) {
            
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $requestData = $this->getRequest()->getParams()
                ?: $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
            $name = $requestData['name'] ?? null;
            $email = $requestData['email'] ?? null;
            $resend = $requestData['resend'] ?? null;
            $mobile = $requestData['mobile'] ?? null;
            $regionId = $requestData['region'] ?? null;
            $mobileAuthLogin = $requestData['mobileAuth'] ?? null;
            if ($mobileAuthLogin) {
                $customer = $this->customerRepository->get($email);
                $customer->setCustomAttribute('twofactorauth_phone_number', $mobileAuthLogin);
                $this->customerRepository->save($customer);
            }
            $shouldCheckExistingAccount = isset($requestData['shouldCheckExistingAccount'])
                ? (int)$requestData['shouldCheckExistingAccount'] : 1;
            $password = rand(100000, 999999);
            $collection = $this->twoFactorRepositoryInterface->getByEmail($email);
            $date = $this->date->gmtDate();
            if (!empty($mobile)) {
                $mobile = str_replace(" ", "", $mobile);
            }
            $callingCode = empty($regionId)
                ? ''
                : ('+'. $this->helper->getCallingCodeByCountryCode($regionId));
            $mobile = !empty($mobile) && !empty($callingCode) &&
                        substr($mobile, 0, 1) !== '+'
                            ? $callingCode . $mobile
                            : $mobile;
            if (!$this->customerSession->getCustomer()->getGroupId()) {
                if (empty($email)) {
                    $email = $this->customerSession->getCustomer()->getEmail();
                }
                if (empty($mobile)) {
                    $regionId = $this->customerSession->getCustomer()->getPrimaryBillingAddress()->getCountryId();
                    $callingCode = '+'.$this->helper->getCallingCodeByCountryCode($regionId);
                    $mobile = $callingCode . $this->customerSession
                                    ->getCustomer()->getPrimaryBillingAddress()
                                    ->getTelephone();
                }
            }
            if (empty($mobile)) {
                $mobile = $this->customerHelper->getCustomerPhoneNumberByEmail($email);
            }
            if ($mobileAuthLogin) {
                $mobile = $mobileAuthLogin;
            }
            if (is_array($collection->getData())) {
                $collection->setEmail($email);
                $collection->setAuthCode($password);
                if ($this->helper->sendOtpSource() == 'backupcode') {
                    $collection->setVerified(1);
                }
                $collection->setCreatedAt($date);
                $collection->save($collection);
            } else {
                $this->twoFactorInterface->setEmail($email);
                $this->twoFactorInterface->setAuthCode($password);
                if ($this->helper->sendOtpSource() == 'backupcode') {
                    $this->twoFactorInterface->setVerified(1);
                }
                $this->twoFactorRepositoryInterface->save($this->twoFactorInterface);
            }

            $isMobileOtpEnabled = $this->helper->isTwilioAuthEnabled();
            $sendOtpSource = $this->helper->sendOtpSource();
            if ($sendOtpSource == 'mobile' && $isMobileOtpEnabled) {
                 $response = $this->sendOTPToPhone($mobile, $password);
                $otpMedium = __('Mobile Number');
            } elseif ($sendOtpSource == 'email' && $isMobileOtpEnabled) {
                $response = $this->sendOTPToEmail($email, $name, $password, $mobile);
                $otpMedium = __('Email ID');
            } elseif ($sendOtpSource == 'emaillink' && $isMobileOtpEnabled) {
                $response = $this->sendEmailLink($email, $name, $password, $mobile, $collection);
                $otpMedium = __('Email Link');
            } elseif ($sendOtpSource == 'pushnotify' && $isMobileOtpEnabled) {
                $response = $this->pushnotification($password);
                $otpMedium = __('Push Notification');
            } elseif ($sendOtpSource == 'totp' && $isMobileOtpEnabled) {
                $response = $this->totpauthenticator();
                $otpMedium = __('TOTP/Authenticator');
            } elseif ($sendOtpSource == 'backupcode' && $isMobileOtpEnabled) {
                $response = $this->backupcode();
                $otpMedium = __('Backup Code');
            }
            if ($response['error']) {
                $response['additional_info'] = $response['message'];
                $response['message'] = __('Unable to send Auth Code. Please try again later.');
                return $this->resultJson->create()->setData($response);
            } else {
                if ($sendOtpSource == 'backupcode') {
                    $successMessage = __("Please Enter the %1", $otpMedium);
                } elseif ($sendOtpSource == 'totp') {
                    $successMessage = __("Please Scan the Qr Code to get the Auth Code %1", $otpMedium);
                } else {
                    $successMessage = $resend
                    ? __("A new Auth Code has been sent to your registered %1. Please enter the Auth Code.", $otpMedium)
                    : __("Please Enter the Auth Code sent to your registered %1", $otpMedium);
                }
                $result = ['error' => false, 'message' => $successMessage];
                return $this->resultJson->create()->setData($result);
            }
        } else {
            $this->messageManager->addError(__("Something Went Wrong."));
            $result = ['error' => true, 'message'=>__("Something Went Wrong."), 'errorCode'=>"exception" ];
            return $this->resultJson->create()->setData($result);
        }
    }

    /**
     * Function to send One time password on Mobile
     *
     * @param string $mobile
     * @param integer $password
     * @return array
     */
    private function sendOTPToPhone($mobile, $password)
    {
        try {
            $reciever = $mobile;
            $message = $this->helper->getTwilioConfigMessage();
            if (!empty($message)) {
                $message = str_replace('{auth_code}', $password, $message);
                $client = $this->helper->makeTwilloClient();
               
                $result = $this->helper->sendMessage($client, $reciever, $message);
            }
        } catch (\Exception $e) {
            $result = ['error' => true, 'message'=>$e->getMessage()];
        }

        return $result;
    }

    /**
     * Function to send One time password on email
     *
     * @param string $emailid
     * @param string $name
     * @param integer $password
     * @param integer $mobile
     * @return array
     */
    private function sendOTPToEmail($emailid, $name, $password, $mobile)
    {
        $client = $this->helper->makeTwilloClient();
        $email = new \SendGrid\Mail\Mail();
        $time_to_expire = $this->helper->getOtpTimeToExpireString();
        $sendermailid = $this->helper->getSenderMailID();
        $sendgridid = $this->helper->getSendGridId();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $senderName = $this->scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
        $email->setFrom($sendermailid, $senderName);
        $email->setSubject("Twofactorauth verification Security Code");
        $email->addTo($emailid, $name);
        $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
        $email->addContent(
            "text/html",
            '<table cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" valign="top" style="padding:20px 0 20px 0">
                    <!-- [ header starts here] -->
                    <table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" 
                    width="650" style="border:1px solid #E0E0E0;">
                        <tr>
                            <td valign="top">
                                <h3 style="font-size:22px; font-weight:normal; 
                                line-height:22px; margin:0 0 11px 0;">Hello '.$name.',</h3>
                                <p>Your one time verification code is <b>password</b> -'.$password.'</p>
                                <p>Enter the verification code to complete the process.</p>
                                <p>The OTP is valid for <span >'.$time_to_expire.'</span>.</p>
                                <p>Thank You</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>'
        );
        $sendgrid = new \SendGrid($sendgridid);
        try {
            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202 || $response->statusCode() === 200) {
                return [
                    'error' => false,
                    'message' =>
                     __("Message has been sent successfully.") .
                     __("But Unable to verify status with Twilio Server"),
                ];
            }
        } catch (Exception $e) {
            $result = ['error' => true, 'message'=>$e->getMessage()];
        }
    }

      /**
       * Function to send verify link to email
       *
       * @param string $emailid
       * @param string $name
       * @param integer $password
       * @param integer $mobile
       * @param array $collection
       * @return array
       */
    private function sendEmailLink($emailid, $name, $password, $mobile, $collection)
    {
        $client = $this->helper->makeTwilloClient();
        $email = new \SendGrid\Mail\Mail();
        $time_to_expire = $this->helper->getOtpTimeToExpireString();
        $sendermailid = $this->helper->getSenderMailID();
        $sendgridid = $this->helper->getSendGridId();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope);
        $senderName = $this->scopeConfig->getValue('trans_email/ident_support/name', $storeScope);
        $email->setFrom($sendermailid, $senderName);
        $email->setSubject("Twofactorauth verification Security Code");
        $email->addTo($emailid, $name);
        $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
        $email->addContent(
            "text/html",
            '<table cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" valign="top" style="padding:20px 0 20px 0">
                    <!-- [ header starts here] -->
                    <table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" 
                    width="650" style="border:1px solid #E0E0E0;">
                        <tr>
                            <td valign="top">
                                <h3 style="font-size:22px; font-weight:normal; 
                                line-height:22px; margin:0 0 11px 0;">Hello '.$name.',</h3>
                                <p>Your verification link is here : 
                                <a href='.$this->url->getUrl('twofactorauth/customer/varifyclient/email/'.
                                $this->urlEncoder->encode($emailid).'', ['_secure' => true]).'>Click To Verify</a>'.
                                ' </p>
                                <p>Click on the verification link and verify the your account</p>
                                <p>The link expire in <span >'.$time_to_expire.'</span>.</p>
                                <p>Thank You</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>'
        );
        $sendgrid = new \SendGrid($sendgridid);
        try {
            $response = $sendgrid->send($email);
            if ($response->statusCode() === 202 || $response->statusCode() === 200) {
                $collection->setEmail($emailid);
                $collection->setAuthCode(0);
                $collection->setVerified(0);
                $collection->setCreatedAt($this->date->gmtDate());
                $collection->save($collection);
                return [
                    'error' => false,
                    'message' =>
                     __("Message has been sent successfully.") .
                     __("But Unable to verify status with Twilio Server"),
                ];
            }
        } catch (Exception $e) {
            $result = ['error' => true, 'message'=>$e->getMessage()];
        }
    }
    
    /**
     * Push notification Function
     *
     * @param int $password
     */
    private function pushnotification($password)
    {
        
        $collection = $this->_collectionFactory->create();
        $collection->setOrder(
            'created_at',
            'desc'
        );
        $notificationData = [];
        $notification = [];
        $notificationData['title'] = __("One time verification code");
        $notificationData['body'] = __("Your one time verification code is password -").$password;
        $notificationData['actions'][0]['action'] = $this->storeManager->getStore()->getBaseUrl();
        $notificationData['actions'][0]['title'] =__("One time verification code");
        $notificationData['icon'] = '';
        $notification['notification'] = $notificationData;
        
        foreach ($collection as $key => $user) {
           
            if ($user->getBrowser() == self::CHROME) {
                $this->helper->sendToChrome($user->getToken(), $notification);
                return [
                    'error' => false,
                    'message' =>__("Message has been sent successfully"),
                ];
            } elseif ($user->getBrowser() == self::FIREFOX) {
                $this->helper->sendToFirefox($user->getToken(), $notification);
                return [
                    'error' => false,
                    'message' =>__("Message has been sent successfully"),
                ];
            }
        }
    }

    /**
     * Backup code function
     *
     * @return array
     */
    private function backupcode()
    {
        return [
            'error' => false,
            'message' =>__("Message has been sent successfully"),
        ];
    }

    /**
     * Totp Authenticator Function
     */
    private function totpauthenticator()
    {
        $qrCode = $this->totpauth->getQR();
        return [
            'error' => false,
            'message' =>__("Message has been sent successfully"),
        ];
    }
      /**
       * [generateTemplate description].
       *
       * @param Mixed $emailTemplateVariables
       * @param Mixed $senderInfo
       * @param Mixed $receiverInfo
       */
    private function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->transportBuilder
            ->setTemplateIdentifier($this->template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

      /**
       * Return template id.
       *
       * @param string $xmlPath
       *
       * @return mixed
       */
    private function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int $storeId
     *
     * @return mixed
     */
    private function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return object
     */
    private function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Function to check if an already exists with mobile number provided by customer
     *
     * @param string $mobile
     *
     * @return bool
     */
    private function checkExistingAccount($mobile)
    {
        $accountExists = $this->customerFactory->create()->getCollection()
            ->addFieldToFilter('twofactorauth_phone_number', ['eq'=>$mobile])
            ->getSize();
        if ($accountExists > 0) {
            return true;
        } else {
            return false;
        }
    }
}
