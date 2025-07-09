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
namespace Webkul\TwoFactorAuth\Api\Data;

interface TwoFactorAuthInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';

    public const EMAIL = 'email';

    public const CREATED_AT = 'created_at';

    public const AUTH_CODE = 'auth_code';

    public const VERIFIED = 'verified';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return int $id
     */
    public function setId($id);

    /**
     * Get Verified
     *
     * @return int|null
     */
    public function getVerified();

    /**
     * Set Verified
     *
     * @param int $flag
     * @return int $id
     */
    public function setVerified($flag);

    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set Email
     *
     * @param string $email
     * @return int $id
     */
    public function setEmail($email);

    /**
     * Set CreatedAt
     *
     * @param string $created_at
     * @return string date
     */
    public function setCreatedAt($created_at);

    /**
     * Get CreatedAt
     *
     * @return date
     */
    public function getCreatedAt();

    /**
     * Get AuthCode
     *
     * @return int|null
     */
    public function getAuthCode();

    /**
     * Set AuthCode
     *
     * @param int $code
     * @return int $code
     */
    public function setAuthCode($code);
}
