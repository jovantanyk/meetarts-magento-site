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
namespace Webkul\MpBuyerSellerChat\Controller\Profile;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Model\Session as CustomerSession;

class Upload extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
     /**
      * @var string
      */
    protected $_customerEntityTypeId;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     *
     * @var \Webkul\MpBuyerSellerChat\Model\CustomerDataFactory
     */
    protected $chatCustomerFactory;

    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Webkul\MpBuyerSellerChat\Model\CustomerDataFactory $chatCustomerFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param CustomerSession $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Webkul\MpBuyerSellerChat\Model\CustomerDataFactory $chatCustomerFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        CustomerSession $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->chatCustomerFactory = $chatCustomerFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $type = $this->getRequest()->getParam('type');
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'mpchatsystem/profile/'.$this->customerSession->getCustomerId()
        );
        $url = $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                'mpchatsystem/profile/'.$this->customerSession->getCustomerId();
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'file']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($path);
            $collection = $this->chatCustomerFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $this->customerSession->getCustomerId()])
                ->addFieldToFilter('registered_as', ['eq' => $type]);
            if ($collection->getSize()) {
                $entityId = $collection->getFirstItem()->getEntityId();
                $model = $this->chatCustomerFactory->create()->load($entityId);
                $model->setImage($result['file']);
                $model->setId($entityId);
                $model->save();
            }
            $response->setImageName($url.'/'.$result['file']);
            $response->setMessage(
                __('Image updated successfully.')
            );
        } catch (\Exception $e) {
            $response->setMessage(
                $e->getMessage()
            );
            $response->setError(true);
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
