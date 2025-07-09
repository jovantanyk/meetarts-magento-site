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
namespace Webkul\TwoFactorAuth\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface;

/**
 * TwoFactorAuth Model Class Of TwoFactorAuth Module
 */
class TwoFactorAuth extends \Magento\Framework\Model\AbstractModel implements TwoFactorAuthInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * otp cache tag.
     */
    public const CACHE_TAG = 'wk_twofactorauth';

    /**
     * @var string
     */
    private $cacheTag = 'wk_twofactorauth';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    private $eventPrefix = 'wk_twofactorauth';

    /**
     * Initialize resource model.
     */
    public function _construct()
    {
        $this->_init(\Webkul\TwoFactorAuth\Model\ResourceModel\TwoFactorAuth::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteGallery();
        }
        return parent::load($id, $field);
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Email.
     *
     * @return string
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * Set Email.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set Created At
     *
     * @param string $created_at
     * @return void
     */
    public function setCreatedAt($created_at)
    {
        return $this->setData(self::CREATED_AT, $created_at);
    }

    /**
     * Get CreatedAt
     *
     * @return date
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set AuthCode
     *
     * @param int $auth_code
     * @return int $auth_code
     */
    public function setAuthCode($auth_code)
    {
        return $this->setData(self::AUTH_CODE, $auth_code);
    }

    /**
     * Get AuthCode
     *
     * @return int|null
     */
    public function getAuthCode()
    {
        return parent::getData(self::AUTH_CODE);
    }
    /**
     * Get Verified
     *
     * @return int|null
     */
    public function getVerified()
    {
        return parent::getData(self::VERIFIED);
    }

    /**
     * Set Verified
     *
     * @param int $flag
     * @return int $flag
     */
    public function setVerified($flag)
    {
        return $this->setData(self::VERIFIED, $flag);
    }
}
