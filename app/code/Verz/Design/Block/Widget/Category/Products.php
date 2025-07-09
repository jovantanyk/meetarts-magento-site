<?php
/**
 * @package   Verz_Design
 * @copyright Copyright (c) 2022 VerzDesign (https://www.verzdesign.com/)
 * @contacts  enquiry@verzdesign.com
 */

namespace Verz\Design\Block\Widget\Category;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Products
 * @package Verz\Design\Block\Widget\Category
 */

class Products extends Template implements BlockInterface
{    
     /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * GridProduct constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Verz\Design\Helper\Data $verz_hepler
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,      
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Verz\Design\Helper\Data $verz_hepler,
		array $data = []
    )
    {    
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->verz_hepler = $verz_hepler;
        parent::__construct($context, $data);
    }   
    /**
     * Retrieve Product Collection
     * @return array
     */
    public function getProductCollection()
    {
        if (!$this->getData('id_path')) {
            throw new \RuntimeException('Parameter id_path is not set.');
        }
        $limit = $this->getData('products_count');
        if($limit<=0)
        {
            $limit = 10;
        }
        $rewriteData = $this->parseIdPath($this->getData('id_path'));
        if(isset($rewriteData[1]))
        {
            $cat_id = $rewriteData[1];
        }
        $category = $this->verz_hepler->getCategory($cat_id);
        $_productCollection = $category->getProductCollection()->addAttributeToSelect('*')->addAttributeToSort('position','asc')->setPageSize($limit);
        return $_productCollection;
    }
    /**
     * Retrieve Title
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }
    /**
     * Retrieve ID Path
     * @param int $idPath
     * @return string
     */
    protected function parseIdPath($idPath)
    {
        $rewriteData = explode('/', $idPath);

        if (!isset($rewriteData[0]) || !isset($rewriteData[1])) {
            throw new \RuntimeException('Wrong id_path structure.');
        }
        return $rewriteData;
    }

}