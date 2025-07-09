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
namespace Webkul\TwoFactorAuth\Block;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;

class EditAccountForm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Customer\Model\SessionFactory $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerModel = $customerModel;
        $this->_customerSession = $customerSession->create();
    }

    /**
     * Get customer phone number
     *
     * @return boolean
     */
    public function getCustomerPhoneNumber()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customerCollection = $this->_customerModel->create()->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter("entity_id", ['eq' => $this->_customerSession->getId()])
            ->load();
            if (!($customerCollection->getSize() < 1)) {
                foreach ($customerCollection as $customerData) {
                    return $customerData->getTwofactorauthPhoneNumber();
                }
            }
        }
        return false;
    }
}
