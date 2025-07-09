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

namespace Webkul\TwoFactorAuth\Model\Attribute\Backend;

use Webkul\TwoFactorAuth\Helper\Customer as CustomerHelper;
use Webkul\TwoFactorAuth\Helper\Data as TwoFactorAuthHelper;

/**
 * Validate phone number for customer registration.
 */
class PhoneNumber extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var TwoFactorAuth
     */
    private $twoFactorAuth;

    /**
     * @param CustomerHelper $customerHelper
     * @param TwoFactorAuthHelper $twoFactorAuthHelper
     */
    public function __construct(
        CustomerHelper $customerHelper,
        TwoFactorAuthHelper $twoFactorAuthHelper
    ) {
        $this->twoFactorAuth = $twoFactorAuthHelper;
        $this->customerHelper = $customerHelper;
    }

    /**
     * Validate phone number of customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validate($customer)
    {
        if ($this->twoFactorAuth->isModuleEnable()) {
            $phoneNumber = $customer->getTwofactorauthPhoneNumber();
            $result = $this->customerHelper->validatePhonenumber($phoneNumber, $customer->getId());
            if ($result['errors']) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    $result['messages'][CustomerHelper::PHONENUMBER_INVALID_FORMAT]
                        ?? $result['messages'][CustomerHelper::PHONENUMBER_ALREADY_EXISTS]
                            ?? __('Invalid phone number.')
                );
            }
        }
        return true;
    }
}
