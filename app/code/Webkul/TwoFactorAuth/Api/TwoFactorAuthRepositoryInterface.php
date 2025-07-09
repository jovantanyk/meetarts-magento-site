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
namespace Webkul\TwoFactorAuth\Api;

/**
 * @api
 */
interface TwoFactorAuthRepositoryInterface
{
    /**
     * Save Data.
     *
     * @param \Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface $data
     * @return \Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface $data);

    /**
     * Retrieve Data.
     *
     * @param string $customerEmail
     * @return \Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByEmail($customerEmail);

    /**
     * Delete Data.
     *
     * @param string $customerEmail
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByEmail($customerEmail);
}
