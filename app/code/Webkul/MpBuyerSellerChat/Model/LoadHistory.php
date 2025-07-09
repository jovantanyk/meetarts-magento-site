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

use Webkul\MpBuyerSellerChat\Api\LoadHistoryInterface;
use Webkul\MpBuyerSellerChat\Api\SaveMessageInterface;
use Webkul\MpBuyerSellerChat\Api\Data\MessageInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MpBuyerSellerChat\Model\ResourceModel\CustomerData\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class LoadHistory implements LoadHistoryInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystemIo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Filesystem\Io\File $filesystemIo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Filesystem\Io\File $filesystemIo,
        \Magento\Framework\Filesystem $filesystem,
        SerializerJson $serializerJson
    ) {
        $this->date = $date;
        $this->urlDecoder = $urlDecoder;
        $this->resourceConnection = $resourceConnection;
        $this->filesystemIo = $filesystemIo;
        $this->filesystem = $filesystem;
        $this->serializerJson = $serializerJson;
    }

    /**
     * Return chat history
     *
     * @api
     * @param string $senderUniqueId
     * @param string $receiverUniqueId
     * @param int $loadTime
     * @return string
     */
    public function loadHistory($senderUniqueId, $receiverUniqueId, $loadTime)
    {
        $loadDate = date("Y-m-d H:i:s");
        if ($loadTime == 1) {
            $loadDate = date('Y-m-d H:i:s', strtotime($loadDate . ' -1 day'));
        } elseif ($loadTime == 2) {
            $loadDate = date('Y-m-d H:i:s', strtotime($loadDate . ' -7 day'));
        } elseif ($loadTime == 3) {
            $loadDate = date('Y-m-d H:i:s', strtotime($loadDate . ' -1825 day'));
        } else {
            $loadDate = date('Y-m-d H:i:s', strtotime($loadDate . ' -12 hour'));
        }

        $resource = $this->resourceConnection;
        $connection = $resource->getConnection();
        $historyTable = $resource->getTableName('marketplace_chat_history');
        $customerTable = $resource->getTableName('marketplace_chat_customer_info');

        $queryString = "((history.sender_unique_id = "."'".$senderUniqueId."'"." AND ".
        "history.receiver_unique_id="."'".$receiverUniqueId."')".
        " OR "."(history.sender_unique_id = "."'".$receiverUniqueId."'".
        " AND "."history.receiver_unique_id="."'".$senderUniqueId."'))".
        " AND "."history.date >='".$loadDate."'";

        $result = $connection->select()->from(
            ['customer' => $customerTable],
            ['history.*']
        )->joinLeft(
            ['history' => $historyTable],
            'customer.unique_id = history.sender_unique_id'
        )->where($queryString)
            ->order('history.date', 'ASC');

        $chatData = $connection->fetchAssoc($result);
        $result = $chatData;
 
        $previousDate = '';
        $messageData['messages'] = [];
        foreach ($result as $key => $data) {
            $changeDate = false;
            $currentDate = strtotime($this->date->gmtDate('Y-m-d', $data['date']));
            if ($previousDate == '') {
                $previousDate = strtotime($this->date->gmtDate('Y-m-d', $data['date']));
                $changeDate = true;
            } elseif ($currentDate !== $previousDate) {
                $changeDate = true;
                $previousDate = strtotime($this->date->gmtDate('Y-m-d', $data['date']));
            }
            $data['message_type'] = 'text';

            $file = $this->urlDecoder->decode($data['message']);

            $fileSystem = $this->filesystem;
            $directory = $fileSystem->getDirectoryRead(DirectoryList::MEDIA);

            $fileName = 'marketplace/chatsystem/' . ltrim($file, '/');
            $filePath = $directory->getAbsolutePath($fileName);

            if ($directory->isFile($fileName)) {

                $extension = $this->filesystemIo->getPathInfo($filePath, PATHINFO_EXTENSION);
                switch (strtolower($extension['extension'])) {
                    case 'gif':
                        $contentType = 'image/gif';
                        $data['message_type'] = 'image';
                        break;
                    case 'jpg':
                        $contentType = 'image/jpeg';
                        $data['message_type'] = 'image';
                        break;
                    case 'png':
                        $contentType = 'image/png';
                        $data['message_type'] = 'image';
                        break;
                    default:
                        $contentType = 'application/octet-stream';
                        $data['message_type'] = 'file';
                        break;
                }
            }
            $data['time'] = $this->date->gmtDate('h:i A', $data['date']);
            $data['date'] = $this->date->gmtDate('Y-m-d h:i A', $data['date']);
            $data['changeDate'] = $changeDate;
            $messageData['messages'][$key] = $data;
        }

        return $this->serializerJson->serialize($messageData);
    }
}
