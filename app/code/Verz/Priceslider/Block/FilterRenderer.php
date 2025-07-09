<?php
/**
*
* @category  Verz
* @package   Verz_Priceslider
* @author    Verz
*/

namespace Verz\Priceslider\Block;

use Magento\Catalog\Model\Layer\Filter\Price;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer {

    protected $registry;
    protected $request;
    protected $productCollection;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Backend\Block\Template\Context $context,        
        array $data = []
    ) {
       $this->registry = $registry; 
       $this->request = $request;
       $this->scopeConfig = $scopeConfig;
       $this->productCollection = $productCollection;       
       parent::__construct($context, $data);
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter) {
        $this->assign('filterItems', $filter->getItems());
        $this->assign('filter', $filter);
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        return $html;
    }

    /**
     * Get price range
     * @return string
     */
    public function getPriceRange($filter) {
    	$Filterprice = array('min' => 0 , 'max'=>0);
        
       $category = $this->registry->registry('current_category');
        if($category)
        {
            $ProductFactory = $this->productCollection->addAttributeToSelect('price')->setOrder('price', 'DESC')->addCategoryFilter($category);
            $maxPrice = $ProductFactory->getMaxPrice();
            ///$minPrice = $ProductFactory->getMinPrice();     
            $Filterprice['min'] = (int)$ProductFactory->getMinPrice();
            $Filterprice['max'] = ceil($maxPrice)+10;
        }
    	elseif($filter instanceof \Magento\CatalogSearch\Model\Layer\Filter\Price){
			$priceArr = $filter->getResource()->loadPrices(10000000000);
     		$Filterprice['min'] = (int)reset($priceArr);
     		$Filterprice['max'] = end($priceArr)+10;
    	}
    	return $Filterprice;
    }

    /**
     * Get price range filter
     * @return string
     */
    public function getPriceRangeFiltered($filter) {
        if($this->request->getParam('price')) {
            $combinedPrice = $this->request->getParam('price');
            $pricearray = explode('-', $combinedPrice);
            $min = $pricearray[0];
            $max = $pricearray[1];
            $filterprice = array('min' => $min, 'max' => $max);
        } else {
            $filterprice = $this->getPriceRange($filter);
        }
        return $filterprice;
    }

    /**
     * Get filter Url
     * @return string
     */
    public function getFilterUrl($filter) {
        $query = ['price' => ''];
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
 
     /**
     * Get store currency symbol
     * @return string
     */
    public function getCurrencySymbol()
    {
        return "test";
    }
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

     /**
     * Set template based on module enable/disable
     * @return string
     */
    public function setTemplate($template)
    {
       $isEnabled = $this->isModuleEnabled();
       if ($isEnabled) {
            $template = 'Verz_Priceslider::filter.phtml';
        }  else {
            $template = 'Magento_LayeredNavigation::layer/filter.phtml';
       }
       return parent::setTemplate($template);
    }

     /**
     * Get module is enabled or disabled
     * @return boolean
     */
    public function isModuleEnabled() 
    {
       $isEnabled = $this->_scopeConfig->getValue('verz_priceslider_section/general/slider', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $isEnabled;
        //return true;
    }
}