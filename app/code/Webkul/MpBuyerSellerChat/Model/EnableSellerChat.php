<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Model;

use Webkul\MpBuyerSellerChat\Api\ChangeStatusInterface;
use Webkul\MpBuyerSellerChat\Model\CustomerDataRepository as CustomerDataRepository;
use Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerData\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class EnableSellerChat
{
    /**
     * @var CustomerDataRepository
     */
    protected $dataRepository;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /**
     * @var CustomerDataInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @param CustomerDataRepository $dataRepository
     * @param CustomerDataInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        SerializerJson $serializerJson
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->serializerJson = $serializerJson;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @return string chat data
     */
    public function enable()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer) {
            $customerData = [
                'customer_id' => $customer->getId(),
                'unique_id' => $this->generateUniqueId(),
                'registered_as' => 'seller',
                'chat_status' => 1
            ];

            $dataObject = $this->customerDataFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $dataObject,
                $customerData,
                \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface::class
            );
            try {
                $this->dataRepository->save($dataObject);
                $customerData['message'] = __('chat enabled');
                $customerData['error'] = false;
            } catch (\Exception $e) {
                $customerData['error'] = true;
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
            return $this->serializerJson->serialize($customerData);
        }
    }

    /**
     * Generate UniqueId
     *
     * @return string
     */
    public function generateUniqueId()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pass [] = 'W';
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}
