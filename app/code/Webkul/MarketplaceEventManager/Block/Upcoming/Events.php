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
namespace Webkul\MarketplaceEventManager\Block\Upcoming;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Events extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * @var _mpproduct
     */
    protected $_mpproduct;

    /**
     * @var _product
     */
    protected $_product;

    /**
     * @var _filesystem
     */
    protected $_filesystem;

    /**
     * @var _storeManager
     */
    protected $_storeManager;
    
    /**
     * @var _imageFactory
     */
    protected $_imageFactory;

    /**
     * @var _memhelper
     */
    protected $_memhelper;

    /**
     * @var _productCollection
     */
    protected $_productCollection = null;

    /**
     * @var _mphelper
     */
    protected $_mphelper;

    /**
     * @var _catalogOutputHelper
     */
    protected $_catalogOutputHelper;

    /**
     * @var _wishlistDataHelper
     */
    protected $_wishlistDataHelper;

    /**
     * @var _catalogComapareHelper
     */
    protected $_catalogComapareHelper;
    
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Webkul\Marketplace\Model\Product $mpproduct
     * @param Product $product
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Webkul\MarketplaceEventManager\Helper\Data $memhelper
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\CatalogInventory\Helper\Stock $stockFilter
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Helper\Output $catalogOutputHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistDataHelper
     * @param \Magento\Catalog\Helper\Product\Compare $catalogComapareHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Webkul\Marketplace\Model\Product $mpproduct,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Webkul\MarketplaceEventManager\Helper\Data $memhelper,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Output $catalogOutputHelper,
        \Magento\Wishlist\Helper\Data $wishlistDataHelper,
        \Magento\Catalog\Helper\Product\Compare $catalogComapareHelper,
        array $data = []
    ) {
        $this->_mpproduct = $mpproduct;
        $this->_product = $product;
        $this->_filesystem = $context->getFilesystem();
        $this->_storeManager = $context->getStoreManager();
        $this->_imageFactory = $imageFactory;
        $this->_memhelper = $memhelper;
        $this->_mphelper = $mphelper;
        $this->timezone = $timezone;
        $this->_stockFilter = $stockFilter;
        $this->imageHelper = $imageHelper;
        $this->_catalogOutputHelper = $catalogOutputHelper;
        $this->_wishlistDataHelper = $wishlistDataHelper;
        $this->_catalogComapareHelper = $catalogComapareHelper;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }
    
    /**
     * GetProductCollection
     */
    public function _getProductCollection()
    {
        $collection = $this->_product->create()
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('type_id', ['eq' => 'etickets']);
        $this->_stockFilter->addInStockFilterToCollection($collection);
        if ($this->_memhelper->getEventFromDate() && $this->_memhelper->getEventToDate()) {
            // case 1 : check for config event date range
            $collection->addFieldToFilter('event_start_date', ['gteq' => $this->_memhelper->getEventFromDate()]);
            $collection->addFieldToFilter('event_end_date', ['lteq' => $this->_memhelper->getEventToDate()]);
        } elseif ($this->_memhelper->getEventFromDate() && !$this->_memhelper->getEventToDate()) {
            // case 2 : check for config event from date is only set
            $collection->addFieldToFilter('event_start_date', ['gteq' => $this->_memhelper->getEventFromDate()]);
        } elseif (!$this->_memhelper->getEventFromDate() && $this->_memhelper->getEventToDate()) {
            // case 3 : check for config event end date is only set
            $collection->addFieldToFilter('event_end_date', ['lteq' => $this->_memhelper->getEventToDate()]);
        }
        $today = $this->_memhelper->getTodayDate(); //default timezone
        $collection->addFieldToFilter('event_end_date', ['gteq' => $today]);

        $query = $this->getRequest()->getParam('q');
        if ($query) {
            $collection->addFieldToFilter('name', ['like'=>'%'.$query.'%']);
        }
        $collection->addAttributeToSelect('*');

        $toolbar = $this->getToolbarBlock();
        $this->configureProductToolbar($toolbar, $collection);

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        $this->_productCollection = $collection;

        return $collection;
    }

    /**
     * GetProductModel
     *
     * @param int $pid
     */
    public function getProductModel($pid)
    {
        return $this->_product->create()->load($pid);
    }

    /**
     * ImageResize
     *
     * @param string $image
     */
    public function imageResize($image)
    {
        if (!$image || $image == 'no_selection') {
            return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
        }
        if ($image) {
            $absPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
            . 'catalog/product'.$image;
            $imageResized = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                                                ->getAbsolutePath('resized/').$image;
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absPath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(true);
            $imageResize->resize(300);
            $dest = $imageResized ;
            $imageResize->save($dest);
            $resizedURL= $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$image;
            return $resizedURL;
        }
    }

    /**
     * CheckSellerStatus
     *
     * @param int $id
     */
    public function checkSellerStatus($id)
    {
        $data = $this->_mphelper->getSellerDataBySellerId($id);
        foreach ($data as $key => $value) {
            return $value->getIsSeller();
        }
    }

    /**
     * GetProductById
     *
     * @param int $id
     */
    public function getProductById($id)
    {
        return $this->_product->create()->load($id);
    }

    /**
     * ConfigureProductToolbar
     *
     * @param object $toolbar
     * @param object $collection
     */
    public function configureProductToolbar($toolbar, $collection)
    {
        $availableOrders = $this->getAvailableOrders();
        if (isset($availableOrders['position'])) {
            unset($availableOrders['position']);
        }
        if ($availableOrders) {
            $toolbar->setAvailableOrders($availableOrders);
        }
        $sortBy = $this->getSortBy();
        if ($sortBy) {
            $toolbar->setDefaultOrder($sortBy);
        }
        $defaultDirection = $this->getDefaultDirection();
        if ($defaultDirection) {
            $toolbar->setDefaultDirection($defaultDirection);
        }
        $sortModes = $this->getModes();
        if ($sortModes) {
            $toolbar->setModes($sortModes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }

    /**
     * GetDefaultDirection
     */
    public function getDefaultDirection()
    {
        return 'asc';
    }

    /**
     * GetSortBy
     */
    public function getSortBy()
    {
        return 'name';
    }

    /**
     * GetProductImage
     *
     * @param string $_product
     */
    public function getProductImage($_product)
    {
        $image_url = $this->imageHelper->init($_product, 'product_page_image_large')->getUrl();
        return $image_url;
    }
    
    /**
     * GetLocaleTime
     *
     * @param datetime $dateTime
     */
    public function getLocaleTime($dateTime)
    {
        return $this->timezone->formatDate(
            $dateTime,
            \IntlDateFormatter::FULL,
            true
        );
    }

    /**
     * ConverToTz
     *
     * @param string $dateTime
     * @param string $toTz
     * @param string $fromTz
     */
    protected function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }

    /**
     * GetCatalogOutputHelper
     */
    public function getCatalogOutputHelper()
    {
        return $this->_catalogOutputHelper;
    }
    
    /**
     * GetWishlistDataHelper
     */
    public function getWishlistDataHelper()
    {
        return $this->_wishlistDataHelper;
    }

    /**
     * GetCatalogCompareHelper
     */
    public function getCatalogCompareHelper()
    {
        return $this->_catalogComapareHelper;
    }
}
