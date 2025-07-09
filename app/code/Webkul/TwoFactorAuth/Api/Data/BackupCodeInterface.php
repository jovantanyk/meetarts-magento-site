<?php
/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Api\Data;

interface BackupCodeInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    public const ID = 'entity_id';
    public const EMAIL = 'email';
    public const BACKUPCODE = 'backupcode';
    public const ACTIVE = 'active';
    public const CREATED_AT = 'created_at';
    public const UPDATE_AT = 'updated_at';

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
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email);

    /**
     * Get backupcode
     *
     * @return string
     */
    public function getBackupcode();

    /**
     * Set backupcode
     *
     * @param string $backupcode
     */
    public function setBackupcode($backupcode);

    /**
     * Get active
     *
     * @return int
     */
    public function getActive();

    /**
     * Set active
     *
     * @param int $active
     */
    public function setActive($active);
    /**
     * Get created time
     *
     * @return timestamp
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt function
     *
     * @param timestamp $createdAT
     * @return timestamp
     */
    public function setCreatedAt($createdAT);

    /**
     * Get updated time
     *
     * @return timestamp
     */
    public function getUpdatedAt();

    /**
     * Set UpdatedAt function
     *
     * @param timestamp $updateAT
     * @return timestamp
     */
    public function setUpdatedAt($updateAT);
}
