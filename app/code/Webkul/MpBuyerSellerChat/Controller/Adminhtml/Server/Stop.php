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

class Stop extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Shell
     */
    protected $shell;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Shell $shell
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
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
        $getUserPath = $this->shell->execute('whereis fuser');
        if ($getUserPath) {
            $getUserPath = explode(' ', $getUserPath);
            if (isset($getUserPath[1])) {
                $stopServer = $this->shell->execute($getUserPath[1].' -k '.$data['port'].'/tcp');
                $this->messageManager->addSuccess(__('Server has been stopped.'));
            }
        } else {
            $response->setError(true);
            $response->setMessage(__('Something went wrong.'));
            $this->messageManager->addError(__('Something went wrong.'));
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
