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
namespace Webkul\MpBuyerSellerChat\Controller\Adminhtml\Server;

class Start extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Shell
     */
    protected $shell;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Shell $shell
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directoryList = $directoryList;
        $this->shell = $shell;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $data = $this->getRequest()->getParams();
        $response->setRoot($this->directoryList->getRoot());
        $rootPath = $this->directoryList->getRoot();
        $node = $this->shell->execute('whereis node');
        
        $nodePath = explode(' ', $node);
        if (!isset($nodePath[1]) || $nodePath[1] == '') {
            $node = $this->shell->execute('whereis nodejs');
            $nodePath = explode(' ', $node);
        }
        
        if (count($nodePath)) {
            $this->shell->execute($nodePath[1].' '.$rootPath.'/server.js' . " > /dev/null &");
            $response->setMessage(
                __('Server Running.')
            );
            $this->messageManager->addSuccess(__('Server has been started.'));
        } else {
            $response->setError(true);
            $response->addMessage(__('Nodejs Path not found.'));
            $this->messageManager->setError(__('Nodejs Path not found.'));
        }

        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
