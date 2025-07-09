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
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;
use Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface;

class Validate extends Action
{
    /**
     * @var \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory
     */
    private $twoFactorFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJson;

    /**
     * @var TwoFactorAuthRepositoryInterface
     */
    private $twoFactorRepositoryInterface;

    /**
     * @param \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     * @param twoFactorRepositoryInterface $twoFactorRepositoryInterface
     * @param \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorFactory
     * @param \Webkul\TwoFactorAuth\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param TwoFactorAuthInterface $twoFactorInterface
     * @param Context $context
     */
    public function __construct(
        \Webkul\TwoFactorAuth\Helper\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface,
        \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorFactory,
        \Webkul\TwoFactorAuth\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        TwoFactorAuthInterface $twoFactorInterface,
        Context $context
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJson = $resultJson;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
        $this->twoFactorInterface = $twoFactorInterface;
        $this->twoFactorFactory = $twoFactorFactory;
        $this->_logger = $logger;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Execute function for validate the Otp from Customers
     *
     * @param none
     * @return mixed
     */
    public function execute()
    {
      
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $email = $this->getRequest()->getParam('email');
            $otp = $this->getRequest()->getParam('user_otp');
            $authData = $this->twoFactorRepositoryInterface->getByEmail($email);
            if (is_array($authData->getData())) {
                $otpCreatedTimestamp = strtotime($authData->getCreatedAt());
                $currentTimestamp = time();
                $timeDiff = $currentTimestamp - $otpCreatedTimestamp;
                $authExpiryTime = $this->helper->authCodeExpiry();
                if ($authExpiryTime >= 60 && $authExpiryTime <= 300) {
                    $authExpiryTime = $authExpiryTime;
                } else {
                    $authExpiryTime = 60;
                }
                if ($timeDiff >= $authExpiryTime) {
                    $result = ['error' => true,'message'=>__('Auth Code expired.Please resend Auth and try again.')];
                    return $this->resultJson->create()->setData($result);
                }
                if ($authData->getAuthCode() == $otp) {
                    $authData->setAuthCode(0);
                    $this->twoFactorRepositoryInterface->save($authData);
                    $result = ['error' => false,'message'=>__('Auth Code verified.')];
                    return $this->resultJson->create()->setData($result);
                } else {
                    $result = ['error' => true, 'message'=> __('You have entered a wrong code. Please try again.')];
                    return $this->resultJson->create()->setData($result);
                }
            }
            $result = ['error' => true,'message'=>__('Something Went Wrong.')];
            return $this->resultJson->create()->setData($result);
        } else {
            $result = ['error' => true,'message'=>__('Something Went Wrong.')];
            return $this->resultJson->create()->setData($result);
        }
    }
}
