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

class BeforeCustDeleteObserver implements ObserverInterface
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
     * @var \Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface
     */
    protected $twoFactorRepositoryInterface;
    
    /**
     * Construct function
     *
     * @param RequestInterface $request
     * @param CustomerHelper $customerHelper
     * @param TwoFactorHelper $twoFactorHelper
     * @param \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorAuthFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface $twoFactorRepositoryInterface
     */
    public function __construct(
        RequestInterface $request,
        CustomerHelper $customerHelper,
        TwoFactorHelper $twoFactorHelper,
        \Webkul\TwoFactorAuth\Model\TwoFactorAuthFactory $twoFactorAuthFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface  $twoFactorRepositoryInterface
    ) {
        $this->twoFactorHelper = $twoFactorHelper;
        $this->customerHelper = $customerHelper;
        $this->twoFactorAuthFactory = $twoFactorAuthFactory;
        $this->date = $date;
        $this->request = $request;
        $this->twoFactorRepositoryInterface = $twoFactorRepositoryInterface;
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
                $twoFactorAuthFactory = $this->twoFactorAuthFactory->create()
                ->getCollection()
                ->addFieldToFilter("email", $customer->getEmail());
            if ($twoFactorAuthFactory->getSize()> 0) {
                $authData = $this->twoFactorRepositoryInterface->getByEmail($customer->getEmail());
                $authData->setVerified(0);
                 $authData->save($authData);
            }
           
        }
        return $this;
    }
}
