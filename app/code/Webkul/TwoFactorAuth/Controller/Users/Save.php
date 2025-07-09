<?php
/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\TwoFactorAuth\Controller\Users;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\TwoFactorAuth\Model\UsersToken;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\TwoFactorAuth\Api\UsersTokenRepositoryInterface;

class Save extends Action
{
    private const NAME = 'guest';

    /**
     * @var UsersToken
     */
    protected $_userTokenModel;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var UsersTokenRepository
     */
    protected $_usersTokenRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @param Context $context
     * @param UsersToken $userTokenModel
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param UsersTokenRepositoryInterface $usersTokenRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     */
    public function __construct(
        Context $context,
        UsersToken $userTokenModel,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        UsersTokenRepositoryInterface $usersTokenRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        parent::__construct($context);
        $this->_userTokenModel = $userTokenModel;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_date = $date;
        $this->_usersTokenRepository = $usersTokenRepository;
        $this->_customerSession = $customerSession;
        $this->_timezoneInterface = $timezoneInterface;
    }

    /**
     * Save Users
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = null;
        $time = $this->_timezoneInterface->date()->format('m/d/y H:i:s');
        ;
        $params = $this->getRequest()->getParams();
        if ($this->_customerSession->isLoggedIn()) {
            $params['name'] = $this->_customerSession->getCustomer()->getName();
        } else {
            $params['name'] = self::NAME;
        }
        $tokenCollection = $this->_usersTokenRepository->getByToken($params['token']);
        if (!$tokenCollection->getSize()) {
            $params['created_at'] = $time;
            $id = $this->_userTokenModel
                ->addData($params)->save()
                ->getId();
            if ($id) {
                $result = ['error' => false, 'info' => $id];
            } else {
                $result = ['error'=> 'user token did not saved in database' ];
            }
        }
        return $this->_resultJsonFactory->create()->setData($result);
    }
}
