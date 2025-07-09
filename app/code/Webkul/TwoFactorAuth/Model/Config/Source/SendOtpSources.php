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
    
namespace Webkul\TwoFactorAuth\Model\Config\Source;

class SendOtpSources implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * ToOptionArray()
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'mobile', 'label' => __('Mobile')],
            ['value' => 'email', 'label' => __('Email')],
            ['value' => 'emaillink', 'label' => __('Sending Email Link')],
            ['value' => 'pushnotify', 'label' => __('Push Notification')],
            ['value' => 'totp', 'label' => __('TOTP/Authenticator')],
            ['value' => 'backupcode', 'label' => __('Backup Code')],
        ];
    }
}
