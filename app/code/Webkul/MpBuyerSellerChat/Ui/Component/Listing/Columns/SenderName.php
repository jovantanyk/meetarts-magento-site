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

namespace Webkul\MpBuyerSellerChat\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Model\CustomerFactory as CustomerModelFactory;
use Webkul\MpBuyerSellerChat\Model\CustomerDataFactory as MpBuyerCustomerFactory;

/**
 * Class ViewAction.
 */
class SenderName extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var CustomerModelFactory
     */
    protected $customerModelFactory;

    /**
     * @var MpBuyerCustomerFactory
     */
    protected $mpBuyerCustomerFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param CustomerModelFactory $customerModelFactory
     * @param MpBuyerCustomerFactory $mpBuyerCustomerFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CustomerModelFactory $customerModelFactory,
        MpBuyerCustomerFactory $mpBuyerCustomerFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->customerModelFactory = $customerModelFactory;
        $this->mpBuyerCustomerFactory = $mpBuyerCustomerFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $chatCustomer = $this->mpBuyerCustomerFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('unique_id', ['eq' => $item['sender_unique_id']]);
                    if ($chatCustomer->getSize()) {
                        $customer = $this->customerModelFactory->create()
                            ->load($chatCustomer->getFirstItem()->getCustomerId());
                        $item[$this->getData('name')] = $customer->getName();
                    }
                }
            }
        }

        return $dataSource;
    }
}
