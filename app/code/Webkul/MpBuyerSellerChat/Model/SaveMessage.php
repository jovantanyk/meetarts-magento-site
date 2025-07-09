<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Model;

use Webkul\MpBuyerSellerChat\Api\SaveMessageInterface;
use Webkul\MpBuyerSellerChat\Api\Data\MessageInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
 
class SaveMessage implements SaveMessageInterface
{
    /**
     * @var MessageRepository
     */
    protected $messageRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var PreorderItemsInterfaceFactory
     */
    protected $messageFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $encoder;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Webkul\MpBuyerSellerChat\Model\CustomerDataFactory
     */
    protected $customerDataFactory;

    /**
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @param MessageRepository $messageRepository
     * @param MessageInterfaceFactory $messageFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\MpBuyerSellerChat\Model\CustomerDataFactory $customerDataFactory
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        MessageRepository $messageRepository,
        MessageInterfaceFactory $messageFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\MpBuyerSellerChat\Model\CustomerDataFactory $customerDataFactory,
        SerializerJson $serializerJson
    ) {
        $this->messageRepository = $messageRepository;
        $this->messageFactory = $messageFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_date = $date;
        $this->encoder = $encoder;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->serializerJson = $serializerJson;
    }

   /**
    * Returns greeting message to user
    *
    * @api
    * @param string $senderUniqueId
    * @param string $receiverUniqueId
    * @param string $message
    * @param string $dateTime
    * @param string $msgType
    * @return string Greeting message with users name.
    */
    public function saveMessage($senderUniqueId, $receiverUniqueId, $message, $dateTime, $msgType = '')
    {
        $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer) {
            if ($msgType == 'image' || $msgType == 'file') {
                
                $message = $this->encoder->encode(trim($message));
            }
            $message = [
                'sender_unique_id' => $senderUniqueId,
                'receiver_unique_id' => $receiverUniqueId,
                'message'   => $message,
                'date'  => $this->_date->gmtDate('Y-m-d H:i:s', $dateTime),
                'sender_name'   => $this->getNameById($senderUniqueId),
                'receiver_name'   => $this->getNameById($receiverUniqueId)
            ];
            $dataObject = $this->messageFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $dataObject,
                $message,
                \Webkul\MpBuyerSellerChat\Api\Data\MessageInterface::class
            );
            
            try {
                $this->messageRepository->save($dataObject);
                $message['errors'] = false;
            } catch (\Exception $e) {
                $message['errors'] = true;
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
            return $this->serializerJson->serialize($message);
        }
    }

    /**
     * Fetch name by id
     *
     * @param int $uniqueId
     * @return string
     */
    public function getNameById($uniqueId)
    {
        $chatCustomer = $this->customerDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('unique_id', ['eq' => $uniqueId]);
        if ($chatCustomer->getSize()) {
            $customer = $this->customerFactory->create()
                ->load($chatCustomer->getFirstItem()->getCustomerId());
            return $customer->getName();
        }
    }
}
