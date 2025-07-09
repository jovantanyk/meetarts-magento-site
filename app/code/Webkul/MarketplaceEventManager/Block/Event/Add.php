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

/*
 * Webkul Marketplace Product Create Block
 */
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\GoogleOptimizer\Model\Code as ModelCode;
use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var ModelCode
     */
    protected $_modelCode;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var Option
     */
    protected $_option;
    
    /**
     * @var Value
     */
    protected $_value;

    /**
     * @var Helper
     */
    protected $_mpHelper;

    /**
     * @var EventHelper
     */
    protected $_mpEventManagerHelper;

    /**
     * @var CatalogHelper
     */
    protected $_catalogCategoryHelper;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Product $product
     * @param Category $category
     * @param ModelCode $modelCode
     * @param HelperData $helperData
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Catalog\Model\Product\Option\Value $value
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper
     * @param \Magento\Catalog\Helper\Category $catalogCategoryHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     * @param \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        ProductFactory $product,
        CategoryFactory $category,
        ModelCode $modelCode,
        HelperData $helperData,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Catalog\Model\Product\Option\Value $value,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MarketplaceEventManager\Helper\Data $mpEventManagerHelper,
        \Magento\Catalog\Helper\Category $catalogCategoryHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = [],
        \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages = null
    ) {
        $this->_product = $product;
        $this->_category = $category;
        $this->_modelCode = $modelCode;
        $this->_helperData = $helperData;
        $this->_productRepository = $productRepository;
        $this->_option = $option;
        $this->_value = $value;
        $this->_mpHelper = $mpHelper;
        $this->_mpEventManagerHelper = $mpEventManagerHelper;
        $this->_catalogCategoryHelper = $catalogCategoryHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->wysiwygImages = $wysiwygImages ?: \Magento\Framework\App\ObjectManager::getInstance()
                                  ->create(\Magento\Cms\Helper\Wysiwyg\Images::class);
        parent::__construct($context, $data);
    }

    /**
     * GetProduct
     *
     * @param int $id
     */
    public function getProduct($id)
    {
        return $this->_product->create()->load($id);
    }

    /**
     * GetProductOptions
     *
     * @param int $id
     */
    public function getProductOptions($id)
    {
        return $this->_option->getProductOptionCollection($this->getProduct($id))->getData();
    }

    /**
     * GetValueCollectionOfOption
     *
     * @param int $option
     */
    public function getValueCollectionOfOption($option)
    {
        return $this->_value->getValuesCollection($this->_option->load($option));
    }

    /**
     * GetCategory
     */
    public function getCategory()
    {
        return $this->_category->create();
    }

    /**
     * Get Googleoptimizer Fields Values.
     *
     * @param ModelCode|null $experimentCodeModel
     *
     * @return array
     */
    public function getGoogleoptimizerFieldsValues()
    {
        $entityId = $this->getRequest()->getParam('id');
        $storeId = $this->_helperData->getCurrentStoreId();
        $experimentCodeModel = $this->_modelCode->loadByEntityIdAndType($entityId, 'product', $storeId);
        $result = [];
        $result['experiment_script'] =
        $experimentCodeModel ? $experimentCodeModel->getExperimentScript() : '';
        $result['code_id'] =
        $experimentCodeModel ? $experimentCodeModel->getCodeId() : '';

        return $result;
    }

    /**
     * GetProductBySku
     *
     * @param int $sku
     */
    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    /**
     * GetMpHelper
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }

    /**
     * GetMpEventManagerHelper
     */
    public function getMpEventManagerHelper()
    {
        return $this->_mpEventManagerHelper;
    }

    /**
     * GetCatalogCategoryHelper
     */
    public function getCatalogCategoryHelper()
    {
        return $this->_catalogCategoryHelper;
    }

    /**
     * GetJsonHelper
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }

    /**
     * GetWysiwygUrl function
     *
     * @return string
     */
    public function getWysiwygUrl()
    {
        $currentTreePath = $this->wysiwygImages->idEncode(
            \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
        );
        $url =  $this->getUrl(
            'marketplace/wysiwyg_images/index',
            [
                'current_tree_path' => $currentTreePath
            ]
        );
        return $url;
    }
}
