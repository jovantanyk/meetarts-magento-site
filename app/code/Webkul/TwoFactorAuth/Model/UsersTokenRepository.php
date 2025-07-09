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
use Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken\Collection;

class UsersTokenRepository implements \Webkul\TwoFactorAuth\Api\UsersTokenRepositoryInterface
{
    /**
     * @var \Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken
     */
    protected $_resourceModel;

    /**
     * @var UsersTokenFactory
     */
    protected $_usersTokenFactory;
    /**
     * @var \Webkul\TwoFactorAuth\Model\ResourceModel\Ebayaccounts\Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param UsersTokenFactory $usersTokenFactory
     * @param \Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken\CollectionFactory $collectionFactory
     * @param \Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken $resourceModel
     */
    public function __construct(
        UsersTokenFactory $usersTokenFactory,
        \Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken\CollectionFactory $collectionFactory,
        \Webkul\TwoFactorAuth\Model\ResourceModel\UsersToken $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_usersTokenFactory = $usersTokenFactory;
        $this->_collectionFactory = $collectionFactory;
    }
    
    /**
     * Get by token id
     *
     * @param  string $token
     * @return object
     */
    public function getByToken($token)
    {
        return $this->_collectionFactory->create()->addFieldToFilter('token', $token);
    }
}
