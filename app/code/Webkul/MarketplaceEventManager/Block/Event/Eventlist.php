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

namespace Webkul\MarketplaceEventManager\Block\Event;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Eventlist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /** @var \Magento\Catalog\Model\Product */
    protected $_productlists;

    /** @var \Webkul\Marketplace\Helper\Data */
    protected $_mpHelper;

    /**
     * @param Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CollectionFactory $productCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\MarketplaceEventManager\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $productCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\MarketplaceEventManager\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_imageHelper = $context->getImageHelper();
        $this->_priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Event List'));
    }

    /**
     * Get formatted by price and currency.
     *
     * @param float $price
     * @param string $currency
     *
     * @return array || float
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_priceCurrency->format(
            $price,
            true,
            2,
            null,
            $currency
        );
    }

    /**
     * GetAllProducts
     *
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        $storeId = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Data::class
        )->getCurrentStoreId();
        $websiteId = $this->_objectManager->create(
            \Webkul\Marketplace\Helper\Data::class
        )->getWebsiteId();
        if (!($customerId = $this->helper->getLoggedInSellerId())) {
            return false;
        }
        if (!$this->_productlists) {
            $paramData = $this->getRequest()->getParams();
            $filter = '';
            $filterStatus = '';
            $filterDateFrom = '';
            $filterDateTo = '';
            $from = null;
            $to = null;

            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $paramData['s'] : '';
            }
            if (isset($paramData['status'])) {
                $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
            }
            if (isset($paramData['from_date'])) {
                $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
            }
            if (isset($paramData['to_date'])) {
                $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
            }
            if ($filterDateTo) {
                $todate = date_create($filterDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if (!$to) {
                $to = date('Y-m-d 23:59:59');
            }
            if ($filterDateFrom) {
                $fromdate = date_create($filterDateFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            $eavAttribute = $this->_objectManager->get(
                \Magento\Eav\Model\ResourceModel\Entity\Attribute::class
            );
            $proAttId = $eavAttribute->getIdByCode('catalog_product', 'name');
            $proStatusAttId = $eavAttribute->getIdByCode('catalog_product', 'status');

            $catalogProductEntity = $this->_objectManager->create(
                \Webkul\Marketplace\Model\ResourceModel\Product\Collection::class
            )->getTable('catalog_product_entity');

            $catalogProductEntityVarchar = $this->_objectManager->create(
                \Webkul\Marketplace\Model\ResourceModel\Product\Collection::class
            )->getTable('catalog_product_entity_varchar');

            $catalogProductEntityInt = $this->_objectManager->create(
                \Webkul\Marketplace\Model\ResourceModel\Product\Collection::class
            )->getTable('catalog_product_entity_int');

            /* Get Seller Product Collection for current Store Id */

            $storeCollection = $this->_objectManager->create(
                \Webkul\Marketplace\Model\Product::class
            )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = '.$storeId.' AND 
                cpev.value like "%'.$filter.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = '.$storeId.' AND 
                cpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $storeCollection->getSelect()->where(
                    'cpei.value = '.$filterStatus
                );
            }

            $storeCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            if ($from && $to) {
                $storeCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }
            $storeCollection->addFieldToFilter('type_id', 'etickets');
            $storeCollection->getSelect()->group('mageproduct_id');

            $storeProductIDs = $storeCollection->getAllIds();

            /* Get Seller Product Collection for 0 Store Id */

            $adminStoreCollection = $this->_objectManager->create(
                \Webkul\Marketplace\Model\Product::class
            )
            ->getCollection();

            if (count($storeCollection->getAllIds())) {
                $adminStoreCollection->addFieldToFilter(
                    'mageproduct_id',
                    ['nin' => $storeCollection->getAllIds()]
                );
            }
            $adminStoreCollection->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = 0 AND 
                cpev.value like "%'.$filter.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = 0 AND 
                cpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $adminStoreCollection->getSelect()->where(
                    'cpei.value = '.$filterStatus
                );
            }

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );
            if ($from && $to) {
                $adminStoreCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $adminStoreCollection->getSelect()->group('mageproduct_id');
            $adminStoreCollection->addFieldToFilter('type_id', 'etickets');
            $adminProductIDs = $adminStoreCollection->getAllIds();

            $productIDs = array_merge($storeProductIDs, $adminProductIDs);

            $collection = $this->_objectManager->create(
                \Webkul\Marketplace\Model\Product::class
            )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToFilter(
                'mageproduct_id',
                ['in' => $productIDs]
            );

            $collection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            $collection->addFieldToFilter('type_id', 'etickets');

            $this->_productlists = $collection;
        }

        return $this->_productlists;
    }

    /**
     * PrepareLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.product.list.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
        }

        return $this;
    }

    /**
     * GetProductData
     *
     * @param int $id
     */
    public function getProductData($id = '')
    {
        return $this->_objectManager->create(
            \Magento\Catalog\Model\Product::class
        )->load($id);
    }

    /**
     * GetPagerHtml
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * ImageHelperObj
     */
    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }

    /**
     * GetSalesdetail
     *
     * @param string $productId
     */
    public function getSalesdetail($productId = '')
    {
        $data = [
            'quantitysoldconfirmed' => 0,
            'quantitysoldpending' => 0,
            'amountearned' => 0,
            'clearedat' => 0,
            'quantitysold' => 0,
        ];
        $sum = 0;
        $arr = [];
        $quantity = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Saleslist::class
        )
        ->getCollection()
        ->addFieldToFilter(
            'mageproduct_id',
            $productId
        );

        foreach ($quantity as $rec) {
            $status = $rec->getCpprostatus();
            $data['quantitysold'] = $data['quantitysold'] + $rec->getMagequantity();
            if ($status == 1) {
                $data['quantitysoldconfirmed'] = $data['quantitysoldconfirmed'] + $rec->getMagequantity();
            } else {
                $data['quantitysoldpending'] = $data['quantitysoldpending'] + $rec->getMagequantity();
            }
        }

        $amountearned = $this->_objectManager->create(\Webkul\Marketplace\Model\Saleslist::class)
                        ->getCollection()
                        ->addFieldToFilter(
                            'cpprostatus',
                            \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING
                        )
                        ->addFieldToFilter(
                            'mageproduct_id',
                            $productId
                        );
        foreach ($amountearned as $rec) {
            $data['amountearned'] = $data['amountearned'] + $rec['actual_seller_amount'];
            $arr[] = $rec['created_at'];
        }
        $data['created_at'] = $arr;

        return $data;
    }

    /**
     * GetMpHelper
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
}
