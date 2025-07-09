<?php
/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Api\Data;

interface UsersTokenInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    public const ID = 'entity_id';
    public const TOKEN = 'token';
    public const BROWSER = 'browser';
    public const CREATED_AT = 'created_at';

    /**
     * Get id
     *
     * @return string
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * Get token
     *
     * @return string
     */
    public function getToken();

    /**
     * Set token
     *
     * @param string $token
     */
    public function setToken($token);

    /**
     * Get browser
     *
     * @return string
     */
    public function getBrowser();

    /**
     * Set browser
     *
     * @param string $browser
     */
    public function setBrowser($browser);

    /**
     * Get created time
     *
     * @return timestamp
     */
    public function getcreatedAt();

    /**
     * Set created time
     *
     * @param timestamp $createdAt
     */
    public function setCreatedAt($createdAt);
}
