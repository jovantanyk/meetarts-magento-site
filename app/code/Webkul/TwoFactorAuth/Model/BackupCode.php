<?php
/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Model;

use Webkul\TwoFactorAuth\Api\Data\BackupCodeInterface;
use Magento\Framework\DataObject\IdentityInterface;

class BackupCode extends \Magento\Framework\Model\AbstractModel implements BackupCodeInterface
{
    /**
     * CMS page cache tag.
     */
    private const CACHE_TAG = 'wk_backupcode';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_backupcode';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_backupcode';

    /**
     *
     * @var resourceModel
     */
    protected $_resourceModel;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\TwoFactorAuth\Model\ResourceModel\BackupCode::class);
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get backupcode
     *
     * @return string
     */
    public function getBackupcode()
    {
        return $this->getData(self::BACKUPCODE);
    }

    /**
     * Set backupcode
     *
     * @param string $backupcode
     */
    public function setBackupcode($backupcode)
    {
        return $this->setData(self::BACKUPCODE, $backupcode);
    }

     /**
      * Get active
      *
      * @return string
      */
    public function getActive()
    {
        return $this->getData(self::ACTIVE);
    }

    /**
     * Set active
     *
     * @param string $active
     */
    public function setActive($active)
    {
        return $this->setData(self::ACTIVE, $active);
    }

    /**
     * Get created time
     *
     * @return timestamp
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created time
     *
     * @param timestamp $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated time
     *
     * @return timestamp
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATE_AT);
    }

    /**
     * Set updated time
     *
     * @param timestamp $updateAT
     * @return timestamp
     */
    public function setUpdatedAt($updateAT)
    {
        return $this->setData(self::UPDATE_AT, $updateAT);
    }
}
