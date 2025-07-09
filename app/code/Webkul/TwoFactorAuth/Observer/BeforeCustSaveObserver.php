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

namespace Webkul\TwoFactorAuth\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Webkul\TwoFactorAuth\Helper\Customer as CustomerHelper;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorHelper;

class BeforeCustSaveObserver implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var TwoFactorHelper
     */
    private $twoFactorHelper;

    /**
     * @var TwoFactorAuthFactory
     */
    private $twoFactorAuthFactory;

     /**
      * @var \Magento\Framework\Stdlib\DateTime\DateTime
      */
    protected $date;
    
    /**
     * Construct function
     *
     * @param RequestInterface $request
     * @param CustomerHelper $customerHelper
     * @param TwoFactorHelper $twoFactorHelper
     * @param \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorAuthFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        RequestInterface $request,
        CustomerHelper $customerHelper,
        TwoFactorHelper $twoFactorHelper,
        \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorAuthFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->twoFactorHelper = $twoFactorHelper;
        $this->customerHelper = $customerHelper;
        $this->twoFactorAuthFactory = $twoFactorAuthFactory;
        $this->date = $date;
        $this->request = $request;
    }

    /**
     * Observer for adding phone number with customer details
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($this->twoFactorHelper->isModuleEnable()) {
            
            if ($this->twoFactorHelper->sendOtpSource() == 'backupcode') {
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
            }
            $callingCode = $this->request->getPostValue('region')
                ?: ($this->request->getPostValue('callingcode')
                    ? '+' . $this->request->getPostValue('callingcode')
                    : null);
            $phonenumber = $this->request->getPostValue('mobile_number');
            $phonenumberWithCallingCode = $callingCode && $phonenumber
                ? $this->customerHelper->getTelephoneWithCallingCode($callingCode, $phonenumber)
                : null;
            if ($phonenumberWithCallingCode) {
                $customer->setTwofactorauthPhoneNumber($phonenumberWithCallingCode);
            }
        }
        return $this;
    }
}
