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

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Webkul\TwoFactorAuth\Api\TwoFactorAuthRepositoryInterface;

class TwoFactorAuthRepository implements TwoFactorAuthRepositoryInterface
{
    /**
     * @var TwoFactorAuth
     */
    private $twoFactorAuthModel;

    /**
     * @param TwoFactorAuth $twoFactorAuthModel
     */
    public function __construct(
        TwoFactorAuth $twoFactorAuthModel
    ) {
        $this->twoFactorAuthModel = $twoFactorAuthModel;
    }

    /**
     * Save twofactor auth data
     *
     * @param \Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface $twoFactorAuth
     *
     * @throws CouldNotSaveException
     */
    public function save(\Webkul\TwoFactorAuth\Api\Data\TwoFactorAuthInterface $twoFactorAuth)
    {
        try {
            $this->twoFactorAuthModel->save($twoFactorAuth);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the page: %1', $e->getMessage()),
                $e
            );
        }
    }

    /**
     * Get details by email
     *
     * @param $string $customerEmail
     * @return Array $collection
     */
    public function getByEmail($customerEmail)
    {
        $collection = $this->twoFactorAuthModel->load($customerEmail, 'email');
        return $collection;
    }

    /**
     * Delete auth data by email
     *
     * @param string $customerEmail
     * @throws couldnotdeleteException
     */
    public function deleteByEmail($customerEmail)
    {
        try {
            $collection = $this->getByEmail($customerEmail);
            $collection->delete();
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not save the page: %1', $e->getMessage()),
                $e
            );
        }
    }
}
