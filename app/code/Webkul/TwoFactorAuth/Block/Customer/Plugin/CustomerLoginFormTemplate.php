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

namespace Webkul\TwoFactorAuth\Block\Customer\Plugin;

use Webkul\TwoFactorAuth\Helper\Data as TwoFactorHelper;

class CustomerLoginFormTemplate
{
    /**
     * @var TwoFactorHelper
     */
    private $twoFactorAuthHelper;

    /**
     * @param TwoFactorHelper $twoFactorAuthHelper
     */
    public function __construct(TwoFactorHelper $twoFactorAuthHelper)
    {
        $this->twoFactorAuthHelper = $twoFactorAuthHelper;
    }

    /**
     * Get Template based on module configuration
     *
     * @param \Magento\Customer\Block\Form\Login $subject
     * @param string $result
     * @return string
     */
    public function afterGetTemplate(
        \Magento\Customer\Block\Form\Login $subject,
        $result
    ) {
        return $this->twoFactorAuthHelper->isModuleEnable() ? 'Webkul_TwoFactorAuth::customer_login.phtml' : $result;
    }
}
