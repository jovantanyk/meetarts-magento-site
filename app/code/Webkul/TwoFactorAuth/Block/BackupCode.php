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

namespace Webkul\TwoFactorAuth\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Webkul\TwoFactorAuth\Helper\Data;

class BackupCode extends Template
{
   /**
    * Constructor function
    *
    * @param Context $context
    * @param Session $customerSession
    * @param CustomerRepositoryInterface $customerRepository
    * @param Data $helper
    * @param \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory
    * @param array $data
    */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        Data $helper,
        \Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Set Title for twofactor auth verification
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('TwoFactor Auth Verification'));
    }

    /**
     * Return the Customer given the customer Id stored in the session.
     *
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    /**
     * Get Backup Code
     *
     * @return array
     */
    public function getcollection()
    {
        $customerEmail = $this->customerRepository->getById($this->customerSession->getCustomerId())->getEmail();
        $collectiondata = $this->collectionFactory->create();
        $collectiondata->addFieldToFilter('email', $customerEmail);
        $collectiondata->addFieldToFilter('active', 1);
        return $collectiondata;
    }
}
