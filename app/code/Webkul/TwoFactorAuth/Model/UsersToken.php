<?php
/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Model;

use Webkul\TwoFactorAuth\Api\Data\UsersTokenInterface;
use Magento\Framework\DataObject\IdentityInterface;

class UsersToken extends \Magento\Framework\Model\AbstractModel implements UsersTokenInterface
{
    /**
     * CMS page cache tag.
     */
    private const CACHE_TAG = 'wk_pushnotification_userstoken';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_pushnotification_userstoken';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_pushnotification_userstoken';

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
        $this->_init(\Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken::class);
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
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * Set token
     *
     * @param string $token
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Get browser
     *
     * @return string
     */
    public function getBrowser()
    {
        return $this->getData(self::BROWSER);
    }

    /**
     * Set browser
     *
     * @param string $browser
     */
    public function setBrowser($browser)
    {
        return $this->setData(self::BROWSER, $browser);
    }

    /**
     * Get created time
     *
     * @return timestamp
     */
    public function getcreatedAt()
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
}
