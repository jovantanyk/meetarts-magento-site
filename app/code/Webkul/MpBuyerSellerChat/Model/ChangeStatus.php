<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Model;

use Webkul\MpBuyerSellerChat\Api\ChangeStatusInterface;
use Webkul\MpBuyerSellerChat\Model\CustomerDataRepository as CustomerDataRepository;
use Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class ChangeStatus implements ChangeStatusInterface
{

    /**
     * @var Items
     */
    protected $dataRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerDataInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

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
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        SerializerJson $serializerJson
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerFactory = $customerFactory;
        $this->serializerJson = $serializerJson;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param int $status
     * @param string $type
     * @return string.
     */
    public function changeStatus($status, $type = '')
    {
        $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customerId);
        $customerData = [];
        if ($customer) {
            $collection = $this->customerDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('registered_as', ['eq' => $type]);
            if ($collection->getSize()) {
                $chatCustomer = $collection->getFirstItem();
                $entityId = $chatCustomer->getId();
                
                $savedData = (array) $chatCustomer->getData();
                $customerData = array_merge(
                    $savedData,
                    ['customer_id' => $customer->getId(),'chat_status' => $status]
                );
                $customerData['entity_id'] = $entityId;

                $dataObject = $this->customerDataFactory->create();

                $this->dataObjectHelper->populateWithArray(
                    $dataObject,
                    $customerData,
                    \Webkul\MpBuyerSellerChat\Api\Data\CustomerDataInterface::class
                );
                try {
                    $this->dataRepository->save($dataObject);
                    $customerData['message'] = 'status changed';
                    $customerData['error'] = false;
                } catch (\Exception $e) {
                    $customerData['error'] = true;
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
            }
            return $this->serializerJson->serialize($customerData);
        }
    }
}
