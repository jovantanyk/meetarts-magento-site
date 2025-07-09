<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceEventManager\Model;

class Sendmail
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Webkul\MarketplaceEventManager\Helper\Data
     */
    protected $_eventHelper;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\customer\Model\Customer $customer
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Webkul\MarketplaceEventManager\Helper\Data $eventHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Webkul\MarketplaceEventManager\Helper\Data $eventHelper
    ) {
        $this->_product = $product;
        $this->_customer = $customer;
        $this->_order = $order;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_eventHelper = $eventHelper;
    }

    /**
     * SendReminderMail
     *
     * @param array $params
     */
    public function sendReminderMail($params)
    {
        $product = $this->_product->create()->load($params['pid']);
        $seller = $this->_customer->create()->load($params['cid']);
        $failedEmail = [];
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        foreach ($params['buyerlist'] as $email) {
            $customer = $this->_customer->create()->setWebsiteId($websiteId);
            $customer->loadByEmail($email);
            $customername = 'Guest';
            if ($customer->getId()) {
                $customername = $customer->getName();
            } else {
                $orderCollection = $this->_order->create()->getCollection();
                $orderCollection->addFieldToFilter('customer_email', ['eq'=>$email]);
                foreach ($orderCollection as $cusdata) {
                    $customername = $cusdata->getBillingAddress()->getCustomerFirstName();
                    break;
                }
            }
            $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                 'store' => $this->_storeManager->getStore()->getId()];

            $emailTempVariables = [];
            $emailTempVariables['myvar1'] = $product->getName();
            $emailTempVariables['myvar2'] = $customername;
            $emailTempVariables['myvar3'] = $seller->getName();
            $emailTempVariables['myvar4'] = __($params['event_message']);
            $emailTempVariables['myvar5'] = __($params['event_subject']);
            $emailTempVariables['myvar6'] = date('g:ia \o\n l jS F Y', strtotime($product->getEventStartDate()));
            $emailTempVariables['myvar7'] = date('g:ia \o\n l jS F Y', strtotime($product->getEventEndDate()));
            $emailTempVariables['myvar8'] = $product->getEventVenue();
            $emailTempVariables['myvar9'] = $product->getImageUrl();
            $emailTempVariables['myvar10'] = $product->getProductUrl();
            $from = ['email' => $seller->getEmail(), 'name' => $seller->getName()];
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $this->_eventHelper->getRemainderTemplate()
                )
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($emailTempVariables)
                ->setFrom($from)
                ->addTo($email, $customername)
                ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                return ['error' => 1, 'message' => $e->getMessage()];
            }
        }
        return ['error' => 0];
    }
}
