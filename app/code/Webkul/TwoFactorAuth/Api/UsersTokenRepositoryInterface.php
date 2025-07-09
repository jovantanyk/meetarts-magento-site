<?php
/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Api;

/**
 * @api
 */
interface UsersTokenRepositoryInterface
{
    /**
     * Get by token id
     *
     * @param  string $token
     * @return object
     */
    public function getByToken($token);
}
