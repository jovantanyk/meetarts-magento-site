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
namespace Webkul\TwoFactorAuth\Model\ResourceModel\TwoFactorAuth;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * TwoFactorAuth Model ResoucrceModel Collection Class
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Webkul\TwoFactorAuth\Model\TwoFactorAuth::class,
            \Webkul\TwoFactorAuth\Model\ResourceModel\TwoFactorAuth::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
